<?php
$logFile = __DIR__ . '/activity.log';
$target = "https://google.com";

$ch = curl_init($target);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

file_put_contents($logFile, date('[Y-m-d H:i:s]') . " [Worker] Run. Status: $status\n", FILE_APPEND);