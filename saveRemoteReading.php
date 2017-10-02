<?php

// Read post request for sensor name and sensor value

// TODO: Stop SQL injection!
$name = $_POST["name"];
$value = $_POST["value"];

//echo "Reading: " . $value;

try {
    //open the database
    $db = new PDO('sqlite:/var/www/database/myDB.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //create the database
    $db->exec("CREATE TABLE IF NOT EXISTS temps (id INTEGER PRIMARY KEY, timestamp TEXT, sensor TEXT, value REAL)");

    //insert some data...
    $db->exec("INSERT INTO temps (timestamp, sensor, value) VALUES (datetime(), '" . $name . "', " . $value . ");");

    // close the database connection
    $db = NULL;
} catch (PDOException $e) {
    print 'Exception : ' . $e->getMessage();
}
