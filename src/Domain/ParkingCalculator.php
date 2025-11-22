<?php
declare(strict_types=1);

namespace App\Domain;

final class ParkingCalculator
{
    /** @var array<string,ParkingPolicy> */
    private array $policies = [];

    /** @param array<string,ParkingPolicy> $policies */
    public function __construct(array $policies)
    {
        $this->policies = $policies;
    }

    public function calculate(string $vehicleType, string $checkin, string $checkout): float
    {
        $key = strtolower($vehicleType);
        if (!isset($this->policies[$key])) {
            return 0.0;
        }
        $amount = $this->policies[$key]->calculate($checkin, $checkout);
        return max(0.0, $amount);
    }

    /** @return string[] */
    public function supportedTypes(): array
    {
        return array_keys($this->policies);
    }
}
