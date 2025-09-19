@extends('layouts.app')
@section('content')
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">商品一覧</h2>
        <a href="{{ route('products.create') }}" class="px-4 py-2 bg-green-600 text-white rounded">新規商品</a>
    </div>
    <table class="w-full bg-white shadow rounded">
        <thead class="bg-gray-100">
        <tr>
            <th class="p-2">ID</th><th class="p-2">SKU</th><th class="p-2">商品名</th><th class="p-2">価格</th><th class="p-2">操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $p)
            <tr class="border-t">
                <td class="p-2">{{ $p->id }}</td>
                <td class="p-2">{{ $p->sku }}</td>
                <td class="p-2">{{ $p->name }}</td>
                <td class="p-2">¥{{ number_format($p->price) }}</td>
                <td class="p-2">
                    <a href="{{ route('products.edit',$p) }}" class="text-blue-600">編集</a> |
                    <form method="post" action="{{ route('products.destroy',$p) }}" class="inline">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('削除しますか？')" class="text-red-600">削除</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
