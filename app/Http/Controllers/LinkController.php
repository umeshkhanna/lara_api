<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\LinkProduct;
use Illuminate\Support\Str;
use App\Http\Resources\LinkResource;

class LinkController extends Controller
{
    public function index($userid){
    	$link = Link::with('orders')->where('user_id', $userid)->get();
    	return LinkResource::collection($link);
    }

    public function store(Request $request){
    	$link = Link::create([
    		'user_id' => $request->user()->id,
    		'code' => Str::random(6)
    	]);

    	foreach($request->input('products') as $product_id){
    		LinkProduct::create([
    			'link_id' => $link->id,
    			'product_id' => $product_id
    		]);
    	}

    	return $link;
    }

    public function show($code){
    	$links = Link::with('user', 'products')->where('code', $code)->first();
    	return $links;
    }
}
