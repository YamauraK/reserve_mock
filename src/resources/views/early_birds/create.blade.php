@extends('layouts.app')
@section('content')
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-xl font-bold mb-4">早期予約割引の新規作成</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
                <div class="font-semibold mb-1">入力内容を確認してください</div>
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('early-birds.store') }}" class="space-y-4">
            @include('early_birds.form', ['model'=>$model])
        </form>
    </div>
@endsection
