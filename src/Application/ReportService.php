<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\VehicleRepository;

final class ReportService
{
    public function __construct(
        private VehicleRepository $repository
    ) {}

    /**
     * @return array{
     *   total_vehicles: int,
     *   total_revenue: float,
     *   by_type: array<string, array{count: int, revenue: float}>
     * }
     */
    public function generateReport(): array
    {
        $vehicles = $this->repository->all();
        
        $report = [
            'total_vehicles' => count($vehicles),
            'total_revenue' => 0.0,
            'by_type' => [
                'car' => ['count' => 0, 'revenue' => 0.0],
                'motorcycle' => ['count' => 0, 'revenue' => 0.0],
                'truck' => ['count' => 0, 'revenue' => 0.0],
            ],
        ];

        foreach ($vehicles as $vehicle) {
            $type = strtolower($vehicle->vehicleType());
            $amount = $vehicle->amount();
            
            $report['total_revenue'] += $amount;
            
            if (isset($report['by_type'][$type])) {
                $report['by_type'][$type]['count']++;
                $report['by_type'][$type]['revenue'] += $amount;
            }
        }

        return $report;
    }
}

