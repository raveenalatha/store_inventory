@extends('layouts.app')

@section('title', 'Login | Store Inventory')

@section('content')
<div class="container login-wrap d-flex align-items-center py-5">
    <div class="row justify-content-center w-100">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <h1 class="h4 mb-1">Store Inventory</h1>
                <p class="page-sub mb-0">Sign in to continue</p>
            </div>

            <div class="panel p-4">
                @if ($errors->any())
                    <div class="alert alert-danger py-2" role="alert">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="/login" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            required
                            autofocus
                            autocomplete="email"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required
                            autocomplete="current-password"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-brand w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
