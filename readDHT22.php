<?php

//$strVal = (exec("sudo /home/pi/Adafruit_Python_DHT/examples/AdafruitDHT.py 2302 2")); // Requires the adafruit DHT library downloading and installing
$strVal = exec("python /var/www/DHT22.py"); // Requires pigpiod to be installed and running
$temperature = get_string_between($strVal, "Temp=","*");
$humidity = get_string_between($strVal, "Humidity=","%");

// Debug
echo "Temp: " . $temperature . "\n";
echo "Humidity: " . $humidity . "\n";

try {
    //open the database
    $db = new PDO('sqlite:/var/www/database/myDB.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //create the database
    $db->exec("CREATE TABLE IF NOT EXISTS temps (id INTEGER PRIMARY KEY, timestamp TEXT, sensor TEXT, value REAL)");

    //insert some data...
    $db->exec("INSERT INTO temps (timestamp, sensor, value) VALUES (datetime(), 'DHT22_Temp', " . $temperature . ");");
    $db->exec("INSERT INTO temps (timestamp, sensor, value) VALUES (datetime(), 'DHT22_Humidity', " . $humidity . ");");

    // close the database connection
    $db = NULL;
} catch (PDOException $e) {
    print 'Exception : ' . $e->getMessage();
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}