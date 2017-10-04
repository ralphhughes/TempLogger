<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';

// Read post request for sensor name and sensor value, filter should help against SQL injection
$name = filter_input(INPUT_POST, 'name',FILTER_SANITIZE_STRING);
$value = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_NUMBER_FLOAT);

//echo "Reading: " . $value;

// This method uses prepared statements anyway
Database:logValueToDB($name, $value);
