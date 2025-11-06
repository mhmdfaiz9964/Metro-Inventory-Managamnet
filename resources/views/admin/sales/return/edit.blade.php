@extends('layouts.app')

@section('content')
<div class="container-fluid my-5 px-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 text-white"><i class="bi bi-pencil-square me-2 text-white"></i>Edit Sale Return</h4>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    
                    <form action="{{ route('admin.sales-returns.update', $saleReturn->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.sales.return.form')


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
