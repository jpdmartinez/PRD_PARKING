<?php
declare(strict_types=1);

namespace App\Domain;

interface VehicleRepository
{
    /** @return Vehicle[] */
    public function all(): array;

    public function find(int $id): ?Vehicle;

    public function create(Vehicle $vehicle): Vehicle;

    public function update(Vehicle $vehicle): void;

    public function delete(int $id): void;
}
