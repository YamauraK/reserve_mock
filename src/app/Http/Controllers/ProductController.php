<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $rows = Product::orderBy('name')->paginate(20);
        return view('masters.products.index', compact('rows'));
    }

    public function create()
    {
        return view('masters.products.form', ['row' => new Product()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);
        Product::create($data);
        return redirect()->route('products.index')->with('status', '商品を作成しました');
    }

    public function edit(Product $product)
    {
        return view('masters.products.form', ['row' => $product]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);
        $product->update($data);
        return redirect()->route('products.index')->with('status', '商品を更新しました');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('status', '商品を削除しました');
    }
}
