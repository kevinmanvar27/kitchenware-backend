<!DOCTYPE html>
<html>
<head>
    <title>Without GST Invoice - {{ $invoiceNumber }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <style>
        /*
         * NOTE: Using DejaVu Sans is crucial for PDF generation
         * via tools like Dompdf to correctly display non-ASCII characters (e.g., Hindi, special symbols).
         */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
            margin: 0;
            background: #fff;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        /* HEADER */
        .header {
            text-align: center;
            padding-bottom: 12px;
            margin-bottom: 25px;
            border-bottom: 2px solid #6c757d;
        }

        .header-logo {
            max-width: 180px;
            max-height: 70px;
        }

        .header-title {
            font-size: 20px;
            margin: 5px 0 0;
            font-weight: bold;
            color: #6c757d;
        }
        
        .without-gst-badge {
            display: inline-block;
            background: #6c757d;
            color: #fff;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            margin-top: 5px;
        }

        /* SECTION TITLES */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #6c757d;
            color: #6c757d;
        }

        /* FLEX GRID (Emulated for PDF) */
        .row {
            display: flex;
            width: 100%;
            margin-bottom: 18px;
        }

        .col-6 {
            width: 50%;
            padding-right: 10px;
        }

        .col-6:last-child {
            padding-right: 0;
            padding-left: 10px;
        }

        .col-auto {
            margin-right: 25px;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 8px;
        }

        th {
            background: #6c757d;
            color: #fff;
            padding: 8px 5px;
            text-align: left;
            border: 1px solid #ccc;
        }

        /* Default TD styling (used for Items Table) */
        td {
            padding: 6px 5px;
            border: 1px solid #ccc;
        }

        .text-center { text-align: center; }
        .text-end    { text-align: right; }

        .total-row {
            background: #f5f5f5;
            font-weight: bold;
        }

        /* NOTES BOX */
        .notes {
            border: 1px solid #6c757d;
            background: rgba(108, 117, 125, 0.08);
            padding: 8px;
            margin-top: 10px;
            font-size: 10px;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            margin-top: 35px;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        @php
            $logoPath = null;
            if ($vendor->store_logo) {
                // Check in vendor root folder
                if (file_exists(public_path('storage/vendor/' . $vendor->store_logo))) {
                    $logoPath = public_path('storage/vendor/' . $vendor->store_logo);
                }
                // Check in vendor-specific subfolder
                elseif (file_exists(public_path('storage/vendor/' . $vendor->id . '/' . $vendor->store_logo))) {
                    $logoPath = public_path('storage/vendor/' . $vendor->id . '/' . $vendor->store_logo);
                }
                // Check direct path
                elseif (file_exists(public_path('storage/' . $vendor->store_logo))) {
                    $logoPath = public_path('storage/' . $vendor->store_logo);
                }
            }
            // Fallback to site header logo
            if (!$logoPath && setting('header_logo')) {
                if (file_exists(public_path('storage/' . setting('header_logo')))) {
                    $logoPath = public_path('storage/' . setting('header_logo'));
                }
            }
        @endphp
        
        @if($logoPath)
            <img src="{{ $logoPath }}" class="header-logo">
        @else
            <h1>{{ $vendor->store_name ?? $vendor->business_name ?? $vendor->name ?? setting('site_title') }}</h1>
        @endif

        <div class="header-title">INVOICE</div>
        <div class="without-gst-badge">WITHOUT GST</div>
    </div>

    <table style="width:100%; margin-bottom:20px;">
        <tr>
            <td style="width:50%; vertical-align:top; padding-right:15px; border: none;">
                <div class="section-title">From</div>

                <div><strong>Company:</strong> {{ $vendor->store_name ?? $vendor->business_name ?? $vendor->name ?? setting('site_title') }}</div>
                @if($vendor->business_address)
                    <div><strong>Address:</strong> {{ $vendor->business_address }}@if($vendor->city), {{ $vendor->city }}@endif @if($vendor->state), {{ $vendor->state }}@endif @if($vendor->postal_code) - {{ $vendor->postal_code }}@endif</div>
                @elseif($vendor->address)
                    <div><strong>Address:</strong> {{ $vendor->address }}</div>
                @endif
                @if($vendor->business_email)
                    <div><strong>Email:</strong> {{ $vendor->business_email }}</div>
                @elseif($vendor->email)
                    <div><strong>Email:</strong> {{ $vendor->email }}</div>
                @endif
                @if($vendor->business_phone)
                    <div><strong>Phone:</strong> {{ $vendor->business_phone }}</div>
                @elseif($vendor->phone)
                    <div><strong>Phone:</strong> {{ $vendor->phone }}</div>
                @endif
                @if($vendor->gst_number)
                    <div><strong>GST No:</strong> {{ $vendor->gst_number }}</div>
                @endif
            </td>

            <td style="width:50%; vertical-align:top; padding-left:15px; border: none;">
                <div class="section-title">To</div>

                @if($customer)
                    <div><strong>Name:</strong> {{ $customer['name'] ?? 'N/A' }}</div>
                    <div><strong>Email:</strong> {{ $customer['email'] ?? 'N/A' }}</div>

                    @if(!empty($customer['address']))
                        <div><strong>Address:</strong> {{ $customer['address'] }}</div>
                    @endif

                    @if(!empty($customer['mobile_number']))
                        <div><strong>Phone:</strong> {{ $customer['mobile_number'] }}</div>
                    @endif
                @elseif($invoice->user)
                    <div><strong>Name:</strong> {{ $invoice->user->name }}</div>
                    <div><strong>Email:</strong> {{ $invoice->user->email }}</div>

                    @if($invoice->user->address)
                        <div><strong>Address:</strong> {{ $invoice->user->address }}</div>
                    @endif

                    @if($invoice->user->mobile_number)
                        <div><strong>Phone:</strong> {{ $invoice->user->mobile_number }}</div>
                    @endif
                @else
                    <div><strong>Customer:</strong> Guest Customer</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="row">
        <div class="col-auto"><strong>Invoice #:</strong> {{ $invoiceNumber }}</div>
        <div class="col-auto"><strong>Date:</strong> {{ $invoiceDate }}</div>
        <div class="col-auto"><strong>Status:</strong> {{ $invoice->status }}</div>
    </div>

    <div>
        <div class="section-title">Items</div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Description</th>
                    <th class="text-end">Price (₹)</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Total (₹)</th>
                </tr>
            </thead>

            <tbody>
                @forelse($cartItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $item['product_name'] ?? 'Product' }}
                            @if(!empty($item['product_variation_id']))
                                {{-- Display attributes for variation products --}}
                                @if(!empty($item['variation_attributes']))
                                    <br>
                                    <small style="color: #666; font-size: 9px;">
                                        @foreach($item['variation_attributes'] as $attrName => $attrValue)
                                            <strong>{{ $attrName }}:</strong> {{ $attrValue }}@if(!$loop->last), @endif
                                        @endforeach
                                    </small>
                                @endif
                                @if(!empty($item['variation_sku']))
                                    <br>
                                    <small style="color: #666; font-size: 9px;">
                                        <strong>SKU:</strong> {{ $item['variation_sku'] }}
                                    </small>
                                @endif
                            @endif
                        </td>
                        <td>{!! Str::limit(strip_tags($item['product_description'] ?? ''), 40) !!}</td>
                        <td class="text-end">{{ number_format($item['price'] ?? 0, 2) }}</td>
                        <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                        <td class="text-end">{{ number_format(($item['total'] ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No items found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row">
        <table style="width:100%;">
            @php
                // Extract values from invoice data or use defaults
                $subtotal = $invoiceData['subtotal'] ?? $total ?? 0;
                $shipping = $invoiceData['shipping'] ?? 0;
                $discountAmount = $invoiceData['discount_amount'] ?? 0;
                
                // No tax for without-GST invoices
                $totalAmount = $invoiceData['total'] ?? ($subtotal + $shipping - $discountAmount);
            @endphp
            
            <tr>
                <td>Subtotal:</td>
                <td class="text-end">₹{{ number_format($subtotal, 2) }}</td>
            </tr>

            <tr>
                <td>Shipping:</td>
                <td class="text-end">₹{{ number_format($shipping, 2) }}</td>
            </tr>

            <tr>
                <td>Discount Amount:</td>
                <td class="text-end">-₹{{ number_format($discountAmount, 2) }}</td>
            </tr>

            @if(!empty($invoiceData['coupon']) && !empty($invoiceData['coupon_discount']) && $invoiceData['coupon_discount'] > 0)
            <tr>
                <td>Coupon ({{ $invoiceData['coupon']['code'] }}):</td>
                <td class="text-end text-success">-₹{{ number_format($invoiceData['coupon_discount'], 2) }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td><strong>Total:</strong></td>
                <td class="text-end"><strong>₹{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    @if(!empty($invoiceData['notes']))
    <div class="notes">
        <strong>Notes:</strong><br>
        {{ $invoiceData['notes'] }}
    </div>
    @endif

    <div class="footer">
        This is a computer-generated document and does not require a signature.<br>
        This invoice is exempt from GST.<br>
        Thank you for your business!
    </div>

</div>

</body>
</html>
