<?php

declare(strict_types=1);

namespace App\Domain;

final class Vehicle
{
    public function __construct(
        private ?int $id,
        private string $plate,
        private string $vehicleType,
        private float $baseRate,
        private float $amount,
        private string $checkin,
        private string $checkout
    ){}

    public function hasCheckin(): bool
    {
        return $this->checkin !== '';
    }

    public function hasCheckout(): bool
    {
        return $this->checkout !== '';
    }

    public function id(): ?int {return $this->id;}
    public function plate(): string {return $this->plate;}
    public function vehicleType(): string {return $this->vehicleType;}
    public function baseRate(): float {return $this->baseRate;}
    public function amount(): float {return $this->amount;}
    public function checkin(): string {return $this->checkin;}
    public function checkout(): string {return $this->checkout;}

    public function withId(int $id): self
    {
        return new self($id, $this->plate, $this->vehicleType, $this->baseRate, $this->amount, $this->checkin, $this->checkout);
    }
}