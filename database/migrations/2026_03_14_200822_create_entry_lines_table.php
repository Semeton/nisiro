<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_lines', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary();
            $blueprint->uuid('tenant_id')->index();
            $blueprint->uuid('entry_id')->index();
            $blueprint->uuid('ledger_id')->index();
            $blueprint->string('type'); // debit, credit
            $blueprint->decimal('amount', 15, 2);
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();

            $blueprint->foreign('entry_id')
                ->references('id')
                ->on('entries')
                ->onDelete('cascade');

            $blueprint->foreign('ledger_id')
                ->references('id')
                ->on('ledgers')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_lines');
    }
};
