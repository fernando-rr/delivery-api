<?php

namespace Tests\Unit\Services;

use App\DTOs\RestaurantCreateDTO;
use App\DTOs\RestaurantDTO;
use App\Services\RestaurantCreatorService;
use Mockery\MockInterface;
use Tests\CentralTestCase;

class RestaurantCreatorServiceTest extends CentralTestCase
{
    public function test_it_creates_restaurant_and_provisions_database(): void
    {
        // Arrange
        // Partial mock to intercept only the database provisioning call
        // allowing the rest of the service (Eloquent, DTOs) to work with the real (test) DB.
        $service = $this->partialMock(RestaurantCreatorService::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('provisionTenantDatabase')
                ->once()
                ->withArgs(function ($dbName) {
                    return str_starts_with($dbName, 'tenant_');
                });
        });

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
            'db_name' => 'tenant_'.$result->id,
        ]);
        $this->assertEquals('tenant_'.$result->id, $result->db_name);
    }
}
