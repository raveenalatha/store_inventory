@extends('layouts.shell')

@section('title', 'Change Password | Store Inventory')

@section('page')
<div class="mb-4">
    <h1 class="page-title mb-1">Change Password</h1>
    <p class="page-sub mb-0">Update the password for {{ auth()->user()->email }}.</p>
</div>

<div class="panel p-3 p-md-4" style="max-width: 480px;">
    <form method="POST" action="/password" novalidate>
        @csrf

        <div class="mb-3">
            <label for="current_password" class="form-label">Current password</label>
            <input
                id="current_password"
                type="password"
                name="current_password"
                class="form-control @error('current_password') is-invalid @enderror"
                required
                autocomplete="current-password"
            >
            @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
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
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="form-control"
                required
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="btn btn-brand">Update Password</button>
    </form>
</div>
@endsection
