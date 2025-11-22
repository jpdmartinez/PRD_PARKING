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

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $service->create($_POST);
    
    if ($result['ok']) {
        $success = true;
        $_POST = [];
    } else {
        $errors = $result['errors'] ?? [];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Veículo - Sistema de Estacionamento</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px; text-decoration: none; display: inline-block; }
        .btn:hover { background-color: #45a049; }
        .error { color: red; margin-top: 5px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px; }
        .success { color: green; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px; }
        a { display: inline-block; margin-top: 10px; color: #4CAF50; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Cadastrar Veículo</h1>
    
    <?php if ($success): ?>
        <div class="success">Veículo cadastrado com sucesso! Agora você pode fazer o check-in na lista de veículos.</div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="plate">Placa:</label>
            <input type="text" id="plate" name="plate" value="<?= htmlspecialchars($_POST['plate'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="vehicle_type">Tipo de Veículo:</label>
            <select id="vehicle_type" name="vehicle_type" required>
                <option value="">Selecione...</option>
                <option value="car" <?= ($_POST['vehicle_type'] ?? '') === 'car' ? 'selected' : '' ?>>Carro (R$ 5,00/h)</option>
                <option value="motorcycle" <?= ($_POST['vehicle_type'] ?? '') === 'motorcycle' ? 'selected' : '' ?>>Moto (R$ 3,00/h)</option>
                <option value="truck" <?= ($_POST['vehicle_type'] ?? '') === 'truck' ? 'selected' : '' ?>>Caminhão (R$ 10,00/h)</option>
            </select>
        </div>
        
        <button type="submit" class="btn">Cadastrar Veículo</button>
    </form>
    
    <a href="index.php">← Voltar para a lista</a>
</body>
</html>
