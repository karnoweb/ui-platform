<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ui_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_key', 128)->index();
            $table->string('page_key', 160);
            $table->string('type', 128);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('draft_revision')->default(1);
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->timestamps();

            $table->unique(['tenant_key', 'page_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ui_pages');
    }
};
