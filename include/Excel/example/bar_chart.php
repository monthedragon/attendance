<?php
include_once("include/Excel/Excel.php");

$excel_xml = new Excel();
$excel_xml->setFileName("excel_file_".time());

//Start creating the chart
$chart = $excel_xml->createChart(Excel::BAR_CHART);
$chart->setChartTitle("Attendance Summary (Science Symposium)");
$chart->setChartAxisTitle("Attendance");
$chart->setDataTableCellStart("A3");
//$chart->showChartDataTable();
//$chart->showChartDataLabels();
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
$rows = 10;
for($i = 1; $i <= $rows; $i++) {
    $data = array();
    for ($a = 1; $a <= 5; $a++) {
        $data[] = mt_rand(0,1000);
    }
    $chart->setChartRowData($data, "Length $i");
}
$style = array(
    "cell" => array(
        "background" => array(
            "color" => "EDF0CB",
        ),
        "border" => array(
            "all" => array(
                "style" => "hair"
            )
        )
    )
);
$chart->setChartHeaderStyle($excel_xml->createNewStyle($style));
$style['cell']['background']['color'] = "DAE193";
$chart->setChartDataCellStyle($excel_xml->createNewStyle($style));
$style['cell']['background']['color'] = "B1B771";
$chart->setChartSeriesStyle($excel_xml->createNewStyle($style));

//start drawing the chart on the excel file
$excel_xml->addChart($chart);

//download the Excel File
$excel_xml->download();