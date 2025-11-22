<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantsMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {--fresh : Wipe the database before running migrations} {--seed : Seed the database after migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration for all tenants...');

        try {
            $restaurants = Restaurant::where('active', true)->get();
        } catch (\Exception $e) {
            $this->error('Could not fetch restaurants: ' . $e->getMessage());

            return 1;
        }

        if ($restaurants->isEmpty()) {
            $this->info('No active tenants found.');

            return 0;
        }

        foreach ($restaurants as $restaurant) {
            $dbName = 'tenant_' . $restaurant->id;
            $this->info("Migrating tenant: {$restaurant->name} (DB: {$dbName})");

            try {
                // Configure tenant database connection
                Config::set('database.connections.tenant.database', $dbName);
                DB::purge('tenant');
                DB::reconnect('tenant');

                $options = [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ];

                $command = 'migrate';

                if ($this->option('fresh')) {
                    $command = 'migrate:fresh';
                    if ($this->option('seed')) {
                        $options['--seed'] = true;
                    }
                }

                Artisan::call($command, $options);

                $this->info(Artisan::output());

            } catch (\Exception $e) {
                $this->error("Failed to migrate tenant {$restaurant->name} ({$dbName}): " . $e->getMessage());
            }
        }

        $this->info('All tenant migrations completed.');

        return 0;
    }
}
