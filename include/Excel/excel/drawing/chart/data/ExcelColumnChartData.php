<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */


class ExcelColumnChartData
    extends ExcelChartData
{
    public $chartType = "Column";

    //Specific Line Chart Settings
    private $_chartHeaderStyle = false;
    private $_chartDataCellStyle = false;
    private $_chartSeriesStyle = false;

    private $_columnShape = "box";
    private $_is3D = false;
    private $_groupingDisplay = "clustered"; //clustered, stacked, percentStacked, standard


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

    public function setColumnShape($shape = "box") {
        $allowed_shapes = array("box", "cylinder", "pyramid", "cone");
        if (!in_array($shape, $allowed_shapes)) {
            throw new Exception("$shape unknown: These are the only allowed Shapes for column charts: ". implode(", ", $allowed_shapes));
            exit();
        }
        $this->_columnShape = $shape;
    }

    public function set3D($is3D = true) {
        $this->_is3D = $is3D;
    }

    public function setGroupingDisplay($grouping = "clustered") {
        $allowed_display = array("clustered", "stacked", "percentStacked", "standard");
        if (!in_array($grouping, $allowed_display)) {
            throw new Exception("$grouping unknown: These are the only allowed Grouping Display for column charts: ". implode(", ", $allowed_display));
            exit();
        }
        $this->_groupingDisplay = $grouping;
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

    public function getColumnShape() {
        return $this->_columnShape;
    }

    public function getIs3D() {
        return $this->_is3D;
    }

    public function getGroupingDisplay() {
        return $this->_groupingDisplay;
    }
}

?>