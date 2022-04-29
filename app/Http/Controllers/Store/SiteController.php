<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SiteController extends Controller
{

    protected function findProductById($id)
    {
        return Product::findOrFail($id);
    }

    public function index()
    {
        $featuredProducts = Product::with('images')
            ->orderBy('id', 'DESC')
            ->where('is_Featured', config('app.is_featured'))
            ->limit(config('app.limit.featured'))
            ->get();

        $newProducts = Product::with('images')
            ->orderBy('id', 'DESC')
            ->limit(config('app.limit.new'))
            ->get();

        return view('store.index')->with(compact('featuredProducts', 'newProducts'));
    }
}