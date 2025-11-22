<?php

declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\ParkingService;
use App\Domain\ParkingCalculator;
use App\Domain\VehicleValidator;
use App\Domain\Policies\CarPolicy;
use App\Domain\Policies\MotorcyclePolicy;
use App\Domain\Policies\TruckPolicy;
use App\Infra\SqliteVehicleRepository;

$dbPath = __DIR__ . '/../storage/database.sqlite';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS vehicles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  plate TEXT NOT NULL,
  vehicle_type TEXT NOT NULL,
  base_rate REAL NOT NULL CHECK(base_rate >= 0),
  amount REAL NOT NULL CHECK(amount >= 0),
  checkin TEXT DEFAULT "",
  checkout TEXT DEFAULT ""
)');

$repository = new SqliteVehicleRepository($pdo);
$validator = new VehicleValidator();

$policies = [
    'car' => new CarPolicy(),
    'motorcycle' => new MotorcyclePolicy(),
    'truck' => new TruckPolicy(),
];

$calculator = new ParkingCalculator($policies);
$service = new ParkingService($repository, $validator, $calculator);

$vehicles = $service->all();

$checkinSuccess = isset($_GET['checkin_success']);
$checkoutSuccess = isset($_GET['checkout_success']);
$checkoutAmount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Estacionamento - Lista</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn { padding: 8px 12px; margin: 2px; text-decoration: none; border-radius: 4px; display: inline-block; font-size: 14px; border: none; cursor: pointer; }
        .btn-primary { background-color: #4CAF50; color: white; }
        .btn-warning { background-color: #ff9800; color: white; }
        .btn-info { background-color: #2196F3; color: white; }
        .btn:hover { opacity: 0.9; }
        .status { padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-parked { background-color: #ff9800; color: white; }
        .status-completed { background-color: #4CAF50; color: white; }
        .status-waiting { background-color: #9e9e9e; color: white; }
    </style>
</head>
<body>
    <h1>Veículos Estacionados</h1>
    
    <?php if ($checkinSuccess): ?>
        <div class="alert alert-success">Check-in realizado com sucesso!</div>
    <?php endif; ?>
    
    <?php if ($checkoutSuccess): ?>
        <div class="alert alert-success">Check-out realizado com sucesso! Valor a pagar: R$ <?= number_format($checkoutAmount, 2, ',', '.') ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div>
        <a href="register.php" class="btn btn-primary">Cadastrar Novo Veículo</a>
        <a href="reports.php" class="btn btn-info">Ver Relatórios</a>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Placa</th>
                <th>Tipo</th>
                <th>Tarifa/Hora</th>
                <th>Status</th>
                <th>Entrada</th>
                <th>Saída</th>
                <th>Valor Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $typeRates = [
                'car' => 5.00,
                'motorcycle' => 3.00,
                'truck' => 10.00,
            ];
            
            if (empty($vehicles)): ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">Nenhum veículo cadastrado.</td>
            </tr>
            <?php else:
                foreach ($vehicles as $vehicle): 
                    $type = strtolower($vehicle->vehicleType());
                    $hourlyRate = $typeRates[$type] ?? 0.0;
                    $hasCheckin = $vehicle->hasCheckin();
                    $hasCheckout = $vehicle->hasCheckout();
                    
                    if (!$hasCheckin) {
                        $status = 'Aguardando Check-in';
                        $statusClass = 'status-waiting';
                    } elseif ($hasCheckin && !$hasCheckout) {
                        $status = 'Estacionado';
                        $statusClass = 'status-parked';
                    } else {
                        $status = 'Finalizado';
                        $statusClass = 'status-completed';
                    }
            ?>
            <tr>
                <td><?= htmlspecialchars((string)($vehicle->id() ?? '')) ?></td>
                <td><?= htmlspecialchars($vehicle->plate()) ?></td>
                <td><?= htmlspecialchars(ucfirst($vehicle->vehicleType())) ?></td>
                <td>R$ <?= number_format($hourlyRate, 2, ',', '.') ?></td>
                <td><span class="status <?= $statusClass ?>"><?= $status ?></span></td>
                <td>
                    <?php
                    if ($hasCheckin) {
                        $checkinDate = \DateTime::createFromFormat(\DateTime::ATOM, $vehicle->checkin());
                        if ($checkinDate) {
                            $checkinDate->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
                            echo htmlspecialchars($checkinDate->format('d/m/Y - H:i'));
                        } else {
                            echo htmlspecialchars($vehicle->checkin());
                        }
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    if ($hasCheckout) {
                        $checkoutDate = \DateTime::createFromFormat(\DateTime::ATOM, $vehicle->checkout());
                        if ($checkoutDate) {
                            $checkoutDate->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
                            echo htmlspecialchars($checkoutDate->format('d/m/Y - H:i'));
                        } else {
                            echo htmlspecialchars($vehicle->checkout());
                        }
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>R$ <?= number_format($vehicle->amount(), 2, ',', '.') ?></td>
                <td>
                    <?php if (!$hasCheckin): ?>
                        <a href="checkin.php?id=<?= $vehicle->id() ?>" class="btn btn-warning" onclick="return confirm('Confirmar check-in para este veículo?')">Check-in</a>
                    <?php elseif (!$hasCheckout): ?>
                        <a href="checkout.php?id=<?= $vehicle->id() ?>" class="btn btn-primary" onclick="return confirm('Confirmar check-out para este veículo? O valor será calculado automaticamente.')">Check-out</a>
                    <?php else: ?>
                        <span style="color: #4CAF50;">✓ Finalizado</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php 
                endforeach;
            endif; ?>
        </tbody>
    </table>
</body>
</html>
