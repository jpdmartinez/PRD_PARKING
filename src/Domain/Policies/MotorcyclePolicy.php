<?php
declare(strict_types=1);

namespace App\Domain\Policies;

use App\Domain\ParkingPolicy;

final class MotorcyclePolicy implements ParkingPolicy
{
    private const HOURLY_RATE = 3.0;

    public function calculate(string $checkin, string $checkout): float
    {
        $checkinDate = \DateTime::createFromFormat(\DateTime::ATOM, $checkin);
        $checkoutDate = \DateTime::createFromFormat(\DateTime::ATOM, $checkout);
        
        if ($checkinDate === false || $checkoutDate === false) {
            return 0.0;
        }
        
        $interval = $checkinDate->diff($checkoutDate);

        $hours = (int) ceil(
            ($interval->days * 24) +
            $interval->h +
            ($interval->i / 60) +
            ($interval->s / 3600)
        );

        return $hours * self::HOURLY_RATE;
    }

    public function getHourlyRate(): float
    {
        return self::HOURLY_RATE;
    }
}

