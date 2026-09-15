@extends('layouts.shell', ['active' => 'report'])

@section('title', 'Report | Store Inventory')

@section('page')
<div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="page-title mb-1">Report</h1>
        <p class="page-sub mb-0">Orders between selected dates.</p>
    </div>
</div>

<div class="panel p-3 p-md-4 mb-3">
    <form method="GET" action="/reports" class="row g-3 align-items-end">
        <div class="col-sm-4 col-md-3">
            <label for="from" class="form-label">From date</label>
            <input id="from" type="date" name="from" class="form-control @error('from') is-invalid @enderror" value="{{ old('from', $from) }}" required>
            @error('from')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-4 col-md-3">
            <label for="to" class="form-label">To date</label>
            <input id="to" type="date" name="to" class="form-control @error('to') is-invalid @enderror" value="{{ old('to', $to) }}" required>
            @error('to')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-4 col-md-3">
            <button type="submit" class="btn btn-brand">Show Report</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="panel p-3 stat-card-blue">
            <div class="stat-label">Orders</div>
            <div class="stat-value">{{ $summary['order_count'] }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="panel p-3 stat-card-violet">
            <div class="stat-label">Items sold</div>
            <div class="stat-value">{{ $summary['item_count'] }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="panel p-3 stat-card-amber">
            <div class="stat-label">Tax</div>
            <div class="stat-value">₹{{ number_format((float) $summary['tax_amount'], 2) }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="panel p-3 stat-card-green">
            <div class="stat-label">Total sales</div>
            <div class="stat-value">₹{{ number_format((float) $summary['total_amount'], 2) }}</div>
        </div>
    </div>
</div>

<div class="panel p-3 p-md-4">
    <h2 class="h6 mb-3">Orders from {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</h2>
    @if ($orders->isEmpty())
        <p class="page-sub mb-0">No orders in this date range.</p>
    @else
        <div class="table-responsive">
            <table class="table table-clean align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th class="text-end">Items</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Tax</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td class="text-nowrap">{{ \App\Support\AppDate::display($order->created_at) }}</td>
                            <td>
                                {{ $order->customer ? $order->customer->name : '—' }}
                                @if ($order->customer)
                                    <div class="page-sub">{{ $order->customer->email }}</div>
                                @endif
                            </td>
                            <td class="text-end">{{ $order->items->sum('quantity') }}</td>
                            <td class="text-end">₹{{ number_format((float) $order->subtotal, 2) }}</td>
                            <td class="text-end">₹{{ number_format((float) $order->tax_amount, 2) }}</td>
                            <td class="text-end fw-semibold">₹{{ number_format((float) $order->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
