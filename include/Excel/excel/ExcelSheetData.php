<?php
/**
 * A subpackage of the ExcelXML. This generally handles all the data/cell in ExcelXML
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

/**
 * Class ExcelXMLObjectWorkSheetData
 */
class ExcelSheetData {

    /**
     * @var array $sheetData   this holds the old entire sheet's cells and rows
     */
    public $sheetData = array();

    /**
     * @var array $newSheetData   this holds the new entire sheet's cells and rows
     */
    public $newSheetData = array();

    public function __construct(){ }

    /**
     * this fixes the row number of the specified row element. Since we need to make the rows and cells be in order as much as possible
     *
     * @param DOMNode $node         the node you want to update. This is a row element
     * @param integer $row_number   the new row number you want to replace
     * @return DOMNode $n           processed DomNode
     */
    public function fixRowNumbers($node, $row_number) {
        $newdoc = new DOMDocument;
        $n = $newdoc->importNode($node, true);
        $n->setAttribute("r", $row_number);
        foreach ($n->getElementsByTagName('c') as $k => $v) {
            $cell_id = $v->getAttribute("r");
            $cell_id = preg_replace("/[0-9]/i", "", $cell_id). $row_number;
            $n->getElementsByTagName('c')->item($k)->setAttribute("r", $cell_id);
        }
        return $n;
    }

    /**
     * extracts a specific row from the domdocument.
     *
     * @param int $row_id       row id you want to extract from domDocument
     * @param DomDocument $dom  the dom where you want to get the specific row
     * @return DOMElement       the row element extracted from the domDocument
     */
    public function extractCurrentRow($row_id, $dom) {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace("atom", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        $row = $xpath->query('//atom:worksheet/atom:sheetData/atom:row[@r="'.$row_id.'"]');

        if ($row->length) {
            return $row->item(0);
        }

        return $this->createBlankRowElement($row_id);
    }

    /**
     * add row in the new xml data
     *
     * @param DomNode $row              this is the DomNode you want to copy in the xml. usually this is a Row element
     * @param int $working_sheet_index  the sheet index where you want to copy the new row element
     */
    public function insertSheetRow($row, $working_sheet_index) {
        $this->newSheetData[$working_sheet_index][] = $row;
    }

    /**
     * this returns the whole xml string of the given sheet
     *
     * @param int $sheet            sheet index you want to export
     * @param string $xml_sheets    this is a string xml of the sheet you want to export
     * @return string               the whole xml string of the sheet
     */
    public function getXMLString($sheet = 1, $xml_sheets) {
        $sheet_rows = $this->newSheetData[$sheet];
        $str = '';
        foreach($sheet_rows as $row) {
            $str .= $row->ownerDocument->saveXML($row);
        }
        $sheet = str_replace('{--SHEETDATA--}', "<sheetData>".$str."</sheetData>", $xml_sheets);
        return $sheet;
    }

    /**
     * removes all the children of the domnode
     *
     * @param domNode $cell     domNode you want to remove the children
     * @return domNode mixed    new domNode that has no children
     */
    public function removeChildren($cell) {
        foreach($cell->childNodes as $child) {
            $cell->removeChild($child);
        }
        return $cell;
    }

    /**
     * this makes only a raw row element
     *
     * @param int $row_id   the row id you want to set as attribute
     * @return DOMElement   the newly created RowElement
     */
    private function createBlankRowElement($row_id = 1) {
        $dom = new DOMDocument();
        $element = $dom->createElement("row", '');
        $element->setAttribute("r", $row_id);
        return $element;
    }

    /**
     * sets the row height of the entire row cell
     *
     * @param $height
     * @param $rowToSet
     * @param $sheet_index
     */
    public function setRowHeight($height, $rowToSet, $sheet_index) {
        if (isset($this->newSheetData[$sheet_index]) && $this->newSheetData[$sheet_index]) {
            foreach($this->newSheetData[$sheet_index] as $i => $row) {
                $rowID = $row->getAttribute("r");
                if ($rowID == $rowToSet) {
                    $row->setAttribute("ht", $height);
                    $row->setAttribute("customHeight", 1);
                    $this->newSheetData[$sheet_index][$i] = $row;
                }
            }
        }
    }

}

?>