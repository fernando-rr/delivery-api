<?php

namespace Tests\Unit\Services;

use App\DTOs\RestaurantDTO;
use App\DTOs\RestaurantUpdateDTO;
use App\Models\Restaurant;
use App\Services\RestaurantUpdaterService;
use Tests\CentralTestCase;

class RestaurantUpdaterServiceTest extends CentralTestCase
{
    public function test_it_updates_restaurant_successfully(): void
    {
        // Arrange
        $restaurant = Restaurant::create([
            'name' => 'Old Name',
            'slug' => 'old-slug',
            'contact_phone' => '111111111',
            'db_name' => 'tenant_1',
            'active' => true,
        ]);

        $service = $this->app->make(RestaurantUpdaterService::class);

        $dto = new RestaurantUpdateDTO([
            'id' => $restaurant->id,
            'name' => 'New Name',
            'slug' => 'new-slug',
        ]);

        // Act
        $result = $service->update($dto);

        // Assert
        $this->assertInstanceOf(RestaurantDTO::class, $result);
        $this->assertEquals('New Name', $result->name);
        $this->assertEquals('new-slug', $result->slug);
        $this->assertEquals($restaurant->id, $result->id);

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'New Name',
            'slug' => 'new-slug',
            'contact_phone' => '111111111', // Should remain unchanged
            'active' => true,
        ]);
    }

    public function test_it_updates_only_provided_fields(): void
    {
        // Arrange
        $restaurant = Restaurant::create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'contact_phone' => '123456789',
            'db_name' => 'tenant_2',
            'active' => true,
        ]);

        $service = $this->app->make(RestaurantUpdaterService::class);

        // Update only phone
        $dto = new RestaurantUpdateDTO([
            'id' => $restaurant->id,
            'contact_phone' => '987654321',
        ]);

        // Act
        $service->update($dto);

        // Assert
        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'Original Name', // Unchanged
            'contact_phone' => '987654321', // Updated
        ]);
    }

    public function test_it_throws_validation_exception_when_restaurant_not_found(): void
    {
        // Arrange
        $service = $this->app->make(RestaurantUpdaterService::class);
        $nonExistentId = 99999;

        // Assert
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        // Act
        new RestaurantUpdateDTO([
            'id' => $nonExistentId,
            'name' => 'New Name',
        ]);
    }
}
