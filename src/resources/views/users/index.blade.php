@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold">ユーザー管理</h1>
            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">新規作成</a>
        </div>

        @if(session('success')) <div class="mb-3 text-green-700">{{ session('success') }}</div> @endif
        @if(session('error'))   <div class="mb-3 text-red-700">{{ session('error') }}</div> @endif

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th><th>名前</th><th>メール</th><th>権限</th><th>作成日</th><th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $roleLabels[$u->role] ?? $u->role }}</td>
                        <td>{{ $u->created_at?->format('Y/m/d') }}</td>
                        <td class="text-right">
                            <a href="{{ route('users.edit',$u) }}" class="text-blue-600 hover:underline mr-3">編集</a>
                            <form … class="inline" onsubmit="return confirm('削除しますか？');">
                                @csrf @method('delete')
                                <button class="text-red-600 hover:underline">削除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
