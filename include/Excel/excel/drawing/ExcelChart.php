<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */


include_once "include/Excel/excel/drawing/chart/data/common/ExcelChartData.php";
include_once "include/Excel/excel/drawing/chart/ExcelChartCommon.php";

$charts_arr = array("Line", "Bar", "Radar", "Pie", "Column");
foreach($charts_arr as $chart) {
    include_once "include/Excel/excel/drawing/chart/Excel".$chart."Chart.php";
    include_once "include/Excel/excel/drawing/chart/data/Excel".$chart."ChartData.php";
}

/**
 * Class ExcelXMLObjectDrwaingChart
 * This class is the main class when
 * creating a chart in excel.
 */
class ExcelChart {

    /**
     * Raw xml structure of a drawing
     * @var string
     */
    private $imageRawXMLSettings = '
        <xdr:twoCellAnchor editAs="oneCell">
            <xdr:from>
                <xdr:col>--from_col--</xdr:col>
                <xdr:colOff>--from_col_off--</xdr:colOff>
                <xdr:row>--from_row--</xdr:row>
                <xdr:rowOff>--from_row_off--</xdr:rowOff>
            </xdr:from>
            <xdr:to>
                <xdr:col>--to_col--</xdr:col>
                <xdr:colOff>--to_col_off--</xdr:colOff>
                <xdr:row>--to_row--</xdr:row>
                <xdr:rowOff>--to_row_off--</xdr:rowOff>
            </xdr:to>
            <xdr:graphicFrame macro="">
                <xdr:nvGraphicFramePr>
                    <xdr:cNvPr id="--chart_id--" name="Chart --chart_id--"/>
                    <xdr:cNvGraphicFramePr/>
                </xdr:nvGraphicFramePr>
                <xdr:xfrm>
                    <a:off x="0" y="0"/>
                    <a:ext cx="0" cy="0"/>
                </xdr:xfrm>
                <a:graphic>
                    <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/chart">
                        <c:chart xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:id="rId--chart_id--"/>
                    </a:graphicData>
                </a:graphic>
            </xdr:graphicFrame>
            <xdr:clientData/>
        </xdr:twoCellAnchor>
    ';

    public function __construct(){ }

    /**
     * Create an instance of the chart style you want
     *
     * @param string $chart_type_object
     * @return mixed
     * @throws Exception
     */
    public function getChartData($chart_type_object = 'Line'){
        $chart_type_object = "Excel".$chart_type_object."ChartData";
        $chart = new $chart_type_object;
        return $chart;
    }

    /**
     * creates new chart
     *
     * @param $chart                Chart object
     * @param $drawingObj           Drwaing Object
     * @param $excel_xml_object     The main object
     * @return int                  The chart ID
     * @throws Exception
     */
    public function newChart($chart, $drawingObj, $excel_xml_object) {
        if (!$drawingObj instanceof ExcelDrawing) {
            throw new Exception("newChart must be supplied by 2 objects ExcelXMLObjectDrawingChart and ExcelXMLObjectDrawing");
        }

        $sheet_index = $excel_xml_object->worksheet->getCurrentWorkingSheet();

        $attributes = array();
        $attributes = $chart->getChartLocation();
        $attributes['chart_id'] = isset($attributes['chart_id']) ? $attributes['chart_id'] : $drawingObj->drawingCounter++;

        // xl\drawings\drawing{$sheet_index}.xml
        if (!isset($drawingObj->drawingXMLDom[$sheet_index])) {
            $d = new DOMDocument();
            $d->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" '."".'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"></xdr:wsDr>');
            $drawingObj->drawingXMLDom[$sheet_index] = $d;
        }

        $xml_string = $this->imageRawXMLSettings;
        foreach($attributes as $k => $v) {
            $xml_string = str_replace("--$k--", $v, $xml_string);
        }
        $_twoCellAnchor = new DOMDocument();
        $_twoCellAnchor->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'.$xml_string.'</xdr:wsDr>');
        $twoCellAnchor = $_twoCellAnchor->getElementsByTagName("twoCellAnchor")->item(0);
        $new_twoCellAnchor = $drawingObj->drawingXMLDom[$sheet_index]->importNode($twoCellAnchor,  true);
        $drawingObj->drawingXMLDom[$sheet_index]->getElementsByTagName("wsDr")->item(0)->appendChild($new_twoCellAnchor);


        // xl\drawings\_rels\drawing{$sheet_index}.xml.rels
        if (!isset($drawingObj->drawingXMLRelDom[$sheet_index])) {
            $d = new DOMDocument();
            $d->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
            $drawingObj->drawingXMLRelDom[$sheet_index] = $d;
        }
        $relationship = $drawingObj->drawingXMLRelDom[$sheet_index]->createElement("Relationship");
        $relationship->setAttribute("Id", "rId". $attributes['chart_id']);
        $relationship->setAttribute("Type", "http://schemas.openxmlformats.org/officeDocument/2006/relationships/chart");
        $relationship->setAttribute("Target", "../charts/chart".$attributes['chart_id'].".xml");
        $drawingObj->drawingXMLRelDom[$sheet_index]->getElementsByTagName("Relationships")->item(0)->appendChild($relationship);

        // xl\worksheets\_rels\sheets{$sheet_index}.xml.rels
        if (!isset($drawingObj->imageSheetRelDom[$sheet_index])) {
            $d = new DOMDocument();
            $d->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
            $drawingObj->imageSheetRelDom[$sheet_index] = $d;

            $relationship = $drawingObj->imageSheetRelDom[$sheet_index]->createElement("Relationship");
            $relationship->setAttribute("Id", "rId". $sheet_index);
            $relationship->setAttribute("Type", "http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing");
            $relationship->setAttribute("Target", "../drawings/drawing".$sheet_index.".xml");
            $drawingObj->imageSheetRelDom[$sheet_index]->getElementsByTagName("Relationships")->item(0)->appendChild($relationship);
        }


        /************************************************************
         * this is where chart objects writers are called and
         * the chart data object processing starts here
         ***********************************************************/
        $writer = "Excel".ucfirst($chart->chartType)."Chart";
        $writer = new $writer;
        $writer->dataObject = $chart;
        $writer->dom = $excel_xml_object->util->createDomObject($excel_xml_object->util->defaultExcelTemplateFolder.'/chart_'.strtolower($chart->chartType).'.xml', true);
        $writer->writeFinalXMLDom($excel_xml_object, $chart);
        $drawingObj->chartDom[$attributes['chart_id']] = $writer->dom;
        return $attributes['chart_id'];
    }
}

?>