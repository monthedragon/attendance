<?php
include_once("include/Excel/Excel.php");

$template_path = 'include/Excel/example/template/template.xlsx';
$excel_xml = new Excel($template_path);
$excel_xml->setFileName("excel_file_".time());

//Sheet #1
//rename active sheet
$excel_xml->setActiveSheetName("1New Sheetname ".date("Y-m-d"));
//sheet 1
$excel_xml->addRows(1, 8);
$excel_xml->setCellValue("A9", 'This is a string');
$excel_xml->addRows(9, 1);
$excel_xml->setCellValue("A9", 34, Excel::NUMBER);
$excel_xml->addRows(9, 1);
$excel_xml->setCellValue("A9", 43, Excel::NUMBER);
$excel_xml->addRows(9, 1);

//Sheet #2
$excel_xml->setActiveWorkingSheet(2);
$excel_xml->setActiveSheetName("2New Sheetname ".date("Y-m-d"));
$excel_xml->addRows(1, 8);

//Sheet #1
//sheet 1
$excel_xml->setActiveWorkingSheet(1);
$excel_xml->setCellValue("A9", 348, Excel::NUMBER);
$excel_xml->addRows(9, 1);
$excel_xml->setCellValue("A9", 8123, Excel::NUMBER);
$excel_xml->addRows(9, 1);

$excel_xml->setCellFormula("B12", "SUM(A10:A13)");
$excel_xml->addRows(11, 2);

$excel_xml->mergeCell("O9", "Q15");

//download the Excel File
$excel_xml->download();
