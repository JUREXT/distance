<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use TeamChallengeApps\Distance\Distance;
use PHPUnit\Framework\TestCase;

final class DistanceTest extends TestCase
{
    public function test_it_creates_distance(): void
    {
        $distance = new Distance(1);
        $distance = $this->loadConfig($distance);

        $this->assertSame(1.0, $distance->value);
        $this->assertSame('meters', $distance->unit);
    }

    public function test_it_converts_to_kilometers(): void
    {
        $map = new Collection([
            1000 => 1.0,
            1500 => 1.5,
            2750 => 2.75,
        ]);

        foreach ($map as $meters => $km) {
            $distance = new Distance((float) $meters);
            $distance = $this->loadConfig($distance)->toKilometers();

            $this->assertSame($km, $distance->value);
        }
    }

    public function test_it_converts_from_kilometers(): void
    {
        $map = new Collection([
            1.0 => 1000,
            1.5 => 1500,
            2.75 => 2750,
        ]);

        foreach ($map as $km => $meters) {
            $distance = new Distance((float) $km, 'kilometers');
            $distance = $this->loadConfig($distance)->toMeters();

            $this->assertSame($meters, $distance->value);
        }
    }

    public function test_it_converts_to_miles(): void
    {
        $map = new Collection([
            1000 => 0.62,
            1500 => 0.93,
            2750 => 1.71,
        ]);

        foreach ($map as $meters => $miles) {
            $distance = new Distance((float) $meters);
            $distance = $this->loadConfig($distance->toMiles());

            $this->assertSame($miles, $distance->round());
        }
    }

    public function test_it_converts_to_steps(): void
    {
        $map = new Collection([
            1 => 1,
            1000 => 1458,
            1500 => 2187,
            2750 => 4010,
        ]);

        foreach ($map as $meters => $steps) {
            $distance = new Distance((float) $meters);
            $distance = $this->loadConfig($distance->toSteps());

            $this->assertSame($steps, $distance->round());
        }
    }

    public function test_it_formats_to_string_automatically(): void
    {
        $meters = 10000;

        $distance = new Distance((float) $meters);
        $distance = $this->loadConfig($distance);

        $string = number_format($meters, 2, '.', ',');

        $this->assertSame($string, (string) $distance);
    }

    public function test_it_formats_to_steps_string_without_decimals(): void
    {
        $meters = 10000;

        $distance = new Distance((float) $meters, 'footsteps');
        $distance = $this->loadConfig($distance);

        $string = number_format($meters, 0, '.', ',');

        $this->assertSame($string, (string) $distance);
    }

    public function test_it_formats_to_steps_string_with_suffix(): void
    {
        $meters = 10000;

        $distance = new Distance((float) $meters, 'footsteps');
        $distance = $this->loadConfig($distance);

        $string = number_format($meters, 0, '.', ',') . ' steps';

        $this->assertSame($string, $distance->toStringWithSuffix());
    }

    public function test_it_allows_global_distance_function(): void
    {
        $distance = new Distance(1000);
        $helper = distance_value(1000);

        $this->assertEquals($distance, $helper);
    }

    public function test_it_converts_to_unit_value(): void
    {
        $map = new Collection([
            1000 => 1.0,
            1500 => 1.5,
            2750 => 2.75,
        ]);

        foreach ($map as $meters => $km) {
            $distance = new Distance((float) $meters);
            $distance = $this->loadConfig($distance);

            $this->assertSame($km, $distance->asUnit('kilometers'));
        }
    }

    public function test_it_calculates_percentages(): void
    {
        $distance = $this->loadConfig(new Distance(250));
        $total = $this->loadConfig(new Distance(1000));

        $percentage = $distance->percentageOf($total);

        $this->assertSame(25, $percentage);
    }

    public function test_it_overflows_percentages_by_default(): void
    {
        $distance = $this->loadConfig(new Distance(1500));
        $total = $this->loadConfig(new Distance(1000));

        $percentage = $distance->percentageOf($total);

        $this->assertSame(150, $percentage);
    }

    public function test_it_caps_percentage_at_100(): void
    {
        $distance = $this->loadConfig(new Distance(1500));
        $total = $this->loadConfig(new Distance(1000));

        $percentage = $distance->percentageOf($total, false);

        $this->assertSame(100, $percentage);
    }

    public function test_it_decrements_distance(): void
    {
        $distance = $this->loadConfig(new Distance(1500));
        $subtract = $this->loadConfig(new Distance(500));

        $distance->decrement($subtract);

        $this->assertSame(1000.0, $distance->value);
    }

    public function test_it_stays_clean_after_copying(): void
    {
        $distance = $this->loadConfig(new Distance(1500));
        $subtract = $this->loadConfig(new Distance(500));

        $after = $distance->copy()->decrement($subtract);

        $this->assertSame(1500.0, $distance->value);
        $this->assertSame(1000.0, $after->value);
    }

    protected function loadConfig(Distance $distance): Distance
    {
        $config = require __DIR__ . '/../src/config/config.php';

        return $distance->setConfig($config);
    }
}
