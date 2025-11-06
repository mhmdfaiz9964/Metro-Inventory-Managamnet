@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-plus-circle me-2 text-white"></i>Create Purchase</h4>
        </div>
        <div class="card-body">
                    <h4>Create Manufactured Product</h4>
                </div>
                    <form action="{{ route('admin.purchase_manufactured_products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('admin.manufactured_products.form', ['buttonText' => 'Save Product'])
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
