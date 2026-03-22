<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('users', function (Blueprint $blueprint): void {
                $blueprint->uuid('tenant_id')->nullable()->after('id')->index();
                $blueprint->foreign('tenant_id')
                    ->references('id')
                    ->on('tenants')
                    ->onDelete('cascade');
            });

            return;
        }

        Schema::table('users', function (Blueprint $blueprint): void {
            // Drop traditional ID and re-add as UUID for databases that support this alteration.
            $blueprint->dropColumn('id');
        });

        Schema::table('users', function (Blueprint $blueprint): void {
            $blueprint->uuid('id')->primary()->first();
            $blueprint->uuid('tenant_id')->nullable()->after('id')->index();
            $blueprint->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('users', function (Blueprint $blueprint) use ($driver): void {
            $blueprint->dropForeign(['tenant_id']);
            $blueprint->dropColumn('tenant_id');

            if ($driver !== 'sqlite') {
                $blueprint->dropColumn('id');
            }
        });

        if ($driver !== 'sqlite') {
            Schema::table('users', function (Blueprint $blueprint): void {
                $blueprint->id()->first();
            });
        }
    }
};
