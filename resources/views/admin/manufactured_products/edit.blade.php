@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                    <h4>Edit Manufactured Product</h4>
                </div>

                    <form action="{{ route('admin.purchase_manufactured_products.update', $manufacturedProduct->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('admin.manufactured_products.form', ['buttonText' => 'Update Product'])
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
