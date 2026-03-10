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
        Schema::table('referrals', function (Blueprint $table) {
            // Drop foreign key constraints first if they exist
            $table->dropForeign(['referrer_id']);
            $table->dropForeign(['referred_id']);
        });

        Schema::table('referrals', function (Blueprint $table) {
            // Drop unwanted columns
            $table->dropColumn([
                'referrer_id',
                'referred_id',
                'reward_amount',
                'referred_reward_amount',
                'reward_claimed',
                'referred_reward_claimed',
                'completed_at',
                'expires_at',
                'notes',
            ]);
        });

        Schema::table('referrals', function (Blueprint $table) {
            // Modify status column to use new values
            $table->string('status')->default('active')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->foreignId('referrer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('reward_amount', 10, 2)->default(0);
            $table->decimal('referred_reward_amount', 10, 2)->default(0);
            $table->boolean('reward_claimed')->default(false);
            $table->boolean('referred_reward_claimed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
        });
    }
};
