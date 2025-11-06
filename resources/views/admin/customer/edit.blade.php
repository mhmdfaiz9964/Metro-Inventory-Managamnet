@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-pencil-square me-2 text-white"></i>Edit Customer</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.customer.form')

                <div class="mt-4 d-flex justify-content-between">
                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-warning">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
