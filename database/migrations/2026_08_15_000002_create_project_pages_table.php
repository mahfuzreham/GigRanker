<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('slug', 180);
            $table->string('page_type', 40)->default('service');
            $table->string('title', 255);
            $table->string('meta_description', 320)->nullable();
            $table->longText('content')->nullable();
            $table->string('status', 40)->default('draft')->index();
            $table->timestamps();
            $table->unique(['project_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_pages');
    }
};
