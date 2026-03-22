<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Modules\Bookkeeping\Enums\AccountType;
use Modules\Bookkeeping\Enums\LineType;
use Modules\Bookkeeping\Models\AccountCategory;
use Modules\Bookkeeping\Models\Ledger;
use Modules\System\Models\Tenant;

trait InteractsWithBookkeeping
{
    protected function createAccountCategory(Tenant $tenant, string $name, AccountType $type, bool $isSystem = false): AccountCategory
    {
        return AccountCategory::create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'type' => $type,
            'is_system' => $isSystem,
        ]);
    }

    protected function createLedger(Tenant $tenant, AccountCategory $category, string $name, string $code): Ledger
    {
        return Ledger::create([
            'tenant_id' => $tenant->id,
            'account_category_id' => $category->id,
            'name' => $name,
            'code' => $code,
        ]);
    }

    /**
     * @return array{type: LineType, amount: float}
     */
    protected function debit(Ledger $ledger, float $amount): array
    {
        return ['ledger_id' => $ledger->id, 'type' => LineType::Debit, 'amount' => $amount];
    }

    /**
     * @return array{type: LineType, amount: float}
     */
    protected function credit(Ledger $ledger, float $amount): array
    {
        return ['ledger_id' => $ledger->id, 'type' => LineType::Credit, 'amount' => $amount];
    }
}
