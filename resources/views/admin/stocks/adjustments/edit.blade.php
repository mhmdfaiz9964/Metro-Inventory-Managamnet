@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-pencil-square me-2 text-white"></i>Edit Stock Adjustment</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.stock-adjustments.update', $stockAdjustment->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.stocks.adjustments.form', ['buttonText' => 'Update Adjustment'])
            </form>
        </div>
    </div>
</div>
@endsection
