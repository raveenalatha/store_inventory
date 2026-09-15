<nav class="app-nav">
    <div class="container py-3 d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <a class="app-brand text-decoration-none" href="/dashboard">Store Inventory</a>
            <div class="d-none d-sm-flex align-items-center gap-1">
                <a class="app-link {{ ($active ?? '') === 'dashboard' ? 'active' : '' }}" href="/dashboard">Dashboard</a>
                <a class="app-link {{ ($active ?? '') === 'orders' ? 'active' : '' }}" href="/orders/create">New Order</a>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a class="btn btn-sm btn-brand d-sm-none" href="/orders/create">New Order</a>
            <span class="text-muted small">{{ auth()->user()->name }}</span>
            <form method="POST" action="/logout" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-quiet">Logout</button>
            </form>
        </div>
    </div>
</nav>
