<?php
declare(strict_types=1);

namespace App\Domain;

interface ParkingPolicy
{
    public function calculate(string $checkin, string $checkout): float;
    
    public function getHourlyRate(): float;
}
