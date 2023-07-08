<?php
/**
 * Inherits from Excel class; This class contains performance modification for its base class.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Albert Fajarito
 * @since    2017-05-19
 */

include_once "include/Excel/excel/ExcelWorkSheet_Perf.php";
include_once "include/Excel/Excel.php";

/**
 * Class Excel_perf
 * @author Albert
 */
class Excel_Perf extends Excel
{

    /**
     * Overrides parent class constructor, The difference is the instantiation of the ExcelWorkSheet object here
     * the performance class is used and the pre-setting of TemplateMergeRanges.
     * @param bool $template_path
     * @author Albert
     */
    public function __construct($template_path = false){

        $this->excelFolder = "uploads";
        if (!is_dir($this->excelFolder)) { throw new Exception($this->excelFolder.": Folder not exist. Make sure this folder exist for a temporary location of all the excel files.");}

        $this->util         = new ExcelUtil;
        $this->worksheet    = new ExcelWorkSheet_Perf; // Use performance class version.
        $this->sheetdata    = new ExcelSheetData;
        $this->workbook     = new ExcelWorkBook;
        $this->style        = new ExcelStyle;
        $this->drawing      = new ExcelDrawing;

        $this->util->excelFolder = $this->excelFolder;
        $template_path = $template_path ? $template_path : $this->util->defaultExcelTemplate;
        $this->load($template_path);

        // (rio) Spec : System Action Log
        if (function_exists('create_export_log')) {
            create_export_log(); // call this function from utils to create log for every export action
        }

        #64857 (albert)
        $this->worksheet->SetTemplateMergeRanges(); // Prepare the template merge range values.
    }
}

?>