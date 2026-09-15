@extends('layouts.shell', ['active' => 'dashboard'])

@section('title', 'Dashboard | Store Inventory')

@section('page')
<div class="d-flex align-items-end justify-content-between mb-4">
    <div>
        <h1 class="page-title mb-1">Dashboard</h1>
        <p class="page-sub mb-0">A quick look at stock, orders, and customers.</p>
    </div>
    <a href="/orders/create" class="btn btn-brand">New Order</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="panel p-3 stat-card-blue">
            <div class="stat-label">Total Products</div>
            <div class="stat-value">{{ $stats['total_products'] }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="panel p-3 stat-card-amber">
            <div class="stat-label">Low Stock Products</div>
            <div class="stat-value" style="color: var(--warn);">{{ $stats['low_stock_products'] }}</div>
            <div class="page-sub">Below {{ $stats['low_stock_threshold'] }} units</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="panel p-3 stat-card-violet">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ $stats['total_orders'] }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="panel p-3 stat-card-green">
            <div class="stat-label">Total Customers</div>
            <div class="stat-value">{{ $stats['total_customers'] }}</div>
        </div>
    </div>
</div>

<div class="panel p-3 p-md-4">
    <h2 class="h6 mb-3">Low stock items</h2>
    @if ($lowStockProducts->isEmpty())
        <p class="page-sub mb-0">All products are above the stock threshold.</p>
    @else
        <div class="table-responsive">
            <table class="table table-clean align-middle mb-0">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product</th>
                        <th class="text-end">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lowStockProducts as $product)
                        <tr>
                            <td class="text-muted">{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td class="text-end"><span class="stock-badge">{{ $product->stock_quantity }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
