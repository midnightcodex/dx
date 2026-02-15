<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Attach SQLite databases for each logical schema before migrations run.
     * This lets schema-qualified table names (e.g. auth.users) work in tests.
     */
    protected function beforeRefreshingDatabase()
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $schemas = [
            'auth',
            'shared',
            'inventory',
            'manufacturing',
            'procurement',
            'sales',
        ];

        foreach ($schemas as $schema) {
            try {
                DB::statement("ATTACH DATABASE ':memory:' AS \"{$schema}\"");
            } catch (\Throwable $e) {
                if (!str_contains(strtolower($e->getMessage()), 'already in use')) {
                    throw $e;
                }
            }
        }
    }
}
