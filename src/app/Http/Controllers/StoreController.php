<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $rows = Store::orderBy('name')->paginate(20);
        return view('masters.stores.index', compact('rows'));
    }

    public function create()
    {
        return view('masters.stores.form', ['row' => new Store()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:stores,code'],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'open_time' => ['nullable'],
            'close_time' => ['nullable'],
            'is_active' => ['required', 'boolean'],
        ]);
        Store::create($data);
        return redirect()->route('stores.index')->with('status', '店舗を作成しました');
    }

    public function edit(Store $store)
    {
        return view('masters.stores.form', ['row' => $store]);
    }

    public function update(Request $request, Store $store)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:stores,code,' . $store->id],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'open_time' => ['nullable'],
            'close_time' => ['nullable'],
            'is_active' => ['required', 'boolean'],
        ]);
        $store->update($data);
        return redirect()->route('stores.index')->with('status', '店舗を更新しました');
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return back()->with('status', '店舗を削除しました');
    }
}
