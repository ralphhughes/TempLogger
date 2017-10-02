<?php

//get Temp
$cputemp = (exec("cat /sys/class/thermal/thermal_zone0/temp "));
$temperature = $cputemp / 1000; 

echo "CPU Temp: " . $temperature;

try {
    //open the database
    $db = new PDO('sqlite:/var/www/database/myDB.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //create the database
    $db->exec("CREATE TABLE IF NOT EXISTS temps (id INTEGER PRIMARY KEY, timestamp TEXT, sensor TEXT, value REAL)");

    //insert some data...
    $db->exec("INSERT INTO temps (timestamp, sensor, value) VALUES (datetime(), 'CPU', " . $temperature . ");");

    // close the database connection
    $db = NULL;
} catch (PDOException $e) {
    print 'Exception : ' . $e->getMessage();
}
