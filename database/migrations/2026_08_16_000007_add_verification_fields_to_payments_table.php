<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->foreignId('verified_by_user_id')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('verified_by_user_id');
            $table->index(['status', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropForeign(['verified_by_user_id']);
            $table->dropIndex(['status', 'reviewed_at']);
            $table->dropColumn(['verified_by_user_id', 'reviewed_at']);
        });
    }
};
