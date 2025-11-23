<?php

namespace App\Services;

use App\DTOs\BaseDTO;
use App\DTOs\RestaurantDTO;
use App\DTOs\RestaurantUpdateDTO;
use App\Models\Restaurant;
use App\Services\SaverService\SaverService;

class RestaurantUpdaterService extends SaverService
{
    public function update(RestaurantUpdateDTO $dto): RestaurantDTO
    {
        /** @var RestaurantDTO $result */
        $result = $this->save($dto);

        return $result;
    }

    protected function saveEntity(array $attributes): BaseDTO
    {
        $restaurant = Restaurant::findOrFail($attributes['id']);

        $dataToUpdate = collect($attributes)->except(['id'])->toArray();

        $restaurant->update($dataToUpdate);

        return new RestaurantDTO($restaurant->toArray());
    }
}
