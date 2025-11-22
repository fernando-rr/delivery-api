<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantsArtisanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:artisan {tenant_id} {artisan_cmd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run any artisan command for a specific tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $artisanCmd = $this->argument('artisan_cmd');

        $this->info("Preparing to run '{$artisanCmd}' for tenant ID: {$tenantId}...");

        // Ensure we are connected to central to get restaurant
        DB::reconnect('mysql');
        
        $restaurant = Restaurant::find($tenantId);

        if (!$restaurant) {
            $this->error("Tenant with ID {$tenantId} not found.");
            return 1;
        }

        $dbName = 'tenant_' . $restaurant->id;
        
        try {
            // Configure tenant database connection
            Config::set('database.connections.tenant.database', $dbName);
            DB::purge('tenant');
            DB::reconnect('tenant');

            $this->info("Connected to {$dbName}. Executing command...");

            // We need to append --database=tenant if it's a migration command and not present
            if (str_contains($artisanCmd, 'migrate') && !str_contains($artisanCmd, '--database')) {
                 $artisanCmd .= ' --database=tenant';
            }
             // We need to append path if it's a migration command and not present
            if (str_contains($artisanCmd, 'migrate') && !str_contains($artisanCmd, '--path')) {
                 $artisanCmd .= ' --path=database/migrations/tenant';
            }

            Artisan::call($artisanCmd, [], $this->output);
            
            $this->info("Command completed successfully.");

        } catch (\Exception $e) {
            $this->error("Failed to execute command for tenant {$restaurant->name} ({$dbName}): " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

