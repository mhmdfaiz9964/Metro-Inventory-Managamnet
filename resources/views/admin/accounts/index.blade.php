@extends('layouts.app')

@section('content')
    <h1 class="mb-4">Account Management</h1>

    <div class="row">
        @foreach ($banks as $bank)
            <div class="col-md-6 mb-4">
                <div class="card shadow-lg"
                    style="background-color: #ffffff; border: 2px solid #340965; border-radius: 15px; position: relative; color: #000;">
                    <!-- Card Body -->
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="bi bi-credit-card me-2"></i> {{ $bank->bank_name }} Card
                                </h5>
                                <p class="mb-0">
                                    <small>Account #: {{ $bank->account_number }}</small>
                                </p>
                                <p class="mb-0">
                                    <small>Created At: {{ $bank->created_at->format('d/m/Y') }}</small>
                                </p>
                            </div>
                            <div>
                                <img src="https://cdn-icons-png.flaticon.com/512/9063/9063313.png" alt="Card Logo"
                                    class="img-fluid" style="width: 80px; height: auto;">
                            </div>
                        </div>

                        <hr style="border-color: #340965;">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="mb-0"><strong>Balance:</strong> Rs. {{ number_format($bank->bank_balance, 2) }}</p>
                            <span class="badge bg-success">Active</span>
                        </div>

                        <h6 class="mb-2"><i class="bi bi-list-ul me-2"></i> Recent Transactions</h6>

                        @php
                            $transactions = $bank->transactionsFrom
                                ->merge($bank->transactionsTo)
                                ->sortByDesc('created_at');
                        @endphp

                        @if ($transactions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-dark">
                                    <thead style="background-color: #f1f1f1;">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Amount (Rs.)</th>
                                            <th>From/To Bank</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                                                <td>
                                                    @if ($transaction->to_bank_id == $bank->id)
                                                        <span class="badge bg-success">Credited</span>
                                                    @else
                                                        <span class="badge bg-danger">Debited</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($transaction->amount, 2) }}</td>
                                                <td>
                                                    @if ($transaction->from_bank_id)
                                                        From:
                                                        {{ $transaction->fromBank ? $transaction->fromBank->bank_name : 'N/A' }}
                                                    @else
                                                        To:
                                                        {{ $transaction->toBank ? $transaction->toBank->bank_name : 'N/A' }}
                                                    @endif
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>No transactions found.</p>
                        @endif

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
