<?php

namespace App\Services;

use App\DTOs\BaseDTO;
use App\DTOs\RestaurantCreateDTO;
use App\DTOs\RestaurantDTO;
use App\Models\Restaurant;
use App\Services\SaverService\SaverService;
use Illuminate\Support\Facades\DB;

class RestaurantCreatorService extends SaverService
{
    public function create(RestaurantCreateDTO $dto): RestaurantDTO
    {
        /** @var RestaurantDTO $result */
        $result = $this->save($dto);

        return $result;
    }

    protected function mapDataToSave(array $attributes): array
    {
        // Placeholder unique db_name to satisfy NOT NULL and UNIQUE constraint
        $attributes['db_name'] = 'tenant_pending_'.uniqid();

        return $attributes;
    }

    protected function saveEntity(array $attributes): BaseDTO
    {
        $restaurant = Restaurant::create($attributes);

        return new RestaurantDTO($restaurant->toArray());
    }

    protected function afterSave()
    {
        $id = $this->savedEntity->id;
        $dbName = "tenant_{$id}";

        // Update db_name in database
        Restaurant::where('id', $id)->update(['db_name' => $dbName]);

        // Update the DTO in memory so the return value is correct
        $this->savedEntity->db_name = $dbName;

        $this->provisionTenantDatabase($dbName);

        return $this;
    }

    protected function provisionTenantDatabase(string $dbName): void
    {
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
}
