@extends('layouts.app')

@section('content')
<header class="app-header d-flex align-items-center">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm text-white d-lg-none" type="button" id="sidebar-toggle" aria-label="Menu">☰</button>
            <span class="app-logo">SI</span>
            <span class="app-header-title">Store Inventory</span>
        </div>
        <div class="dropdown">
            <button
                class="profile-trigger"
                type="button"
                id="profile-menu-button"
                data-bs-toggle="dropdown"
                data-bs-offset="0,10"
                aria-expanded="false"
                aria-label="Account menu"
            >{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</button>
            <div class="dropdown-menu dropdown-menu-end profile-menu" aria-labelledby="profile-menu-button">
                <div class="text-center px-2 pb-1">
                    <div class="profile-menu-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="profile-menu-name">{{ auth()->user()->name }}</div>
                    <div class="profile-id-pill">{{ auth()->user()->email }}</div>
                </div>
                <div class="profile-actions">
                    <form method="POST" action="/logout" class="m-0">
                        @csrf
                        <button type="submit" class="profile-action profile-action-logout">
                            <span class="profile-action-icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                            </span>
                            Logout
                        </button>
                    </form>
                    <a class="profile-action profile-action-password" href="/password">
                        <span class="profile-action-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="app-shell d-flex">
    <aside class="app-sidebar" id="app-sidebar">
        <div class="side-label">MENU</div>
        <a class="side-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}" href="/dashboard">Dashboard</a>
        <a class="side-link {{ ($active ?? '') === 'customers' ? 'active' : '' }}" href="/customers">Customer</a>
        <a class="side-link {{ ($active ?? '') === 'products' ? 'active' : '' }}" href="/products">Products</a>
        <a class="side-link {{ ($active ?? '') === 'orders' ? 'active' : '' }}" href="/orders/create">New Order</a>
        <a class="side-link {{ ($active ?? '') === 'report' ? 'active' : '' }}" href="/reports">Report</a>
    </aside>
    <main class="app-main">
        @if (session('status'))
            <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
        @endif
        @yield('page')
    </main>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var toggle = document.getElementById('sidebar-toggle');
    var sidebar = document.getElementById('app-sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }
})();
</script>
@yield('page-scripts')
@endsection
