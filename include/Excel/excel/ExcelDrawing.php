<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

include_once "include/Excel/excel/drawing/ExcelImage.php";
include_once "include/Excel/excel/drawing/ExcelChart.php";
/**
 * Class ExcelXMLObjectDrawing
 */
class ExcelDrawing {

    /**
     * @var string      this setof variable holds only the exact folder location
     */
    public $drawingFolder = "/xl/drawings";
    public $drawingRelFolder = "/xl/drawings/_rels";
    public $sheetRelFolder = "/xl/worksheets/_rels";
    public $chartFolder = "/xl/charts";
    public $mediaFolder    = "/xl/media";

    /**
     * @var objects     this holds all the dom document object
     */
    public $drawingXMLDom;
    public $chartDom;
    public $drawingXMLRelDom;
    public $imageSheetRelDom;

    /**
     * @var int     counter for the drawings
     */
    public $drawingCounter = 1001;

    /**
     * @var holds the object for ExcelXMLObjectDrawingImage and ExcelXMLObjectDrawingChart
     */
    public $image;
    public $chart;

    /**
     * @var bool    identfier if a changes has been made to drawings
     */
    public $hasUpdate = false;

    public function __construct(){  }

    /**
     * loads all the doms and sets the folder location
     *
     * @param $parent_obj   ExcelXMLObjectDrawing object
     */
    public function loadDrawingXML($parent_obj) {
        $this->image            = new ExcelImage;
        $this->chart            = new ExcelChart;

        $this->mediaFolder      = $parent_obj->excelFolder.$this->mediaFolder;
        $this->drawingFolder    = $parent_obj->excelFolder.$this->drawingFolder;
        $this->drawingRelFolder = $parent_obj->excelFolder.$this->drawingRelFolder;
        $this->sheetRelFolder   = $parent_obj->excelFolder.$this->sheetRelFolder;
        $this->chartFolder      = $parent_obj->excelFolder.$this->chartFolder;

        $this->loadAllDrawingDoms();
    }

    /**
     * loads all the dom to be use.
     * This loads all the xml's that is related
     * to chart and images
     */
    private function loadAllDrawingDoms() {
        if (file_exists($this->drawingFolder)) {
            if ($files = ExcelUtil::getFilesInDirByExt($this->drawingFolder, "xml")) {
                foreach($files as $f) {
                    $i = preg_replace("/[^0-9]/","",$f);
                    $i = (int) $i;
                    $d = new DOMDocument();
                    $d->load($this->drawingFolder."/".$f);
                    $this->drawingXMLDom[$i] = $d;
                }
            }
        }

        if (file_exists($this->drawingRelFolder)) {
            if ($files = ExcelUtil::getFilesInDirByExt($this->drawingRelFolder, "rels")) {
                foreach($files as $f) {
                    $i = preg_replace("/[^0-9]/","",$f);
                    $i = (int) $i;
                    $d = new DOMDocument();
                    $d->load($this->drawingRelFolder."/".$f);
                    $this->drawingXMLRelDom[$i] = $d;
                }
            }
        }

        if (file_exists($this->sheetRelFolder)) {
            if ($files = ExcelUtil::getFilesInDirByExt($this->sheetRelFolder, "rels")) {
                foreach($files as $f) {
                    $i = preg_replace("/[^0-9]/","",$f);
                    $i = (int) $i;
                    $d = new DOMDocument();
                    $d->load($this->sheetRelFolder."/".$f);
                    $this->imageSheetRelDom[$i] = $d;
                }
            }
        }
    }

    /**
     * creates new image on the output excel
     *
     * @param $path         image absolute path
     * @param $attributes   settings for the image
     * @param $sheet_index  sheet index
     * @param $excelObject  Excel Object
     * @return bool         returns the index if image is created
     */
    public function addNewImage($path, $attributes, $sheet_index, $excelObject) {
        $this->ensureFolders();
        if ($image_index = $this->image->newImage($path, $attributes, $sheet_index, $this, $excelObject)){
            $this->hasUpdate = true;
            return $image_index;
        }
        return false;
    }

    /**
     * removes the specific image on the cell
     *
     * @param $index    image index
     */
    public function removeImage($index){
        $this->image->removeImage($index, $this);
        $this->hasUpdate = true;
    }

    /**
     * retrieves all the images and the current location of the image
     *
     * @return mixed
     */
    public function getImageDetails(){
        return $this->image->getImageDetails($this);
    }

    /**
     * update the location of the image
     * @param $index
     * @param $attributes
     * @return mixed
     */
    public function moveImage($index, $attributes) {
        $this->hasUpdate = true;
        return $this->image->moveImage($index, $attributes, $this);
    }

    /**
     * writes the xml file from the dom created
     *
     * @param $parent_obj
     * @return bool
     */
    public function writeDrawing($parent_obj) {
        if (!$this->hasUpdate) return true;

        foreach($this->drawingXMLDom as $k => $i) {
            if (isset($this->drawingXMLDom[$k])) {
                file_put_contents($this->drawingFolder."/drawing".$k.".xml", $this->drawingXMLDom[$k]->saveXML());
            }

            if (isset($this->drawingXMLRelDom[$k])) {
                file_put_contents($this->drawingRelFolder."/drawing".$k.".xml.rels", $this->drawingXMLRelDom[$k]->saveXML());
            }

            if (isset($this->imageSheetRelDom[$k])) {
                file_put_contents($this->sheetRelFolder."/sheet".$k.".xml.rels", $this->imageSheetRelDom[$k]->saveXML());
            }

            if (isset($parent_obj->worksheet->newSheetsTemplate[$k]) && isset($this->drawingXMLDom[$k])) {
                $d = new DOMDocument();
                $d->loadXML($parent_obj->worksheet->newSheetsTemplate[$k]);
                if (!$d->getElementsByTagName("drawing")->length) {
                    $drawing = $d->createElement("drawing");
                    $drawing->setAttribute("r:id", "rId".$k);
                    $d->getElementsByTagName("worksheet")->item(0)->appendChild($drawing);
                    $parent_obj->worksheet->newSheetsTemplate[$k] = $d->saveXML();
                }
            }
        }

        $this->writeChartFiles();
        $this->updateContentTypesFile($parent_obj);
    }

    /**
     * creates the chart object
     * @param $chart
     * @param $excel_xml_object
     * @return bool
     */
    public function addChart($chart, $excel_xml_object) {
        $this->ensureFolders();
        if ($image_index = $this->chart->newChart($chart, $this, $excel_xml_object)){
            $this->hasUpdate = true;
            return $image_index;
        }
        return false;
    }


    /***************************************************
     * PRIVATE FUNCTIONS
    ****************************************************/

    /**
     * the content type file is being updated
     * here. If there's an additional chart or any xml file
     * it must be included to this file.
     * Also some default extensions on images is also part of this
     * function
     *
     * @param $parent_obj
     */
    private function updateContentTypesFile($parent_obj) {
        $content_file = $parent_obj->excelFolder.'/[Content_Types].xml';
        $d = new DOMDocument();
        $d->load($content_file);

        /*** Loading Extensions **/
        $image_extensions = $d->getElementsByTagName("Default");
        $extensions = array("jpeg" => 1, "jpg" => 1, "png" => 1, "gif" => 1, "bmp" => 1);
        foreach($image_extensions as $ext){
            if(array_key_exists($ext->getAttribute("Extension"), $extensions)) {
                unset($extensions[$ext->getAttribute("Extension")]);
            }
        }
        if ($extensions) {
            foreach($extensions as $e => $v) {
                $ext = $d->createElement("Default");
                $ext->setAttribute("Extension", $e);
                $ext->setAttribute("ContentType", "image/".$e);
                $d->getElementsByTagName("Types")->item(0)->appendChild($ext);
            }
        }

        /*** Loading Overrides **/
        //drawing files
        $overrides = $d->getElementsByTagName("Override");
        $remove_override_drawing = array();
        foreach($overrides as $override){
            if ($override->getAttribute("ContentType") != "application/vnd.openxmlformats-officedocument.drawing+xml") continue;
            $remove_override_drawing[] = $override;
        }

        foreach($remove_override_drawing as $rd) {
            $d->getElementsByTagName("Types")->item(0)->removeChild($rd);
        }

        $drawings_to_add = array_keys($this->drawingXMLDom);
        foreach($drawings_to_add as $k)  {
            $override = $d->createElement("Override");
            $override->setAttribute("PartName", "/xl/drawings/drawing".$k.".xml");
            $override->setAttribute("ContentType", "application/vnd.openxmlformats-officedocument.drawing+xml");
            $d->getElementsByTagName("Types")->item(0)->appendChild($override);
        }

        //chart files
        foreach($this->chartDom as $k => $chart_dom) {
            $override = $d->createElement("Override");
            $override->setAttribute("PartName", "/xl/charts/chart".$k.".xml");
            $override->setAttribute("ContentType", "application/vnd.openxmlformats-officedocument.drawingml.chart+xml");
            $d->getElementsByTagName("Types")->item(0)->appendChild($override);
        }

        file_put_contents($content_file, $d->saveXML());
    }

    /**
     * chart has a different xml files. Each chart has its own xml file
     * this function writes the files from the dom
     */
    private function writeChartFiles() {
        if (!$this->chartDom) return;
        foreach($this->chartDom as $k => $dom) {
            file_put_contents($this->chartFolder."/chart".$k.".xml", $dom->saveXML());
        }
    }

    /**
     * this ensures the folder existence if a drawing is created
     */
    private function ensureFolders() {
        ExcelUtil::createIfNotExistFolder($this->mediaFolder);
        ExcelUtil::createIfNotExistFolder($this->drawingFolder);
        ExcelUtil::createIfNotExistFolder($this->drawingRelFolder);
        ExcelUtil::createIfNotExistFolder($this->sheetRelFolder);
        ExcelUtil::createIfNotExistFolder($this->chartFolder);
    }
}

?>