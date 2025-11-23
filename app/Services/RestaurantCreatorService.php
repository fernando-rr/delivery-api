<?php

namespace App\Services;

use App\DTOs\BaseDTO;
use App\DTOs\RestaurantCreateDTO;
use App\DTOs\RestaurantDTO;
use App\DTOs\RestaurantUpdateDTO;
use App\Models\Restaurant;
use App\Services\SaverService\SaverService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RestaurantCreatorService extends SaverService
{
    public function __construct(
        protected RestaurantUpdaterService $updaterService
    ) {
    }

    public function create(RestaurantCreateDTO $dto): RestaurantDTO
    {
        /** @var RestaurantDTO $result */
        $result = $this->save($dto);

        return $result;
    }

    protected function mapDataToSave(array $attributes): array
    {
        // Placeholder unique db_name to satisfy NOT NULL and UNIQUE constraint
        $attributes['db_name'] = 'tenant_pending_' . uniqid();

        return $attributes;
    }

    protected function saveEntity(array $attributes): BaseDTO
    {
        $restaurant = Restaurant::create($attributes);

        return new RestaurantDTO($restaurant->toArray());
    }

    protected function afterSave()
    {
        return $this
            ->updateRestaurantDbName()
            ->provisionTenantDatabase()
            ->migrateTenantDatabase();
    }

    protected function updateRestaurantDbName(): static
    {
        $id = $this->savedEntity->id;
        $dbName = "tenant_{$id}";

        $dto = new RestaurantUpdateDTO([
            'id' => $id,
            'db_name' => $dbName,
        ]);

        $this->savedEntity = $this->updaterService->update($dto);

        return $this;
    }

    protected function provisionTenantDatabase(): static
    {
        $dbName = $this->savedEntity->db_name;
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        return $this;
    }

    protected function migrateTenantDatabase(): static
    {
        // Configura a conexão 'tenant' para apontar para o novo banco de dados
        Config::set('database.connections.tenant.database', $this->savedEntity->db_name);
        DB::purge('tenant');
        DB::reconnect('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        return $this;
    }
}
