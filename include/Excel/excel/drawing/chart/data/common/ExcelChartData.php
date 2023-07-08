<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

include_once "include/Excel/chart_data/ChartData.php";

/***
 * A common data object for all the charts
 *
 * Class ExcelChartData
 */
class ExcelChartData
extends ChartData
{

    //common variables
    private $_chartLocation = array();
    private $_dataTableCellStart = "A1";
    private $_chartTemplatePath = false;

    //displays
    private $_chartDataTable = false;

    //needed for excel charts
    public $chartHeaderCell;
    public $chartDataCell;
    public $chartStartingPointToDrawChartForHeader;
    public $chartStartingPointToDrawChartForData;

    /**
     * @param string $cell
     * sets the origin of where to put the table of the data table
     */
    public function setDataTableCellStart($cell = "A1") {
        $this->_dataTableCellStart = $cell;
    }

    /**
     * This sets where you will want to draw the chart
     *
     * @param $chart_location   arrray of points
     * @throws Exception        if cell is not valid
     */
    public function setChartLocation($chart_location) {
        $final_location = array();

        if (!isset($chart_location['from']['cell']) || !isset($chart_location['to']['cell'])) {
            throw new Exception("Undefined from:cell OR to:cell");
        }

        $_f = ExcelUtil::splitColumnRowFromCell($chart_location['from']['cell']);
        $final_location['from_col'] = ExcelUtil::columnToInt($_f['col']) ? ExcelUtil::columnToInt($_f['col']) : 0;
        $final_location['from_row'] = $_f['row'];
        $final_location['from_col_off'] = isset($chart_location['from']['colOff']) ? $chart_location['from']['colOff'] * 10000  : 0;
        $final_location['from_row_off'] = isset($chart_location['from']['rowOff']) ? $chart_location['from']['rowOff'] * 10000 : 0;

        $_f = ExcelUtil::splitColumnRowFromCell($chart_location['to']['cell']);
        $final_location['to_col'] = ExcelUtil::columnToInt($_f['col']) ? ExcelUtil::columnToInt($_f['col']) : 0;
        $final_location['to_row'] = $_f['row'];
        $final_location['to_col_off'] = isset($chart_location['to']['colOff']) ? $chart_location['to']['colOff'] * 10000 : 0;
        $final_location['to_row_off'] = isset($chart_location['to']['rowOff']) ? $chart_location['to']['rowOff'] * 10000 : 0;

        $this->_chartLocation = $final_location;
    }

    /**
     * sets if you want to display the data table in excel chart
     * @param bool $show
     */
    public function showChartDataTable($show = true) {
        $this->_chartDataTable = $show;
    }

    /**
     * @return array
     * get the chart location in the excel
     */
    public function getChartLocation() {
        return $this->_chartLocation;
    }

    /**
     * gets the origin of the data table in excel
     * @return string
     */
    public function getChartDataTableCellStart(){
        return $this->_dataTableCellStart;
    }

    /**
     * get the chart template path
     * @return bool
     */
    public function getChartTemplatePath() {
        return $this->_chartTemplatePath;
    }

    /**
     * gets the boolean value if you wan to display the chart data table inside the chart
     * @return bool
     */
    public function getShowChartDataTable() {
        return $this->_chartDataTable;
    }

}

?>