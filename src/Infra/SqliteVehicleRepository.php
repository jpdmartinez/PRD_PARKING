<?php
declare(strict_types=1);

namespace App\Infra;

use App\Domain\Vehicle;
use App\Domain\VehicleRepository;
use PDO;

final class SqliteVehicleRepository implements VehicleRepository
{
    public function __construct(private PDO $pdo)
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** @return Vehicle[] */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM vehicles ORDER BY id DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map([$this, 'mapRow'], $rows);
    }

    public function find(int $id): ?Vehicle
    {
        $stmt = $this->pdo->prepare('SELECT * FROM vehicles WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? $this->mapRow($r) : null;
    }

    public function create(Vehicle $vehicle): Vehicle
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO vehicles (plate, vehicle_type, base_rate, amount, checkin, checkout)
             VALUES (:plate, :vehicle_type, :base_rate, :amount, :checkin, :checkout)'
        );
        $stmt->execute([
            ':plate' => $vehicle->plate(),
            ':vehicle_type' => $vehicle->vehicleType(),
            ':base_rate' => $vehicle->baseRate(),
            ':amount' => $vehicle->amount(),
            ':checkin' => $vehicle->checkin(),
            ':checkout' => $vehicle->checkout(),
        ]);
        $id = (int)$this->pdo->lastInsertId();
        return $vehicle->withId($id);
    }

    public function update(Vehicle $vehicle): void
    {
        if ($vehicle->id() === null) {
            throw new \InvalidArgumentException('ID obrigatório para update.');
        }
        $stmt = $this->pdo->prepare(
            'UPDATE vehicles
             SET plate=:plate, vehicle_type=:vehicle_type, base_rate=:base_rate, amount=:amount, checkin=:checkin, checkout=:checkout
             WHERE id=:id'
        );
        $stmt->execute([
            ':id' => $vehicle->id(),
            ':plate' => $vehicle->plate(),
            ':vehicle_type' => $vehicle->vehicleType(),
            ':base_rate' => $vehicle->baseRate(),
            ':amount' => $vehicle->amount(),
            ':checkin' => $vehicle->checkin(),
            ':checkout' => $vehicle->checkout()
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM vehicles WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    /** @param array<string,mixed> $r */
    private function mapRow(array $r): Vehicle
    {
        return new Vehicle(
            id: (int)$r['id'],
            plate: (string)$r['plate'],
            vehicleType: (string)$r['vehicle_type'],
            baseRate: (float)$r['base_rate'],
            amount: (float)$r['amount'],
            checkin: (string)($r['checkin'] ?? ''),
            checkout: (string)($r['checkout'] ?? '')
        );
    }
}
