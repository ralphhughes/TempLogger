<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';


if (isset($_POST['rowIds']))  {

    $commaList = implode(', ', $_POST['rowIds'] );
    $sql = "DELETE FROM temps WHERE id in (" . $commaList . ")";
	echo $sql . "<br/>";
	Database::query($sql);
	echo "Got to end of script.";
    exit();
}

if (isset($_GET['sensor'])) {
	$sensor = $_GET['sensor'];
}
if ($sensor != "DHT22_Temp" && $sensor != "DHT22_Humidity") {
	$sensor = "DHT22_Temp";
}

include 'includes/guiHeader.php';
?>

		<title>Remove bad data</title>
	</head>
	<body>
		<form method="POST">
			<table style="border-spacing: 10px;">
				<tr><td>&nbsp;</td><td>ID</td><td>Timestamp</td><td>Value</td></tr>
				<?= printSuspiciousRows($sensor) ?>
			
			</table>
			<input type="submit" value="Delete selected rows">
		</form>

	</body>
</html>




<?php
/*
DHT22_Temp
DHT22_Humidity
MetOfficeForecast
*/ 

function printSuspiciousRows($sensor) {
	$output="";
	$sql="select * from temps where sensor='" . $sensor . "' order by timestamp";
	$result = Database::query($sql);
	//$lastValue
	// id, sensor, timestamp, value
	$arrSuspiciousValues = array();
	while($row = $result->fetch()){
		if (!empty($lastValue)) {
			$delta = $row['value'] - $lastValue;
			// "Suspicious rows" are ones where the value moves more than 10 units from previous reading:
			if (abs($delta) > 10) {
				if (!arrContainsId($arrSuspiciousValues, $lastRow['id'])) {
					array_push($arrSuspiciousValues, $lastRow);
				}
				if (!arrContainsId($arrSuspiciousValues, $row['id'])) {
					array_push($arrSuspiciousValues, $row);
				}
			}
		}
		$lastValue = $row['value'];
		$lastRow = $row;
	}

	foreach($arrSuspiciousValues as $row) {
		if (!empty($lastRow)) {
			if (($row['id'] - $lastRow['id']) > 10) {
				$output=$output.'<tr><td colspan="4"><hr/></td></tr>';
			}
		}
		$output=$output.'<tr><td><input name="rowIds[]" value="' . $row['id'] . '"type="checkbox"/></td>';
		$output=$output. "<td>" . $row['id'] . "</td><td>" . $row['timestamp'] . "</td><td>" . $row['value'] . "</td></tr>";
		$lastRow = $row;
	}
	
	return $output;
}


function arrContainsId($array, $id) {
	foreach($array as $row) {
		
		if ($row['id'] == $id) {
			return true;
		}
	}
	return false;
}
