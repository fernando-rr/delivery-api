<?php

namespace App\Services\SaverService;

use App\DTOs\BaseDTO;
use Exception;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class SaverService
{
    protected $payload;

    protected $logWithPayload = false;

    protected $hiddenPayload = [];

    protected ?BaseDTO $savedEntity = null;

    public function save(BaseDTO $dto): BaseDTO
    {
        $attributes = $dto->toArray();
        $this->payload = $attributes;

        return $this->request(function () use ($attributes) {
            $mappedAttributes = $this->mapDataToSave($attributes);

            $this->beforeSave($mappedAttributes);
            $this->savedEntity = $this->saveEntity($mappedAttributes);
            $this->afterSave();

            return $this->savedEntity;
        });
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    protected function request(callable $callback)
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * @return static
     */
    protected function beforeSave(array $attributes)
    {
        return $this;
    }

    protected function handleException(Throwable $exception): void
    {
        if (
            ($exception instanceof ValidationException) ||
            ($exception->getCode() === 422)
        ) {
            throw $exception;
        }

        if (str_contains($exception->getMessage(), 'Not Found')) {
            throw $exception;
        }

        throw new Exception('Unable to save.', 422);
    }

    protected function getErrorLogMessage(Throwable $exception): string
    {
        return $exception->getMessage();
    }

    protected function getErrorLogData(Throwable $exception): array
    {
        $logData = [
            'source' => get_class($this)
        ];

        if ($this->logWithPayload) {
            $logData['payload'] = json_encode($this->payload);
        }

        return $logData;
    }

    protected function mapDataToSave(array $attributes): array
    {
        return $attributes;
    }

    abstract protected function saveEntity(array $attributes): BaseDTO;

    /**
     * @return static
     */
    protected function afterSave()
    {
        return $this;
    }

    /**
     * @return static
     */
    protected function saveLogs()
    {
        return $this;
    }
}


