<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';

$sensors = array(
    "DS18B20"  =>"/sys/bus/w1/devices/28-0004441efaff/w1_slave",
    "DS18B20_2"=>"/sys/bus/w1/devices/28-0516931d7dff/w1_slave",
    "DS18B20_3"=>"/sys/bus/w1/devices/28-0417011544ff/w1_slave"
    );

foreach($sensors as $sensorName => $sensorPath) {
	$temp = readTempFromSensor($sensorPath);
	Database::logValueToDB($sensorName, $temp);
}

function readTempFromSensor($sensorPath) {

	// Open resource file for thermometer
	$thermometer = fopen($sensorPath, "r");

	// Get the contents of the resource
	$thermometerReadings = fread($thermometer, filesize($sensorPath));

	// Close resource file for thermometer
	fclose($thermometer);

        // Check for a valid CRC
        if (strpos($thermometerReadings[0],'YES') !== false) {

            // We're only interested in the 2nd line, and the value after the t= on the 2nd line
            preg_match("/t=(.+)/", preg_split("/\n/", $thermometerReadings)[1], $matches);
            $temperature = $matches[1] / 1000;

            // Output the temperature for debugging
            print "Path: " . $sensorPath . "\n";
            print "Temp: " . $temperature . "\n";
            print "\n";

            return $temperature;
        } else {
            print "Invalid CRC.";
            return null;
        }
}

