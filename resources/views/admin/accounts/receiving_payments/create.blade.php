@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-plus-circle me-2 text-white"></i>Create Receiving Payment</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.receiving-payments.store') }}" method="POST">
                @csrf
                @include('admin.Accounts.receiving_payments.form', ['receivingPayment' => null, 'buttonText' => 'Create Payment'])

            </form>
        </div>
    </div>
</div>
@endsection
