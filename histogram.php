<?php
require_once 'includes/database.php';
const DATASOURCE="SQLITE"; // MYSQL or SQLITE
// This file is standalone
$con = openDB();

if (!empty($_POST["numDays"])) {
    $unsafeNumDays = $_POST["numDays"];
    $numDays = (int) $unsafeNumDays; // Casting to an INT should get rid of all SQL injection attempts etc
} else {
    $numDays=3; // Default time period on first entering the page
}
if (!empty($_POST["seriesIndex"])) {
    $unsafeSeriesIndex = $_POST["seriesIndex"];
    $seriesIndex = (int) $unsafeSeriesIndex;
    if ($seriesIndex >= count($seriesNames) || $seriesIndex < 0) {
	$seriesIndex = 3;
    }
} else {
    $seriesIndex = 3;
}
$seriesNames = array("CPU","DHT22_Humidity","DHT22_Temp","DS18B20","MetOfficeForecast");
$timePeriods = array("1"=>"1 Day","3"=>"3 Days","7"=>"1 Week","14"=>"2 Weeks","30"=>"1 Month","90"=>"3 Months","183"=>"6 Months","365"=>"1 Year");

include 'includes/guiHeader.php';
?>
        <title>Temperature chart</title>
        
        <script>
$(function () {
    $('#container').highcharts({
        credits: {
             enabled: false
        },
        chart: {
            type: 'column'
        },
        title: {
            text: 'Histogram'
        },
        xAxis: {
            title: {
                text: 'Date'
            }
        },
        yAxis: [{
            title: {
                text: 'Value'
            }
        }],
        tooltip: {
            shared: true,
            headerFormat: '<b>{point.x:%Y-%m-%d %H:%M}</b><br>',
            pointFormat: '{series.name} - {point.y:.2f}<br>'
        },

        plotOptions: {
            column: {
                dataLabels: {
                    enabled: false
                }
            }
        },

        series: [{
            name: '<?=$seriesNames[$seriesIndex];?>',
            data: [
                <?=fetchHourlyData($con, $seriesNames[$seriesIndex], $numDays);?>
            ]
        }]
    });
});            
        
            
        </script>
    </head>

    <body>
        <p>Viewing last <?=$numDays?> days</p>
        <div id="container" style="width: 100%; height: 80%;"></div>
        <form action="" method="post" name="theForm">
            <select name="numDays" onchange="this.form.submit();">
                <option selected="selected" value="">Select time period to view...</option>
                <?=getTimePeriods($timePeriods, $numDays);?>
            </select>
	    <select name="seriesIndex" onchange="this.form.submit();">
                <option value="">Select sensor to view...</option>
		<?=getSensorNames($seriesNames, $seriesIndex);?>
            </select>
        </form>
    </body>
</html>


<?php
function getTimePeriods($timePeriods, $numDays) {
    $output="";
    foreach($timePeriods as $key => $value) {
	if ($key === $numDays) {
            $output = $output . '<option selected="selected" value="' . $key . '">' . $value . "</option>\r\n";
        } else {
            $output = $output . '<option value="' . $key . '">' . $value . "</option>\r\n";
        }
    }
    return $output;
}
function getSensorNames($seriesNames, $seriesIndex) {
    $output = "";
    for($i = 0; $i < count($seriesNames); $i++) {
        if ($i === $seriesIndex) {
	    $output = $output . '<option selected="selected" value="' . $i . '">' . $seriesNames[$i] . "</option>\r\n";
        } else {
            $output = $output . '<option value="' . $i . '">' . $seriesNames[$i] . "</option>\r\n";
        }
    }
    return $output;
}
function fetchHourlyData($con, $sensor, $numDays) {

    $sql="select strftime('%H',timestamp) as hour, avg(value) as value from temps " .
            "where sensor='" . $sensor . "' " .
	    "and timestamp > datetime('now','-" . $numDays . " days') " . 
            "group by strftime('%H',timestamp);";

    $result = $con->query($sql);
    $result->setFetchMode(PDO::FETCH_ASSOC);
    while($row = $result->fetch()){
        $js = $js . "[" . $row['hour'] . ", " . $row['value'] . "],\n";
    }
    return $js;
}

$con = null;
