<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Reporting\Services\ReportExportService;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private ReportExportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ReportExportService::class);
    }

    public function test_exports_to_csv_with_correct_headers(): void
    {
        $headers = ['Ledger', 'Code', 'Amount'];
        $rows = [
            ['Sales Revenue', '4000', '5000.00'],
            ['COGS', '5000', '2000.00'],
        ];

        $response = $this->service->toCsv($headers, $rows, 'test-report.csv');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('test-report.csv', $response->headers->get('Content-Disposition'));

        // Capture the streamed output
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $lines = array_filter(explode("\n", trim($content)));
        $this->assertCount(3, $lines); // header + 2 rows
        $this->assertStringContainsString('Ledger', $lines[0]);
        $this->assertStringContainsString('Sales Revenue', $lines[1]);
        $this->assertStringContainsString('COGS', $lines[2]);
    }

    public function test_csv_handles_empty_data(): void
    {
        $headers = ['Name', 'Value'];
        $rows = [];

        $response = $this->service->toCsv($headers, $rows, 'empty.csv');

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $lines = array_filter(explode("\n", trim($content)));
        $this->assertCount(1, $lines); // header only
    }
}
