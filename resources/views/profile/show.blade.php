@extends('layouts.app')

@section('content')
    <div class="container my-5">


        {{-- Profile Card --}}
        <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-white">My Profile</h4>
                <a href="{{ route('profile.edit') }}" class="btn btn-light btn-sm text-white">Edit Profile</a>
            </div>
            {{-- Animation Image Above Card --}}
            <div class="d-flex justify-content-center mb-4">
                <div style="width:200px; height:200px;">
                    <img src="https://cdn-icons-gif.flaticon.com/10690/10690747.gif" alt="Profile Animation"
                        class="w-100 h-100 object-fit-cover">
                </div>
            </div>

            <div class="card-body p-4">
                {{-- Success Message --}}
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded shadow-sm bg-light">
                            <i class="bi bi-person-fill me-2 text-primary"></i>
                            <strong>First Name:</strong> {{ $user->first_name }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded shadow-sm bg-light">
                            <i class="bi bi-person-fill me-2 text-primary"></i>
                            <strong>Last Name:</strong> {{ $user->last_name }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded shadow-sm bg-light">
                            <i class="bi bi-person-circle me-2 text-primary"></i>
                            <strong>Username:</strong> {{ $user->name }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded shadow-sm bg-light">
                            <i class="bi bi-envelope-fill me-2 text-primary"></i>
                            <strong>Email:</strong> {{ $user->email }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded shadow-sm bg-light">
                            <i class="bi bi-telephone-fill me-2 text-primary"></i>
                            <strong>Mobile Number:</strong> {{ $user->mobile_number ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded shadow-sm bg-light">
                            <i class="bi bi-calendar-fill me-2 text-primary"></i>
                            <strong>Created At:</strong> {{ $user->created_at->format('d M Y') }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-left">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
