@extends('layouts.shell', ['active' => 'products'])

@section('title', 'Products | Store Inventory')

@section('page')
<div class="mb-4">
    <h1 class="page-title mb-1">Products</h1>
    <p class="page-sub mb-0">Add a product with name, SKU, description, price, tax rate, and stock quantity. Duplicate SKUs are not accepted.</p>
</div>

<div class="panel p-3 p-md-4 mb-4">
    <h2 class="h6 mb-3">Add product</h2>
    <form method="POST" action="/products" novalidate>
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control @error('name') is-invalid @enderror"
                    required
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="sku" class="form-label">SKU</label>
                <input
                    id="sku"
                    type="text"
                    name="sku"
                    value="{{ old('sku') }}"
                    class="form-control @error('sku') is-invalid @enderror"
                    required
                >
                @error('sku')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="2"
                    class="form-control @error('description') is-invalid @enderror"
                >{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="price" class="form-label">Price</label>
                <input
                    id="price"
                    type="number"
                    name="price"
                    step="0.01"
                    min="0"
                    value="{{ old('price') }}"
                    class="form-control @error('price') is-invalid @enderror"
                    required
                >
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="tax_rate" class="form-label">Tax rate</label>
                <input
                    id="tax_rate"
                    type="number"
                    name="tax_rate"
                    step="0.01"
                    min="0"
                    max="100"
                    value="{{ old('tax_rate') }}"
                    class="form-control @error('tax_rate') is-invalid @enderror"
                    required
                >
                @error('tax_rate')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <label for="stock_quantity" class="form-label">Stock quantity</label>
                <input
                    id="stock_quantity"
                    type="number"
                    name="stock_quantity"
                    step="1"
                    min="0"
                    value="{{ old('stock_quantity') }}"
                    class="form-control @error('stock_quantity') is-invalid @enderror"
                    required
                >
                @error('stock_quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-brand">Save Product</button>
            </div>
        </div>
    </form>
</div>

<div class="panel p-3 p-md-4">
    <h2 class="h6 mb-3">Product list</h2>
    @if ($products->isEmpty())
        <p class="page-sub mb-0">No products yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-clean align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Description</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Tax rate</th>
                        <th class="text-end">Stock quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->description ?: '—' }}</td>
                            <td class="text-end">{{ number_format((float) $product->price, 2) }}</td>
                            <td class="text-end">{{ number_format((float) $product->tax_rate, 1) }}</td>
                            <td class="text-end">{{ $product->stock_quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
