@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-plus-circle me-2 text-white"></i>Create Stock Adjustment</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.stock-adjustments.store') }}" method="POST">
                @csrf
                @include('admin.stocks.adjustments.form', ['buttonText' => 'Create Adjustment'])
            </form>
        </div>
    </div>
</div>
@endsection
