<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';

//$strVal = (exec("sudo /home/pi/Adafruit_Python_DHT/examples/AdafruitDHT.py 2302 2")); // Requires the adafruit DHT library downloading and installing
$strVal = exec("python " . basename(__DIR__) . "/DHT22.py"); // Requires pigpiod to be installed and running
$temperature = get_string_between($strVal, "Temp=","*");
$humidity = get_string_between($strVal, "Humidity=","%");

// Debug
echo "Temp: " . $temperature . "\n";
echo "Humidity: " . $humidity . "\n";

//insert the data
if ($temperature != '-999') {
    Database::logValueToDB('DHT22_Temp', $temperature);
}
if ($humidity != '-999') {
    Database::logValueToDB('DHT22_Humidity', $humidity);
}
