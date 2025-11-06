@extends('layouts.app')

@section('title','Create Purchase Return')

@section('content')
<div class="container my-5">
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white">Create Purchase Return</h4>
        </div>
        <div class="card-body p-4">

            @include('admin.purchase.return.form', [
                'action' => route('admin.purchase-returns.store'),
               
            ])

        </div>
    </div>
</div>
@endsection
