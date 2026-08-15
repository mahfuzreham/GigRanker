<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('gig_url', 2048);
            $table->string('gig_title', 255)->nullable();
            $table->text('gig_description')->nullable();
            $table->string('service_category', 120)->nullable();
            $table->string('target_country', 120)->nullable();
            $table->string('target_city', 120)->nullable();
            $table->json('keywords')->nullable();
            $table->string('brand_name', 160)->nullable();
            $table->string('fiverr_profile_url', 2048)->nullable();
            $table->string('github_url', 2048)->nullable();
            $table->string('status', 40)->default('draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
