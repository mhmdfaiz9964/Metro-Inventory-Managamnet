@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-pencil-square me-2 text-white"></i>Edit Receiving Payment</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.receiving-payments.update', $receivingPayment->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('admin.Accounts.receiving_payments.form', ['buttonText' => 'Update Payment'])

            </form>
        </div>
    </div>
</div>
@endsection
