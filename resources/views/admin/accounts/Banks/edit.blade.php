@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-dark">
            <h4 class="mb-0 text-white"><i class="bi bi-pencil-square me-2 text-white"></i>Edit Bank Account</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.bank-accounts.update', $bankAccount->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.Accounts.Banks.form', ['bankAccount' => $bankAccount])

                <div class="mt-4 d-flex justify-content-between">
                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-warning">Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
