<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background-color: #f5f5f5; }
        .text-end { text-align: right; }
        .card { border: 1px solid #ccc; margin-bottom: 15px; border-radius: 5px; overflow: hidden; }
        .card-header { background-color: #f5f5f5; padding: 5px 10px; font-weight: bold; }
        .card-body { padding: 5px 10px; }
        ul { padding-left: 15px; margin: 0; }
        li { list-style-type: none; margin-bottom: 3px; }
    </style>
</head>
<body>
    <h2>Sales Report</h2>

    {{-- Summary Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Salesperson</th>
                <th>Date</th>
                <th class="text-end">Total Amount</th>
                <th class="text-end">Payments Received</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $index => $sale)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $sale->salesperson?->name ?? '-' }}</td>
                <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                <td class="text-end">{{ number_format($sale->totalAmount(), 2) }}</td>
                <td class="text-end">{{ number_format($sale->payments->sum('payment_amount'), 2) }}</td>
                <td class="text-end">{{ number_format($sale->totalAmount() - $sale->payments->sum('payment_amount'), 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted">No sales found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Detailed Sale Items --}}
    @foreach($sales as $sale)
    <div class="card">
        <div class="card-header">
            Sale #{{ $sale->id }} | {{ $sale->salesperson?->name ?? '-' }} | Date: {{ $sale->sale_date->format('Y-m-d') }}
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? '-' }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->sale_price, 2) }}</td>
                        <td class="text-end">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <h6>Payments Received</h6>
            <ul>
                @forelse($sale->payments as $payment)
                <li>
                    {{ $payment->payment_method }} | Rs. {{ number_format($payment->payment_amount, 2) }} | Paid by: {{ $payment->paid_by }} | Date: {{ $payment->paid_date }}
                </li>
                @empty
                <li>No payments received</li>
                @endforelse
            </ul>
        </div>
    </div>
    @endforeach

</body>
</html>
