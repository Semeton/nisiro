<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_categories', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->uuid('tenant_id')->index();
            $blueprint->string('name');
            $blueprint->string('type'); // assets, liabilities, equity, revenue, expense
            $blueprint->boolean('is_system')->default(false);
            $blueprint->timestamps();

            $blueprint->unique(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_categories');
    }
};
