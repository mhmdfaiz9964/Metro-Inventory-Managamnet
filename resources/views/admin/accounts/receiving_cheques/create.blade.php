@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-plus-circle me-2 text-white"></i>Create Receiving Cheque</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.receiving-cheques.store') }}" method="POST">
                @csrf
                @include('admin.Accounts.receiving_cheques.form', ['cheque' => null])

                <div class="mt-4 d-flex justify-content-between">
                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Create Cheque</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection