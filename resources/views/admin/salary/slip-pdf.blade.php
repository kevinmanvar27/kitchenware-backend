<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Salary Slip - {{ $payment->user->name }} - {{ $payment->period }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }
        .header p {
            margin: 0;
            color: #666;
        }
        .slip-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            padding: 10px;
            background: #f5f5f5;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px 10px;
            vertical-align: top;
        }
        .info-table .label {
            color: #666;
            width: 30%;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .attendance-summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .attendance-summary td {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .attendance-summary .count {
            font-size: 18px;
            font-weight: bold;
        }
        .attendance-summary .label {
            font-size: 10px;
            color: #666;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .salary-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        .salary-table .amount {
            text-align: right;
            width: 30%;
        }
        .salary-table .total-row {
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .breakdown-table th, .breakdown-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .breakdown-table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .breakdown-table .text-right {
            text-align: right;
        }
        .breakdown-table tfoot td {
            font-weight: bold;
            background: #f5f5f5;
        }
        .net-salary {
            background: #333;
            color: #fff;
            padding: 15px;
            margin: 20px 0;
        }
        .net-salary table {
            width: 100%;
        }
        .net-salary td {
            padding: 0;
        }
        .net-salary .label {
            font-size: 14px;
        }
        .net-salary .amount {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }
        .two-column {
            width: 100%;
        }
        .two-column td {
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .signature-row {
            width: 100%;
            margin-top: 50px;
        }
        .signature-row td {
            width: 50%;
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid #333;
        }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b6d4fe;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .info-box-title {
            font-weight: bold;
            color: #0c5460;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <!-- Company Header -->
    <div class="header">
        <h2>{{ setting('site_title', 'Company Name') }}</h2>
        <p>{{ setting('company_address', '') }}</p>
    </div>
    
    <div class="slip-title">
        SALARY SLIP FOR {{ strtoupper($payment->period) }}
    </div>
    
    <!-- Employee Details -->
    <table class="info-table">
        <tr>
            <td class="label">Employee Name:</td>
            <td><strong>{{ $payment->user->name }}</strong></td>
            <td class="label">Pay Period:</td>
            <td><strong>{{ $payment->period }}</strong></td>
        </tr>
        <tr>
            <td class="label">Email:</td>
            <td>{{ $payment->user->email }}</td>
            <td class="label">Payment Date:</td>
            <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'Pending' }}</td>
        </tr>
        <tr>
            <td class="label">Designation:</td>
            <td>{{ ucfirst(str_replace('_', ' ', $payment->user->user_role)) }}</td>
            <td class="label">Status:</td>
            <td><strong>{{ ucfirst($payment->status) }}</strong></td>
        </tr>
    </table>
    
    <!-- Attendance Summary -->
    <div class="section">
        <div class="section-title">Attendance Summary</div>
        <table class="attendance-summary">
            <tr>
                <td>
                    <div class="count text-success">{{ $payment->present_days }}</div>
                    <div class="label">Present</div>
                </td>
                <td>
                    <div class="count text-danger">{{ $payment->absent_days }}</div>
                    <div class="label">Absent</div>
                </td>
                <td>
                    <div class="count" style="color: #ffc107;">{{ $payment->half_days }}</div>
                    <div class="label">Half Day</div>
                </td>
                <td>
                    <div class="count" style="color: #17a2b8;">{{ $payment->leave_days }}</div>
                    <div class="label">Leave</div>
                </td>
                <td>
                    <div class="count" style="color: #6c757d;">{{ $payment->holiday_days }}</div>
                    <div class="label">Holiday</div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Salary Rate Changes (if mid-month change) -->
    @if(isset($salaryBreakdown) && count($salaryBreakdown) > 1)
    <div class="section">
        <div class="info-box">
            <div class="info-box-title">Salary Rate Changes This Month</div>
            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Daily Rate</th>
                        <th>Present</th>
                        <th>Half Days</th>
                        <th>Leave/Holiday</th>
                        <th class="text-right">Earned</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salaryBreakdown as $breakdown)
                    <tr>
                        <td style="font-size: 10px;">{{ $breakdown['effective_from'] }} - {{ $breakdown['effective_to'] }}</td>
                        <td>₹{{ number_format($breakdown['daily_rate'], 2) }}</td>
                        <td>{{ $breakdown['present_days'] }}</td>
                        <td>{{ $breakdown['half_days'] }}</td>
                        <td>{{ $breakdown['leave_days'] + $breakdown['holiday_days'] }}</td>
                        <td class="text-right">₹{{ number_format($breakdown['earned'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right">Total Earned:</td>
                        <td class="text-right">₹{{ number_format($payment->earned_salary, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
    
    <!-- Salary Breakdown -->
    <table class="two-column">
        <tr>
            <td>
                <div class="section">
                    <div class="section-title" style="color: #28a745;">Earnings</div>
                    <table class="salary-table">
                        <tr>
                            <td>Current Base Salary</td>
                            <td class="amount">₹{{ number_format($payment->base_salary, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Current Daily Rate</td>
                            <td class="amount">₹{{ number_format($payment->daily_rate, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Earned Salary</td>
                            <td class="amount">₹{{ number_format($payment->earned_salary, 2) }}</td>
                        </tr>
                        @if($payment->bonus > 0)
                        <tr>
                            <td>Bonus</td>
                            <td class="amount text-success">+₹{{ number_format($payment->bonus, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td>Total Earnings</td>
                            <td class="amount">₹{{ number_format($payment->earned_salary + $payment->bonus, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="section">
                    <div class="section-title" style="color: #dc3545;">Deductions</div>
                    <table class="salary-table">
                        @if($payment->deductions > 0)
                        <tr>
                            <td>Deductions</td>
                            <td class="amount">₹{{ number_format($payment->deductions, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="2" style="text-align: center; color: #999;">No deductions</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td>Total Deductions</td>
                            <td class="amount">₹{{ number_format($payment->deductions, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
    
    <!-- Net Salary -->
    <div class="net-salary">
        <table>
            <tr>
                <td class="label">Net Salary Payable</td>
                <td class="amount">₹{{ number_format($payment->net_salary, 2) }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Payment Details -->
    <table class="info-table">
        <tr>
            <td class="label">Amount Paid:</td>
            <td class="text-success"><strong>₹{{ number_format($payment->paid_amount, 2) }}</strong></td>
            <td class="label">Pending Amount:</td>
            <td class="text-danger"><strong>₹{{ number_format($payment->pending_amount, 2) }}</strong></td>
        </tr>
        @if($payment->payment_method)
        <tr>
            <td class="label">Payment Method:</td>
            <td>{{ ucfirst($payment->payment_method) }}</td>
            <td class="label">Transaction ID:</td>
            <td>{{ $payment->transaction_id ?? '-' }}</td>
        </tr>
        @endif
    </table>
    
    @if($payment->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <p>{{ $payment->notes }}</p>
    </div>
    @endif
    
    <!-- Signature Section -->
    <table class="signature-row">
        <tr>
            <td>Employee Signature</td>
            <td>Authorized Signature</td>
        </tr>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        <p style="text-align: center; color: #666; font-size: 10px;">
            This is a computer generated salary slip. Generated on {{ now()->format('d M Y, h:i A') }}
        </p>
    </div>
</body>
</html>
