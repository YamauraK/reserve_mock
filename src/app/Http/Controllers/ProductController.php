<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $rows = Product::with('stores:id,name')->orderBy('name')->paginate(20);
        return view('masters.products.index', compact('rows'));
    }

    public function create()
    {
        return view('masters.products.form', [
            'row' => new Product(['is_all_store' => true]),
            'stores' => Store::orderBy('name')->get(),
            'selectedStoreIds' => [],
        ]);
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
            'is_all_store' => ['required', 'boolean'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', Rule::exists('stores', 'id')],
        ]);
        $storeIds = collect($data['store_ids'] ?? [])->filter()->unique()->all();
        unset($data['store_ids']);

        if (!$data['is_all_store'] && empty($storeIds)) {
            return back()->withErrors(['store_ids' => '対象店舗を選択してください。'])->withInput();
        }

        $product = Product::create($data);
        $product->stores()->sync($data['is_all_store'] ? [] : $storeIds);

        return redirect()->route('products.index')->with('status', '商品を作成しました');
    }

    public function edit(Product $product)
    {
        return view('masters.products.form', [
            'row' => $product->load('stores:id'),
            'stores' => Store::orderBy('name')->get(),
            'selectedStoreIds' => $product->stores->pluck('id')->all(),
        ]);
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
            'is_all_store' => ['required', 'boolean'],
            'store_ids' => ['nullable', 'array'],
            'store_ids.*' => ['integer', Rule::exists('stores', 'id')],
        ]);
        $storeIds = collect($data['store_ids'] ?? [])->filter()->unique()->all();
        unset($data['store_ids']);

        if (!$data['is_all_store'] && empty($storeIds)) {
            return back()->withErrors(['store_ids' => '対象店舗を選択してください。'])->withInput();
        }

        $product->update($data);
        $product->stores()->sync($data['is_all_store'] ? [] : $storeIds);

        return redirect()->route('products.index')->with('status', '商品を更新しました');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('status', '商品を削除しました');
    }
}
