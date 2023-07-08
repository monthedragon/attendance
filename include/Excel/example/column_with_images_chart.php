<?php
include_once("include/Excel/Excel.php");

$excel_xml = new Excel();
$excel_xml->setFileName("excel_file_".time());
$chart = $excel_xml->createChart(Excel::COLUMN_CHART);

$chart->setChartTitle("Attendance Summary (Science Symposium)");
$chart->setChartAxisTitle("Axis vertical");

$chart->setDataTableCellStart("A3");
$chart->showChartDataLabels();
$chart->showChartDataTable();
$chart->showChartLegends();

$chart->setColumnShape("box"); // column shapes (box as default, cylinder, pyramid, cone)
$chart->setGroupingDisplay("clustered"); //this is for column chart only (clustered as default, stacked, percentStacked, standard)
//$chart->set3D(); //if you want the box to be a 3D version use this. "cylinder, pyramid, cone" are default to be in 3D.

$chart->setChartLocation(
    array(
        "from" => array(
            "cell"    => "G1",
            "colOff"  => 0,
            "rowOff"  => 0,
        ),
        "to" => array(
            "cell"    => "U37",
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
$rows = 5;
for($i = 1; $i <= $rows; $i++) {
    $data = array();
    for ($a = 1; $a <= 5; $a++) {
        $data[] = mt_rand(0,1000);
    }
    $chart->setChartRowData($data, "Class $i");
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

//generate the chart to drawing object
//sheet 1
$excel_xml->addChart($chart);
addClassImages($excel_xml);

//sheet 2
$excel_xml->setActiveWorkingSheet(2);
$chart->setColumnShape("cylinder");
$chart->setGroupingDisplay("standard");
$excel_xml->addChart($chart);


//sheet 3
$excel_xml->setActiveWorkingSheet(3);
$chart->setColumnShape("pyramid");
$chart->setGroupingDisplay("stacked");
$excel_xml->addChart($chart);

//sheet 4
$excel_xml->setActiveWorkingSheet(4);
$chart->setColumnShape("cone");
$chart->setGroupingDisplay("percentStacked");
$excel_xml->addChart($chart);


$excel_xml->download();


//just adding an image
function addClassImages($excel_xml) {
    $excel_xml->addRows(8, 2);
    $style = array(
        "cell" => array(
            "background" => array(
                "color" => "F8D796",
            ),
            "border" => array(
                "all" => array(
                    "style" => "hair"
                )
            )
        ),
        "text" => array(
            "align-x" => "center",
            "align-y" => "center",
        )
    );

    $start = 8;
    $gap = 5;
    for($i = 1; $i <= 5; $i++){
        $class = 'include/Excel/example/template/sample_images/class'.$i.'.jpg';
        $image_location = array(
            "from" => array(
                "cell"    => "B".$start,
                "colOff"  => 1,
                "rowOff"  => 0,
            ),
            "to" => array(
                "cell"    => "F".($start+$gap),
                "colOff"  => 0,
                "rowOff"  => 0,
            ),
        );

        $excel_xml->createImage($class, $image_location);


        $excel_xml->setCellValue("A".$start, "Class ".$i);
        $excel_xml->setCellStyle("A".$start, $excel_xml->createNewStyle($style));
        $excel_xml->addRows($start, $gap + 1);
        $start = $start + $gap + 1;
    }
}