<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $invoice->invoice_number }}</title>
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
            /* Uses a dynamic setting for theme color */
            border-bottom: 2px solid <?php echo setting('theme_color', '#FF6B00') ?>;
        }

        .header-logo {
            max-width: 180px;
            max-height: 70px;
        }

        .header-title {
            font-size: 20px;
            margin: 5px 0 0;
            font-weight: bold;
            color: <?php echo e(setting('theme_color', '#FF6B00')); ?>;
        }

        /* SECTION TITLES */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid <?php echo e(setting('theme_color', '#FF6B00')) ?>;
            color: <?php echo e(setting('theme_color', '#FF6B00')) ?>;
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
            background: <?php echo e(setting('theme_color', '#FF6B00')) ?>;
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
            /* Using a lightened version of the theme color */
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

        /* Store Logo */
        .store-logo {
            max-width: 120px;
            max-height: 50px;
            margin-bottom: 5px;
        }

        /* Store Badge */
        .store-badge {
            background: <?php echo e(setting('theme_color', '#FF6B00')) ?>;
            color: #fff;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            display: inline-block;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        @php
            $logoPath = null;
            // Check for vendor store logo first
            if (isset($store) && $store && !empty($store['store_logo'])) {
                // Check in vendor root folder
                if (file_exists(public_path('storage/vendor/' . $store['store_logo']))) {
                    $logoPath = public_path('storage/vendor/' . $store['store_logo']);
                }
                // Check in vendor-specific subfolder
                elseif (isset($store['vendor_id']) && file_exists(public_path('storage/vendor/' . $store['vendor_id'] . '/' . $store['store_logo']))) {
                    $logoPath = public_path('storage/vendor/' . $store['vendor_id'] . '/' . $store['store_logo']);
                }
                // Check direct path
                elseif (file_exists(public_path('storage/' . $store['store_logo']))) {
                    $logoPath = public_path('storage/' . $store['store_logo']);
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
            <h1>{{ isset($store) && $store ? $store['store_name'] : $siteTitle }}</h1>
        @endif

        <div class="header-title">INVOICE</div>
    </div>

    <table style="width:100%; margin-bottom:20px;">
        <tr>
            <td style="width:50%; vertical-align:top; padding-right:15px; border: none;">
                <div class="section-title">From</div>

                @if(isset($store) && $store)
                    {{-- Vendor/Store Details --}}
                    <div><strong>Store:</strong> {{ $store['store_name'] }}</div>
                    @if(!empty($store['full_address']))
                        <div><strong>Address:</strong> {{ $store['full_address'] }}</div>
                    @elseif(!empty($store['business_address']))
                        <div><strong>Address:</strong> {{ $store['business_address'] }}</div>
                    @endif
                    @if(!empty($store['business_email']))
                        <div><strong>Email:</strong> {{ $store['business_email'] }}</div>
                    @endif
                    @if(!empty($store['business_phone']))
                        <div><strong>Phone:</strong> {{ $store['business_phone'] }}</div>
                    @endif
                    @if(!empty($store['gst_number']))
                        <div><strong>GST No:</strong> {{ $store['gst_number'] }}</div>
                    @endif
                @else
                    {{-- Default Company Details --}}
                    <div><strong>Company:</strong> {{ $siteTitle }}</div>
                    <div><strong>Address:</strong> {{ $companyAddress }}</div>
                    <div><strong>Email:</strong> {{ $companyEmail }}</div>
                    <div><strong>Phone:</strong> {{ $companyPhone }}</div>
                @endif
            </td>

            <td style="width:50%; vertical-align:top; padding-left:15px; border: none;">
                <div class="section-title">To</div>

                @if($invoiceData['customer'])
                    <div><strong>Name:</strong> {{ $invoiceData['customer']['name'] }}</div>
                    <div><strong>Email:</strong> {{ $invoiceData['customer']['email'] }}</div>

                    @if(!empty($invoiceData['customer']['address']))
                        <div><strong>Address:</strong> {{ $invoiceData['customer']['address'] }}</div>
                    @endif

                    @if(!empty($invoiceData['customer']['mobile_number']))
                        <div><strong>Phone:</strong> {{ $invoiceData['customer']['mobile_number'] }}</div>
                    @endif
                @else
                    <div><strong>Customer:</strong> Guest Customer</div>
                @endif
            </td>
        </tr>
    </table>

    <div class="row">
        <div class="col-auto"><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</div>
        <div class="col-auto"><strong>Date:</strong> {{ $invoiceData['invoice_date'] ?? $invoice->created_at->format('Y-m-d') }}</div>
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
                @forelse($invoiceData['cart_items'] ?? [] as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $item['product_name'] }}
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
                        <td class="text-end">{{ number_format($item['price'], 2) }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-end">{{ number_format($item['total'], 2) }}</td>
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
                $gstType = $invoiceData['gst_type'] ?? 'with_gst';
            @endphp
            
            @if(!empty($invoiceData['subtotal']))
            <tr>
                <td>Subtotal:</td>
                <td class="text-end">₹{{ number_format($invoiceData['subtotal'], 2) }}</td>
            </tr>
            @endif

            @if($gstType === 'with_gst' && !empty($invoiceData['tax_percentage']) && $invoiceData['tax_percentage'] > 0)
            <tr>
                <td>GST ({{ $invoiceData['tax_percentage'] }}%):</td>
                <td class="text-end">₹{{ number_format($invoiceData['tax_amount'] ?? 0, 2) }}</td>
            </tr>
            @endif

            @if(!empty($invoiceData['shipping']) && $invoiceData['shipping'] > 0)
            <tr>
                <td>Shipping:</td>
                <td class="text-end">₹{{ number_format($invoiceData['shipping'], 2) }}</td>
            </tr>
            @endif

            @if(!empty($invoiceData['discount_amount']) && $invoiceData['discount_amount'] > 0)
            <tr>
                <td>Discount:</td>
                <td class="text-end">-₹{{ number_format($invoiceData['discount_amount'], 2) }}</td>
            </tr>
            @endif

            @if(!empty($invoiceData['coupon']) && !empty($invoiceData['coupon_discount']) && $invoiceData['coupon_discount'] > 0)
            <tr>
                <td>Coupon ({{ $invoiceData['coupon']['code'] }}):</td>
                <td class="text-end text-success">-₹{{ number_format($invoiceData['coupon_discount'], 2) }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td><strong>Total:</strong></td>
                <td class="text-end"><strong>₹{{ number_format($invoiceData['total'] ?? $invoice->total_amount, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        @if(isset($store) && $store)
            Invoice from {{ $store['store_name'] }}<br>
        @endif
        This is a computer-generated document and does not require a signature.<br>
        Thank you for your business!
    </div>

</div>

</body>
</html>