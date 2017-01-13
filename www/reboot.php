<?php

// reboot.php - by TECH

$rebootTime = preg_replace("/0-9/", "", $_REQUEST['rt']);
$reason = (isset($_REQUEST['reason'])) ? (strtolower(filter_var($_REQUEST['reason'],FILTER_SANITIZE_STRING))) : "SERVER MAINTENANCE TIME!!";

echo "Rebooting in $rebootTime seconds. $reason";

echo exec( "shutdown /r /t $rebootTime /c '$reason'");