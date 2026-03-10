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
        if (!Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade'); // User who referred
                $table->foreignId('referred_id')->nullable()->constrained('users')->onDelete('set null'); // User who was referred
                $table->string('referral_code', 20)->unique();
                $table->enum('status', ['pending', 'completed', 'expired', 'cancelled'])->default('pending');
                $table->decimal('reward_amount', 10, 2)->default(0);
                $table->decimal('referred_reward_amount', 10, 2)->default(0); // Reward for the referred user
                $table->boolean('reward_claimed', false)->default(false);
                $table->boolean('referred_reward_claimed', false)->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['referrer_id', 'status']);
                $table->index('referral_code');
            });
        }
        
        // Add referral settings to settings table
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'referral_enabled')) {
                $table->boolean('referral_enabled')->default(true)->after('frontend_access_permission');
            }
            if (!Schema::hasColumn('settings', 'referral_reward_amount')) {
                $table->decimal('referral_reward_amount', 10, 2)->default(100)->after('referral_enabled');
            }
            if (!Schema::hasColumn('settings', 'referred_reward_amount')) {
                $table->decimal('referred_reward_amount', 10, 2)->default(50)->after('referral_reward_amount');
            }
            if (!Schema::hasColumn('settings', 'referral_expiry_days')) {
                $table->integer('referral_expiry_days')->default(30)->after('referred_reward_amount');
            }
            if (!Schema::hasColumn('settings', 'referral_min_order_amount')) {
                $table->decimal('referral_min_order_amount', 10, 2)->default(500)->after('referral_expiry_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
        
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'referral_enabled',
                'referral_reward_amount',
                'referred_reward_amount',
                'referral_expiry_days',
                'referral_min_order_amount',
            ];
            
            $existingColumns = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $existingColumns[] = $column;
                }
            }
            
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
