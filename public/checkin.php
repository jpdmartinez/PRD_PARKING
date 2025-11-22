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

$id = (int)($_GET['id'] ?? 0);
$result = null;

if ($id > 0) {
    $result = $service->checkin($id);
}

if ($result && $result['ok']) {
    header('Location: index.php?checkin_success=1');
    exit;
} else {
    $errors = $result['errors'] ?? ['ID inválido.'];
    header('Location: index.php?error=' . urlencode($errors[0]));
    exit;
}
