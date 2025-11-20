<?php

namespace App\DTOs;

use App\Contracts\DTOs\BaseCast;
use App\DTOs\Casts\DateCast;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

abstract class BaseDTO
{
    private ?Collection $attributes = null;

    protected array $casts = [
        'date' => DateCast::class,
    ];

    public function __construct(array $attributes)
    {
        $this->validateAttributes($attributes);
        $this->fill($attributes);
    }

    private function getAttributes(): Collection
    {
        if ($this->attributes === null) {
            $this->attributes = collect();
        }

        return $this->attributes;
    }

    private function castAttributeSet(string $attributeName, mixed $attributeValue): mixed
    {
        /* @var BaseCast $castClass */
        $castClass = $this->getCastClassForAttribute($attributeName);

        if ($castClass) {
            return $castClass::set($attributeValue);
        }

        return $attributeValue;
    }

    private function castAttributeGet(string $attributeName, mixed $attributeValue): mixed
    {
        /* @var BaseCast $castClass */
        $castClass = $this->getCastClassForAttribute($attributeName);

        if ($castClass) {
            return $castClass::get($attributeValue);
        }

        return $attributeValue;
    }

    /**
     * Obtém a classe de cast para um atributo baseado no seu tipo
     */
    private function getCastClassForAttribute(string $attributeName): ?string
    {
        $attributeRule = $this->getAttributeRule($attributeName);

        if (!$attributeRule) {
            return null;
        }

        // Extrai o tipo da regra (ex: 'nullable|date' -> 'date')
        $types = explode('|', $attributeRule);

        foreach ($types as $type) {
            if (isset($this->casts[$type])) {
                return $this->casts[$type];
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getAttributeRule(string $attributeName)
    {
        return $this->getFillableAttributes()->get($attributeName);
    }

    public function __set(string $key, mixed $value): void
    {
        if (!$this->getFillableAttributes()->has($key)) {
            return;
        }

        $transformedValue = $this->transformAttributeValue($key, $value);

        $this->validateAttributes(
            $this->getAttributes()->merge([$key => $transformedValue])->toArray()
        );

        $this->getAttributes()->put($key, $transformedValue);
    }

    public function __get($key)
    {
        return $this->castAttributeGet(
            $key,
            $this->getAttributes()->get($key)
        );
    }

    public function __isset(string $key): bool
    {
        return $this->getAttributes()->has($key);
    }

    public function __unset(string $key): void
    {
        $this->getAttributes()->forget($key);
    }

    public function validateAttributes(array $attributes): void
    {
        $rules = $this->getFillableAttributes()->toArray();
        $validator = Validator::make($attributes, $rules);
        $validator->validate();
    }

    public static function getFillableAttributes(): Collection
    {
        return collect([
            'id' => 'integer',
            'created_at' => 'nullable|date',
            'updated_at' => 'nullable|date',
        ]);
    }

    /**
     * Transforma o valor do atributo aplicando: cast -> mutator -> relação
     */
    private function transformAttributeValue(string $attributeName, mixed $attributeValue): mixed
    {
        // 1. Aplica cast (se existir)
        $attributeValue = $this->castAttributeSet($attributeName, $attributeValue);

        // 2. Aplica mutator customizado (se existir)
        $mutatorMethodName = 'set'.Str::studly($attributeName).'Attribute';
        if (method_exists($this, $mutatorMethodName)) {
            $attributeValue = $this->$mutatorMethodName($attributeValue);
        }

        // 3. Aplica transformação de relação/DTO aninhado (se existir)
        $relationClass = Str::studly($attributeName);
        if (method_exists($this, $relationClass)) {
            $DTOClass = $this->$relationClass();

            if (is_array($attributeValue)) {
                if (Arr::isAssoc($attributeValue)) {
                    return new $DTOClass($attributeValue);
                }

                return collect($attributeValue)
                    ->map(fn ($attributes) => new $DTOClass($attributes));
            }

            return new $DTOClass(...$attributeValue);
        }

        return $attributeValue;
    }

    private function fill(array $attributes)
    {
        $this->attributes = collect($attributes)
            ->only($this->getFillableAttributes()->keys())
            ->map(fn ($attributeValue, $attributeName) => $this->transformAttributeValue($attributeName, $attributeValue)
            );
    }

    /**
     * Serializa um valor para array (lida com DTOs aninhados e Collections)
     */
    private function serializeValue(mixed $value): mixed
    {
        // Serializa DTOs aninhados
        if ($value instanceof self) {
            return $value->toArray();
        }

        // Serializa Collections de DTOs
        if ($value instanceof Collection) {
            return $value->map(fn ($item) => $item instanceof self ? $item->toArray() : $item
            )->toArray();
        }

        return $value;
    }

    public function toArray(): array
    {
        return $this->getAttributes()
            ->map(fn ($attributeValue, $attributeName) => $this->serializeValue(
                $this->castAttributeGet($attributeName, $attributeValue)
            )
            )
            ->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
