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
        // Check if the media table exists
        if (!Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('file_name');
                $table->string('mime_type');
                $table->string('path');
                $table->unsignedBigInteger('size');
                $table->timestamps();
            });
        } else {
            // Table exists, check and add any missing columns
            Schema::table('media', function (Blueprint $table) {
                if (!Schema::hasColumn('media', 'name')) {
                    $table->string('name')->nullable();
                }
                if (!Schema::hasColumn('media', 'file_name')) {
                    $table->string('file_name');
                }
                if (!Schema::hasColumn('media', 'mime_type')) {
                    $table->string('mime_type');
                }
                if (!Schema::hasColumn('media', 'path')) {
                    $table->string('path');
                }
                if (!Schema::hasColumn('media', 'size')) {
                    $table->unsignedBigInteger('size');
                }
                if (!Schema::hasColumn('media', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};