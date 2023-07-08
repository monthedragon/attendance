<?php
/**
 * Final class for Chart Data
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

/***
 * A common data object for all the charts
 *
 * Class ChartData
 */
class ChartData {

    //common variables
    private $_headers = array();
    private $_rowData = array();
    private $_axisTitle = "";
    private $_chartTitle = "";
    private $_hasSeriesNames = false;

    private $_chartDataTableLegend = false;
    private $_chartDataTableDataLabels = false;

    private $_chartBackgroundColor = false;
    private $_chartGraphBackgroundColor = false;
    private $_chartFontFamily = false;
    private $_chartFontSize = false;
    private $_chartFontColor = false;

    /*******************************
     *  Common Setters
     *******************************/
    /**
     * @param array $headers
     * sets the chart headers
     */
    public function setChartHeader($headers = array()) {
        if (!$headers) return;
        $this->_headers = $headers;
    }

    /**
     * @param array $data           the data row
     * @param string $series_name   if supplied the series name will be put on the first element of the data row
     */
    public function setChartRowData($data = array(), $series_name = "") {
        if (!$data) return;
        if (!empty($series_name)) $this->_hasSeriesNames = true;
        array_unshift($data, $series_name);
        $this->_rowData[] = $data;
    }

    /**
     * @param string $axis_title
     * (vertical) chart axis title
     */
    public function setChartAxisTitle($axis_title = "") {
        $this->_axisTitle = $axis_title;
    }

    /**
     * @param string $title
     * this sets the chart title (top)
     */
    public function setChartTitle($title = "") {
        $this->_chartTitle = $title;
    }

    /**
     * @param bool $show
     * sets if you want to show the data label on the chart
     */
    public function showChartDataLabels($show = true) {
        $this->_chartDataTableDataLabels = $show;
    }

    /**
     * @param bool $show
     * sets if you want to show the chart legends on the chart
     */
    public function showChartLegends($show = true) {
        $this->_chartDataTableLegend = $show;
    }

    /**
     * @param string $color
     * sets the background color of the Chart (this pertains to the whole background of the chart)
     */
    public function setChartBackgroundColor($color = "FFFFFF") {
        $this->_chartBackgroundColor = $color;
    }

    /**
     * @param string $color
     * sets the background color of the graph part only
     */
    public function setChartGraphBackgroundColor($color = "FFFFFF") {
        $this->_chartGraphBackgroundColor = $color;
    }

    /**
     * @param string $color
     * sets the chart font color
     */
    public function setChartFontColor($color = "000000") {
        $this->_chartFontColor = $color;
    }

    /**
     * @param string $family
     * sets the Chart font family
     */
    public function setChartFontFamily($family = "Calibri") {
        $this->_chartFontFamily = $family;
    }

    /**
     * @param int $size
     * sets the chart font size
     */
    public function setChartFontSize($size = 11) {
        $this->_chartFontSize = $size;
    }


    /*******************************
     *  Common Getters (Providers)
     *******************************/
    /**
     * @return array    gets the chart header
     */
    public function getChartHeader() {
        return $this->_headers;
    }

    /**
     * @return array    gets the data row.
     *      sample return array(
     *                          array("series" => "series1",
     *                                 "row" => array("row1", "row1", "row1")
     *                          ),
     *                          array("series" => "series2",
     *                                  "row" => array("row2", "row2", "row2")
     *                          )
     *                  )
     * if no series has been setup
     *      sample return array(
     *                          array("series" => "",
     *                                 "row" => array("row1", "row1", "row1")
     *                          ),
     *                          array("series" => "",
     *                                  "row" => array("row2", "row2", "row2")
     *                          )
     *                  )
     */
    public function getChartRowData() {
        $return = array();
        foreach($this->_rowData as $row) {
            if ($this->_hasSeriesNames) {
                $series = array_shift($row);
                $t = array("series" => $series, "row" => $row);
                $return[] = $t;
            }
            else {
                array_shift($row);
                $t = array("series" => '', "row" => $row);
                $return[] = $t;
            }

        }
        return $return;
    }

    /**
     * @return string
     * gets the chart axis title
     */
    public function getChartAxisTitle() {
        return $this->_axisTitle;
    }

    /**
     * @return string
     * gets the chart title
     */
    public function getChartTitle(){
        return $this->_chartTitle;
    }

    /**
     * @return bool
     * tells whether if there's a series name has been set
     */
    public function getIsHasSeriesNames() {
        return (bool) $this->_hasSeriesNames;
    }

    /**
     * @return bool
     * gets if you want to show the chart data labels
     */
    public function getShowChartDataLabels() {
        return $this->_chartDataTableDataLabels;
    }

    /**
     * @return bool
     * gets if you want to show the chart legends
     */
    public function getShowChartLegends() {
        return $this->_chartDataTableLegend;
    }

    /**
     * @return bool
     * gets the chart background color
     */
    public function getChartBackgroundColor() {
        return $this->_chartBackgroundColor;
    }

    /**
     * @return bool
     * gets the background color of graph part only
     */
    public function getChartGraphBackgroundColor() {
        return $this->_chartGraphBackgroundColor;
    }

    /**
     * @return bool
     * gets the chart font color
     */
    public function getChartFontColor() {
        return $this->_chartFontColor;
    }

    /**
     * @return bool
     * gets the chart font family
     */
    public function getChartFontFamily() {
        return $this->_chartFontFamily;
    }

    /**
     * @return bool
     * gets the chart font size
     */
    public function getChartFontSize() {
        return $this->_chartFontSize;
    }

}

?>