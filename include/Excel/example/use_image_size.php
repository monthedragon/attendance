<?php
include_once("include/Excel/Excel.php");
$excel_xml = new Excel();
$excel_xml->setFileName("excel_file_".time());

//before adding the image please add first the rows where the image will be expected to be drawn.
//this is to make an accurate calculation of the image sizes
$excel_xml->addRows(1, 20);

$class = 'include/Excel/example/template/sample_images/class1.jpg';
$image_location = array(
    "from" => array(
        "cell"    => "A1",
        "colOff"  => 0,
        "rowOff"  => 0,
    ),
//    just dont fillup "to" parameter to use the default image size
//    "to" => array(
//        "cell"    => "F6",
//        "colOff"  => 0,
//        "rowOff"  => 0,
//    ),
);
$excel_xml->createImage($class, $image_location);
$excel_xml->download();

