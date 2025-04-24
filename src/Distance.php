<?php

declare(strict_types=1);

namespace TeamChallengeApps\Distance;

use Illuminate\Support\Arr;
use Exception;

class Distance
{
    public float $value;
    public string $unit;

    private ?array $config = null;

    /* Constructors */

    public function __construct(float|int $value, string $unit = 'meters', ?array $config = null)
    {
        $this->setDistance($value, $unit);
        if (!is_null($config)) {
            $this->config = $config;
        }
    }

    public static function make(float|int $distance, string $unit = 'meters', ?array $config = null): static
    {
        return new static($distance, $unit, $config);
    }

    public static function fromMeters(float|int $distance): static
    {
        return new static($distance, 'meters');
    }

    public static function fromKilometers(float|int $distance): static
    {
        return new static($distance, 'kilometers');
    }

    public static function fromMiles(float|int $distance): static
    {
        return new static($distance, 'miles');
    }

    public static function fromFootsteps(float|int $distance): static
    {
        return new static($distance, 'footsteps');
    }

    public static function fromSteps(float|int $distance): static
    {
        return static::fromFootsteps($distance);
    }

    public function copy(): static
    {
        return new static($this->value, $this->unit, $this->config);
    }

    /* Getters and Setters */

    public function setValue(float|int $value, string $unit): static
    {
        $this->value = (float) $value;
        $this->unit = $unit;

        return $this;
    }

    public function setDistance(float|int $distance, string $unit): static
    {
        return $this->setValue($distance, $unit);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getDistance(): float
    {
        return $this->getValue();
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    /* Helpers */

    public function isZero(): bool
    {
        return $this->value == 0.0;
    }

    public function isEmpty(): bool
    {
        return $this->isZero();
    }

    public function percentageOf(Distance $distance, bool $overflow = true): int|string
    {
        $percentage = $this->asBase() / $distance->asBase();

        if ($overflow) {
            return (int) round($percentage * 100);
        }

        if ($percentage >= 1) {
            return '100';
        }

        if ($percentage >= 0.99) {
            return '99';
        }

        return (int) round($percentage * 100);
    }

    public function lt(Distance $distance): bool
    {
        return $this->asBase() < $distance->asBase();
    }

    public function lte(Distance $distance): bool
    {
        return $this->asBase() <= $distance->asBase();
    }

    public function gt(Distance $distance): bool
    {
        return $this->asBase() > $distance->asBase();
    }

    public function gte(Distance $distance): bool
    {
        return $this->asBase() >= $distance->asBase();
    }

    public function isUnit(string $unit): bool
    {
        return $this->unit === $unit;
    }

    public function isMeters(): bool
    {
        return $this->isUnit('meters');
    }

    public function isKilometers(): bool
    {
        return $this->isUnit('kilometers');
    }

    public function isFootsteps(): bool
    {
        return $this->isUnit('footsteps');
    }

    public function isMiles(): bool
    {
        return $this->isUnit('miles');
    }

    public function isSteps(): bool
    {
        return $this->isFootsteps();
    }

    public function isBaseUnit(): bool
    {
        return $this->isMeters();
    }

    public function units(): array
    {
        return array_keys($this->config('units'));
    }

    public function getDecimals(): int
    {
        $key = 'units.' . $this->unit . '.decimals';

        return (int) $this->config($key, 2);
    }

    public function getSuffix(): ?string
    {
        $key = 'units.' . $this->unit . '.suffix';

        return $this->config($key);
    }

    public function getMeasurement(?string $unit = null): float
    {
        $unit = $unit ?? $this->unit;
        $key = 'units.' . $unit . '.unit';

        $measurement = $this->config($key);

        if (!$measurement) {
            throw new Exception('Measurement ' . $unit . ' not found');
        }

        return (float) $measurement;
    }

    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function config(?string $key = null, mixed $fallback = null): mixed
    {
        if (is_null($this->config)) {
            $this->loadConfig();
        }

        if (is_null($key)) {
            return $this->config;
        }

        return Arr::get($this->config, $key, $fallback);
    }

    protected function loadConfig(): void
    {
        if (!function_exists('config')) {
            throw new Exception('Unable to auto load config');
        }

        $this->config = config('distance');
    }

    /* Modifiers */

    public function decrement(Distance $distance): static
    {
        $value = new static($this->asBase() - $distance->asBase(), 'meters', $this->config);

        $this->value = $value->asUnit($this->unit);

        return $this;
    }

    public function increment(Distance $distance): static
    {
        $value = new static($this->asBase() + $distance->asBase(), 'meters', $this->config);

        $this->value = $value->asUnit($this->unit);

        return $this;
    }

    /* Conversions */

    public function toBase(): static
    {
        return $this->isMeters() ? $this : $this->convertTo('meters');
    }

    public function base(): static
    {
        return $this->toBase();
    }

    public function asBase(): float
    {
        return $this->isMeters() ? $this->value : $this->asUnit('meters');
    }

    public function asUnit(string $unit): float
    {
        $from = $this->getMeasurement($this->unit);
        $to = $this->getMeasurement($unit);

        return $this->value * $to * (1 / $from);
    }

    public function convertTo(string $unit): static
    {
        $distance = $this->asUnit($unit);

        return new static($distance, $unit, $this->config);
    }

    public function toMeters(): static
    {
        return $this->convertTo('meters');
    }

    public function toKilometers(): static
    {
        return $this->convertTo('kilometers');
    }

    public function toMiles(): static
    {
        return $this->convertTo('miles');
    }

    public function toFootsteps(): static
    {
        return $this->convertTo('footsteps');
    }

    public function toSteps(): static
    {
        return $this->toFootsteps();
    }

    /* Formatting */

    public function only(array $units): DistanceCollection
    {
        $distance = new DistanceCollection();

        foreach ($this->units() as $unit) {
            if (in_array($unit, $units, true)) {
                $distance->put($unit, $this->convertTo($unit));
            }
        }

        return $distance;
    }

    public function all(): DistanceCollection
    {
        $distance = new DistanceCollection();

        foreach ($this->units() as $unit) {
            $distance->put($unit, $this->convertTo($unit));
        }

        return $distance;
    }

    public function toArray(): array
    {
        return $this->all()->toArray();
    }

    public function toRoundedArray(?array $units = null): array
    {
        $distance = is_null($units) ? $this->all() : $this->only($units);

        return $distance->map(function (Distance $unit): float {
            return $unit->round();
        })->toArray();
    }

    public function round(): float
    {
        return round($this->value, $this->getDecimals());
    }

    public function toString(): string
    {
        $comma = $this->config('format.comma', true) ? ',' : '';
        $suffix = $this->config('format.suffix', false) ? ' ' . $this->getSuffix() : '';

        return number_format($this->value, $this->getDecimals(), '.', $comma) . $suffix;
    }

    public function toStringWithSuffix(): string
    {
        $comma = $this->config('format.comma', true) ? ',' : '';
        $suffix = ' ' . $this->getSuffix();

        return number_format($this->value, $this->getDecimals(), '.', $comma) . $suffix;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function __get(string $property): mixed
    {
        if (in_array($property, $this->units(), true)) {
            $unit = $property;

            return $this->convertTo($unit)->distance;
        }

        return null;
    }
}
