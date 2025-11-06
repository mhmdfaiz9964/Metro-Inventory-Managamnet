<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Report (BOM Components)</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        h2 { text-align: center; }
        .summary { margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Purchase Report (BOM Components)</h2>

    {{-- Filter Summary --}}
    <div class="summary">
        @if(request('supplier_id'))
            <p><strong>Supplier:</strong> {{ $purchases->first()?->supplier?->name ?? '-' }}</p>
        @endif
        @if(request('product_id'))
            <p><strong>Product:</strong> {{ $purchases->first()?->product_id ?? '-' }}</p>
        @endif
        @if(request('date_from') || request('date_to'))
            <p><strong>Date Range:</strong> 
                {{ request('date_from') ?? 'Start' }} - {{ request('date_to') ?? 'End' }}
            </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Supplier</th>
                <th>Purchase Date</th>
                <th>Status</th>
                <th>Payment Status</th>
                <th>Total Cost (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($purchases as $index => $purchase)
                @php
                    $purchaseTotal = $purchase->bomProducts->sum(fn($bom) => $bom->qty * $bom->cost_price);
                    $grandTotal += $purchaseTotal;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $purchase->supplier?->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($purchase->status) }}</td>
                    <td>{{ ucfirst($purchase->payment_status) }}</td>
                    <td class="text-right">{{ number_format($purchaseTotal, 2) }}</td>
                </tr>

                {{-- BOM Component Details --}}
                <tr>
                    <td colspan="6">
                        <table>
                            <thead>
                                <tr>
                                    <th>BOM Component</th>
                                    <th class="text-right">Quantity</th>
                                    <th class="text-right">Cost Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->bomProducts as $bom)
                                    <tr>
                                        <td>{{ $bom->bomComponent?->name ?? '-' }}</td>
                                        <td class="text-right">{{ $bom->qty }}</td>
                                        <td class="text-right">{{ number_format($bom->cost_price, 2) }}</td>
                                        <td class="text-right">{{ number_format($bom->qty * $bom->cost_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No purchases found.</td>
                </tr>
            @endforelse
            <tr>
                <td colspan="5" class="text-right"><strong>Grand Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
