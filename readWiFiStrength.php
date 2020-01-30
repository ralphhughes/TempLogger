<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';

//get Temp
$temp = (exec("/sbin/iwconfig wlan0 | grep -i signal"));
//echo "temp: " . $temp;

preg_match_all('/\d+/', $temp, $tempArr);

//echo "tempArr: ";
//print_r($tempArr);

$linkQuality = 100 * ((float)$tempArr[0][0] / (float)$tempArr[0][1]);
$signalLevel = -1 * (float)$tempArr[0][2];

if (is_nan($linkQuality)) {
  $linkQuality = -1;
}

echo "Link Quality: " . $linkQuality . "\n";
echo "Signal Level: " . $signalLevel . "\n";

$Database->logValueToDB('WiFiLinkQuality', $linkQuality);
$Database->logValueToDB('WiFiSignalLevel', $signalLevel);

