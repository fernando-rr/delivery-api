<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class CentralTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Executa migrações do path específico para o banco em memória (sqlite)
        $this->artisan('migrate', [
            '--path' => 'database/migrations/central',
            '--database' => 'sqlite',
        ]);
    }
}
