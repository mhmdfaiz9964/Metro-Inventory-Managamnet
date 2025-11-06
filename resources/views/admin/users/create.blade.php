@extends('layouts.app')

@section('content')
    <div class="container-fluid my-5 px-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0 text-white"><i class="bi bi-person-plus-fill me-2 text-white"></i>Create New User</h4>
                    </div>
                    <div class="card-body p-4">
                        {{-- Success Message --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- User Creation Form --}}
                        <form action="{{ route('admin.users.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                {{-- First Name --}}
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label fw-bold">First Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="first_name" id="first_name"
                                        class="form-control @error('first_name') is-invalid @enderror"
                                        value="{{ old('first_name') }}" placeholder="Enter first name">
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Last Name --}}
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label fw-bold">Last Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="last_name" id="last_name"
                                        class="form-control @error('last_name') is-invalid @enderror"
                                        value="{{ old('last_name') }}" placeholder="Enter last name">
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Username --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">Username <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name"
                                        class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                        placeholder="Enter username">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-bold">Email <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" placeholder="Enter email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Mobile Number --}}
                                <div class="col-md-6">
                                    <label for="mobile_number" class="form-label fw-bold">Mobile Number</label>
                                    <input type="text" name="mobile_number" id="mobile_number"
                                        class="form-control @error('mobile_number') is-invalid @enderror"
                                        value="{{ old('mobile_number') }}" placeholder="Enter mobile number">
                                    @error('mobile_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-bold">Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Enter password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label fw-bold">Confirm Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control" placeholder="Confirm password">
                                </div>
                            </div>
                            {{-- Role --}}
                            <div class="col-md-6">
                                <label for="role" class="form-label fw-bold">Role <span
                                        class="text-danger">*</span></label>
                                <select name="role" id="role"
                                    class="form-select @error('role') is-invalid @enderror">
                                    <option value="">-- Select Role --</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}"
                                            {{ old('role') == $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Buttons --}}
                            <div class="mt-4 d-flex justify-content-between flex-wrap gap-2">
                                <a href="javascript:void(0)" onclick="window.history.back()"
                                    class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle me-1"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Create User
                                </button>
                            </div>
                        </form>
                    </div> {{-- card-body --}}
                </div> {{-- card --}}
            </div>
        </div>
    </div>
@endsection
