<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';

// Requires pigpiod to be installed and running
$strVal = exec("python ./DHT22.py " . DHT22_PIN); // Pass the gpio pin from config 

$temperature = get_string_between($strVal, "Temp=","*");
$humidity = get_string_between($strVal, "Humidity=","%");

// Debug
echo "Temp: " . $temperature . "\n";
echo "Humidity: " . $humidity . "\n";

//insert the data
if ($temperature != '-999') {
    $Database->logValueToDB('DHT22_Temp', $temperature);
}
if ($humidity != '-999') {
    $Database->logValueToDB('DHT22_Humidity', $humidity);
}
