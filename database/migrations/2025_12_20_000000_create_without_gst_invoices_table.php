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
        if (!Schema::hasTable('without_gst_invoices')) {
            Schema::create('without_gst_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('session_id')->nullable();
                $table->decimal('total_amount', 10, 2);
                $table->json('invoice_data');
                $table->string('status')->default('Draft');
                $table->unsignedBigInteger('original_invoice_id')->nullable();
                $table->timestamps();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('invoice_number');
                $table->index('user_id');
                $table->index('session_id');
                $table->index('original_invoice_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('without_gst_invoices');
    }
};
