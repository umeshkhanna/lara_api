<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use App\Events\ProductUpdatedEvent;

class ProductController extends Controller
{
    
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        $product = Product::create($request->only('title', 'description', 'image', 'price'));

        event(new ProductUpdatedEvent);

        return response($product, Response::HTTP_CREATED);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->only('title', 'description', 'image', 'price'));

        event(new ProductUpdatedEvent);
        
        return response($product, Response::HTTP_ACCEPTED);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        event(new ProductUpdatedEvent);
        
        return response(NULL, Response::HTTP_NO_CONTENT);
    }

    public function frontEnd(){
        if($products = \Cache::get('products_frontend')){
            return $products;
        }
        $products = Product::all();
        \Cache::set('products_frontend', $products, 30*60); //30 mins
        return $products;
    }

    public function backEnd(Request $request){
        $page = $request->input('page', 1);
        $products = \Cache::remember('products_backend', 30*60, function(){
            return Product::all();
        });
        if($s = $request->input('s')){
            $products = $products->filter(function(Product $product)use($s){
                return Str::contains($product->title, $s) || Str::contains($product->description, $s);
            });
        }

        if($sort = $request->input('sort')){
            $products = $products->sortBy([
                ['price', $sort]
            ]);
        }

        $total = $products->count();
        return [
            "data" => $products->forPage($page, 10)->values(),
            "meta" => [
                'total' => $total,
                'page' => $page,
                'last_page' => ceil($total / 10)
            ]
        ];

    }
}
