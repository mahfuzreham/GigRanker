<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_backups', function (Blueprint $table): void {
            $table->id();
            $table->uuid('backup_id')->unique();
            $table->foreignId('deployment_id')->nullable()->constrained('deployments')->nullOnDelete();
            $table->string('environment', 40)->index();
            $table->string('type', 40)->default('database');
            $table->string('status', 30)->default('pending')->index();
            $table->string('path', 2048)->nullable();
            $table->string('storage_disk', 80)->default('local');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_backups');
    }
};
