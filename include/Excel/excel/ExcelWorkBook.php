<?php
/**
 * A subpackage of the ExcelXML. This holds the main wrapper of the whole xlsx file.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

/**
 * Class ExcelXMLObjectWorkBook
 */
class ExcelWorkBook {

    /**
     * @var string $workbookFile       the full path of the workbook.xml
     */
    public $workbookFile = "/xl/workbook.xml";

    /**
     * @var string  holds the rel path of workbook
     */
    public $workbookRels = "/xl/_rels/workbook.xml.rels";

    /**
     * @var $workbookRelDom     holds the DomDocument of the Rels
     */
    public $workbookRelsDom;

    /**
     * @var DomDocument $workbookDom            the DomDocument of the workbook.xml
     */
    public $workbookDom;

    /**
     * @var int     this sets the active sheet upon opening the workbook
     */
    public $activeTab = 0;

    public function __construct(){ }

    /**
     * loads the workbook xml file to create DomDocument object
     *
     * @param object $parent_obj   ExcelXML object
     */
    public function loadWorkBookXML($parent_obj) {
        $this->workbookFile = $parent_obj->excelFolder.$this->workbookFile;
        $this->workbookDom = $parent_obj->util->createDomObject($this->workbookFile, true);
        $this->fixSheetId();

        $this->workbookRels = $parent_obj->excelFolder.$this->workbookRels;
        $this->workbookRelsDom = $parent_obj->util->createDomObject($this->workbookRels, true);
    }

    /**
     * this fixes the SheetId attribute to match the r:Id attribute of the worksheets
     */
    private function fixSheetId() {
        $sheets = $this->workbookDom->getElementsByTagName("sheet");
        foreach($sheets as $sheet) {
            $rId = $sheet->getAttribute("r:id");
            $rId = str_replace("rId", "", $rId);

            $sheet->setAttribute("sheetId", $rId);
        }
    }

    /**
     * renames the sheet
     *
     * @param int $index    the sheet index you want to rename
     * @param string $name  the new sheet name
     */
    public function renameSheet($index, $name) {
        $sheets = $this->workbookDom->getElementsByTagName("sheet");

        $i = 0;
        foreach($sheets as $sheet) {
            if ($sheet->getAttribute("sheetId") == $index) {
                $this->workbookDom->getElementsByTagName("sheet")->item($i)->setAttribute("name", $name);
            }
            $i++;
        }
    }

    /**
     * returns all the sheet names and their indexes
     * @return array
     */
    public function getSheetNames() {
        $return = array();
        $sheets = $this->workbookDom->getElementsByTagName("sheet");

        foreach($sheets as $sheet) {
            $return[$sheet->getAttribute('sheetId')] =  $sheet->getAttribute('name');
        }

        return $return;
    }

    /**
     * creates new blank sheet dynamically
     *
     * @param int $index    the new sheet index
     */
    public function createSheetRels($index) {
        $sheet = $this->workbookDom->createElement("sheet");
        $sheet->setAttribute("name", "Sheet".$index);
        $sheet->setAttribute("sheetId", $index);
        $sheet->setAttribute("r:id", "rId".$index);
        $this->workbookDom->getElementsByTagName("sheets")->item(0)->appendChild($sheet);

        $r = $this->workbookRelsDom->createElement("Relationship");
        $r->setAttribute("Id", "rId".$index);
        $r->setAttribute("Type", "http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet");
        $r->setAttribute("Target", "worksheets/sheet".$index.".xml");
        $this->workbookRelsDom->getElementsByTagName("Relationships")->item(0)->appendChild($r);

        $index++;
        $relationships = $this->workbookRelsDom->getElementsByTagName("Relationship");
        foreach($relationships as $r) {
            if ($r->getAttribute("Type") != "http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet") {
                $r->setAttribute("Id", "rId".$index++);
            }
        }
    }

    /**
     * this cleans the unused sheets in the template
     *
     * @param array $active_sheets  an array of active sheets
     */
    public function cleanUnusedSheets($active_sheets) {
        $indexes = array_keys($active_sheets);

        $sheets = $this->workbookDom->getElementsByTagName("sheet");
        $sheet_to_remove = array();
        foreach($sheets as $sheet) {
            if (!in_array($sheet->getAttribute("sheetId"), $indexes)){
                $sheet_to_remove[] = $sheet;
            }
        }

        foreach ($sheet_to_remove as $s) {
            $sheet->parentNode->removeChild($s);
        }
    }

    /**
     * this writes the xml dom document to the workbook file
     */
    public function writeWorkbook() {
        $this->workbookDom->getElementsByTagName("workbookView")->item(0)->setAttribute("activeTab", $this->activeTab);
        $this->fixSheetPrintAreaBeforePrinting();
        file_put_contents($this->workbookFile, $this->workbookDom->saveXML());
        file_put_contents($this->workbookRels, $this->workbookRelsDom->saveXML());
    }

    /**
     * this will create defined name in the workbook file that will tell what us the exact
     * location of the printed area "PRINT_AREA"
     *
     * @param $name
     */
    public function setSheetPrintArea($name) {
        $defined = $this->workbookDom->getElementsByTagName("definedNames")->item(0);
        if (!$defined) {
            $d = $this->workbookDom->createElement("definedNames");
            $this->workbookDom->getElementsByTagName("workbook")->item(0)->insertBefore($d, $this->workbookDom->getElementsByTagName("calcPr")->item(0));
        }

        $dn = $this->workbookDom->createElement("definedName", $name);
        $dn->setAttribute("name", "_xlnm.Print_Area");
        $dn->setAttribute("localSheetId", "0");
        $this->workbookDom->getElementsByTagName("definedNames")->item(0)->appendChild($dn);
    }

    /**
     * this is just a little helper that aligns all the ids of Print_Area into their respective sheet
     */
    private function fixSheetPrintAreaBeforePrinting() {
        $sheet         = $this->workbookDom->getElementsByTagName("sheet");
        $definedName   = $this->workbookDom->getElementsByTagName("definedName");
        if (!$definedName) return;

        $sheetArray = array();
        foreach($sheet as $s) {
            $sheetArray[] = $s->getAttribute("name");
        }

        foreach($definedName as $d) {
            $name = $d->nodeValue;
            $name = explode("!", $name);
            $name = str_replace("'","",$name[0]);

            if (in_array($name, $sheetArray)) {
                $index = array_search($name, $sheetArray);
                $d->setAttribute("localSheetId", $index);
            }
        }
    }

    /**
     * (ivan)
     * This will create defined name in the workbook file that will tell what
     * the ROWs or COLUMNs to repeat(perPage) in printing the excel sheet.
     */
    public function setSheetPrintTitles($name) {
        $defined = $this->workbookDom->getElementsByTagName("definedNames")->item(0);
        if (!$defined) {
            $d = $this->workbookDom->createElement("definedNames");
            $this->workbookDom->getElementsByTagName("workbook")->item(0)->insertBefore($d, $this->workbookDom->getElementsByTagName("calcPr")->item(0));
        }

        $dn = $this->workbookDom->createElement("definedName", $name);
        $dn->setAttribute("name", "_xlnm.Print_Titles");
        $dn->setAttribute("localSheetId", "0");
        $this->workbookDom->getElementsByTagName("definedNames")->item(0)->appendChild($dn);
    }
}

?>