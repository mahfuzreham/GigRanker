<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 40);
            $table->string('method', 20);
            $table->string('status', 20)->default('pending');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('merchant_reference', 80)->unique();
            $table->string('transaction_reference', 120)->nullable();
            $table->string('proof_url', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['method', 'status']);
            $table->unique(['method', 'transaction_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
