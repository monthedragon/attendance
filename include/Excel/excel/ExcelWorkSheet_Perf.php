<?php
/**
 * Child class of ExcelWorkSheet; Contains performance modifications for its base class. Use this with Excel_perf.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Albert Fajarito
 * @since    2017-05-19
 */

include_once "include/Excel/excel/ExcelWorkSheet.php";

/**
 * Class ExcelXMLObjectWorkSheet
 */
class ExcelWorkSheet_Perf extends ExcelWorkSheet{

    /**
     * Temporary container; Contains merged cell range parts (row and column). In the base class this is derived
     * every Excel::addrow() and takes too much time.
     * @var array
     * @author Albert
     */
    public $mergedcellRanges = array();

    /**
     * #64857 Performance update: This method performs the same functionality as checkMergedCellFromTemplate() but use
     * $this->mergedcellRanges which is prepared earlier as source of merge cell range names.
     * @param $rowInTemplate
     * @param $rowNumberInNewSheet
     * @author Albert
     */
    public function checkMergedCellFromTemplate($rowInTemplate, $rowNumberInNewSheet){
        if (!isset($this->mergedcellRanges[$this->currentSheet]) || !$this->mergedcellRanges[$this->currentSheet])
            return;

        $mergedCells = $this->mergedcellRanges[$this->currentSheet];

        if(array_key_exists($rowInTemplate, $mergedCells)){
            foreach($mergedCells[$rowInTemplate] AS $rangeValues){

                $firstCell = $rangeValues[0];
                $secondCell = $rangeValues[1];

                $newToRowCell =  $rowNumberInNewSheet + ($secondCell['row'] - $firstCell['row']);
                $newSetOfCell2 = $secondCell['col'].$newToRowCell;
                $newSetOfCell1 = $firstCell['col'].$rowNumberInNewSheet;
                $this->cellsToMerge[$this->currentSheet][] = array($newSetOfCell1, $newSetOfCell2); //this will be merged when the sheet is in the download stage
            }
        }
    }

    /**
     * Override parent method, the difference with parent method; here the DomDocument object is injected rather
     * than instantiated since mergeCell is looped during rendering process.
     *
     * @param string $cell1     the start cell of the merge cell
     * @param string $cell2     to what cell you want to merge
     * @param DomDocument $d
     * @throws Exception        cells not valid
     * @author Albert
     */
    public function mergeCell($cell1, $cell2 , $d) {
        $c1 = ExcelUtil::splitColumnRowFromCell($cell1);
        $c2 = ExcelUtil::splitColumnRowFromCell($cell2);

        if (( ExcelUtil::columnToInt($c1['col']) > ExcelUtil::columnToInt($c2['col'])) || ($c1['row'] > $c2['row'])) {
            throw new Exception("$cell1, $cell2 are not a valid cells to merge");
        }

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
    }

    /**
     * Overload parent module; The difference with parent module is here we instantiate the DomDocument object
     * rather and load the XML per worksheet rather than per cell then pass them to mergeCell()
     * @author Albert
     */
    public function writeMergedCells() {
        $d = new DOMDocument();
        foreach($this->newSheetsTemplate as $key => $v) {
            if ($cells = $this->cellsToMerge[$key]) {
                $this->setCurrentWorkSheet($key);
                $d->loadXML($this->newSheetsTemplate[$this->currentSheet]);
                foreach($cells as $c) {
                    $this->mergeCell($c[0], $c[1],$d);
                }
                $this->newSheetsTemplate[$this->currentSheet] = $d->saveXML();
            }
        }
    }

    /**
     * This method sets $this->mergedcellRanges values. Values is array of merged-cell ranges [start, end] and their
     * respective row and col. The values are grouped per merged-cell's row number. This was previously done
     * per addRow() which resulted in poor performance.
     * @author Albert
     */
    public function SetTemplateMergeRanges(){
        if (!isset($this->mergedCellsFromTemplate[$this->currentSheet]) || !$this->mergedCellsFromTemplate[$this->currentSheet])
            return;

        $mergedCells = $this->mergedCellsFromTemplate[$this->currentSheet];
        foreach ($mergedCells as $cell) {
            $range = explode(':', $cell);
            $firstCell  = ExcelUtil::splitColumnRowFromCell($range[0]);
            $secondCell = ExcelUtil::splitColumnRowFromCell($range[1]);

            $this->mergedcellRanges[$this->currentSheet][$firstCell['row']][] = array(
                array(
                    'row' => $firstCell['row'],
                    'col' => $firstCell['col']
                ),
                array(
                    'row' => $secondCell['row'],
                    'col' => $secondCell['col']
                ),
            );
        }
    }
}

?>