<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bank Accounts Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        h1, h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        .section-title {
            background-color: #f2f2f2;
            font-weight: bold;
            padding: 6px;
        }
    </style>
</head>
<body>

<h1>Bank Accounts Report</h1>
@if(request()->filled('bank_name') || request()->filled('owner_name'))
    <h2>Filters Applied:</h2>
    <p>
        @if(request()->filled('bank_name')) Bank Name: {{ request('bank_name') }} @endif
        @if(request()->filled('owner_name')) | Owner Name: {{ request('owner_name') }} @endif
    </p>
@endif

@foreach($bankAccounts as $account)
    <table>
        <tr>
            <th colspan="2" class="section-title">{{ $account->bank_name }} (Owner: {{ $account->owner_name }})</th>
        </tr>
        <tr>
            <th>Account Number</th>
            <td>{{ $account->account_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Balance</th>
            <td>{{ number_format($account->balance ?? 0, 2) }}</td>
        </tr>
    </table>

    {{-- Transactions From --}}
    @if($account->transactionsFrom->count())
        <table>
            <tr><th colspan="5" class="section-title">Transactions From This Account</th></tr>
            <tr>
                <th>Transaction ID</th>
                <th>To Bank</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
            @foreach($account->transactionsFrom as $tx)
                <tr>
                    <td>{{ $tx->transaction_id }}</td>
                    <td>{{ $tx->toBank->bank_name ?? '-' }}</td>
                    <td>{{ number_format($tx->amount, 2) }}</td>
                    <td>{{ ucfirst($tx->type) }}</td>
                    <td>{{ $tx->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Transactions To --}}
    @if($account->transactionsTo->count())
        <table>
            <tr><th colspan="5" class="section-title">Transactions To This Account</th></tr>
            <tr>
                <th>Transaction ID</th>
                <th>From Bank</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
            @foreach($account->transactionsTo as $tx)
                <tr>
                    <td>{{ $tx->transaction_id }}</td>
                    <td>{{ $tx->fromBank->bank_name ?? '-' }}</td>
                    <td>{{ number_format($tx->amount, 2) }}</td>
                    <td>{{ ucfirst($tx->type) }}</td>
                    <td>{{ $tx->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Cheques --}}
    @if($account->cheques->count())
        <table>
            <tr><th colspan="6" class="section-title">Cheques</th></tr>
            <tr>
                <th>Cheque No</th>
                <th>Reason</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Cheque Date</th>
            </tr>
            @foreach($account->cheques as $cheque)
                <tr>
                    <td>{{ $cheque->cheque_no }}</td>
                    <td>{{ $cheque->reason }}</td>
                    <td>{{ ucfirst($cheque->type) }}</td>
                    <td>{{ number_format($cheque->amount, 2) }}</td>
                    <td>{{ ucfirst($cheque->status) }}</td>
                    <td>{{ $cheque->cheque_date?->format('Y-m-d') ?? '-' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Receiving Cheques --}}
    @if($account->receivingCheques->count())
        <table>
            <tr><th colspan="5" class="section-title">Receiving Cheques</th></tr>
            <tr>
                <th>Cheque No</th>
                <th>Amount</th>
                <th>Received From</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            @foreach($account->receivingCheques as $rcheque)
                <tr>
                    <td>{{ $rcheque->cheque_no }}</td>
                    <td>{{ number_format($rcheque->amount, 2) }}</td>
                    <td>{{ $rcheque->received_from }}</td>
                    <td>{{ ucfirst($rcheque->status) }}</td>
                    <td>{{ $rcheque->cheque_date?->format('Y-m-d') ?? '-' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Fund Transfers --}}
    @if($account->fundTransfersFrom->count() || $account->fundTransfersTo->count())
        <table>
            <tr><th colspan="5" class="section-title">Fund Transfers</th></tr>
            <tr>
                <th>Transfer ID</th>
                <th>From Bank</th>
                <th>To Bank</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
            @foreach($account->fundTransfersFrom as $ft)
                <tr>
                    <td>{{ $ft->id }}</td>
                    <td>{{ $ft->fromBank->bank_name ?? '-' }}</td>
                    <td>{{ $ft->toBank->bank_name ?? '-' }}</td>
                    <td>{{ number_format($ft->amount, 2) }}</td>
                    <td>{{ $ft->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
            @foreach($account->fundTransfersTo as $ft)
                <tr>
                    <td>{{ $ft->id }}</td>
                    <td>{{ $ft->fromBank->bank_name ?? '-' }}</td>
                    <td>{{ $ft->toBank->bank_name ?? '-' }}</td>
                    <td>{{ number_format($ft->amount, 2) }}</td>
                    <td>{{ $ft->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    {{-- Payments --}}
    @if($account->payments->count())
        <table>
            <tr><th colspan="5" class="section-title">Payments</th></tr>
            <tr>
                <th>Payment ID</th>
                <th>Amount</th>
                <th>Paid By</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
            @foreach($account->payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->payer_name ?? '-' }}</td>
                    <td>{{ ucfirst($payment->type) }}</td>
                    <td>{{ $payment->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <hr style="margin: 30px 0;">
@endforeach

</body>
</html>
