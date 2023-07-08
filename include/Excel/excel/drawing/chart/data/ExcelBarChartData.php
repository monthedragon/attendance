<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */


class ExcelBarChartData 
extends ExcelChartData
{
    public $chartType = "Bar";

    //Specific Bar Chart Settings
    private $_chartHeaderStyle = false;
    private $_chartDataCellStyle = false;
    private $_chartSeriesStyle = false;

    /***********************************
     * (Setters)
     **********************************/

    public function setChartHeaderStyle($style = false) {
        $this->_chartHeaderStyle = $style;
    }

    public function setChartDataCellStyle($style = false) {
        $this->_chartDataCellStyle = $style;
    }

    public function setChartSeriesStyle($style = false) {
        $this->_chartSeriesStyle = $style;
    }

    /***********************************
     * (Getters)
     **********************************/

    public function getChartHeaderStyle() {
        return $this->_chartHeaderStyle;
    }

    public function getChartDataCellStyle() {
        return $this->_chartDataCellStyle;
    }

    public function getChartSeriesStyle() {
        return $this->_chartSeriesStyle;
    }
}

?>