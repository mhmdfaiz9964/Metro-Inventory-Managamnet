@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-plus-circle me-2 text-white"></i>Create Sale</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sales.store') }}" method="POST">
                @csrf
                @include('admin.sales.form', ['buttonText' => 'Create Sale'])

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
