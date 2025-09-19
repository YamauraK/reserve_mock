<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','予約管理システム')</title>

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.4/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>
<div class="admin-shell">
    {{-- Sidebar --}}
    <aside id="sidebar" class="admin-sidebar">
        <div class="admin-brand">
            <span class="dot"></span> <span>Admin</span>
        </div>
        <nav class="admin-nav">
            <div class="admin-section">Main</div>
            <a class="admin-link {{ request()->routeIs('reservations.index','reservations.show') ? 'active' : '' }}"
               href="{{ route('reservations.index') }}">予約一覧</a>
            <a class="admin-link {{ request()->routeIs('reservations.create') ? 'active' : '' }}"
               href="{{ route('reservations.create') }}">新規予約</a>
            <a class="admin-link" href="{{ route('reservations.export') }}">CSVエクスポート</a>

            <div class="admin-section">Masters</div>
            <a class="admin-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}"
               href="{{ route('campaigns.index') }}">企画管理</a>
            <a class="admin-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
               href="{{ route('products.index') }}">商品管理</a>
            <a class="admin-link {{ request()->routeIs('stores.*') ? 'active' : '' }}"
               href="{{ route('stores.index') }}">店舗管理</a>
            <a class="admin-link {{ request()->routeIs('early-birds.*') ? 'active' : '' }}"
               href="{{ route('early-birds.index') }}">早割設定</a>

            <div class="admin-section">Admin</div>
            <a class="admin-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
               href="{{ route('users.index') }}">ユーザー管理</a>
            <a class="admin-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
               href="{{ route('roles.index') }}">権限管理</a>
        </nav>
    </aside>

    {{-- Header --}}
    <header class="admin-header">
        <div class="header-left">
            <button id="sidebarToggle" class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
            <div class="header-title">@yield('title','予約管理システム')</div>
        </div>
        <div class="header-actions">
            <form method="post" action="{{ route('logout') }}">@csrf
                <button class="btn-danger">ログアウト</button>
            </form>
        </div>
    </header>

    {{-- Main --}}
    <main class="admin-main">
        @if(session('status'))
            <div class="mb-3 card"><div class="card-body" style="background:#fff7ed;border-left:4px solid #f59e0b">
                    {{ session('status') }}
                </div></div>
        @endif

        @yield('content')
    </main>
</div>

<div id="sidebarBackdrop" class="sidebar-backdrop"></div>
<script>
    const sidebar  = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const btn      = document.getElementById('sidebarToggle');

    // 画面幅によって挙動を変える（モバイル＝ドロワー／デスクトップ＝折りたたみ）
    const isMobile = () => window.matchMedia('(max-width: 1024px)').matches;

    // デスクトップの状態を保存（リロードしても維持）
    const loadPref = () => {
        const v = localStorage.getItem('sidebarCollapsed');
        if (v === '1') document.body.classList.add('sidebar-collapsed');
    };
    const savePref = () => {
        localStorage.setItem('sidebarCollapsed',
            document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
    };

    // モバイル：ドロワー開閉
    const openDrawer  = () => { sidebar.classList.add('open');  backdrop.classList.add('show'); };
    const closeDrawer = () => { sidebar.classList.remove('open'); backdrop.classList.remove('show'); };

    // ボタン動作
    const toggle = () => {
        if (isMobile()) {
            // モバイル：サイドバーをスライド表示
            sidebar.classList.contains('open') ? closeDrawer() : openDrawer();
        } else {
            // デスクトップ：ページ全体を折りたたみクラスで切替
            document.body.classList.toggle('sidebar-collapsed');
            savePref();
        }
        // ARIA 反映
        btn.setAttribute('aria-expanded',
            !document.body.classList.contains('sidebar-collapsed'));
    };

    // 初期化
    loadPref();
    btn.addEventListener('click', toggle);
    backdrop.addEventListener('click', closeDrawer);
    window.addEventListener('resize', () => {
        // 画面幅を跨いだ時にモバイルの残留状態をクリーンアップ
        if (!isMobile()) closeDrawer();
    });

    // 初期ARIA
    btn.setAttribute('aria-expanded',
        !document.body.classList.contains('sidebar-collapsed'));
</script>

</body>
</html>
