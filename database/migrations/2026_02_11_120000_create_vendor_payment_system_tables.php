<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create vendor_wallets table
        if (!Schema::hasTable('vendor_wallets')) {
            Schema::create('vendor_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->decimal('total_earned', 15, 2)->default(0.00);
                $table->decimal('total_paid', 15, 2)->default(0.00);
                $table->decimal('pending_amount', 15, 2)->default(0.00);
                $table->decimal('hold_amount', 15, 2)->default(0.00);
                $table->enum('status', ['active', 'hold', 'suspended'])->default('active');
                $table->timestamp('last_payout_at')->nullable();
                $table->timestamps();
                
                $table->unique('vendor_id');
            });
        }

        // Create vendor_bank_accounts table
        if (!Schema::hasTable('vendor_bank_accounts')) {
            Schema::create('vendor_bank_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->string('account_holder_name');
                $table->string('account_number');
                $table->string('ifsc_code');
                $table->string('bank_name');
                $table->string('branch_name')->nullable();
                $table->string('account_type')->default('savings'); // savings, current
                $table->string('razorpay_contact_id')->nullable();
                $table->string('razorpay_fund_account_id')->nullable();
                $table->enum('fund_account_status', ['pending', 'created', 'failed'])->default('pending');
                $table->text('fund_account_error')->nullable();
                $table->boolean('is_primary')->default(true);
                $table->boolean('is_verified')->default(false);
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                
                $table->index(['vendor_id', 'is_primary']);
            });
        }

        // Create vendor_earnings table to track individual earnings
        if (!Schema::hasTable('vendor_earnings')) {
            Schema::create('vendor_earnings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
                $table->foreignId('invoice_id')->nullable()->constrained('proforma_invoices')->onDelete('set null');
                $table->decimal('order_amount', 15, 2);
                $table->decimal('commission_rate', 5, 2);
                $table->decimal('commission_amount', 15, 2);
                $table->decimal('vendor_earning', 15, 2);
                $table->enum('status', ['pending', 'confirmed', 'paid', 'cancelled'])->default('pending');
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->index(['vendor_id', 'status']);
                $table->index('created_at');
            });
        }

        // Enhance existing vendor_payouts table with RazorpayX fields
        if (Schema::hasTable('vendor_payouts')) {
            Schema::table('vendor_payouts', function (Blueprint $table) {
                if (!Schema::hasColumn('vendor_payouts', 'razorpay_payout_id')) {
                    $table->string('razorpay_payout_id')->nullable()->after('transaction_id');
                }
                if (!Schema::hasColumn('vendor_payouts', 'razorpay_fund_account_id')) {
                    $table->string('razorpay_fund_account_id')->nullable()->after('razorpay_payout_id');
                }
                if (!Schema::hasColumn('vendor_payouts', 'payout_mode')) {
                    $table->string('payout_mode')->default('NEFT')->after('payment_method'); // NEFT, RTGS, IMPS, UPI
                }
                if (!Schema::hasColumn('vendor_payouts', 'utr')) {
                    $table->string('utr')->nullable()->after('razorpay_fund_account_id'); // Unique Transaction Reference
                }
                if (!Schema::hasColumn('vendor_payouts', 'failure_reason')) {
                    $table->text('failure_reason')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('vendor_payouts', 'retry_count')) {
                    $table->integer('retry_count')->default(0)->after('failure_reason');
                }
                if (!Schema::hasColumn('vendor_payouts', 'is_automated')) {
                    $table->boolean('is_automated')->default(false)->after('retry_count');
                }
                if (!Schema::hasColumn('vendor_payouts', 'razorpay_status')) {
                    $table->string('razorpay_status')->nullable()->after('status');
                }
            });
        }

        // Create payout_logs table for detailed tracking
        if (!Schema::hasTable('payout_logs')) {
            Schema::create('payout_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payout_id')->constrained('vendor_payouts')->onDelete('cascade');
                $table->string('event_type'); // initiated, processing, completed, failed, reversed
                $table->string('razorpay_status')->nullable();
                $table->json('api_response')->nullable();
                $table->text('message')->nullable();
                $table->timestamps();
                
                $table->index(['payout_id', 'event_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_logs');
        Schema::dropIfExists('vendor_earnings');
        Schema::dropIfExists('vendor_bank_accounts');
        Schema::dropIfExists('vendor_wallets');
        
        if (Schema::hasTable('vendor_payouts')) {
            Schema::table('vendor_payouts', function (Blueprint $table) {
                $columns = [
                    'razorpay_payout_id', 'razorpay_fund_account_id', 'payout_mode',
                    'utr', 'failure_reason', 'retry_count', 'is_automated', 'razorpay_status'
                ];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('vendor_payouts', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
