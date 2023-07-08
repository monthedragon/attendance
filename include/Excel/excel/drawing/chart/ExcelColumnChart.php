<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

class ExcelColumnChart extends ExcelChartCommon
{
    /**
     * @var string chart unique identifier
     */
    public $chartType = "column";

    /**
     * this creates the whole dom document of the Chart Data
     * @param $sheetname sheetname
     */
    public function createDataTable($sheetname) {
        $header = $this->createHeadersDom($sheetname);

        $_c = ExcelUtil::splitColumnRowFromCell($this->dataObject->chartDataCell);
        $new_data_cell_start_point = $_c['col'].$this->dataObject->chartStartingPointToDrawChartForData;

        if ($header) {
            $ranges = ExcelUtil::getAllCellFrom($new_data_cell_start_point, count($this->dataObject->getChartRowData()), "row");

        }
        else {
            $ranges = ExcelUtil::getAllCellFrom($new_data_cell_start_point, count($this->dataObject->getChartRowData()) - 1, "row");
        }

        foreach($this->dataObject->getChartRowData() as $index => $row) {
            //new ser data
            $row_dom = $this->createRowDataTemplateDom($index);

            //set header data
            if ($header) {
                $row_dom->getElementsByTagName("cat")->item(0)->appendChild( $row_dom->importNode($header, true) );
            }
            else {
                $remove_header_element = $row_dom->getElementsByTagName("cat")->item(0);
                $row_dom->getElementsByTagName("rowData")->item(0)->getElementsByTagName("ser")->item(0)->removeChild($remove_header_element);
            }

            //set series data
            if ($this->dataObject->getIsHasSeriesNames()) {
                $series = $this->createSeriesDom($row['series'],$sheetname, $ranges[$index]);
                $row_dom->getElementsByTagName("tx")->item(0)->appendChild( $row_dom->importNode($series, true) );
            }
            else {
                $remove_series_element = $row_dom->getElementsByTagName("tx")->item(0);
                $row_dom->getElementsByTagName("rowData")->item(0)->getElementsByTagName("ser")->item(0)->removeChild($remove_series_element);
            }
            array_shift($row);
            //set the row data

            $data = $this->createRowDataDom($ranges[$index], $row['row'], $sheetname);
            $row_dom->getElementsByTagName("val")->item(0)->appendChild( $row_dom->importNode($data, true) );

            //add the new ser data to line chart dom
            $row = $row_dom->getElementsByTagName("ser")->item(0);
            $this->dom->getElementsByTagName("barChart")->item(0)->appendChild($this->dom->importNode($row, true));
        }

    }

    /**
     * sets all the settings of the chart
     * fonts, font color, and other setings are being populated here
     */
    public function updateChartDataTableSettings() {
        $this->dom->getElementsByTagName("title")->item(0)->getElementsByTagName("tx")->item(0)->getElementsByTagName("t")->item(0)->nodeValue = $this->dataObject->getChartTitle();
        $this->dom->getElementsByTagName("valAx")->item(0)->getElementsByTagName("title")->item(0)->getElementsByTagName("t")->item(0)->nodeValue = $this->dataObject->getChartAxisTitle();

        if (!$this->dataObject->getShowChartDataTable()) {
            $dtable = $this->dom->getElementsByTagName("dTable")->item(0);
            $this->dom->getElementsByTagName("plotArea")->item(0)->removeChild($dtable);
        }

        if (!$this->dataObject->getShowChartLegends()) {
            $legend = $this->dom->getElementsByTagName("legend")->item(0);
            $this->dom->getElementsByTagName("chart")->item(0)->removeChild($legend);
        }

        if ($this->dataObject->getShowChartDataLabels()) {
            $this->dom->getElementsByTagName("barChart")->item(0)->getElementsByTagName("dLbls")->item(0)->getElementsByTagName("showVal")->item(0)->setAttribute("val", 1);
        }

        if ($this->dataObject->getChartBackgroundColor()) {
            $this->dom->getElementsByTagName("spPr")->item(0)->getElementsByTagName("solidFill")->item(0)->getElementsByTagName("srgbClr")->item(0)->setAttribute("val", $this->dataObject->getChartBackgroundColor());
        }

        if ($this->dataObject->getChartFontSize()) {
            $this->dom->getElementsByTagName("txPr")->item(0)->getElementsByTagName("p")->item(0)->getElementsByTagName("defRPr")->item(0)->setAttribute("sz", $this->dataObject->getChartFontSize() * 100);
        }

        if ($this->dataObject->getChartFontColor()) {
            $this->dom->getElementsByTagName("txPr")->item(0)->getElementsByTagName("p")->item(0)->getElementsByTagName("defRPr")->item(0)->getElementsByTagName("srgbClr")->item(0)->setAttribute("val", $this->dataObject->getChartFontColor());
        }

        if ($this->dataObject->getChartFontFamily()) {
            $this->dom->getElementsByTagName("txPr")->item(0)->getElementsByTagName("p")->item(0)->getElementsByTagName("defRPr")->item(0)->getElementsByTagName("latin")->item(0)->setAttribute("typeface", $this->dataObject->getChartFontFamily());
            $this->dom->getElementsByTagName("txPr")->item(0)->getElementsByTagName("p")->item(0)->getElementsByTagName("defRPr")->item(0)->getElementsByTagName("ea")->item(0)->setAttribute("typeface", $this->dataObject->getChartFontFamily());
        }

        if ($this->dataObject->getGroupingDisplay() != "clustered") {
            $this->dom->getElementsByTagName("plotArea")->item(0)->getElementsByTagName("grouping")->item(0)->setAttribute("val", $this->dataObject->getGroupingDisplay());
        }
    }

    /**
     * creates the raw rowData xml dom document
     * @param int $index
     * @return DOMDocument
     */
    public function createRowDataTemplateDom($index = 0) {
        $d = new DOMDocument();
        $d->loadXML('
            <rowData xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart">
                <c:ser>
                    <c:idx val="'.$index.'"/>
                    <c:order val="'.$index.'"/>
                    <c:tx/>
                    <c:invertIfNegative val="1"/>
                    <c:cat/>
                    <c:val/>
                    <c:smooth val="0"/>
                </c:ser>
            </rowData>
        ');
        return $d;
    }

    /**
     * This is a special function for Column chart only.
     * By default the template is just a plain flat column. So if the user
     * activated the 3D feature some of the elements in the dom must be updated
     * to handle 3D features like Cylinder, Cone Pyramid and also a 3D Box shapes.
     * This is the purpose of this function.
     *
     * IF 3D
     * The logic is transform all the dom elements for 3D and at the end you will notice that
     * we rename barChart to bar3DChart
     */
    public function specialColumnChartFunctions() {
        if (in_array($this->dataObject->getGroupingDisplay(),array("stacked", "percentStacked"))) {
            $overlap = $this->dom->createElement("c:overlap");
            $overlap->setAttribute("val", 100);
            $this->dom->getElementsByTagName("barChart")->item(0)->appendChild($overlap);

            $dLblPos = $this->dom->getElementsByTagName("barChart")->item(0)->getElementsByTagName("dLbls")->item(0)->getElementsByTagName("dLblPos")->item(0);
            $this->dom->getElementsByTagName("barChart")->item(0)->getElementsByTagName("dLbls")->item(0)->removeChild($dLblPos);
        }

        if ($this->dataObject->getGroupingDisplay() == "standard" || $this->dataObject->getColumnShape() != "box") {
            $this->dataObject->set3D();
        }

        if ($this->dataObject->getIs3D()) {
            $dLblPos = $this->dom->getElementsByTagName("barChart")->item(0)->getElementsByTagName("dLbls")->item(0)->getElementsByTagName("dLblPos")->item(0);
            if ($dLblPos) {
                $this->dom->getElementsByTagName("barChart")->item(0)->getElementsByTagName("dLbls")->item(0)->removeChild($dLblPos);
            }

            //chart insert
                $chartBackgroundColor = $this->dataObject->getChartGraphBackgroundColor() ? $this->dataObject->getChartGraphBackgroundColor() : "FFFFFF";
                $d = new DOMDocument();
                $d->loadXML('
                    <chartData xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
                        <c:view3D>
                            <c:rotX val="15"/>
                            <c:rotY val="20"/>
                            <c:rAngAx val="1"/>
                        </c:view3D>
                        <c:floor>
                            <c:thickness val="0"/>
                        </c:floor>
                        <c:sideWall>
                            <c:thickness val="0"/>
                            <c:spPr>
                                <a:solidFill>
                                    <a:srgbClr val="'.$chartBackgroundColor.'"/>
                                </a:solidFill>
                            </c:spPr>
                        </c:sideWall>
                        <c:backWall>
                            <c:thickness val="0"/>
                            <c:spPr>
                                <a:solidFill>
                                    <a:srgbClr val="'.$chartBackgroundColor.'"/>
                                </a:solidFill>
                            </c:spPr>
                        </c:backWall>
                    </chartData>
                ');

                $this->dom->getElementsByTagName("chart")->item(0)->appendChild( $this->dom->importNode($d->getElementsByTagName("view3D")->item(0), true) );
                $this->dom->getElementsByTagName("chart")->item(0)->appendChild( $this->dom->importNode($d->getElementsByTagName("floor")->item(0), true) );
                $this->dom->getElementsByTagName("chart")->item(0)->appendChild( $this->dom->importNode($d->getElementsByTagName("sideWall")->item(0), true) );
                $this->dom->getElementsByTagName("chart")->item(0)->appendChild( $this->dom->importNode($d->getElementsByTagName("backWall")->item(0), true) );

            //shape
                $shape = $this->dom->createElement("c:shape");
                $shape->setAttribute("val", $this->dataObject->getColumnShape());
                $this->dom->getElementsByTagName("barChart")->item(0)->appendChild($shape);

            //serAX
                $axId = $this->dom->createElement("c:axId");
                $axId->setAttribute("val", "50149120");
                $this->dom->getElementsByTagName("barChart")->item(0)->appendChild($axId);

                $d = new DOMDocument();
                $d->loadXML('
                        <serAxData xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart">
                            <c:serAx>
                                <c:axId val="50149120"/>
                                <c:scaling>
                                    <c:orientation val="minMax"/>
                                </c:scaling>
                                <c:delete val="0"/>
                                <c:axPos val="b"/>
                                <c:majorTickMark val="out"/>
                                <c:minorTickMark val="none"/>
                                <c:tickLblPos val="nextTo"/>
                                <c:crossAx val="82701696"/>
                            </c:serAx>
                        </serAxData>
                    ');
                $this->dom->getElementsByTagName("plotArea")->item(0)->appendChild( $this->dom->importNode($d->getElementsByTagName("serAx")->item(0), true) );

            //legend position
                if ($this->dataObject->getShowChartLegends() && $this->dataObject->getGroupingDisplay() == "standard") {
                    $this->dom->getElementsByTagName("legend")->item(0)->getElementsByTagName("legendPos")->item(0)->setAttribute("val", "t");
                }

            //rename barChart to bar3DChart
                $barChartXML = $this->dom->saveXML();
                $barChartXML = str_replace("c:barChart", "c:bar3DChart", $barChartXML);
                $d = new DOMDocument();
                $d->loadXML($barChartXML);
                $this->dom = $d;
        }
        else {
            //chart background
            $chartBackgroundColor = $this->dataObject->getChartGraphBackgroundColor() ? $this->dataObject->getChartGraphBackgroundColor() : "FFFFFF";
            $d = new DOMDocument();
            $d->loadXML('
                        <chartColor xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
                            <c:spPr>
                                <a:solidFill>
                                    <a:srgbClr val="'.$chartBackgroundColor.'"/>
                                </a:solidFill>
                            </c:spPr>
                        </chartColor>
                    ');

            $this->dom->getElementsByTagName("plotArea")->item(0)->appendChild( $this->dom->importNode($d->getElementsByTagName("spPr")->item(0), true) );
        }
    }

    /******************************************************************************************
     * Finalization methods (draw charts and propagate the row data to the excel cells)
     ******************************************************************************************/

    /**
     * This is the main function of the writer
     * All the doms and other settings are all wrap here
     *
     * @param $excel_xml_object
     */
    public function writeFinalXMLDom($excel_xml_object) {
        if ($this->dataObject->getChartHeader()) {
            $this->dataObject->chartHeaderCell = $this->dataObject->getChartDataTableCellStart();
            $r = ExcelUtil::getAllCellFrom($this->dataObject->chartHeaderCell, 1, "row");
            $this->dataObject->chartDataCell = $r[1];

            $this->dataObject->chartStartingPointToDrawChartForHeader = $excel_xml_object->getCurrentSheetRow();
            $this->dataObject->chartStartingPointToDrawChartForData = $excel_xml_object->getCurrentSheetRow() + 1;
        }
        else {
            $this->dataObject->chartDataCell = $this->dataObject->getChartDataTableCellStart();
            $this->dataObject->chartStartingPointToDrawChartForData = $excel_xml_object->getCurrentSheetRow();
        }

        //proceed on writing
        $sheetnames = $excel_xml_object->workbook->getSheetNames();
        $this->createDataTable($sheetnames[$excel_xml_object->worksheet->getCurrentWorkingSheet()]);

        $this->updateChartDataTableSettings();
        $this->specialColumnChartFunctions();

        //Lastly, we need to write the rowData to output cell
        $this->writeRowDataToExcelCells($excel_xml_object);
    }

}

?>