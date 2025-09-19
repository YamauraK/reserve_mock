@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold">早期予約割引の編集</h1>
            <form method="post" action="{{ route('early-birds.destroy',$model) }}"
                  onsubmit="return confirm('削除しますか？')">
                @csrf @method('delete')
                <button class="btn-danger">削除</button>
            </form>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
                <div class="font-semibold mb-1">入力内容を確認してください</div>
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('early-birds.update',$model) }}" class="space-y-4">
            @method('put')
            @include('early_birds.form', ['model'=>$model])
        </form>
    </div>
@endsection
