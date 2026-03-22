<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->uuid('tenant_id')->index();
            $blueprint->uuid('account_category_id')->index();
            $blueprint->string('name');
            $blueprint->string('code');
            $blueprint->text('description')->nullable();
            $blueprint->timestamps();

            $blueprint->unique(['tenant_id', 'code']);

            $blueprint->foreign('account_category_id')
                ->references('id')
                ->on('account_categories')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
