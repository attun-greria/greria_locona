<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 監査ログ（ADM-015 / SEC-007 / 10-1 audit_logs）。
 * 公開・非公開・削除・CSV取込・権限変更などの主要操作を記録する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 64);                  // created / updated / deleted / published / ...
            $table->string('target_type')->nullable();     // 対象モデル
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('changes')->nullable();           // 変更内容
            $table->string('ip_address', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['target_type', 'target_id']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
