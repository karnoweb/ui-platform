<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_ui_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_key', 128)->unique();
            $table->string('theme_key', 128);
            $table->string('preset_key', 128)->nullable();
            $table->json('overrides')->nullable();
            $table->unsignedBigInteger('draft_revision')->default(1);
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_ui_configs');
    }
};
