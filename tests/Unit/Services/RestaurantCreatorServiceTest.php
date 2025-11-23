<?php

namespace Tests\Unit\Services;

use App\DTOs\RestaurantCreateDTO;
use App\DTOs\RestaurantDTO;
use App\Services\RestaurantCreatorService;
use App\Services\RestaurantUpdaterService;
use Tests\CentralTestCase;

class RestaurantCreatorServiceTest extends CentralTestCase
{
    public function test_it_creates_restaurant_and_provisions_database(): void
    {
        // Arrange
        $updaterService = $this->app->make(RestaurantUpdaterService::class);

        $service = \Mockery::mock(RestaurantCreatorService::class, [$updaterService])->makePartial();
        $service->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('provisionTenantDatabase')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        $service->shouldReceive('migrateTenantDatabase')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        // Use the mocked service directly.

        $dto = new RestaurantCreateDTO([
            'name' => 'Test Restaurant',
            'contact_phone' => '123456789',
            'slug' => 'test-restaurant',
            'domain' => 'test.delivery.local',
        ]);

        // Act
        $result = $service->create($dto);

        // Assert
        $this->assertInstanceOf(RestaurantDTO::class, $result);
        $this->assertDatabaseHas('restaurants', [
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'db_name' => 'tenant_' . $result->id,
        ]);
        $this->assertEquals('tenant_' . $result->id, $result->db_name);
    }
}
