<?php

error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

$config = parse_ini_file(__DIR__ . '/config.ini');

$query = rawurldecode($_SERVER['QUERY_STRING']);

if (null === $query) {
    die("Error\n\"Must pass a query, eg: {$_SERVER['SCRIPT_NAME']}?select * from table\"");
}

$pdo = new PDO("mysql:dbname={$config['mysql_db']};host={$config['mysql_host']}", $config['mysql_user'], $config['mysql_pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

try {
    $data = $pdo->query($query);
    $fp = fopen('php://output', 'w');

    $columns = [];
    for ($i = 0; $i < $data->columnCount(); $i++) {
        $columns[] = $data->getColumnMeta($i)['name'];
    }
    fputcsv($fp, $columns);

    while ($row = $data->fetch(PDO::FETCH_NUM)) {
        fputcsv($fp, $row);
    }
} catch (Exception $e) {
    die("Error\n" . preg_replace('/[\'":,;]/', '', preg_replace('/.{50}/', "\\0\n", $e->getMessage())));
}
