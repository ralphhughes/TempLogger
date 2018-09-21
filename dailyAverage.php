<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/util.php';

$numDays = getNumDays();
$seriesIndexes = getSelectedSeries();
$seriesNames = getSeriesFromDB($Database);
$timePeriods = array("1" => "1 Day", "2" => "2 Days", "4" => "4 Days", "7" => "1 Week", "14" => "2 Weeks", "30" => "1 Month", "42" => "6 Weeks", "91" => "3 Months", "183" => "6 Months", "274" => "9 Months", "365" => "1 Year", "548" => "18 Months", "731" => "2 Years");

include 'includes/guiHeader.php';
?>

<script src="http://code.highcharts.com/modules/exporting.js"></script>
<script src="http://highcharts.github.io/export-csv/export-csv.js"></script>
<title>Daily averages chart - <?= gethostname(); ?></title>

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
                min: Date.UTC(0, 0, 0, 0, 0),
                max: Date.UTC(0, 0, 0, 24, 0),
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
                headerFormat: '<b>{point.x:%H:%M}</b><br>',
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

<?= fetchAllSeries($Database, $seriesIndexes, $seriesNames, $numDays); ?>

            ]
        });
    });
</script>
</head>

<body>
    <p>Viewing last <?= $numDays ?> days</p>


    <div id="container" style="width: 100%; height: 80%;"></div>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="get" onsubmit="return validateForm()" name="theForm">
        <select name="numDays">
            <?= getTimePeriodOptionHTML($timePeriods, $numDays); ?>
        </select>

        Select sensor(s):<br/>
        <select name="seriesIndexes[]" multiple>
            <?= getSensorOptionHTML($seriesNames, $seriesIndexes); ?>
        </select>
        <input type="submit" value="Go"/>
    </form>
    <a href="<?php echo str_replace('dailyAverage.php', 'temperatureChart.php', $_SERVER['REQUEST_URI']); ?>">Back to chart</a>
</body>
</html>

<?php

// Read page vars
function getNumDays() {
    if (!empty($_GET["numDays"])) {
        $numDays = filter_input(INPUT_GET, 'numDays', FILTER_SANITIZE_NUMBER_INT);
    } else {
        $numDays = 2; // Default time period on first entering the page
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

function fetchAllSeries($Database, $seriesIndexes, $seriesNames, $numDays) {
    $output = "";
    foreach ($seriesIndexes as $i => $currentSeriesIndex) {
        $sensor = $seriesNames[$currentSeriesIndex];
        $output = $output . "{\r\n\t\tname: '" . $sensor . "',\r\n\t\tdata: [\r\n";
        // Sensible values 10 min, 1 hour, 6 hours, 1 day, 1 week (increases by approx factor of 6 each time)
        // Resolution determined by amount of data
        $sql = "SELECT " .
                "strftime('%H', TIMESTAMP, 'localtime') AS hour," .
                "strftime('%M', timestamp, 'localtime') as minute, " .
                "avg(value) as value " .
                "from temps " .
                "where sensor='" . $sensor . "' " .
                "        and timestamp > datetime('now','-" . $numDays . " days') " .
                "group by strftime('%H', timestamp, 'localtime'), strftime('%M', timestamp, 'localtime');";

        $result = $Database->query($sql);
        $js = "";
        while ($row = $result->fetch()) {
            $js = $js . "\t\t\t[Date.UTC(0,0,0," .
                    $row['hour'] . "," . $row['minute'] . "), " . $row['value'] . "],\n";
        }
        $output = $output . $js;
        $output = $output . "\t\t],\r\n";

        $output = $output . "},\r\n\r\n";
    }
    return $output;
}
