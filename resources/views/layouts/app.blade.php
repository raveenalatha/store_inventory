<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Store Inventory')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --header: #0c5b60;
            --sidebar: #0b3038;
            --sidebar-hover: rgba(255,255,255,.08);
            --sidebar-active: rgba(255,255,255,.12);
            --accent: #2dd4bf;
            --bg: #eef2f4;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --brand: #0c5b60;
            --warn: #d97706;
        }
        body { background: var(--bg); color: var(--ink); }
        .login-wrap { min-height: 100vh; }
        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
        }
        .page-title { font-size: 1.2rem; font-weight: 700; }
        .page-sub { color: var(--muted); font-size: .9rem; }
        .stat-label { color: var(--muted); font-size: .8rem; }
        .stat-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
        .stock-badge {
            display: inline-block;
            min-width: 2rem;
            padding: .15rem .5rem;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-weight: 600;
            font-size: .8rem;
            text-align: center;
        }
        .table-clean th {
            color: var(--muted);
            font-weight: 600;
            font-size: .8rem;
            border-bottom-color: var(--line);
        }
        .btn-brand { background: var(--header); border-color: var(--header); color: #fff; }
        .btn-brand:hover { background: #09484c; color: #fff; }
        .btn-quiet { background: #fff; border: 1px solid var(--line); color: var(--ink); }

        .app-header {
            height: 56px;
            background: var(--header);
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .app-logo {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #fff;
            color: var(--header);
            font-weight: 800;
            font-size: .85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .app-header-title { font-weight: 700; letter-spacing: .02em; }
        .profile-trigger {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,.5);
            background: linear-gradient(180deg, #6b7cff 0%, #3d54f0 100%);
            color: #fff;
            font-weight: 700;
            font-size: .95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            line-height: 1;
        }
        .profile-trigger:hover,
        .profile-trigger:focus,
        .profile-trigger.show {
            color: #fff;
            box-shadow: 0 0 0 3px rgba(255,255,255,.18);
        }
        .profile-menu {
            width: 260px;
            padding: 1.15rem .85rem .65rem;
            border: 0;
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .16);
        }
        .profile-menu-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(180deg, #6b7cff 0%, #3d54f0 100%);
            color: #fff;
            font-weight: 700;
            font-size: 1.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto .65rem;
        }
        .profile-menu-name {
            font-weight: 700;
            font-size: 1.05rem;
            color: #111827;
        }
        .profile-id-pill {
            display: inline-block;
            background: #e8eef6;
            color: #64748b;
            border-radius: 999px;
            padding: .15rem .7rem;
            font-size: .75rem;
            margin-top: .35rem;
        }
        .profile-actions {
            margin-top: .9rem;
            border-top: 1px solid #eef2f6;
            padding-top: .35rem;
        }
        .profile-action {
            display: flex;
            align-items: center;
            gap: .7rem;
            width: 100%;
            padding: .5rem .4rem;
            border: 0;
            background: transparent;
            border-radius: 12px;
            text-decoration: none;
            color: #111827;
            font-weight: 600;
            font-size: .92rem;
            text-align: left;
        }
        .profile-action:hover { background: #f4f7fb; color: #111827; }
        .profile-action-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .profile-action-logout { color: #e11d48; }
        .profile-action-logout:hover { background: #fff1f4; color: #e11d48; }
        .profile-action-logout .profile-action-icon { background: #fde8ef; color: #e11d48; }
        .profile-action-password .profile-action-icon { background: #e8eef6; color: #334155; }
        .app-shell { min-height: calc(100vh - 56px); }
        .app-sidebar {
            width: 230px;
            background: var(--sidebar);
            min-height: calc(100vh - 56px);
            position: sticky;
            top: 56px;
            flex-shrink: 0;
        }
        .side-label {
            color: rgba(255,255,255,.45);
            font-size: .7rem;
            letter-spacing: .08em;
            padding: 1rem 1rem .35rem;
        }
        .side-link {
            color: rgba(255,255,255,.88);
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .7rem 1rem;
            text-decoration: none;
            border-left: 3px solid transparent;
        }
        .side-link:hover { background: var(--sidebar-hover); color: #fff; }
        .side-link.active {
            background: var(--sidebar-active);
            color: #fff;
            border-left-color: var(--accent);
        }
        .app-main { flex: 1; padding: 1.25rem; min-width: 0; }
        .stat-card-blue { background: #eaf4ff; }
        .stat-card-amber { background: #fff4e5; }
        .stat-card-violet { background: #f3e8ff; }
        .stat-card-green { background: #e8f8ef; }
        #bill-receipt { display: none; }
        .qty-input { max-width: 88px; }
        @media (max-width: 991px) {
            .app-sidebar {
                position: fixed;
                z-index: 25;
                transform: translateX(-100%);
                transition: transform .2s ease;
            }
            .app-sidebar.open { transform: translateX(0); }
        }
    </style>
    @yield('styles')
</head>
<body>
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
