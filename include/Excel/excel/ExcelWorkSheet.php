<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */


/**
 * Class ExcelXMLObjectWorkSheet
 */
class ExcelWorkSheet {

    /**
     * @var array $sheet    this holds all the sheets of our template
     */
    public $sheets              = array();

    /**
     * @var int $currentSheet  this is the definer of the current working sheet
     */
    public $currentSheet       = 1;

    /**
     * @var array $currentSheetRow    this is the container of your current row number
     */
    public $currentSheetRow   = array();

    /**
     * @var string $worksheetFolder    this holds the path of the sheets (NOTE: this is being renamed in ExcelTemplate::loadTemplate())
     */
    public $worksheetFolder    = "/xl/worksheets/";

    /**
     * @var string $blankSheetTemplate    the container for the blank sheet template
     */
    public $blankSheetTemplate = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                                    <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
                                    xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
                                    xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"
                                    mc:Ignorable="x14ac"
                                    xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac">
                                        <dimension ref="A1"/><sheetViews><sheetView workbookViewId="0"/></sheetViews><sheetFormatPr defaultRowHeight="15"/>
                                        <sheetData> </sheetData>
                                        <pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>
                                    </worksheet>
            ';

    /**
     * Temporary container.
     * Before we initialize the sheet xml we need to extract the merged cells in the template.
     * This will be the container of it. This is for reference when using addRows.
     * We need to retain and align all the merge cells from the template going to the new created sheet.
     *
     * @var array $mergedCellsFromTemplate this will be an array of merge cells from the template
     */
    public $mergedCellsFromTemplate = array();

    /**
     * When on the process of processing the data we dont put the merging of cells(copying of merged cell to the new sheeet).
     * The reason for this is efficiency. It is tested the copying of merged cells from the template is more faster rather than
     * merged them in the middle of the process.
     *
     * This will be the container of the final cells to merged.
     * @var array
     */
    public $cellsToMerge = array();

    /**
     * @var array $currentNumRows    this is the container of your current row number
     */
    public $currentNumRows = array();

    /**
     * @var array $newSheetsTemplate  holds the new excel sheets
     */
    public $newSheetsTemplate   = array();

    /**
     * @var string $filename    default filename of the generated excel file
     */
    public $filename = 'Excel_Export';

    /**
     * we want to keep track the template sheet of each newly created sheets.
     * we will set the sheet 1 to template sheet 1.
     * @var array   array( newly created sheet => sheet template )
     */
    public $parentTemplatesOfSheets = array(1 => 1);

    public function __construct(){ }

    /**
     * sets the current working sheet to the specified index
     *
     * @param int $id   the sheet index you want to set as current working sheet
     */
    public function setCurrentWorkSheet($id = 1) {
        $this->currentSheet = $id;
        if (!isset($this->sheets[$this->currentSheet]) || !$this->sheets[$this->currentSheet]) {
            $xml_file = $this->worksheetFolder.'sheet'.$this->currentSheet.'.xml';
            if (file_exists($xml_file)) {
                $this->sheets[$this->currentSheet] = new DomDocument();
                $this->sheets[$this->currentSheet]->load($xml_file);
                $this->initializeXMLTemplate($this->sheets[$this->currentSheet]);
            }
            else {
                $this->sheets[$this->currentSheet] = new DomDocument();
                $this->sheets[$this->currentSheet]->loadXML($this->blankSheetTemplate);
                $this->initializeXMLTemplate($this->sheets[$this->currentSheet]);
            }
        }
    }

    /**
     * get the current working sheet index
     *
     * @return int  current sheet index
     */
    public function getCurrentWorkingSheet() {
        return $this->currentSheet;
    }

    /**
     * gets the domdocument of the current working sheet
     *
     * @return domDocument  DomDocument of the current working sheet
     */
    public function getCurrentWorkSheetDom() {
        return $this->sheets[$this->currentSheet];
    }

    /**
     * gets the current row number you're in
     *
     * @param mixed|int|bool $sheet_number  the sheet index you are referring to
     * @return mixed                        returns the specified sheet row
     */
    public function getCurrentSheetRow($sheet_number = false) {
        $current_sheet = ($sheet_number) ? $sheet_number : $this->currentSheet;

        if (!isset($this->currentNumRows[$current_sheet])) {
            $this->currentNumRows[$current_sheet] = 0;
        }

        return $this->currentNumRows[$current_sheet] + 1;
    }

    /**
     * sets the current row number of the sheet
     *
     * @param mixed|bool|int $sheet_number  the sheet index you are referring to
     * @param int $num                      the number you want to set on the specified sheet
     */
    public function setCurrentSheetRow($sheet_number = false, $num = 1) {
        $current_sheet = ($sheet_number) ? $sheet_number : $this->currentSheet;
        $this->currentNumRows[$current_sheet] = $num;
    }

    /**
     * merge 2 cells to create 1 cell
     *
     * @param string $cell1     the start cell of the merge cell
     * @param string $cell2     to what cell you want to merge
     * @throws Exception        cells not valid
     */
    public function mergeCell($cell1, $cell2) {
        $c1 = ExcelUtil::splitColumnRowFromCell($cell1);
        $c2 = ExcelUtil::splitColumnRowFromCell($cell2);

        if (( ExcelUtil::columnToInt($c1['col']) > ExcelUtil::columnToInt($c2['col'])) || ($c1['row'] > $c2['row'])) {
            throw new Exception("$cell1, $cell2 are not a valid cells to merge");
        }


        $d = new DOMDocument();
        $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);
        $merges = $d->getElementsByTagName("mergeCell");
        $count = $merges->length;

        if ($count) {
            $m = $d->createElement("mergeCell");
            $m->setAttribute("ref", "$cell1:$cell2");
            $d->getElementsByTagName("mergeCells")->item(0)->appendChild($m);
            $d->getElementsByTagName("mergeCells")->item(0)->setAttribute("count", ($count + 1));

        }
        else {
            $c = $d->createElement("mergeCells");
            $c->setAttribute("count", 1);
            $insertCellMergeBeforeThisCell = ExcelUtil::mergeCellElementLocation($d);
            $d->getElementsByTagName("worksheet")->item(0)->insertBefore($c, $d->getElementsByTagName($insertCellMergeBeforeThisCell)->item(0));

            $m = $d->createElement("mergeCell");
            $m->setAttribute("ref", "$cell1:$cell2");
            $d->getElementsByTagName("mergeCells")->item(0)->appendChild($m);
        }

        $this->newSheetsTemplate[$this->currentSheet] = $d->saveXML();
    }

    /**
     * check whether a specified cell exist on the template
     *
     * @param string $cell      the cell id you want check
     * @return bool             if exist (true) else not exists (false)
     */
    public function isCellExists($cell) {
        $currentDom = $this->getCurrentWorkSheetDom();
        $xpath = new DOMXPath($currentDom);
        $xpath->registerNamespace("atom", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        $currentCell = $xpath->query('//atom:worksheet/atom:sheetData/atom:row/atom:c[@r="'.$cell.'"]');
        if ($currentCell->length)
            return $currentCell->item(0);
        else
            return false;
    }

    /**
     * check if the specified row exist in the current template
     *
     * @param int $row  the row id you want to check
     * @return bool     if exist (true) else not exists (false)
     */
    public function isRowExists($row) {
        $currentDom = $this->getCurrentWorkSheetDom();
        $xpath = new DOMXPath($currentDom);
        $xpath->registerNamespace("atom", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        $currentRow = $xpath->query('//atom:worksheet/atom:sheetData/atom:row[@r="'.$row.'"]');

        return ($currentRow->length) ? true : false;
    }

    /**
     * create a fresh row in sheet
     *
     * @param int $row  row id of the row you want to create
     */
    public function createRow($row) {
        $entries = $this->getCurrentWorkSheetDom()->getElementsByTagName('row');
        $before_node = false;
        $insert = false;
        foreach ($entries as $k => $v) {
            $before_node = $v;
            if ($v->getAttribute("r") > $row) { $insert = true; break; };
        }

        $row_element = $this->getCurrentWorkSheetDom()->createElement("row");
        $row_element->setAttribute("r", $row);

        if ($insert) {
            $this->getCurrentWorkSheetDom()->getElementsByTagName("sheetData")->item(0)->insertBefore($row_element, $before_node);
        }
        else {
            $this->getCurrentWorkSheetDom()->getElementsByTagName("sheetData")->item(0)->appendChild($row_element);
        }

        //this will refresh all the namespaces of worksheet.
        $r = $this->getCurrentWorkSheetDom()->saveXML();
        $d = new DOMDocument();
        $d->loadXML($r);
        $this->sheets[$this->currentSheet] = $d;
    }

    /**
     * create a fresh cell in the row
     *
     * @param string $cell  the cell id you want to create
     */
    public function createCell($cell) {
        $col_row = ExcelUtil::splitColumnRowFromCell($cell);

        if (!$this->isRowExists($col_row['row'])) {
            $this->createRow($col_row['row']);
        }

        $entries = $this->getCurrentWorkSheetDom()->getElementsByTagName('row');
        foreach ($entries as $k => $v) {
            if ($v->getAttribute("r") == $col_row['row']) {
                $cells = $v->getElementsByTagName("c");
                $insert = false;
                $before_node = false;
                foreach($cells as $_cell) {
                    $before_node = $_cell;
                    $_col_row = ExcelUtil::splitColumnRowFromCell($_cell->getAttribute("r"));
                    if ( ExcelUtil::columnToInt($_col_row['col']) > ExcelUtil::columnToInt($col_row['col'])) {
                        $insert = true; break;
                    }
                }

                $row_element = $this->getCurrentWorkSheetDom()->createElement("c");
                $row_element->setAttribute("r", $cell);

                if ($insert) {
                    $v->insertBefore($row_element, $before_node);
                }
                else {
                    $v->appendChild($row_element);
                }

            }
        }

        //this will refresh all the namespaces of worksheet.
        $r = $this->getCurrentWorkSheetDom()->saveXML();
        $d = new DOMDocument();
        $d->loadXML($r);
        $this->sheets[$this->currentSheet] = $d;
    }

    /**
     * @param string $cell                                  the cell you want to check
     * @param mixed|bool|string $return_upper_right_corner  set the return type
     * @return bool                                         false if not otherwise true if it is in the a merged cell
     */
    public function isItInMergedCell($cell, $return_upper_right_corner = false) {
        $d = new DOMDocument();
        $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);
        $merges = $d->getElementsByTagName("mergeCell");
        foreach($merges as $merge) {
            if ($upper_right_corner_cell = ExcelUtil::isInMergedCell($cell, $merge->getAttribute("ref"))) {
                return ($return_upper_right_corner) ? $upper_right_corner_cell : true;
            }
        }

        return ($return_upper_right_corner) ? $cell : false;
    }

    public function getRangedOfMergedCellByInCell($cell) {
        $d = new DOMDocument();
        $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);
        $merges = $d->getElementsByTagName("mergeCell");
        foreach($merges as $merge) {
            if (ExcelUtil::isInMergedCell($cell, $merge->getAttribute("ref"))) {
                return $merge->getAttribute("ref");
            }
        }

        return false;
    }

    /**
     * this checks the existence of the sheet file
     *
     * @param $index    sheet index
     * @return bool     true if exist otherwise false not exist
     */
    public function isSheetXMLFileExists($index) {
        $xml_file = $this->worksheetFolder.'sheet'.$index.'.xml';
        return file_exists($xml_file) ? true : false;
    }

    /**
     * this makes a xml string of the template
     *
     * @param domdocument $dom  the domdocument you want to set in the new sheet
     */
    public function initializeXMLTemplate($dom){
        $search = "/<sheetData(.*)sheetData>/";
        $replace = "{--SHEETDATA--}";
        $string = $dom->saveXML();
        $r = preg_replace($search, $replace, $string);
        $r = str_replace('<sheetData/>', $replace, $r);

        //get all merge cells first before removing it from the initialization
        $this->extractMergedCells($r);

        //initialize sheets without merged cells
        $r = preg_replace("/<mergeCells(.*)mergeCells>/", '', $r);

        $this->newSheetsTemplate[$this->currentSheet] = $r;
    }

    /***
     * when the xlsx file is about to be downloaded the sheet
     * generated must be present in content_types file.
     * This function ensures that sheet are included to content
     * types file.
     *
     * @param $folder       folder/path where the xlsx are located
     * @param $sheet_key    the sheet index
     * @return bool         just return true if the sheet is already present in content type file
     */
    public function addSheetToContentTypes($folder, $sheet_key) {
        $content_file = $folder.'/[Content_Types].xml';
        $d = new DOMDocument();
        $d->load($content_file);

        $overrides = $d->getElementsByTagName("Override");
        foreach($overrides as $_override) {
            if($_override->getAttribute("PartName") == "/xl/worksheets/sheet".$sheet_key.".xml") {
                return true;
            }
        }

        $override = $d->createElement("Override");
        $override->setAttribute("PartName", "/xl/worksheets/sheet".$sheet_key.".xml");
        $override->setAttribute("ContentType", "application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml");
        $d->getElementsByTagName("Types")->item(0)->appendChild($override);

        file_put_contents($content_file, $d->saveXML());
    }

    /**
     * update the page display of the sheet
     *
     * @param $type example "pagebreak"
     */
    public function setSheetViewDisplay($type) {
        if (empty($type)) return;

        //add more view here in the future
        $arrayViews = array(
            "pagebreak" => "pageBreakPreview",
        );

        $d = new DOMDocument();
        $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);

        $sheetViews = $d->getElementsByTagName("sheetViews")->item(0);
        if (!$sheetViews) {
            $s = $d->getElementsByTagName("sheetViews");
            $d->getElementsByTagName("worksheet")->item(0)->appendChild($s);
        }

        $sheetView = $d->getElementsByTagName("sheetView")->item(0);
        if ($sheetView) {
            $d->getElementsByTagName("sheetView")->item(0)->setAttribute("view", $arrayViews[$type]);
            $d->getElementsByTagName("sheetView")->item(0)->setAttribute("zoomScale", "100");
            $d->getElementsByTagName("sheetView")->item(0)->setAttribute("zoomScaleNormal", "100");
            $d->getElementsByTagName("sheetView")->item(0)->setAttribute("zoomScaleSheetLayoutView", "100");
            $d->getElementsByTagName("sheetView")->item(0)->setAttribute("workbookViewId", "0");
        }
        else {
            $sheetView = $d->createElement("sheetView");
            $sheetView->setAttribute("view", $arrayViews[$type]);
            $sheetView->setAttribute("zoomScale", "100");
            $sheetView->setAttribute("zoomScaleNormal", "100");
            $sheetView->setAttribute("zoomScaleSheetLayoutView", "100");
            $sheetView->setAttribute("workbookViewId", "0");
            $d->getElementsByTagName("sheetViews")->item(0)->appendChild($sheetView);
        }

        $this->newSheetsTemplate[$this->currentSheet] = $d->saveXML();
    }

    /**
     * set page break on the current sheet
     * @param $currentSheetRow
     * @param $where
     */
    public function setPageBreak($currentSheetRow, $where) {
        $d = new DOMDocument();
        $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);

        $where = ($where == "row") ? "rowBreaks" : "colBreaks";

        $breaks = $d->getElementsByTagName($where)->item(0);
        if (!$breaks) {
            $s = $d->createElement($where);
            $s->setAttribute("count", 0);
            $s->setAttribute("manualBreakCount", 0);
            $d->getElementsByTagName("worksheet")->item(0)->appendChild($s);
        }

        $brk = $d->createElement("brk");
        $brk->setAttribute("id", $currentSheetRow);
        $brk->setAttribute("max", ($where == "rowBreaks") ? 16383 : 1048575 );
        $brk->setAttribute("man", 1);
        $d->getElementsByTagName($where)->item(0)->appendChild($brk);

        $count = $d->getElementsByTagName($where)->item(0)->getElementsByTagName("brk")->length;
        $d->getElementsByTagName($where)->item(0)->setAttribute("count", $count);
        $d->getElementsByTagName($where)->item(0)->setAttribute("manualBreakCount", $count);

        $this->newSheetsTemplate[$this->currentSheet] = $d->saveXML();
    }

    /**
     * this queries all the cells in the sheet to find an specific cell
     *
     * @param $cell         the cell youre looking for
     * @return bool|DOMNode
     */
    public function getCellDomElement($cell) {
        $currentDom = $this->getCurrentWorkSheetDom();
        $xpath = new DOMXPath($currentDom);
        $xpath->registerNamespace("atom", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        $currentCell = $xpath->query('//atom:worksheet/atom:sheetData/atom:row/atom:c[@r="'.$cell.'"]');

        if ($currentCell->length) {
            return $currentCell->item(0);
        }
        else {
            return false;
        }
    }

    /**
     * this will extract all the merged cells in the template.
     * we need this on tracking the merged cells so that we can put the same in the new generated sheet.
     *
     * @param $xml  string the domSheet string
     * @return bool
     */
    private function extractMergedCells($xml) {
        if (empty($xml)) return false;

        $dom = ExcelUtil::createDomObject($xml, false);
        $mergeCells = $dom->getElementsByTagName("mergeCell");
        if (!$mergeCells->length)
            return false;

        $arrayOfMergeCells = array();
        foreach($mergeCells as $m) {
            $arrayOfMergeCells[] = $m->getAttribute("ref");
        }

        if ($arrayOfMergeCells)
            $this->mergedCellsFromTemplate[$this->currentSheet] = $arrayOfMergeCells;

        return true;
    }

    /**
     * when adding rows we need to check if the added row is included on the merged cells in the template.
     * this will collect all the rows if it is in the list of merged cells from the sheet template
     *
     * @param $rowInTemplate        this is the row id from the template.
     * @param $rowNumberInNewSheet  this is the new row id when the row id from the template is added in the new generated sheet
     */
    public function checkMergedCellFromTemplate($rowInTemplate, $rowNumberInNewSheet) {
        if (!isset($this->mergedCellsFromTemplate[$this->currentSheet]) || !$this->mergedCellsFromTemplate[$this->currentSheet])
            return;

        $mergedCells = $this->mergedCellsFromTemplate[$this->currentSheet];
        foreach ($mergedCells as $cell) {
            $range = explode(':', $cell);
            $firstCell = ExcelUtil::splitColumnRowFromCell($range[0]);

            if ($firstCell['row'] == $rowInTemplate) {
                $secondCell = ExcelUtil::splitColumnRowFromCell($range[1]);
                $newToRowCell =  $rowNumberInNewSheet + ($secondCell['row'] - $firstCell['row']);
                $newSetOfCell2 = $secondCell['col'].$newToRowCell;
                $newSetOfCell1 = $firstCell['col'].$rowNumberInNewSheet;
                $this->cellsToMerge[$this->currentSheet][] = array($newSetOfCell1, $newSetOfCell2); //this will be merged when the sheet is in the download stage
            }
        }
    }

    /**
     * when downloading write all the merged cells by sheet before we write the dom to the actual file.
     * The reason why plotting the merged cell from the template to new sheet is efficiency.
     * This is much faster than plotting the merged cell on the process.
     *
     */
    public function writeMergedCells() {
        foreach($this->newSheetsTemplate as $key => $v) {
            if ($cells = $this->cellsToMerge[$key]) {
                foreach($cells as $c) {
                    $this->setCurrentWorkSheet($key);
                    $this->mergeCell($c[0], $c[1]);
                }
            }
        }
    }

    /**
     * This sets the column width unique in each columns.
     *
     * @param $width     pixel size
     * @param $column   what column we are trying to set the width
     */
    public function setColumnWidth($width, $column){
        $d = new DOMDocument();
        $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);
        $merges = $d->getElementsByTagName("cols");
        $count = $merges->length;

        if (!$count) {
            $c = $d->createElement("cols");
            $d->getElementsByTagName("worksheet")->item(0)->insertBefore($c, $d->getElementsByTagName("sheetData")->item(0));
        }

        //fix shared column width
        $this->fixColumnWidthSharing($d);

        //collect all the cols in the sheet
        //this will help us verify where to put the new col element.
        //Note: the col must be in order
        $cols_children = $d->getElementsByTagName("col");
        if ($cols_children) {
            $children_array = array();
            foreach($cols_children as $child) {
                $children_array[] = $child->getAttribute("min");
            }
        }

        $col_index_where_to_insert_new_column = false;
        foreach($children_array as $index => $val) {
            if ($col_index_where_to_insert_new_column === false) {
                if ($column == $val)
                    $col_index_where_to_insert_new_column = "existed";
                else if ($val > $column)
                    $col_index_where_to_insert_new_column = $index;
            }
        }

        //if false : we can just insert the new column at the bottom
        //if existed: we will just update the width attribute
        //if int : we will insert the new column before the said index
        if ($col_index_where_to_insert_new_column == false){
            $newColElement = $d->createElement("col");
            $newColElement->setAttribute("min", $column);
            $newColElement->setAttribute("max", $column);
            $newColElement->setAttribute("width", $width);
            $newColElement->setAttribute("customWidth", 1);
            $d->getElementsByTagName("cols")->item(0)->appendChild($newColElement);
        }
        else if($col_index_where_to_insert_new_column == "existed") {
            $f = array_flip($children_array);
            $new_col_index = $f[$column];
            $d->getElementsByTagName("col")->item($new_col_index)->setAttribute("width", $width);
        }
        else if($col_index_where_to_insert_new_column >= 0) {
            $newColElement = $d->createElement("col");
            $newColElement->setAttribute("min", $column);
            $newColElement->setAttribute("max", $column);
            $newColElement->setAttribute("width", $width);
            $newColElement->setAttribute("customWidth", 1);
            $d->getElementsByTagName("cols")->item(0)->insertBefore($newColElement, $d->getElementsByTagName("col")->item($col_index_where_to_insert_new_column) );
        }

        $this->newSheetsTemplate[$this->currentSheet] = $d->saveXML();
    }

    /**
     * extracts the column width from the template
     *
     * @param $column
     * @return float
     */
    public function getColumnWidth($column) {
        $d = new DOMDocument();
        $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);

        //fix shared column width
        $this->fixColumnWidthSharing($d);
        //refresh new added columns here
        $this->newSheetsTemplate[$this->currentSheet] = $d->saveXML();

        $d = new DOMDocument();
        $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);

        $xpath = new DOMXPath($d);
        $xpath->registerNamespace("atom", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        $currentCell = $xpath->query('//atom:worksheet/atom:cols/atom:col[@min="'.$column.'"]');

        if ($currentCell->length) {
            $width = $currentCell->item(0)->getAttribute("width");
            return $width ? $width : ExcelUtil::convertToColumnWidth(ExcelUtil::defaultExcelCellWidth);
        }

        //if there's no set column in the template we will just use ExcelUtil::defaultExcelCellWidth as default
        //since ExcelUtil::defaultExcelCellWidth is the default column width of excel
        return ExcelUtil::convertToColumnWidth(ExcelUtil::defaultExcelCellWidth);
    }

    /**
     * this will fix shared column width
     * Example:
     *          <col min="2" max="2" width="10.140625" customWidth="1"/>
                <col min="3" max="3" width="10.140625" customWidth="1"/>
                <col min="5" max="8" width="10.140625" customWidth="1"/>
                <col min="9" max="12" width="11.140625" customWidth="1"/>
     * Notice that the last 2 element shared the one width.
     * Meaning column 5 - 8 are just the same width and also column 9 - 12 has the same width.
     *
     * This must be sparated for us to set individual columns width.
     * Each column must have his own record cell so that we can make a unique width for each.
     *
     * @param $xmlObj
     */
    private function fixColumnWidthSharing($xmlObj) {
        $column_to_fix = array();

        $cols_children = $xmlObj->getElementsByTagName("col");
        if (!$cols_children->length) return;

        foreach($cols_children as $i => $child) {
            $min = $child->getAttribute("min");
            $max = $child->getAttribute("max");
            $width = $child->getAttribute("width");
            if ($min != $max){
                $column_to_fix[$i] = array($min, $max, $width);
            }
        }

        if (!$column_to_fix) return;
        foreach($column_to_fix as $index => $minMax) {
            $width = $minMax[2];
            $xmlObj->getElementsByTagName("col")->item($index)->setAttribute("max", $minMax[0]);

            for($i = ($minMax[0] + 1); $i <= $minMax[1]; $i++) {
                $newColElement = $xmlObj->createElement("col");
                $newColElement->setAttribute("min", $i);
                $newColElement->setAttribute("max", $i);
                $newColElement->setAttribute("width", $width);
                $newColElement->setAttribute("customWidth", 1);
                $xmlObj->getElementsByTagName("cols")->item(0)->appendChild($newColElement);
            }
        }

        //rearrange the order of the column
        $arrayOfColumns = array();
        $cols_children = $xmlObj->getElementsByTagName("col");
        foreach($cols_children as $col){
            $arrayOfColumns[$col->getAttribute("min")] = $col;
        }

        ksort($arrayOfColumns);

        //this will overwrite the order of the col
        foreach ($arrayOfColumns as $a) {
            $xmlObj->getElementsByTagName("cols")->item(0)->appendChild($a);
        }

    }

}

?>