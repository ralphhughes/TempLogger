<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

$sensors = array("DS18B20"=>"/sys/bus/w1/devices/28-0004441efaff/w1_slave",
		 "DS18B20_2"=>"/sys/bus/w1/devices/28-0516931d7dff/w1_slave");

foreach($sensors as $sensorName => $sensorPath) {
	$temp = readTempFromSensor($sensorPath);
	writeToDatabase($sensorName, $temp);
}

function readTempFromSensor($sensorPath) {

	// Open resource file for thermometer
	$thermometer = fopen($sensorPath, "r");

	// Get the contents of the resource
	$thermometerReadings = fread($thermometer, filesize($sensorPath));

	// Close resource file for thermometer
	fclose($thermometer);

	// We're only interested in the 2nd line, and the value after the t= on the 2nd line
	preg_match("/t=(.+)/", preg_split("/\n/", $thermometerReadings)[1], $matches);
	$temperature = $matches[1] / 1000;

	// Output the temperature for debugging
	print "Path: " . $sensorPath . "\n";
	print "Temp: " . $temperature . "\n";
	print "\n";

	return $temperature;
}

function writeToDatabase($sensorName, $temperature) {
  try {
    //open the database
    $db = new PDO('sqlite:/var/www/database/myDB.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //create the database
    $db->exec("CREATE TABLE IF NOT EXISTS temps (id INTEGER PRIMARY KEY, timestamp TEXT, sensor TEXT, value REAL)");

    //insert some data...
    $db->exec("INSERT INTO temps (timestamp, sensor, value) VALUES (datetime(), '" . $sensorName . "', " . $temperature . ");");

    // close the database connection
    $db = NULL;
  } catch (PDOException $e) {
    print 'Exception : ' . $e->getMessage();
  }
}
