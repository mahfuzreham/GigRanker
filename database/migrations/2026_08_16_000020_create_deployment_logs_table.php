<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deployment_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->string('status', 30);
            $table->string('repository')->nullable();
            $table->string('branch')->nullable();
            $table->string('commit_sha', 64)->nullable();
            $table->string('commit_message', 500)->nullable();
            $table->text('details')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
            $table->index(['action', 'status']);
            $table->index(['repository', 'branch']);
            $table->index('commit_sha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_logs');
    }
};
