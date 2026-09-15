@extends('layouts.shell', ['active' => 'customers'])

@section('title', 'Customers | Store Inventory')

@section('page')
<div class="mb-4">
    <h1 class="page-title mb-1">Customers</h1>
    <p class="page-sub mb-0">Add a customer with name, email, and password. Duplicate email IDs are not accepted.</p>
</div>

<div class="panel p-3 p-md-4 mb-4" style="max-width: 560px;">
    <h2 class="h6 mb-3">Add customer</h2>
    <form method="POST" action="/customers" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror"
                required
                autocomplete="name"
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email ID</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                required
                autocomplete="email"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                required
                autocomplete="new-password"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="form-control"
                required
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="btn btn-brand">Save Customer</button>
    </form>
</div>

<div class="panel p-3 p-md-4">
    <h2 class="h6 mb-3">Customer list</h2>
    @if ($customers->isEmpty())
        <p class="page-sub mb-0">No customers yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-clean align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email ID</th>
                        <th>Added</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td class="text-nowrap">{{ \App\Support\AppDate::display($customer->created_at) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
