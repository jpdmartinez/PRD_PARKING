<?php

declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\ReportService;
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
$reportService = new ReportService($repository);
$report = $reportService->generateReport();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema de Estacionamento</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
        h1 { color: #333; }
        .report-section { background-color: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #ddd; }
        .summary { display: flex; gap: 20px; margin-bottom: 30px; }
        .summary-card { flex: 1; background-color: #4CAF50; color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .summary-card h2 { margin: 0 0 10px 0; font-size: 2em; }
        .summary-card p { margin: 0; font-size: 0.9em; opacity: 0.9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; text-decoration: none; border-radius: 4px; }
        .btn-primary { background-color: #4CAF50; color: white; }
        .btn-secondary { background-color: #2196F3; color: white; }
        .revenue { font-weight: bold; color: #2e7d32; }
    </style>
</head>
<body>
    <h1>Relatório de Estacionamento</h1>
    
    <div>
        <a href="index.php" class="btn btn-secondary">← Voltar para Lista</a>
        <a href="register.php" class="btn btn-primary">Cadastrar Veículo</a>
    </div>

    <div class="summary">
        <div class="summary-card">
            <h2><?= $report['total_vehicles'] ?></h2>
            <p>Total de Veículos</p>
        </div>
        <div class="summary-card">
            <h2>R$ <?= number_format($report['total_revenue'], 2, ',', '.') ?></h2>
            <p>Faturamento Total</p>
        </div>
    </div>

    <div class="report-section">
        <h2>Faturamento por Tipo de Veículo</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo de Veículo</th>
                    <th>Quantidade</th>
                    <th>Faturamento</th>
                    <th>Tarifa por Hora</th>
                </thead>
            <tbody>
                <?php
                $typeLabels = [
                    'car' => 'Carro',
                    'motorcycle' => 'Moto',
                    'truck' => 'Caminhão',
                ];
                $typeRates = [
                    'car' => 'R$ 5,00',
                    'motorcycle' => 'R$ 3,00',
                    'truck' => 'R$ 10,00',
                ];
                foreach ($report['by_type'] as $type => $data):
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($typeLabels[$type] ?? ucfirst($type)) ?></strong></td>
                    <td><?= $data['count'] ?></td>
                    <td class="revenue">R$ <?= number_format($data['revenue'], 2, ',', '.') ?></td>
                    <td><?= $typeRates[$type] ?? '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="report-section">
        <h2>Resumo das Tarifas</h2>
        <ul>
            <li><strong>Carro:</strong> R$ 5,00 por hora</li>
            <li><strong>Moto:</strong> R$ 3,00 por hora</li>
            <li><strong>Caminhão:</strong> R$ 10,00 por hora</li>
        </ul>
    </div>
</body>
</html>
