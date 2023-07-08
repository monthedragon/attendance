<?php
include_once("include/Excel/Excel.php");

$excel_xml = new Excel();
$excel_xml->setFileName("excel_file_".time());
 
//Create chart object and set the settings
$chart = $excel_xml->createChart(Excel::LINE_CHART);
$chart->setChartTitle("Attendance Summary (Science Symposium)");
$chart->setChartAxisTitle("Attendance");
$chart->setDataTableCellStart("A3");
$chart->showChartDataTable();

//    $chart->showChartDataLabels();
//    $chart->setShowChartMarker();

$chart->showChartLegends();
$chart->setChartLocation(
    array(
        "from" => array(
            "cell"    => "G1",
            "colOff"  => 0,
            "rowOff"  => 0,
        ),
        "to" => array(
            "cell"    => "S30",
            "colOff"  => 0,
            "rowOff"  => 0,
        )
    )
);
$chart->setChartBackgroundColor("F8D796");
$chart->setChartGraphBackgroundColor("F2F2C6");

$chart->setChartFontSize(10);
$chart->setChartFontColor("000000");
$chart->setChartFontFamily("Calibri");

$chart->setChartHeader(array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday"));
//generate rows of data
//example: $chart->setRowData(array(23,24,12,323,123), "Class 1");
$rows = 10;
for($i = 1; $i <= $rows; $i++) {
    $data = array();
    for ($a = 1; $a <= 5; $a++) {
        $data[] = mt_rand(0,1000);
    }
    $chart->setChartRowData($data, "Class $i");
}


//generate the chart to drawing object
$excel_xml->addChart($chart);
$excel_xml->download();
