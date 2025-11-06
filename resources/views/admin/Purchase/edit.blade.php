@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-pencil-square me-2 text-white"></i>Edit Purchase</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.Purchase.form', ['purchase' => $purchase])
            </form>
        </div>
    </div>
</div>
<style>
    button.btn.btn-outline-danger.btn-sm.w-100.p-0.remove-payment {
    width: 50px !important;
}
</style>
@endsection
