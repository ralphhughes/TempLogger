<?php
// Run this every hour or every 3 hours?



$timestamp = gmdate('Y-m-d') . 'T' . gmdate('H') . 'Z';


// Three hourly forecast for Llandudno North Shore
$url = 'http://datapoint.metoffice.gov.uk/public/data/val/wxfcs/all/json/354790?res=3hourly&time=' . $timestamp . '&key=e11ec6f0-6bd7-4eda-b482-048f0f50a9e3';

//$url = 'http://datapoint.metoffice.gov.uk/public/data/val/wxfcs/all/json/354790?res=3hourly&key=e11ec6f0-6bd7-4eda-b482-048f0f50a9e3';
    
//$url = 'http://datapoint.metoffice.gov.uk/public/data/val/wxfcs/all/json/354790?res=3hourly&key=e11ec6f0-6bd7-4eda-b482-048f0f50a9e3';

// Hourly observations for Llandudno North Shore
//$url = 'http://datapoint.metoffice.gov.uk/public/data/val/wxobs/all/json/354790?res=hourly&time=' . $timestamp . '&key=e11ec6f0-6bd7-4eda-b482-048f0f50a9e3';

//print $url;
$json = file_get_contents($url);
//print_r($json);

$data = json_decode($json, true);

//print_r(json_last_error()); // call after json_decode

//print_r($data);
        
// T is temperature in degrees C
// F is feels like temperature in degrees C
// $ denotes the number of minutes after midnight GMT on the day represented by the Period object in which the Rep object is found.
$forecasts = $data['SiteRep']['DV']['Location']['Period'];
var_dump($forecasts);

//$offset = $data['SiteRep']['DV']['Location']['Period'][0]['Rep'][0]['$'];
//print 'Time: ' . $offset / 60 . ':00 ';
//$temp = $data['SiteRep']['DV']['Location']['Period'][0]['Rep'][0]['T'];
print 'Temperature: ' . $forecasts['Rep']['T'];

try {
    //open the database
    $db = new PDO('sqlite:/var/www/database/myDB.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //create the database
    $db->exec("CREATE TABLE IF NOT EXISTS temps (id INTEGER PRIMARY KEY, timestamp TEXT, sensor TEXT, value REAL)");

// create index time_idx on temps(timestamp);
// create index sensor_idx on temps(sensor);

    //insert some data...
    $db->exec("INSERT INTO temps (timestamp, sensor, value) VALUES (datetime(), 'MetOfficeForecast', " . $forecasts['Rep']['T'] . ");");

    // close the database connection
    $db = NULL;
} catch (PDOException $e) {
    print 'Exception : ' . $e->getMessage();
}
