<?php
/**
 * This is the main Class of package ExcelXML. ExcelXML generates xlsx files, most commonly used in reports.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

include_once "include/Excel/excel/utils/ExcelUtil.php";
include_once "include/Excel/excel/ExcelWorkSheet.php";
include_once "include/Excel/excel/ExcelSheetData.php";
include_once "include/Excel/excel/ExcelWorkBook.php";
include_once "include/Excel/excel/ExcelStyle.php";
include_once "include/Excel/excel/ExcelDrawing.php";


/**
 * Class Excel
 */
class Excel
{
    /**
     * @var string $excelFolder    this holds the excel folder path.
     */
    public $excelFolder;

    /**
     * @var object $util    Object holder for ExcelUtil
     */
    public $util;

    /**
     * @var object $woksheet    Object holder for ExcelWorkSheet
     */
    public $worksheet;

    /**
     * @var object $sheetdata    Object holder for ExcelWorkSheetData
     */
    public $sheetdata;

    /**
     * @var object $workbook    Object holder for ExcelWorkBook
     */
    public $workbook;

    /**
     * @var object $style       Object holder for ExcelStyle
     */
    public $style;

    /**
     * @var ExcelDrawing Object holder for ExcelDrawing
     */
    public $drawing;

    /**
     * @var constant NUMBER    This is a number data type identifier in cells. Example: ExcelXML::setCellValue("A1", 345, ExcelXML::NUMBER);
     */
    const NUMBER = "n";

    /**
     * @var constant STRING    This is a string data type identifier in cells. Example: ExcelXML::setCellValue("A1", 'This is a string', ExcelXML::STRING);
     */
    const STRING = "s";

    //Types of Chart you can create
    //use: Excel::createChart($excel_xml::COLUMN_CHART)
    const LINE_CHART = "Line";
    const BAR_CHART = "Bar";
    const RADAR_CHART = "Radar";
    const PIE_CHART = "Pie";
    const COLUMN_CHART = "Column";

    /**
     * This is a main construct function of the class. This initiates all the classes needed.
     * This is like a bootstrapper of the whole package.
     **/
    public function __construct($template_path = false){
        $this->excelFolder = "uploads";
        if (!is_dir($this->excelFolder)) { throw new Exception($this->excelFolder.": Folder not exist. Make sure this folder exist for a temporary location of all the excel files.");}

        $this->util         = new ExcelUtil;
        $this->worksheet    = new ExcelWorkSheet;
        $this->sheetdata    = new ExcelSheetData;
        $this->workbook     = new ExcelWorkBook;
        $this->style        = new ExcelStyle;
        $this->drawing      = new ExcelDrawing;

        $this->util->excelFolder = $this->excelFolder;
//        $this->util->cleanExcelTempFolder($this->excelFolder);
        $template_path = $template_path ? $template_path : $this->util->defaultExcelTemplate;
        $this->load($template_path);

        // (rio) Spec : System Action Log
        if (function_exists('create_export_log')) {
            create_export_log(); // call this function from utils to create log for every export action
        }
    }

    /**
     * This loads the template path of the excel.
     * This is where the original xlsx file is renamed to .zip
     * then extract the files(xml files) into a folder
     *
     * @param string $path  this is the full path of the excel template
     */
    protected function load($path) {
        $this->excelFolder = $this->excelFolder.'/'.time();
        $zip_file = $this->excelFolder . '.zip';
        copy($path, $zip_file);
		
        mkdir($this->excelFolder, 0777);
        $this->util->unzip($zip_file, $this->excelFolder);
        unlink($zip_file);

        $this->worksheet->worksheetFolder = $this->excelFolder.$this->worksheet->worksheetFolder;
        $this->worksheet->setCurrentWorkSheet();

        //load all the known objects
        $this->workbook->loadWorkBookXML($this);
        $this->style->loadStyleXML($this);
        $this->drawing->loadDrawingXML($this);
    }

    /**
     * Sets the filename of the excel file
     *
     * @param string $name  desired filename of the generated excel file
     */
    public function setFileName($name){
        $this->worksheet->filename = $name;
    }

    /**
     * this copies some specific rows from the template to the new created excel file
     *
     * @example addRows(4, 3);  start copying from row 4. And continue copying the next 2 rows. In result row 4, row 5 and row 6 will be copied
     *
     * @param integer $row_start    the start of the row in the template that you want to copy
     * @param integer $number_of_rows   this is the range of rows you want to copy from the $row_start
     **/
    public function addRows($row_start, $number_of_rows) {
        $current_row = $row_start;
        for ($i=1; $i <= $number_of_rows ; $i++) {
            $node_row   = $this->sheetdata->extractCurrentRow($current_row, $this->worksheet->getCurrentWorkSheetDom());
            $row_number = $this->worksheet->getCurrentSheetRow();
            $node_row   = $this->sheetdata->fixRowNumbers($node_row, $row_number);
            $this->sheetdata->insertSheetRow($node_row, $this->worksheet->getCurrentWorkingSheet());
            $this->worksheet->setCurrentSheetRow(false, $row_number);
            $this->worksheet->checkMergedCellFromTemplate($current_row, $row_number); //check the new added row if it is in a merged cell. if it does just collect the array of merged cells
            $current_row++;
        }
    }

    /**
     * set the current active sheet template you want to work
     *
     * @param integer $id   this is the index of the sheets you want to work on. (NOTE: the index starts in 1 and NOT in 0)
     */
    public function setActiveWorkingSheet($id) {
        $this->worksheet->setCurrentWorkSheet($id);

        if (!$exists = $this->worksheet->isSheetXMLFileExists($id)) {
            //this must be a new blank template, so we must create a RELS on workbook and other dependencies
            $this->workbook->createSheetRels($id);
            $this->worksheet->parentTemplatesOfSheets[$id] = $id;
        }
    }

    /********************
     * NOTE:
     *      Difference between addWorksheet vs cloneSheet
     *      addWorksheet: This creates a new sheet but using the template of the current template.
     *                    Also it has a parameter where you can set directly the name of the new sheet
     *                    Example:
     *                          You are working on sheet #2 when you execute Excel::addWorksheet("sheet_name"); this creates sheet #3
     *                          but uses sheet template #2.
     *      cloneSheet:   If you want to add new sheet and use specific sheet template this function will handle that.
     *                    Example:
     *                          Currently you are now working on sheet #8 but you want to create sheet #9 but uses
     *                          sheet template #3. You can execute Excel::cloneSheet(3) [this will create sheet #9 but uses sheet template #3]
     ********************/

    /**
     * this will clone the sheet from the template to the new excel workbook
     * note: we only clone the template not the data if there's already an added cells/row
     *
     * @param int $sheet_id     the sheet index to clone
     */
    public function cloneSheet($sheet_id = 0) {
        $sheet_to_clone = $sheet_id;

        $xml_file = $this->worksheet->worksheetFolder.'sheet'.$sheet_to_clone.'.xml';

        $id = count($this->worksheet->sheets) + 999;
        if (file_exists($xml_file)) {
            $this->worksheet->sheets[$id] = new DomDocument();
            $this->worksheet->sheets[$id]->load($xml_file);

            $this->worksheet->currentSheet = $id;
            $this->worksheet->initializeXMLTemplate($this->worksheet->sheets[$id]);
            $this->workbook->createSheetRels($id);
        }
        $this->worksheet->parentTemplatesOfSheets[$id] = $sheet_to_clone;
    }

    /**
     * this adds a new worksheet but uses the template of the current working sheet
     * @param bool $name
     */
    public function addWorksheet($name = false) {
        $currentSheetID = $this->worksheet->getCurrentWorkingSheet();
        $templateSheetId = $this->worksheet->parentTemplatesOfSheets[$currentSheetID];

        $this->cloneSheet($templateSheetId);
        if ($name !== false) {
            $this->setActiveSheetName($name);
        }
    }

    /**
     * This sets the sheet name of the current working sheet
     *
     * @param string $name  the sheet name you want to set on the current working sheet
     */
    public function setActiveSheetName($name = "") {
        if (empty($name)) return;
        $this->workbook->renameSheet($this->worksheet->getCurrentWorkingSheet(), $name);
    }

    /**
     * sets the value of the cell
     * values can be set to number or string only
     *
     * @param string $cell      this is the id of the target cell Ex "A1"
     * @param string/int $value the value you want to put in the cell
     * @param constant $data_type   self constant::NUMBER/STRING
     */
    public function setCellValue($cell, $value, $data_type = Excel::STRING){
        //if the cell is in the range of any merged cells return the upper right corner cell id
        $cell = $this->worksheet->isItInMergedCell($cell, true);
        if (!$cell_exist = $this->worksheet->isCellExists($cell)){
            $this->worksheet->createCell($cell);
            $v = $this->worksheet->getCellDomElement($cell);
        }
        else{
            $v = $cell_exist;
        }

        $this->sheetdata->removeChildren($v);
        switch ($data_type) {
            case self::STRING:
                $v->setAttribute("t", "inlineStr");
                if (!$_is = $v->getElementsByTagName('is')->item(0)) {
                    $is = $this->worksheet->getCurrentWorkSheetDom()->createElement("is");
                    $v->appendChild($is);
                }

                if (!$_t = $v->getElementsByTagName('is')->item(0)->getElementsByTagName('t')->item(0)) {
                    $t = $this->worksheet->getCurrentWorkSheetDom()->createElement("t");
                    $v->getElementsByTagName('is')->item(0)->appendChild($t);
                }
                $v->getElementsByTagName('is')->item(0)->getElementsByTagName('t')->item(0)->nodeValue = $value;
                break;
            case self::NUMBER:
                $v->setAttribute("t", "n");
                foreach($v->childNodes as $n){
                    $v->removeChild($n);
                }
                $t = $this->worksheet->getCurrentWorkSheetDom()->createElement("v", $value);
                $v->appendChild($t);
                break;
        }

    }

    /**
     * (rio) Set Text Values with Different Inline Styling (String Data Type Only)
     * note :
     *    [20170814] for now it's only color attribute, but can be enhanced later
     *
     * @param $cell
     * @param $value_arr
     */
    public function setCellStringValueInlineStyling($cell, $value_arr) {

        //if the cell is in the range of any merged cells return the upper right corner cell id
        $cell = $this->worksheet->isItInMergedCell($cell, true);
        if (!$cell_exist = $this->worksheet->isCellExists($cell)){
            $this->worksheet->createCell($cell);
            $v = $this->worksheet->getCellDomElement($cell);
        }
        else{
            $v = $cell_exist;
        }

        $this->sheetdata->removeChildren($v);

        // create <si>
        $v->setAttribute("t", "inlineStr");
        if (!$_is = $v->getElementsByTagName('is')->item(0)) {
            $is = $this->worksheet->getCurrentWorkSheetDom()->createElement("is");
            $v->appendChild($is);
        }

        // loop thru each text value with specific styling
        foreach ($value_arr as $i => $values) {

            $value = $values['value']; // value to print
            $style = $values['style']; // style for the specific value

            // create <r>
            if (!$_r = $v->getElementsByTagName('is')->item(0)->getElementsByTagName('r')->item($i)) {
                $r = $this->worksheet->getCurrentWorkSheetDom()->createElement("r");
                $v->getElementsByTagName('is')->item(0)->appendChild($r);
            }
            // create <rPr> inside <r> ; this would create inner styling
            if (!$_rPr = $v->getElementsByTagName('is')->item(0)->getElementsByTagName('r')->item($i)->getElementsByTagName('rPr')->item(0)) {

                $rPr  = $this->worksheet->getCurrentWorkSheetDom()->createElement("rPr");
                $v->getElementsByTagName('is')->item(0)->getElementsByTagName('r')->item($i)->appendChild($rPr);

                // If color style is set, append color
                if (isset($style['color']) && !empty($style['color'])) {
                    $color= $this->worksheet->getCurrentWorkSheetDom()->createElement("color");
                    $color->setAttribute("rgb", $style['color']);
                    $v->getElementsByTagName('is')->item(0)->getElementsByTagName('r')->item($i)->getElementsByTagName('rPr')->item(0)->appendChild($color);
                }
            }
            // create <t> inside <r>
            if (!$_t = $v->getElementsByTagName('is')->item(0)->getElementsByTagName('r')->item($i)->getElementsByTagName('t')->item(0)) {
                $t = $this->worksheet->getCurrentWorkSheetDom()->createElement("t");
                $v->getElementsByTagName('is')->item(0)->getElementsByTagName('r')->item($i)->appendChild($t);
            }
            // set <t> value to display per <r>
            $v->getElementsByTagName('is')->item(0)->getElementsByTagName('r')->item($i)->getElementsByTagName('t')->item(0)->nodeValue = $value;

        }

    }

    /**
     * creates a formula in a cell
     *
     * @param string $cell      this is the id of the target cell Ex "A1"
     * @param string $formula   the formula you want set on the cell Ex "SUM(A1:A10)". (NOTE: no equal sign)
     */
    public function setCellFormula($cell, $formula){
        if (!$cell_exist = $this->worksheet->isCellExists($cell)){
            $this->worksheet->createCell($cell);
            $v = $this->worksheet->getCellDomElement($cell);
        }
        else{
            $v = $cell_exist;
        }

        $v->setAttribute("t", "str");
        foreach($v->childNodes as $n){
            $v->removeChild($n);
        }

        $t = $this->worksheet->getCurrentWorkSheetDom()->createElement("f", $formula);
        $v->appendChild($t);
    }

    /**
     * merge the cells dynamically (Note: when you want to merge cell. The cell id of the merge cells will be always be the $cell1)
     * @example self::mergeCell('A1', 'C6'). The new id is A1. You set value to the merged cells by using the A1 id
     * take note that merge cell is automatically on the output excel not on the template.
     *
     * @param string $cell1        this is the "from what cell" you want to merge
     * @param string $cell2        "to what cell" you want to merge
     * @throws Exception    this throws when 1 of the cells is empty
     */
    public function mergeCell($cell1, $cell2) {
        if (empty($cell1) || empty($cell2)) throw new Exception("Please supply two valid cell when using mergeCell");
        $this->worksheet->mergeCell($cell1, $cell2);
    }

    /**
     * downloads the new excel file you've created
     * all the xml dom are written to their respective xml file
     * then it will be zipped and rename to xlsx.
     */
    public function download() {
        $folder = $this->excelFolder.'/xl/worksheets';

        //before the writing of sheets to file check if there are cells to merge
        $this->worksheet->writeMergedCells();

        //before the writing of sheets to file check if it has image
        $this->drawing->writeDrawing($this);

        foreach ($this->worksheet->newSheetsTemplate as $key => $value) {
            if (!isset($this->sheetdata->newSheetData[$key])) { // remove unused sheets
                $this->deleteSheets(array($key));
                continue;
            }

            $xml_file = $folder.'/sheet'.$key.'.xml';
            $string = $this->sheetdata->getXMLString($key, $value);
            file_put_contents($xml_file, $string);
            $this->worksheet->addSheetToContentTypes($this->excelFolder, $key);
        }

        //this will just sync the sheets generated to the sheets from template
        foreach ($_files = scandir($folder) as $file) {
            if (substr($file,strlen($file) - 4, strlen($file)) == '.xml'){
                $files[] = $file;
            }
        }

        //this will delete unused sheet from the Workbook Dom before writing it to the file
        $this->workbook->cleanUnusedSheets($this->worksheet->sheets);

        //writing domDocuments to file
        $this->workbook->writeWorkbook();
        $this->style->writeStyle();

        $this->util->zipToXLSX($this->excelFolder, $this->worksheet->filename);
    }

    /**
     * this makes a string xml of the sheet provided
     * from the sheet's xml dom
     *
     * @param int $index    the sheet index you want to export
     * @return string       the xml string of the sheet
     */
    public function getXmlSheetString($index = 1){
        return $this->sheetdata->getXMLString($index, $this->worksheet->newSheetsTemplate[$index]);
    }

    /**
     * get the current sheet row of the specified sheet
     *
     * @param mixed|int|bool $sheet_index   sheet index (if false just get the current sheet)
     * @return int                          current sheet row
     */
    public function getCurrentSheetRow($sheet_index = false) {
        return $this->worksheet->getCurrentSheetRow($sheet_index);
    }

    /**
     * creates a new style
     *
     * @param array $styles     array of styles
     * @return int              the new style id
     */
    public function createNewStyle($styles = array()) {
        return $this->style->createStyle($styles);
    }

    /**
     * setting a style in a particular cell
     *
     * @param string $cell         the target cell
     * @param int    $style_id     the style id you want to set
     */
    public function setCellStyle($cell, $style_id){
        $cell = $this->worksheet->isItInMergedCell($cell, true);

        if (!$cell_exist = $this->worksheet->isCellExists($cell)){
            $this->worksheet->createCell($cell);
        }

        $v = $this->worksheet->getCellDomElement($cell);
        $style_id ? $v->setAttribute("s", $style_id) : $v->removeAttribute("s");

        $this->repairBorderIfInMergedCell($cell, $style_id);
        $this->repairMergedCellStylesAutomatically($cell);
    }

    /**
	 * (mon)
	 * LOGIC came from setCellStyle(), but this method assumes that all your target cells are EXISTING
	 * This method is minified version of setCellStyle(), there are some redundant code that being removed
	 * This is also for performance purposes
     */
    public function setCellStyle_2($cell, $style_id){
		if (!$cell_exist = $this->worksheet->isCellExists($cell)){
            $this->worksheet->createCell($cell);
        }
		
        $v = $this->worksheet->getCellDomElement($cell);
        $style_id ? $v->setAttribute("s", $style_id) : $v->removeAttribute("s");
    }

    /**
     * gets the current cell style of a particular cell
     *
     * @param $cell     the target cell
     * @return int      the id of the cell (default : 0)
     */
    public function getCellStyle($cell) {
        if (!$cell) return 0;
        $l = $this->worksheet->getCellDomElement($cell);
        return ($l) ? $l->getAttribute("s") : 0 ;
    }

    /**
     * this creates/add new image on the excel output file
     *
     * @param $path             the absolute image path
     * @param $image_location   exact location of the image in the excel. This is an array like settings
     * @throws Exception        throw exception if the image path doesnt exist
     * @return bool
     */
    public function createImage($path, $image_location){
        if (!file_exists($path)) throw new Exception("$path file not found");

        $final_location = array();

        if (!isset($image_location['from']['cell'])) {
            throw new Exception("Undefined from:cell");
        }

        $_f = $this->util->splitColumnRowFromCell($image_location['from']['cell']);
        $final_location['from_col'] = $this->util->columnToInt($_f['col']) ? $this->util->columnToInt($_f['col']) : 0;
        $final_location['from_row'] = $_f['row'];
        $final_location['from_col_off'] = isset($image_location['from']['colOff']) ? $image_location['from']['colOff'] * 10000  : 0;
        $final_location['from_row_off'] = isset($image_location['from']['rowOff']) ? $image_location['from']['rowOff'] * 10000 : 0;

        if (isset($image_location['to']) && isset($image_location['to']['cell']) && !empty($image_location['to']['cell'])){
            $_f = $this->util->splitColumnRowFromCell($image_location['to']['cell']);
            $final_location['to_col'] = $this->util->columnToInt($_f['col']) ? $this->util->columnToInt($_f['col']) : 0;
            $final_location['to_row'] = $_f['row'];
            $final_location['to_col_off'] = isset($image_location['to']['colOff']) ? $image_location['to']['colOff'] * 10000 : 0;
            $final_location['to_row_off'] = isset($image_location['to']['rowOff']) ? $image_location['to']['rowOff'] * 10000 : 0;
        }
        else {
            //this means that we will keep the original size of the image.
            //we will get the to_col and to_row based on the image size.
            $final_location['to_col'] = false;
            $final_location['to_row'] = false;
            $final_location['to_col_off'] = false;
            $final_location['to_row_off'] = false;
        }

        return $this->drawing->addNewImage($path, $final_location, $this->worksheet->getCurrentWorkingSheet(), $this);

    }

    /**
     * get all the images and their details
     *
     * @param $index    image index
     */
    public function removeImage($index) {
        return $this->drawing->removeImage($index);
    }

    /**
     * retrieve images detail
     * @return mixed
     */
    public function getImageDetails(){
        return $this->drawing->getImageDetails();
    }

    /**
     * @param $index
     * @param $attributes
     * @return mixed
     */
    public function moveImage($index, $attributes) {
        return $this->drawing->moveImage($index, $attributes);
    }

    /**
     * a public function wrapper that creates the chart object
     * @param $type     chart type class name
     * @return mixed
     */
    public function createChart($type) {
        return $this->drawing->chart->getChartData($type);
    }

    /**
     * Adds the chart type object to the whole excel xml class
     * @param $chart
     */
    public function addChart($chart) {
        $_chart = clone $chart;
        //Draw the chart and create the chart{index}.xml in xl/charts/ folder
        $this->drawing->addChart($_chart, $this);
    }

    /**
     * This function is similar to the addRows. The differences is the row number is not updated or being fixed.
     * @param $row_start
     * @param $number_of_rows
     */
    public function addRowsExactly($row_start, $number_of_rows) {
        $current_row = $row_start;
        for ($i=1; $i <= $number_of_rows ; $i++) {
            $node_row   = $this->sheetdata->extractCurrentRow($current_row, $this->worksheet->getCurrentWorkSheetDom());
            $this->sheetdata->insertSheetRow($node_row, $this->worksheet->getCurrentWorkingSheet());
            $current_row++;
        }
    }

    /******************************************************************************
     * PRIVATE FUNCTIONS
     * this are helpers for ExcelXML functions
     ******************************************************************************/

    /***
     * this is a special function just to repair borders of the merged cell.
     * Borders are broken when you set border to a cell.
     * The reason why it is a long function is that all the sides are being checked.
     *
     * @param $cell         target cell
     * @param $style_id     style sheet id
     * @return bool         return if finished
     */
    protected function repairBorderIfInMergedCell($cell, $style_id) {
        if (!$this->worksheet->isItInMergedCell($cell)) {
            return true;
        }


        if ($this->style->checkIfStyleAttributeExist($style_id, "border")) {
            if ($range = $this->worksheet->getRangedOfMergedCellByInCell($cell)) {
                $sides = $this->util->getAllSidesInMergedCell($range);

                //extract all corners
                $corners = array(   $sides['top'][0], //top left
                    $sides['top'][count($sides['top']) - 1], //top right
                    $sides['bottom'][count($sides['bottom']) - 1], //bottom right
                    $sides['bottom'][0] // bottom left
                );


                $sides_has_style = array();
                $border_style_element_dom = $this->style->extractStyleAttribute($style_id, "border");

                foreach($border_style_element_dom->childNodes as $n) {
                    $style = array();
                    if ($s = $n->getAttribute("style")){
                        $style[$n->tagName]["style"] = $s;
                        if($color = $n->getElementsByTagName("color")->item(0)->getAttribute("rgb")) {
                            $style[$n->tagName]["color"] = $color;
                        }
                    }


                    if ($style) {
                        $i = $this->createNewStyle(array("cell" => array("border" => $style)));
                        foreach ($sides[$n->tagName] as $_c) {
                            if (in_array($_c, $corners)) continue; //corners has a different logic fo styling

                            //this is the fix if the merged cell is in one row only. we should not override the style of the top. we just need
                            //to add a new style for the bottom. top are already made since top comes first in the $sides before bottom
                            if ($n->tagName == "bottom" && $corners[0] == $corners[3]) {

                                $_style_top = array();

                                if ($_n = $this->getCellStyle($_c)){
                                    $_i = $this->style->extractStyleAttribute($_n, "border");
                                    $_i = $_i->getElementsByTagName("top")->item(0);

                                    if ($s = $_i->getAttribute("style")){
                                        $_style_top["top"]["style"] = $s;
                                        if($color = $_i->getElementsByTagName("color")->item(0)->getAttribute("rgb")) {
                                            $_style_top["top"]["color"] = $color;
                                        }
                                    }

                                    $_style_top += $style;
                                    $_p = $this->createNewStyle(array("cell" => array("border" => $_style_top)));
                                    $this->_repairBorderCellStyle($_c, $_p);
                                    continue;
                                }
                            }

                            //this is the fix if the merged cell is in one column only. we should not override the style of the right. we just need
                            //to add a new style for the left. top are already made since left comes first in the $sides before right
                            if ($n->tagName == "right" && $corners[0] == $corners[1]) {
                                $_style_left = array();

                                if ($_n = $this->getCellStyle($_c)){
                                    $_i = $this->style->extractStyleAttribute($_n, "border");
                                    $_i = $_i->getElementsByTagName("left")->item(0);

                                    if ($s = $_i->getAttribute("style")){
                                        $_style_left["left"]["style"] = $s;
                                        if($color = $_i->getElementsByTagName("color")->item(0)->getAttribute("rgb")) {
                                            $_style_left["left"]["color"] = $color;
                                        }
                                    }

                                    $_style_left += $style;

                                    $_p = $this->createNewStyle(array("cell" => array("border" => $_style_left)));
                                    $this->_repairBorderCellStyle($_c, $_p);
                                    continue;
                                }
                            }

                            //applying the style for all the sides
                            $this->_repairBorderCellStyle($_c, $i);
                        }

                        $sides_has_style[$n->tagName] = $style;
                    }
                }

                if ($sides_has_style){
                    //if the merged cell are in one row or column this is the fix
                    if ($corners[0] == $corners[3] || $corners[0] == $corners[1]) {
                        if($corners[0] == $corners[3]){ // horizontal
                            //left
                            $s = array();
                            if (isset($sides_has_style['left'])) {
                                $s['left'] =  $sides_has_style['left']['left'];
                            }
                            if (isset($sides_has_style['top'])) {
                                $s['top'] =  $sides_has_style['top']['top'];
                            }
                            if (isset($sides_has_style['bottom'])) {
                                $s['bottom'] =  $sides_has_style['bottom']['bottom'];
                            }
                            if ($s) {
                                $i = $this->createNewStyle(array("cell" => array("border" => $s)));
                                $borderId = $this->style->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->item($i-1)->getAttribute("borderId");
                                $this->style->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->item($style_id-1)->setAttribute("borderId", $borderId);
                            }

                            //right
                            $corner = $corners[2];
                            $s = array();
                            if (isset($sides_has_style['top'])) {
                                $s['top'] =  $sides_has_style['top']['top'];
                            }
                            if (isset($sides_has_style['right'])) {
                                $s['right'] =  $sides_has_style['right']['right'];
                            }
                            if (isset($sides_has_style['bottom'])) {
                                $s['bottom'] =  $sides_has_style['bottom']['bottom'];
                            }
                            if ($s) {
                                $i = $this->createNewStyle(array("cell" => array("border" => $s)));
                                $this->_repairBorderCellStyle($corner, $i);
                            }

                        }

                        if($corners[0] == $corners[1]){ // vertical
                            //top
                            $s = array();
                            if (isset($sides_has_style['left'])) {
                                $s['left'] =  $sides_has_style['left']['left'];
                            }
                            if (isset($sides_has_style['top'])) {
                                $s['top'] =  $sides_has_style['top']['top'];
                            }
                            if (isset($sides_has_style['right'])) {
                                $s['right'] =  $sides_has_style['right']['right'];
                            }
                            if ($s) {
                                $i = $this->createNewStyle(array("cell" => array("border" => $s)));
                                $borderId = $this->style->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->item($i-1)->getAttribute("borderId");
                                $this->style->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->item($style_id-1)->setAttribute("borderId", $borderId);
                            }

                            //bottom
                            $corner = $corners[3];
                            $s = array();
                            if (isset($sides_has_style['left'])) {
                                $s['left'] =  $sides_has_style['left']['left'];
                            }
                            if (isset($sides_has_style['right'])) {
                                $s['right'] =  $sides_has_style['right']['right'];
                            }
                            if (isset($sides_has_style['bottom'])) {
                                $s['bottom'] =  $sides_has_style['bottom']['bottom'];
                            }
                            if ($s) {
                                $i = $this->createNewStyle(array("cell" => array("border" => $s)));
                                $this->_repairBorderCellStyle($corner, $i);
                            }

                        }

                    }
                    else {
                        //fix the corners
                        //top right
                        $corner = $corners[1];
                        $s = array();
                        if (isset($sides_has_style['top'])) {
                            $s['top'] =  $sides_has_style['top']['top'];
                        }
                        if (isset($sides_has_style['right'])) {
                            $s['right'] =  $sides_has_style['right']['right'];
                        }
                        if ($s) {
                            $i = $this->createNewStyle(array("cell" => array("border" => $s)));
                            $this->_repairBorderCellStyle($corner, $i);
                        }

                        //bottom right
                        $corner = $corners[2];
                        $s = array();
                        if (isset($sides_has_style['right'])) {
                            $s['right'] =  $sides_has_style['right']['right'];
                        }
                        if (isset($sides_has_style['bottom'])) {
                            $s['bottom'] =  $sides_has_style['bottom']['bottom'];
                        }
                        if ($s) {
                            $i = $this->createNewStyle(array("cell" => array("border" => $s)));
                            $this->_repairBorderCellStyle($corner, $i);
                        }

                        //bottom left
                        $corner = $corners[3];
                        $s = array();
                        if (isset($sides_has_style['left'])) {
                            $s['left'] =  $sides_has_style['left']['left'];
                        }
                        if (isset($sides_has_style['bottom'])) {
                            $s['bottom'] =  $sides_has_style['bottom']['bottom'];
                        }
                        if ($s) {
                            $i = $this->createNewStyle(array("cell" => array("border" => $s)));
                            $this->_repairBorderCellStyle($corner, $i);
                        }

                        /****
                         * this is crucial corner since this is where the main style is set for the rest of the merged cells
                         * At this point will only create the border style not the entire xf or style for the cell
                         ****/
                        //top right
                        $corner = $corners[0];
                        $s = array();
                        if (isset($sides_has_style['left'])) {
                            $s['left'] =  $sides_has_style['left']['left'];
                        }
                        if (isset($sides_has_style['top'])) {
                            $s['top'] =  $sides_has_style['top']['top'];
                        }
                        if ($s) {
                            $i = $this->createNewStyle(array("cell" => array("border" => $s)));
                            $xf = $this->getCellStyle($corner);
                            $borderId = $this->style->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->item($xf)->getAttribute("borderId");
                            $this->style->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->item($style_id)->setAttribute("borderId", $borderId);
                        }
                    }
                }
            }
        }

        return true;
    }

    /***
     * this function is only called by repairBorderIfInMergedCell function only
     *
     * @param $cell         target cell
     * @param $style_id     style sheet id
     */
    protected function _repairBorderCellStyle($cell, $style_id){
        if (!$cell_exist = $this->worksheet->isCellExists($cell)){
            $this->worksheet->createCell($cell);
        }
        $v = $this->worksheet->getCellDomElement($cell);
        if($style_id) {
            $v->setAttribute("s", $style_id);
        }
        else {
            $v->removeAttribute("s");
        }

    }

    /**
     * When you are setting a style to a merged cell you must repair the cells of all the cells included on that merged cells.
     * Since the merge cell is already on the output excel. We need to add the Rows automatically on the output excel IF
     * the output cell doesnt have yet the rows.
     * Take note: mergeCell function sets setting on the ouput cell. This does not really adding rows of the rows included on the merge cells range.
     *
     * @param string $cell      target Cell
     * @return bool             just return if not included oa merge cell
     */
    protected function repairMergedCellStylesAutomatically($cell) {
        if (!$this->worksheet->isItInMergedCell($cell)) {
            return true;
        }
        $range = $this->worksheet->getRangedOfMergedCellByInCell($cell);
        $range = explode(":", $range);
        $from = $this->util->splitColumnRowFromCell($range[0]);
        $to = $this->util->splitColumnRowFromCell($range[1]);
        $row_range = $to['row'] - $from['row'];


        $row_start = $from['row'];
        $number_of_rows = $row_range+1;

        $current_row = $row_start;
        for ($i=1; $i <= $number_of_rows ; $i++) {
            $node_row   = $this->sheetdata->extractCurrentRow($current_row, $this->worksheet->getCurrentWorkSheetDom());
            $this->sheetdata->insertSheetRow($node_row, $this->worksheet->getCurrentWorkingSheet());
            $current_row++;
        }

    }

    /**
     * A simple cleaning function
     * Delete unwanted sheets
     *
     * @param $active_sheets    array of sheet indexes
     */
    public function deleteSheets($active_sheets) {
        $indexes = $active_sheets;

        $sheets = $this->workbook->workbookDom->getElementsByTagName("sheet");
        $sheet_to_remove = array();
        foreach($sheets as $sheet) {
            if (in_array($sheet->getAttribute("sheetId"), $indexes)){
                $sheet_to_remove[] = $sheet;
            }
        }

        foreach ($sheet_to_remove as $s) {
            $sheet->parentNode->removeChild($s);
        }
    }

    /**
     * add new page break
     *
     * @param bool $order_number    the row where you want to add
     * @param bool $is_row          you can add page break in row or column
     */
    public function addPageBreak($order_number = false, $is_row = true) {
        $currentSheetRow = ($order_number === false) ? $this->getCurrentSheetRow() : $order_number;
        $where = (($is_row === true) || $is_row === "row") ? "row" : "col";
        $this->worksheet->setPageBreak($currentSheetRow, $where);
    }

    /**
     * If you want the default display of a sheet is in a PageBreakView then this is for that function
     */
    public function setPageBreakPreviewDisplay() {
        $this->worksheet->setSheetViewDisplay("pagebreak");
    }

    /**
     * this sets the Print_Area of the sheet
     *
     * @param $to_cell
     * @param string $from_cell
     * @return bool
     */
    public function setPrintArea($to_cell, $from_cell = "A1") {
        if (!$to_cell) return false;
        $sheetNames = $this->workbook->getSheetNames();
        $sheetName = $sheetNames[$this->worksheet->getCurrentWorkingSheet()];
        $to_cell = ExcelUtil::splitColumnRowFromCell($to_cell);
        $from_cell = ExcelUtil::splitColumnRowFromCell($from_cell);
        $stringCell = "$".implode("$", $from_cell) .":"."$". implode("$", $to_cell);
        $this->workbook->setSheetPrintArea("'{$sheetName}'!{$stringCell}");
    }

    /**
     * (ivan)
     * Sample Code: "$this->excel_xml->setPrintTitles(12,1);"
     * for now: this method is only working for the STATIC header
     *
     * Franz 75444, related to 75677
     * Modification: Enclosed $sheetName with '' to fix the bug on Excel->setPrinttitles regarding illegal characters
     */
    public function setPrintTitles($to_row, $from_row = "1") {
        if (!$to_row) return false;
        $sheetNames = $this->workbook->getSheetNames();
        $sheetName = $sheetNames[$this->worksheet->getCurrentWorkingSheet()];
        $stringCell = "$" . $from_row.":$". $to_row;
        $this->workbook->setSheetPrintTitles("'{$sheetName}'!{$stringCell}");//Franz 75444
    }

    /**
     * @param $size                 pixel size
     * @param $column               what column we are trying to set the width
     * @param bool $useExactWidth   if this is true we will the exact $size parameter; no need for convertToColumnWidth function
     */
    public function setColumnWidth($column, $size, $useExactWidth = false) {
        $size = $useExactWidth ? $size : $this->util->convertToColumnWidth(floor($size));
        $column = $this->util->columnToInt($column);
        $this->worksheet->setColumnWidth($size, $column);
    }

    /**
     * this will retrieve the current width value of the column
     *
     * @param $column
     * @return bool|float
     */
    public function getColumnWidth($column) {
        $column = $this->util->columnToInt($column);
        return $this->worksheet->getColumnWidth($column);
    }

    /**
     * getting the row height is from sheetdata.
     * because the element of the rows are included in sheetdata.
     *
     * @param $row
     * @return float|int|string
     */
    public function getRowHeight($row){
        $rowElement = $this->sheetdata->extractCurrentRow($row, $this->worksheet->getCurrentWorkSheetDom());

        $ht = ExcelUtil::defaultExcelCellHeight;
        if ($rowElement) {
            $_ht = $rowElement->getAttribute('ht');
            if ($_ht)
                $ht = $_ht;

            return ExcelUtil::convertRowHeightToInt($ht);
        }

        return $ht;
    }

    /**
     * this will set the row height on the "new sheet".
     * The tricky part here is; you must first addRows the row you want to set the height.
     * The row you are targetting must be in added first. Setting the Row Height
     * is in the "new sheet" not in the template.
     *
     * Legend:
     *  new sheet : means the new sheet that will be downloaded(the new generated sheet).
     *
     * @param $row
     * @param $height
     */
    public function setRowHeight($row, $height) {
        $height = $this->util->convertToRowHeight(floor($height));
        $sheet_index = $this->worksheet->getCurrentWorkingSheet();
        $this->sheetdata->setRowHeight($height, $row, $sheet_index);
    }
}

?>