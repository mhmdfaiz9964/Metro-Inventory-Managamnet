<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $sale->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif; /* Safe font for DOMPDF */
            color: #333;
            margin: 0;
            padding: 10px;
            font-size: 13px;
            line-height: 1.4;
        }

        table {
            border-collapse: collapse;
        }

        h4 {
            margin: 20px 0 10px;
        }

        /* Table Styling */
        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 12px;
            text-align: left;
        }

        th { background-color: #eee; }
        td.text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #eee; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 10px;
            color: #fff;
        }

        .badge-success { background-color: #28a745; }
        .badge-info { background-color: #17a2b8; }
        .badge-secondary { background-color: #6c757d; }

        .payment-details {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #000;
            font-size: 12px;
        }

        .payment-summary {
            margin-top: 10px;
            padding: 5px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
        <tr>
            <!-- Logo + Company Info -->
            <td width="60%" style="vertical-align: top;">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width:120px;">
                            <img src="{{ public_path('assets/img/logo-new.png') }}" alt="Logo" style="width:120px;">
                        </td>
                        <td style="padding-left:10px; font-size:12px; color:#333;">
                            <strong>METRO MARQO (PVT) LTD</strong><br>
                            Colombo, Sri Lanka<br>
                            Email: info@metro.com<br>
                            Phone: +94 77 123 1234
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Invoice Info -->
            <td width="40%" style="vertical-align: top; text-align:right; font-size:12px;">
                <h2 style="margin:0; font-size:18px;">Invoice #{{ $sale->id }}</h2>
                Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}<br>
                Salesperson: {{ $sale->salesperson->name ?? '-' }}<br>
                Customer: {{ $sale->customer->name ?? '-' }}<br>
                Total: Rs. {{ number_format($sale->total_amount, 2) }}<br>
                <div style="margin-top:10px;">
                    <img src="data:image/png;base64, {!! $qrCode !!}" width="100" height="100">
                </div>
            </td>
        </tr>
    </table>

    <!-- Products Table -->
    <h4>Products</h4>
    <table width="100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Sale Price</th>
                <th>Discount</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
                <td>{{ $item->quantity }}</td>
                <td class="text-right">Rs. {{ number_format($item->sale_price, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($item->discount, 2) }} ({{ $item->discount_type }})</td>
                <td class="text-right">Rs. {{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">Subtotal</td>
                <td class="text-right">Rs. {{ number_format($sale->items->sum('total'), 2) }}</td>
            </tr>
            @if(($sale->salePayments->first()?->discount ?? 0) > 0)
            <tr class="total-row">
                <td colspan="5" class="text-right">Overall Discount</td>
                <td class="text-right">- Rs. {{ number_format($sale->salePayments->first()->discount ?? 0, 2) }} ({{ $sale->salePayments->first()->discount_type ?? 'amount' }})</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="5" class="text-right">Total</td>
                <td class="text-right">Rs. {{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Payment Details -->
    <div class="payment-details">
        <strong>Payment Details</strong><br>
        @foreach($sale->salePayments as $index => $payment)
            <div style="margin-bottom: 10px; {{ $index > 0 ? 'border-top: 1px solid #ccc; padding-top: 10px;' : '' }}">
                <strong>Payment {{ $index + 1 }}:</strong><br>
                Payment Method: {{ strtoupper($payment->payment_method) }}<br>
                @if($payment->bank_account_id)
                Bank Account: {{ strtoupper($payment->bank->name ?? '-') }}<br>
                @endif
                Paid By: {{ $payment->customer->name ?? $sale->customer->name ?? '-' }}<br>
                Paid Date: {{ \Carbon\Carbon::parse($payment->paid_date)->format('Y-m-d') ?? '-' }}<br>
                @if(($payment->discount ?? 0) > 0)
                Discount: Rs. {{ number_format($payment->discount ?? 0, 2) }} ({{ $payment->discount_type ?? 'amount' }})<br>
                @endif
                Amount Paid: Rs. {{ number_format($payment->payment_paid ?? 0, 2) }}<br>
                @if(isset($payment->cheque_no))
                Cheque No: {{ $payment->cheque_no }}<br>
                Cheque Date: {{ \Carbon\Carbon::parse($payment->cheque_date)->format('Y-m-d') ?? '-' }}
                @endif
            </div>
        @endforeach
        @if($sale->salePayments->isEmpty())
            No payments recorded.
        @else
            <div class="payment-summary">
                <strong>Total Paid: Rs. {{ number_format($sale->salePayments->sum('payment_paid'), 2) }}</strong><br>
                <strong>Balance Due: Rs. {{ number_format($sale->total_amount - $sale->salePayments->sum('payment_paid'), 2) }}</strong>
            </div>
        @endif
    </div>

    <div class="footer">
        Generated by METRO MARQO (PVT) LTD &copy; {{ date('Y') }}
    </div>
</body>
</html>