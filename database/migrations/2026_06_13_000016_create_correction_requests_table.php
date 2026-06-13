<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 修正・削除依頼（ADM-014 / SEC-012）。
 * 自治体・主催者からの修正、削除依頼を記録し対応状況を管理する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requester_name')->nullable();
            $table->string('requester_email')->nullable();
            $table->enum('type', ['correction', 'deletion', 'other'])->default('correction');
            $table->text('message');
            // open / in_progress / resolved / rejected
            $table->enum('status', ['open', 'in_progress', 'resolved', 'rejected'])->default('open');
            $table->text('admin_note')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
