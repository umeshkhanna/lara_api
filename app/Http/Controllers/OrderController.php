<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use App\Models\Link;
use App\Models\Product;
use App\Models\OrderItem;
use DB;
use Illuminate\Support\Str;
use App\Events\OrderCompletedEvent;

class OrderController extends Controller
{
    public function index(){
    	return OrderResource::collection(Order::all());
    }

    public function store(Request $request){
    	if(!$link = Link::where('code', $request->input('code'))->first()){
    		abort(400, 'Invalid Code');
    	}

	    try{
	    	DB::beginTransaction();

	    	$order = new Order();
	    	$order->user_id = $link->user->id;
	    	$order->code = $link->code;
	    	$order->ambassador_email = $link->user->email;
	    	$order->first_name = $request->input('first_name');
	    	$order->last_name = $request->input('last_name');
	    	$order->email = $request->input('email');
	    	$order->address = $request->input('address');
	    	$order->country = $request->input('country');
	    	$order->zip = $request->input('zip');
	    	$order->city = $request->input('city');
	    	$order->save();

	    	foreach($request->input('products') as $item){
	    		$product = Product::find($item['product_id']);

	    		$orderItem = new OrderItem();
	    		$orderItem->order_id = $order->id;
	    		$orderItem->product_title = $product->title;
	    		$orderItem->price = $product->price;
	    		$orderItem->quantity = $item['quantity'];
	    		$orderItem->ambassador_revenue = 0.1*$product->price*$item['quantity'];
	    		$orderItem->admin_revenue = 0.9*$product->price*$item['quantity'];
	    		$orderItem->save();
	    	}

	    	DB::commit();
	    }catch(\Throwable $e){
	    	DB::rollback();
	    	return response([
	    		"error" => $e->getMessage()
	    	], 400);
	    }
    	return $order->load('orderItems');
    }

    public function confirm(Request $request){
    	if(!$order = Order::where('id', $request->input('order_id'))->first()){
    		return response([
    			"error" => "order not found"
    		], 404);
    	}

    	$order->complete = 1;
    	$order->transaction_id = Str::random(12);
    	$order->save();

    	event(new OrderCompletedEvent($order));

    	return response(["message" => "success"], 201);
    }














}
