<?php
chdir(dirname(__FILE__));
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

 $headers = array(
    'Content-type: application/json',
    'x-ha-access: chess386',
  );

//insert the data
if ($temperature != '-999' && $humidity != '-999') {
  $Database->logValueToDB('DHT22_Temp', $temperature);
  $Database->logValueToDB('DHT22_Humidity', $humidity);
  writeToFile($temperature, $humidity);
}

function writeToFile($temperature, $humidity) {

  $fp = fopen("./dht22", "w");
  fwrite($fp, '{  "temperature": ' . $temperature . ', "humidity": ' . $humidity . '}');
  fclose($fp);

}
