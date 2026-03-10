<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $invoiceNumber }}</title>
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
            border-bottom: 2px solid {{ setting('theme_color', '#FF6B00') }};
        }

        .header-logo {
            max-width: 180px;
            max-height: 70px;
        }

        .header-title {
            font-size: 20px;
            margin: 5px 0 0;
            font-weight: bold;
            color: {{ setting('theme_color', '#FF6B00') }};
        }

        /* SECTION TITLES */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid {{ setting('theme_color', '#FF6B00') }};
            color: {{ setting('theme_color', '#FF6B00') }};
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
            background: {{ setting('theme_color', '#FF6B00') }};
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
            border: 1px solid {{ setting('theme_color', '#FF6B00') }};
            background: rgba(255, 107, 0, 0.08);
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
        
        .payment-info {
            margin-top: 15px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .payment-info table td {
            border: none;
            padding: 4px 5px;
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
                $gstType = $invoiceData['gst_type'] ?? 'with_gst';
                
                // Handle GST based on type
                if ($gstType === 'without_gst') {
                    $taxPercentage = 0;
                    $taxAmount = 0;
                } else {
                    $taxPercentage = $invoiceData['tax_percentage'] ?? 18;
                    $taxAmount = $invoiceData['tax_amount'] ?? ($subtotal * $taxPercentage / 100);
                }
                
                // Calculate final total
                $totalAmount = $invoiceData['total'] ?? ($subtotal + $shipping + $taxAmount - $discountAmount);
            @endphp
            
            <tr>
                <td>Subtotal:</td>
                <td class="text-end">₹{{ number_format($subtotal, 2) }}</td>
            </tr>

            @if($gstType === 'with_gst')
            <tr>
                <td>GST ({{ $taxPercentage }}%):</td>
                <td class="text-end">₹{{ number_format($taxAmount, 2) }}</td>
            </tr>
            @endif

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
                <td class="text-end" style="color: green;">-₹{{ number_format($invoiceData['coupon_discount'], 2) }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td><strong>Total:</strong></td>
                <td class="text-end"><strong>₹{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Payment Information -->
    <div class="payment-info">
        <div class="section-title">Payment Information</div>
        <table style="width:100%;">
            <tr>
                <td><strong>Total Amount:</strong></td>
                <td class="text-end">₹{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Paid Amount:</strong></td>
                <td class="text-end" style="color: green;">₹{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Pending Amount:</strong></td>
                <td class="text-end" style="color: {{ $invoice->pending_amount > 0 ? 'red' : 'green' }};">₹{{ number_format($invoice->pending_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Payment Status:</strong></td>
                <td class="text-end">
                    @switch($invoice->payment_status)
                        @case('paid')
                            <span style="color: green; font-weight: bold;">PAID</span>
                            @break
                        @case('partial')
                            <span style="color: orange; font-weight: bold;">PARTIAL</span>
                            @break
                        @default
                            <span style="color: red; font-weight: bold;">UNPAID</span>
                    @endswitch
                </td>
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
        Thank you for your business!
    </div>

</div>

</body>
</html>