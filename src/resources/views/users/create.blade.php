@extends('layouts.app')
@section('content')
    <div class="max-w-lg mx-auto p-6">
        <h1 class="text-xl font-bold mb-4">ユーザー新規作成</h1>
        <form method="post" action="{{ route('users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">名前</label>
                <input name="name" value="{{ old('name',$user->name ?? '') }}" class="form-input" required>
                @error('name')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">メールアドレス</label>
                <input type="email" name="email" value="{{ old('email',$user->email ?? '') }}" class="form-input" required>
                @error('email')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">パスワード{{ isset($user)?'（変更時のみ）':'' }}</label>
                <input type="password" name="password" class="form-input" @if(!isset($user)) required @endif>
                @error('password')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">権限</label>
                <select name="role" class="form-input" required>
                    @foreach($roles as $r)
                        <option value="{{ $r['value'] }}" @selected(old('role')==$r['value'])>{{ $r['label'] }}</option>
                    @endforeach
                </select>
                @error('role')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">店舗（店舗権限の場合）</label>
                <select name="store_id" class="form-input">
                    <option value="">未選択</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" @selected(old('store_id')==$store->id)>{{ $store->name }}</option>
                    @endforeach
                </select>
                <div class="text-xs text-gray-500 mt-1">※「店舗」権限のユーザーは所属店舗を選択してください。</div>
                @error('store_id')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
            </div>
            <div class="flex gap-2">
                <button class="btn-primary">{{ isset($user)?'更新':'作成' }}</button>
                <a href="{{ route('users.index') }}" class="btn-outline">戻る</a>
            </div>
        </form>
    </div>
@endsection
