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
            <div class="flex gap-2">
                <button class="btn-primary">{{ isset($user)?'更新':'作成' }}</button>
                <a href="{{ route('users.index') }}" class="btn-outline">戻る</a>
            </div>
        </form>
    </div>
@endsection
