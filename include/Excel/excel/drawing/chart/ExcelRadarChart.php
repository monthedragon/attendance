<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

class ExcelRadarChart extends ExcelChartCommon
{
    /**
     * @var string chart unique identifier
     */
    public $chartType = "line";

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
}

?>