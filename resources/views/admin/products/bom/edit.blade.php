@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-dark">
                <h4 class="mb-0 text-white"><i class="bi bi-pencil-square me-2 text-white"></i>Edit BOM Component</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.bom.update', $productId) }}" method="POST">
                    @method('PUT')
                    @include('admin.products.bom.form', ['boms' => $boms, 'productId' => $productId])

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-outline-secondary">Back</a>
                        <button type="submit" class="btn btn-warning">Update BOM</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
