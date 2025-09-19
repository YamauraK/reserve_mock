<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ログイン | 予約管理</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet">
    @php
        $path = public_path('css/admin.css');
        $ver  = file_exists($path) ? filemtime($path) : time(); // 存在しない場合の保険
    @endphp
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ $ver }}">
</head>
<body class="admin-auth">
<div class="auth-card">
    <h1 class="auth-title">予約管理 ログイン</h1>
    <form method="post" action="{{ route('login.post') }}" class="space-y-4">
        @csrf
        <div>
            <label class="form-label">メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-input" required>
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="form-label">パスワード</label>
            <input type="password" name="password" class="form-input" required>
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1"> ログイン状態を保持
        </label>
        <button class="btn-primary w-full">ログイン</button>
    </form>
</div>
</body>
</html>
