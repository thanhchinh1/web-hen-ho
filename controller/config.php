<?php
// Render cung cấp DATABASE_URL dạng: mysql://user:pass@host:port/dbname

function parseDatabaseUrl() {
    $databaseUrl = getenv('DATABASE_URL');
    
    if (!$databaseUrl) {
        // Local development
        return [
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'webhenho',
            'username' => 'root',
            'password' => ''
        ];
    }
    
    // Parse DATABASE_URL từ Render
    $url = parse_url($databaseUrl);
    
    return [
        'host' => $url['host'],
        'port' => $url['port'] ?? 3306,
        'database' => ltrim($url['path'], '/'),
        'username' => $url['user'],
        'password' => $url['pass']
    ];
}

$config = parseDatabaseUrl();

try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
        $config['host'],
        $config['port'],
        $config['database']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}
?>