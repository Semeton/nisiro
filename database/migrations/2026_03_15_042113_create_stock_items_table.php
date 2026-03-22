<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->uuid('tenant_id')->index();
            $blueprint->string('name');
            $blueprint->string('sku');
            $blueprint->decimal('cost_price', 15, 2)->default(0);
            $blueprint->decimal('selling_price', 15, 2)->default(0);
            $blueprint->decimal('quantity_on_hand', 10, 2)->default(0);
            $blueprint->decimal('reorder_level', 10, 2)->nullable();
            $blueprint->timestamps();

            $blueprint->unique(['tenant_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
