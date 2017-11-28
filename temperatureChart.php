<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';

$numDays = getNumDays();
$seriesIndexes = getSelectedSeries();
$selectedSamplingPeriod = getSelectedSamplingPeriod();
$seriesNames = getSeriesFromDB($Database);
$timePeriods = array("1" => "1 Day", "2" => "2 Days", "4" => "4 Days", "7" => "1 Week", "14" => "2 Weeks", "30" => "1 Month", "42" => "6 Weeks", "91" => "3 Months", "183" => "6 Months", "274" => "9 Months", "365" => "1 Year", "548" => "18 Months", "731" => "2 Years");
$samplingPeriods = array("0" => "All readings", "1" => "Hourly", "6" => "6 hours", "24" => "Daily");


include 'includes/guiHeader.php';
?>

<script src="http://code.highcharts.com/modules/exporting.js"></script>
<script src="http://highcharts.github.io/export-csv/export-csv.js"></script>
<title>Temperature chart - <?=gethostname();?></title>

<script>
    $(function () {
        $('#container').highcharts({
            credits: {
                enabled: false
            },
            chart: {
                type: 'spline',
                zoomType: 'x'
            },
            title: {
                text: 'Temperature'
            },
            xAxis: {
                type: 'datetime',
                title: {
                    text: 'Date'
                }
            },
            yAxis: [{
                    title: {
                        text: 'Temperature (\xB0C)'
                    }
                }
            ],
            tooltip: {
                shared: false,
                headerFormat: '<b>{point.x:%Y-%m-%d %H:%M}</b><br>',
                pointFormat: '<span style="color:{point.color}">\u25CF</span> {series.name}: <b>{point.y:.1f}°C</b><br/>',
            },

            plotOptions: {
                spline: {
                    marker: {
                        enabled: false
                    }
                }
            },

            series: [

<?= fetchAllSeries($Database, $seriesIndexes, $seriesNames, $numDays, $selectedSamplingPeriod); ?>

            ]
        });
    });
    function validateForm() {
        var numDays = document.getElementsByName('numDays')[0].value;
        var samplingPeriod = document.getElementsByName('samplingPeriod')[0].value;
        if (parseInt(samplingPeriod, 10) === 0) {
            samplingPeriod = 1 / 6;
        }
        var roughEstNumResults = (numDays * 24) / samplingPeriod;
        if (roughEstNumResults > 9000) {
            alert('You have asked for a very large quantity of data. Please decrease the date range, or increase the averaging period');
            return false;
        } else {
            return true;
        }
    }
</script>
<style>
    table td { border: 1px solid #000000; 
               vertical-align: top;}
    </style>
</head>

<body>
    <p>Viewing last <?= $numDays ?> days</p>
    <div id="container" style="width: 100%; height: 80%;"></div>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="get" onsubmit="return validateForm()" name="theForm">
    <table>
        <tr>
            <td>
                1) Select time period:<br/>
                <select name="numDays">
                    <?= getTimePeriodOptionHTML($timePeriods, $numDays); ?>
                </select>
            </td>
            <td>
                2) Select sensor(s):<br/>
                <select name="seriesIndexes[]" multiple>
                    <?= getSensorOptionHTML($seriesNames, $seriesIndexes); ?>
                </select>
            </td>
            <td>
                3) Select averaging period:<br/>
                <select name="samplingPeriod">
                    <?= getSamplingPeriods($samplingPeriods, $selectedSamplingPeriod); ?>
                </select>
            </td>
            <td>
                4) <input type="submit" value="Go"/>
            </td>
    </table>
</form>
</body>
</html>
<?php

function getSamplingPeriods($samplingPeriods, $selectedSamplingPeriod) {
    $output = "";
    foreach ($samplingPeriods as $key => $value) {
        if ($key == $selectedSamplingPeriod) {
            $output = $output . "\t\t\t\t" . '<option selected value="' . $key . '">' . $value . "</option>\r\n";
        } else {
            $output = $output . "\t\t\t\t" . '<option value="' . $key . '">' . $value . "</option>\r\n";
        }
    }
    return $output;
}

function getTimePeriodOptionHTML($timePeriods, $numDays) {
    $output = "";
    foreach ($timePeriods as $key => $value) {
        if ($key == $numDays) {
            $output = $output . "\t\t\t\t" . '<option selected value="' . $key . '">' . $value . "</option>\r\n";
        } else {
            $output = $output . "\t\t\t\t" . '<option value="' . $key . '">' . $value . "</option>\r\n";
        }
    }
    return $output;
}

function getSensorOptionHTML($seriesNames, $seriesIndexes) {
    $output = "";
    for ($i = 0; $i < count($seriesNames); $i++) {
        if (in_array($i, $seriesIndexes)) {
            $output = $output . "\t\t\t\t" . '<option selected value="' . $i . '">' . $seriesNames[$i] . "</option>\r\n";
        } else {
            $output = $output . "\t\t\t\t" . '<option value="' . $i . '">' . $seriesNames[$i] . "</option>\r\n";
        }
    }
    return $output;
}

function getSeriesFromDB($Database) {
    $sql = "select distinct(sensor) from temps";
    foreach ($Database->query($sql) as $row) {
        $array[] = $row['sensor'];
    }
    return $array;
}

function fetchAllSeries($Database, $seriesIndexes, $seriesNames, $numDays, $selectedSamplingPeriod) {
    $output = "";
    foreach ($seriesIndexes as $i => $currentSeriesIndex) {
        $sensor = $seriesNames[$currentSeriesIndex];
        $output = $output . "{\r\n\t\tname: '" . $sensor . "',\r\n\t\tdata: [\r\n";
        // Sensible values 10 min, 1 hour, 6 hours, 1 day, 1 week (increases by approx factor of 6 each time)
        switch ($selectedSamplingPeriod) {
            case 24:
                // Take daily averages
                $sql = "select strftime('%Y',timestamp,'localtime') as year,  	strftime('%m',timestamp,'localtime') as month,  	strftime('%d',timestamp,'localtime') as day, " .
                        "'00' as hour, '00' as minute, avg(value) as value " .
                        "from temps where timestamp > datetime('now','-" . $numDays . " days') and sensor='" . $sensor . "' " .
                        "group by strftime('%Y',timestamp,'localtime'),  	strftime('%m',timestamp,'localtime'),  	strftime('%d',timestamp,'localtime') order by timestamp;";
                break;
            case 6: //split day into 6 hour chunks
                $sql = "select strftime('%Y',timestamp,'localtime') as year,        strftime('%m',timestamp,'localtime') as month,          strftime('%d',timestamp,'localtime') as day, " .
                        "strftime('%H',timestamp,'localtime') as hour, '00' as minute, avg(value) as value " .
                        "from temps where timestamp > datetime('now','-" . $numDays . " days') and sensor='" . $sensor . "' " .
                        "group by strftime('%Y',timestamp,'localtime'),         strftime('%m',timestamp,'localtime'),   strftime('%d',timestamp,'localtime'), strftime('%H',timestamp,'localtime') / 6 order by timestamp;";
                break;
            case 1:
                // Take hourly averages
                $sql = "select strftime('%Y',timestamp,'localtime') as year,  	strftime('%m',timestamp,'localtime') as month,  	strftime('%d',timestamp,'localtime') as day, " .
                        "strftime('%H',timestamp,'localtime') as hour, '00' as minute, avg(value) as value " .
                        "from temps where timestamp > datetime('now','-" . $numDays . " days') and sensor='" . $sensor . "' " .
                        "group by strftime('%Y',timestamp,'localtime'),  	strftime('%m',timestamp,'localtime'),  	strftime('%d',timestamp,'localtime'), strftime('%H',timestamp,'localtime') order by timestamp;";
                break;
            default:
            case 0:
                // Resolution determined by amount of data
                $sql = "select strftime('%Y',timestamp,'localtime') as year,  	strftime('%m',timestamp,'localtime') as month,  	strftime('%d',timestamp,'localtime') as day, " .
                        "strftime('%H',timestamp,'localtime') as hour,  	strftime('%M',timestamp,'localtime') as minute,  	value " .
                        "from temps where sensor='" . $sensor . "' " .
                        "and timestamp > datetime('now','-" . $numDays . " days') order by timestamp;";
                break;
        }

        $result = $Database->query($sql);
        $js = "";
        while ($row = $result->fetch()) {
            $js = $js . "\t\t\t[Date.UTC(" . $row['year'] . "," . ($row['month'] - 1) . "," . $row['day'] . "," .
                    $row['hour'] . "," . $row['minute'] . "), " . $row['value'] . "],\n";
        }
        $output = $output . $js;
        $output = $output . "\t\t],\r\n";

        $zones = "\t\tzones: [\n\t\t\t{value: 0, color: '#f7a35c'},\n";
        $zones = $zones . "\t\t\t{value: 10,color: '#7cb5ec'},\n";
        $zones = $zones . "\t\t\t{color: '#90ed7d'}\n";
        $zones = $zones . "\t\t],\n";
        if ($sensor == 'DHT22_Temp_disabled') {
            $output = $output . $zones;
        }
        $output = $output . "},\r\n\r\n";
    }
    return $output;
}

// Read page vars
function getNumDays() {
    if (!empty($_GET["numDays"])) {
        $numDays = filter_input(INPUT_GET, 'numDays', FILTER_SANITIZE_NUMBER_INT);
    } else {
        $numDays = 3; // Default time period on first entering the page
    }
    return $numDays;
}

function getSelectedSeries() {
    if (isset($_GET["seriesIndexes"])) {
        $arrayIsSafe = true;
        foreach ($_GET["seriesIndexes"] as $key => $value) {
            // Need to ensure every element is an int
            if ($key != (int) $key || $value != (int) $value) {
                $arrayIsSafe = false;
            }
            if ($arrayIsSafe) {
                $seriesIndexes = $_GET["seriesIndexes"];
            } else {
                die("Unknown page param");
            }
        }
    } else {
        $seriesIndexes = array("0", "1");
    }
    return $seriesIndexes;
}

function getSelectedSamplingPeriod() {
    if (isset($_GET["samplingPeriod"])) {
        $selectedSamplingPeriod = filter_input(INPUT_GET, 'samplingPeriod', FILTER_SANITIZE_NUMBER_INT);
    } else {
        $selectedSamplingPeriod = 0;
    }
    return $selectedSamplingPeriod;
}
