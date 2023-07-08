<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Mon Tuyay
 * @since    2015-06-23
 */


class ExcelPieChartData
    extends ExcelBarChartData
{
    public $chartType = "Pie";

    private $_chartPiePercentage = false;

    /**
     * identify the type of the pie either (pie or pie3D) default is pie
     * */
    private $_pieType = 'pie';
    private $_chartExplosionVal = 0;

     /***********************************
     * (Setters)
     **********************************/

    public function setChartDataPiePercentage($style = true) {
        $this->_chartPiePercentage = $style;
    }

    public function setPieChartType($pieType = ''){
        if(!empty($pieType)){
            $this->_pieType = $pieType;
        }
    }

    /**
     * set the value of explosion of slice of the PIE the default is set to 25
     * */
    public function setChartDataPieExplosionValue($value = false) {
        if($value){
            $this->_chartExplosionVal = $value;
        }
    }


    /***********************************
     * (Getters)
     **********************************/

    public function getChartDataPiePercentage() {
        return $this->_chartPiePercentage;
    }

    public function getPieChartType(){
        return $this->_pieType;
    }

    public function getChartDataPieExplosionValue() {
        return $this->_chartExplosionVal;
    }

    //TODO if needed add a way to control c:view3D
}

?>