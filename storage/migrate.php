<?php
declare(strict_types=1);

$dir = __DIR__;
$dbPath = $dir . '/database.sqlite';

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS vehicles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  plate TEXT NOT NULL,
  vehicle_type TEXT NOT NULL,
  base_rate REAL NOT NULL CHECK(base_rate >= 0),
  amount REAL NOT NULL CHECK(amount >= 0),
  checkin TEXT NOT NULL,
  checkout TEXT NOT NULL
);
SQL;

$pdo->exec($sql);

echo "OK: database.sqlite e tabela vehicles criados/atualizados.\n";
