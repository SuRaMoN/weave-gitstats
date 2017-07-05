<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

function writeCsv($query, $csvPath)
{
    if (is_file($csvPath)) {
        return;
    }
    $csv = fopen($csvPath, 'w');
    ob_start(function ($buffer) use ($csv) {
        fwrite($csv, $buffer);
        return '';
    });
    $_SERVER['QUERY_STRING'] = rawurlencode("$query");
    require(__DIR__ . '/csv.php');
    ob_end_flush();
    fclose($csv);
}

$dir = sys_get_temp_dir() . '/' . uniqid('export-', true);
mkdir($dir);
foreach (glob(__DIR__ . '/*-dashboards/*.weave') as $dashboard) {
    $relativePath = basename(dirname($dashboard)) . '/' . basename($dashboard);
    $newPath = $dir . '/' . $relativePath;
    if (! is_dir(dirname($newPath))) {
        mkdir(dirname($newPath));
    }
    copy($dashboard, $newPath);
    $zip = new ZipArchive();
    $zip->open($newPath);
    $json = json_decode($zip->getFromName('weave-json/history.json'), true);
    foreach ($json['currentState'] as & $entry) {
        if ('weavejs.data.source.CSVDataSource' !== $entry['className']) {
            continue;
        }
        $url = $entry['sessionState']['url'];
        if (strpos($url, 'csv.php?') !== 0) {
            continue;
        }
        $query = substr($url, 8);
        $csvPath = $dir . '/' . md5($query) . '.csv';
        writeCsv($query, $csvPath);
        $entry['sessionState']['url'] = basename($csvPath);
    }
    $zip->addFromString('weave-json/history.json', json_encode($json));
    $zip->close();
}

copy(__DIR__ . '/weave.html', "$dir/weave.html");

ob_start();
$statisticsOnly = true;
require(__DIR__ . '/index.php');
$indexHtml = ob_get_clean();

file_put_contents("$dir/index.html", $indexHtml);

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename=export.zip');

chdir($dir);
passthru('zip -r - .');
exec('rm -Rf ' . escapeshellarg($dir));
