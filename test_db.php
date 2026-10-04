<?php
mysqli_report(MYSQLI_REPORT_OFF);
$passwords = ['root', 'password', 'mysql', 'admin', 'Sohma24', ''];
foreach($passwords as $p) {
    $m = new mysqli('127.0.0.1', 'root', $p);
    if (!$m->connect_error) {
        echo "SUCCESS WITH PASSWORD: '$p'\n";
        exit;
    }
}
echo "ALL FAILED\n";
