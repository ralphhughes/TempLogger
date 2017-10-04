<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';

//get Temp
$cputemp = (exec("cat /sys/class/thermal/thermal_zone0/temp "));
$temperature = $cputemp / 1000; 

echo "CPU Temp: " . $temperature;

$Database->logValueToDB('CPU', $temperature);

