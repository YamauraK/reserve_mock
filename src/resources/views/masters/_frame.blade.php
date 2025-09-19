@extends('layouts.app')
@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">{{ $title }}</h2>
        <a href="{{ $createUrl }}">新規作成</a>
    </div>
    {!! $slot !!}
@endsection
