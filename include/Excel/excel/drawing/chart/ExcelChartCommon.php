<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

class ExcelChartCommon
{
    /**
     * @var dataObject  the holder of the Data Object of different charts.
     */
    public $dataObject;

    /**
     * @var $dom DomDocument    this is the holder of the dom document of the chart.
     */
    public $dom;

    /**
     * This creates the whole serVal of the dom.
     * Creates the row, headers, series data in chart dom.
     *
     * @param $sheetname    sheetname
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
            $this->dom->getElementsByTagName("radarChart")->item(0)->appendChild($this->dom->importNode($row, true));
        }

    }

    /**
     * sets all the settings of the chart
     * fonts, font color, and other setings are being populated here
     */
    public function updateChartDataTableSettings() {
        $this->dom->getElementsByTagName("title")->item(0)->getElementsByTagName("tx")->item(0)->getElementsByTagName("t")->item(0)->nodeValue = $this->dataObject->getChartTitle();

        if (!$this->dataObject->getShowChartLegends()) {
            $legend = $this->dom->getElementsByTagName("legend")->item(0);
            $this->dom->getElementsByTagName("chart")->item(0)->removeChild($legend);
        }

        if ($this->dataObject->getShowChartDataLabels()) {
            $this->dom->getElementsByTagName("radarChart")->item(0)->getElementsByTagName("dLbls")->item(0)->getElementsByTagName("showVal")->item(0)->setAttribute("val", 1);
        }

        if ($this->dataObject->getChartBackgroundColor()) {
            $this->dom->getElementsByTagName("spPr")->item(1)->getElementsByTagName("solidFill")->item(0)->getElementsByTagName("srgbClr")->item(0)->setAttribute("val", $this->dataObject->getChartBackgroundColor());
        }

        if ($this->dataObject->getChartGraphBackgroundColor()) {
            $this->dom->getElementsByTagName("plotArea")->item(0)->getElementsByTagName("spPr")->item(0)->getElementsByTagName("solidFill")->item(0)->getElementsByTagName("srgbClr")->item(0)->setAttribute("val", $this->dataObject->getChartGraphBackgroundColor());
        }

        if ($this->dataObject->getChartFontSize()) {
            $this->dom->getElementsByTagName("txPr")->item(0)->getElementsByTagName("p")->item(0)->getElementsByTagName("defRPr")->item(0)->setAttribute("sz", $this->dataObject->getChartFontSize() * 100);
        }

        if ($this->dataObject->getChartFontColor()) {
            $this->dom->getElementsByTagName("txPr")->item(0)->getElementsByTagName("p")->item(0)->getElementsByTagName("defRPr")->item(0)->getElementsByTagName("srgbClr")->item(0)->setAttribute("val", $this->dataObject->getChartFontColor());
        }

        if ($this->dataObject->getChartFontFamily()) {
            $this->dom->getElementsByTagName("txPr")->item(0)->getElementsByTagName("p")->item(0)->getElementsByTagName("defRPr")->item(0)->getElementsByTagName("latin")->item(0)->setAttribute("typeface", $this->dataObject->getChartFontFamily());
        }

        if ($this->dataObject->getChartRadarType() != "marker") {
            $this->dom->getElementsByTagName("radarStyle")->item(0)->setAttribute("val", $this->dataObject->getChartRadarType());
        }
    }

    /***********************************************
     *  Create Header, Series and the data row DOM
     **********************************************/
    /**
     * this creates a node for series
     *
     * @param $series       series name
     * @param $sheetname    sheetname
     * @param $cell         the cell where the series is located
     * @return DOMNode      series node
     */
    public function createSeriesDom($series, $sheetname, $cell) {
        $d = new DOMDocument();
        $d->loadXML('
            <seriesData xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart">
                <c:strRef>
                    <c:f/>
                    <c:strCache>
                        <c:ptCount val="0"></c:ptCount>
                    </c:strCache>
                </c:strRef>
           </seriesData>
        ');

        $cell = ExcelUtil::splitColumnRowFromCell($cell);
        $f = "'$sheetname'!$".$cell['col']."$".$cell['row'];

        $d->getElementsByTagName("seriesData")->item(0)->getElementsByTagName("f")->item(0)->nodeValue = $f;
        $d->getElementsByTagName("ptCount")->item(0)->setAttribute("val",1);

        $c = $d->createElement("c:pt");
        $c->setAttribute("idx", 0);
        $value = $d->createElement("c:v");
        $value->nodeValue = $series;
        $c->appendChild($value);

        $d->getElementsByTagName("strCache")->item(0)->appendChild($c);

        return $d->getElementsByTagName("strRef")->item(0);
    }

    /**
     * creates the header dom document object
     * @param $sheetname
     * @return bool|DOMNode
     */
    public function createHeadersDom($sheetname){
        $headers = $this->dataObject->getChartHeader();
        if (empty($headers)) { return false;}

        $d = new DOMDocument();
        $d->loadXML('
            <headerData xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart">
            <c:strRef>
                <c:f/>
                <c:strCache>
                    <c:ptCount val="0"/>
                </c:strCache>
            </c:strRef>
           </headerData>
        ');

        $_c = ExcelUtil::splitColumnRowFromCell($this->dataObject->chartHeaderCell);
        $new_header_cell_start_point = $_c['col'].$this->dataObject->chartStartingPointToDrawChartForHeader;

        $ranges = ExcelUtil::getAllCellFrom($new_header_cell_start_point, count($headers), "col");

        if ($this->dataObject->getIsHasSeriesNames()) {
            $from = ExcelUtil::splitColumnRowFromCell($ranges[1]);
            $to = ExcelUtil::splitColumnRowFromCell($ranges[count($ranges)-1]);
        }
        else {
            $from = ExcelUtil::splitColumnRowFromCell($ranges[0]);
            $to = ExcelUtil::splitColumnRowFromCell($ranges[count($ranges)-2]);
        }

        $f = "'$sheetname'!$".$from['col']."$".$from['row'].":$".$to['col']."$".$to['row'];
        $d->getElementsByTagName("headerData")->item(0)->getElementsByTagName("f")->item(0)->nodeValue = $f;
        $d->getElementsByTagName("ptCount")->item(0)->setAttribute("val", count($headers));
        foreach($headers as $index => $header) {
            $cell = $d->createElement("c:pt");
            $cell->setAttribute("idx", $index);
            $value = $d->createElement("c:v");
            $value->nodeValue = $header;
            $cell->appendChild($value);

            $d->getElementsByTagName("strCache")->item(0)->appendChild($cell);
        }
        return $d->getElementsByTagName("strRef")->item(0);
    }

    /**
     * this creates each row of the whole chart
     * @param $cell
     * @param $row
     * @param $sheetname
     * @return DOMNode
     */
    public function createRowDataDom($cell, $row, $sheetname){
        $d = new DOMDocument();
        $d->loadXML('
            <rowData xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart">
                <c:numRef>
                    <c:f/>
                    <c:numCache>
                        <c:formatCode>General</c:formatCode>
                        <c:ptCount val="0"></c:ptCount>
                    </c:numCache>
                </c:numRef>
           </rowData>
        ');


        $start_cell = $cell;
        $ranges = ExcelUtil::getAllCellFrom($start_cell, $this->dataObject->getIsHasSeriesNames() ? count($row) : count($row) - 1, "col");

        $from = $this->dataObject->getIsHasSeriesNames() ? ExcelUtil::splitColumnRowFromCell($ranges[1]) : ExcelUtil::splitColumnRowFromCell($ranges[0]);

        $to = ExcelUtil::splitColumnRowFromCell($ranges[count($ranges) - 1]);

        $f = "'$sheetname'!$".$from['col']."$".$from['row'].":$".$to['col']."$".$to['row'];

        $d->getElementsByTagName("rowData")->item(0)->getElementsByTagName("f")->item(0)->nodeValue = $f;
        $d->getElementsByTagName("ptCount")->item(0)->setAttribute("val", count($row));
        foreach($row as $index => $data) {
            $cell = $d->createElement("c:pt");
            $cell->setAttribute("idx", $index);
            $value = $d->createElement("c:v");
            $value->nodeValue = $data;
            $cell->appendChild($value);
            $d->getElementsByTagName("numCache")->item(0)->appendChild($cell);
        }
        return $d->getElementsByTagName("numRef")->item(0);
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
                    <c:marker>
                        <c:symbol val="none"/>
                    </c:marker>
                    <c:tx/>
                    <c:cat/>
                    <c:val/>
                    <c:smooth val="0"/>
                </c:ser>
            </rowData>
        ');
        return $d;
    }

    /******************************************************************************************
     * Finalization methods (draw charts and propagate the row data to the excel cells)
     ******************************************************************************************/

    /**
     * This output the row data to the output cell
     * @param $excel_xml_object Excel Object
     */
    public function writeRowDataToExcelCells($excel_xml_object) {
        $headers = $this->dataObject->getChartHeader();
        if ($headers) {
            if ($this->dataObject->getIsHasSeriesNames()) {
                $header_cols = ExcelUtil::getAllCellFrom($this->dataObject->chartHeaderCell, count($headers), "col");
                $c = array_shift($header_cols);
            }
            else {
                $header_cols = ExcelUtil::getAllCellFrom($this->dataObject->chartHeaderCell, count($headers) - 1, "col");
            }

            foreach($header_cols as $index => $cell) {
                $excel_xml_object->setCellValue($cell, $headers[$index]);
                if ($style = $this->dataObject->getChartHeaderStyle()) {
                    $excel_xml_object->setCellStyle($cell, (int) $style);
                }
            }
            $_c = ExcelUtil::splitColumnRowFromCell($header_cols[0]);
            $excel_xml_object->addRows($_c['row'], 1);
        }


        //data cell columns
        $_r = $this->dataObject->getChartRowData();

        if ($this->dataObject->getIsHasSeriesNames()){
            $data_cell_cols = ExcelUtil::getAllCellFrom($this->dataObject->chartDataCell, count($_r[0]['row']), "col");
        }
        else {
            $data_cell_cols = ExcelUtil::getAllCellFrom($this->dataObject->chartDataCell, count($_r[0]['row']) - 1, "col");
        }
        $_c = ExcelUtil::splitColumnRowFromCell($data_cell_cols[0]);

        foreach($_r as $index => $data) {
            if (!empty($data['series']) && $this->dataObject->getIsHasSeriesNames()) {
                array_unshift($data['row'],$data['series']);
                $data = $data['row'];
            }
            else {
                $data = $data['row'];
            }

            foreach($data_cell_cols as $data_index => $d) {
                $data_type = ($this->dataObject->getIsHasSeriesNames() && $data_index == 0) ? Excel::STRING : Excel::NUMBER;
                $excel_xml_object->setCellValue($d, $data[$data_index], $data_type);

                if ($data_type == Excel::STRING && $this->dataObject->getChartSeriesStyle() !== false) {
                    $excel_xml_object->setCellStyle($d, (int) $this->dataObject->getChartSeriesStyle());
                }
                if ($data_type == Excel::NUMBER && $this->dataObject->getChartDataCellStyle() !== false) {
                    $excel_xml_object->setCellStyle($d, (int) $this->dataObject->getChartDataCellStyle());
                }
            }
            $excel_xml_object->addRows($_c['row'], 1);
        }
    }

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

        //Lastly, we need to write the rowData to output cell
        $this->writeRowDataToExcelCells($excel_xml_object);
    }

}

?>