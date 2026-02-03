<?php
// Quick database connection test
echo "Testing PostgreSQL connection...\n";

$host = '18xt00.stackhero-network.com';
$port = '8392';
$dbname = 'dux';
$user = 'admin';
$password = 'S0bpckeA2olq5sGnGqxYk7PF2LEYFBXX';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require;connect_timeout=60";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 60
    ]);

    echo "SUCCESS! Connected to PostgreSQL\n";
    echo "Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";

    // List schemas
    $stmt = $pdo->query("SELECT schema_name FROM information_schema.schemata ORDER BY schema_name");
    echo "\nExisting schemas:\n";
    while ($row = $stmt->fetch()) {
        echo "  - " . $row['schema_name'] . "\n";
    }

} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
