<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Vehicle;
use App\Domain\VehicleRepository;
use App\Domain\VehicleValidator;
use App\Domain\ParkingCalculator;

final class ParkingService
{
    public function __construct(
        private VehicleRepository $repository,
        private VehicleValidator $validator,
        private ParkingCalculator $calculator
    ) {}
    
    /**
     * @param array{
     *   plate?:string, vehicle_type?:string,
     *   checkin?:string, checkout?:string
     * } $input
     * @return array{ok:bool, errors?:string[], id?:int}
     */
    public function create(array $input): array
    {
        $errors = $this->validator->validate($input);
        if($errors !== []){
            return ['ok' => false, 'errors' => $errors];
        }
        
        $plate = strtoupper(trim((string)$input['plate']));
        $vehicleType = strtolower(trim((string)$input['vehicle_type']));

        $vehicle = new Vehicle(
            id: null,
            plate: $plate,
            vehicleType: $vehicleType,
            baseRate: 0.0,
            amount: 0.0,
            checkin: '',
            checkout: ''
        );

        $created = $this->repository->create($vehicle);
        return ['ok' => true, 'id' => $created->id()];
    }

    /**
     * @param array{
     *   plate?:string, vehicle_type?:string,
     *   checkin?:string, checkout?:string
     * } $input
     * @return array{ok:bool, errors?:string[], id?:int}
     */
    public function update(int $id, array $input): array
    {

        $existing = $this->repository->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['Veículo não encontrada.']];
        }

        $errors = $this->validator->validate($input, true);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $plate = strtoupper(trim((string)$input['plate']));
        $vehicleType = strtolower(trim((string)$input['vehicle_type']));
        $checkin = (string)($input['checkin'] ?? date('c'));
        $checkout = (string)($input['checkout'] ?? date('c'));

        $amount = $this->calculator->calculate($vehicleType, $checkin, $checkout);

        $updated = new Vehicle(
            id: $id,
            plate: $plate,
            vehicleType: $vehicleType,
            baseRate: 0.0,
            amount: $amount,
            checkin: $checkin,
            checkout: $checkout
        );

        $this->repository->update($updated);
        return ['ok' => true];
    }

    public function delete(int $id): array
    {
         $existing = $this->repository->find($id);
        if (!$existing) {
            return ['ok' => false, 'errors' => ['Veículo não encontrada.']];
        }
        $this->repository->delete($id);
        return ['ok' => true];
    }

    /** @return Vehicle[] */
    public function all(): array
    {
        return $this->repository->all();
    }

    public function find(int $id): ?Vehicle
    {
        return $this->repository->find($id);
    }

    public function checkin(int $id): array
    {
        $vehicle = $this->repository->find($id);
        if (!$vehicle) {
            return ['ok' => false, 'errors' => ['Veículo não encontrado.']];
        }

        if ($vehicle->hasCheckin()) {
            return ['ok' => false, 'errors' => ['Veículo já possui check-in registrado.']];
        }

        $checkinTime = (new \DateTime('now', new \DateTimeZone('America/Sao_Paulo')))->format(\DateTime::ATOM);
        $updated = new Vehicle(
            id: $id,
            plate: $vehicle->plate(),
            vehicleType: $vehicle->vehicleType(),
            baseRate: $vehicle->baseRate(),
            amount: $vehicle->amount(),
            checkin: $checkinTime,
            checkout: $vehicle->checkout()
        );

        $this->repository->update($updated);
        return ['ok' => true];
    }

    public function checkout(int $id): array
    {
        $vehicle = $this->repository->find($id);
        if (!$vehicle) {
            return ['ok' => false, 'errors' => ['Veículo não encontrado.']];
        }

        if (!$vehicle->hasCheckin()) {
            return ['ok' => false, 'errors' => ['Veículo não possui check-in. Realize o check-in primeiro.']];
        }

        if ($vehicle->hasCheckout()) {
            return ['ok' => false, 'errors' => ['Veículo já possui check-out registrado.']];
        }

        $checkoutTime = (new \DateTime('now', new \DateTimeZone('America/Sao_Paulo')))->format(\DateTime::ATOM);
        $amount = $this->calculator->calculate(
            $vehicle->vehicleType(),
            $vehicle->checkin(),
            $checkoutTime
        );

        $updated = new Vehicle(
            id: $id,
            plate: $vehicle->plate(),
            vehicleType: $vehicle->vehicleType(),
            baseRate: $vehicle->baseRate(),
            amount: $amount,
            checkin: $vehicle->checkin(),
            checkout: $checkoutTime
        );

        $this->repository->update($updated);
        return ['ok' => true, 'amount' => $amount];
    }
}