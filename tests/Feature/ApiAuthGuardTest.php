<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiAuthGuardTest extends TestCase
{
    public function test_protected_api_endpoints_require_authentication(): void
    {
        $endpoints = [
            '/api/maintenance/machines',
            '/api/hr/employees',
            '/api/compliance/documents',
            '/api/integrations/accounting/exports',
            '/api/reports/definitions',
            '/api/procurement/purchase-invoices',
            '/api/sales/returns',
        ];

        foreach ($endpoints as $endpoint) {
            $this->getJson($endpoint)->assertStatus(401);
        }
    }
}
