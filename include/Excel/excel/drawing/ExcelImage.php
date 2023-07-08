<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

/**
 * Class ExcelImage
 * This class is responsible everything about images
 * inside the excel
 */
class ExcelImage {

    /**
     * @var string  raw xml setting of an image
     */
    public $imageRawXMLSettings = '
        <xdr:twoCellAnchor editAs="oneCell">
            <xdr:from>
                <xdr:col>--from_col--</xdr:col>
                <xdr:colOff>--from_col_off--</xdr:colOff>
                <xdr:row>--from_row--</xdr:row>
                <xdr:rowOff>--from_row_off--</xdr:rowOff>
            </xdr:from>
            <xdr:to>
                <xdr:col>--to_col--</xdr:col>
                <xdr:colOff>--to_col_off--</xdr:colOff>
                <xdr:row>--to_row--</xdr:row>
                <xdr:rowOff>--to_row_off--</xdr:rowOff>
            </xdr:to>
            <xdr:pic>
                <xdr:nvPicPr>
                    <xdr:cNvPr id="--picture_id--" name="Picture --picture_id--"/>
                    <xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr>
                </xdr:nvPicPr>
                <xdr:blipFill>
                    <a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="rId--picture_id--">
                        <a:extLst>
                            <a:ext uri="{28A0092B-C50C-407E-A947-70E740481C1C}">
                                <a14:useLocalDpi xmlns:a14="http://schemas.microsoft.com/office/drawing/2010/main" val="0"/>
                            </a:ext>
                        </a:extLst>
                    </a:blip>
                    <a:stretch>
                        <a:fillRect/>
                    </a:stretch>
                </xdr:blipFill>
                <xdr:spPr>
                    <a:xfrm>
                        <a:off x="0" y="0"/>
                        <a:ext cx="0" cy="0"/>
                    </a:xfrm>
                    <a:prstGeom prst="rect">
                        <a:avLst/>
                    </a:prstGeom>
                </xdr:spPr>
            </xdr:pic>
            <xdr:clientData/>
        </xdr:twoCellAnchor>
    ';

    /**
     * all the logic that resposible in creating image is done here
     *
     * @param $path             image full path
     * @param $attributes       image settings
     * @param $sheet_index      sheet index
     * @param $drawingObj       ExcelXMLObjectDrawing object
     * @return mixed
     */
    public function newImage($path, $attributes, $sheet_index, $drawingObj, $excelObject) {
        $picture_filename = $this->copyImageToMedia($path, $drawingObj->mediaFolder);
        $attributes['picture_id'] = isset($attributes['picture_id']) ? $attributes['picture_id'] : $drawingObj->drawingCounter++;

        if ($attributes['to_col'] === false && $attributes['to_row'] === false) {
            $attributes = $this->getImageToLocation($drawingObj->mediaFolder.'/'.$picture_filename, $attributes, $excelObject);
        }

        // xl\drawings\drawing{$sheet_index}.xml
            if (!isset($drawingObj->drawingXMLDom[$sheet_index])) {
                $d = new DOMDocument();
                $d->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" '."".'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"></xdr:wsDr>');
                $drawingObj->drawingXMLDom[$sheet_index] = $d;
            }

            $xml_string = $this->imageRawXMLSettings;
            foreach($attributes as $k => $v) {
                $xml_string = str_replace("--$k--", $v, $xml_string);
            }
            $_twoCellAnchor = new DOMDocument();
            $_twoCellAnchor->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'.$xml_string.'</xdr:wsDr>');
            $twoCellAnchor = $_twoCellAnchor->getElementsByTagName("twoCellAnchor")->item(0);
            $new_twoCellAnchor = $drawingObj->drawingXMLDom[$sheet_index]->importNode($twoCellAnchor,  true);
            $drawingObj->drawingXMLDom[$sheet_index]->getElementsByTagName("wsDr")->item(0)->appendChild($new_twoCellAnchor);


        // xl\drawings\_rels\drawing{$sheet_index}.xml.rels
            if (!isset($drawingObj->drawingXMLRelDom[$sheet_index])) {
                $d = new DOMDocument();
                $d->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
                $drawingObj->drawingXMLRelDom[$sheet_index] = $d;
            }
            $relationship = $drawingObj->drawingXMLRelDom[$sheet_index]->createElement("Relationship");
            $relationship->setAttribute("Id", "rId". $attributes['picture_id']);
            $relationship->setAttribute("Type", "http://schemas.openxmlformats.org/officeDocument/2006/relationships/image");
            $relationship->setAttribute("Target", "../media/".$picture_filename);
            $drawingObj->drawingXMLRelDom[$sheet_index]->getElementsByTagName("Relationships")->item(0)->appendChild($relationship);

        // xl\worksheets\_rels\sheets{$sheet_index}.xml.rels
            if (!isset($drawingObj->imageSheetRelDom[$sheet_index])) {
                $d = new DOMDocument();
                $d->loadXML('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>');
                $drawingObj->imageSheetRelDom[$sheet_index] = $d;

                $relationship = $drawingObj->imageSheetRelDom[$sheet_index]->createElement("Relationship");
                $relationship->setAttribute("Id", "rId". $sheet_index);
                $relationship->setAttribute("Type", "http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing");
                $relationship->setAttribute("Target", "../drawings/drawing".$sheet_index.".xml");
                $drawingObj->imageSheetRelDom[$sheet_index]->getElementsByTagName("Relationships")->item(0)->appendChild($relationship);
            }

        return $attributes['picture_id'];
    }

    /**
     * removes the image from all the doms
     *
     * @param $index        image index
     * @param $drawingObj   ExcelXMLObjectDrawing
     * @return bool
     */
    public function removeImage($index, $drawingObj) {
        if (!$drawingObj->drawingXMLDom) return false;

        foreach($drawingObj->drawingXMLDom as $sheet_index => $dom) {
            $anchors = $dom->getElementsByTagName("twoCellAnchor");
            foreach($anchors as $an) {
                $id = $an->getElementsByTagName("cNvPr")->item(0)->getAttribute("id");
                if ($index == $id) {
                    $drawingObj->drawingXMLDom[$sheet_index]->getElementsByTagName("wsDr")->item(0)->removeChild($an);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * retrives all the images in the doms
     *
     * @param $drawingObj   ExcelXMLObjectDrawing
     * @return array
     */
    public function getImageDetails($drawingObj) {
        $return = array();

        if (!$drawingObj->drawingXMLDom) return $return;

        foreach($drawingObj->drawingXMLDom as $sheet_index => $dom) {
            $anchors = $dom->getElementsByTagName("twoCellAnchor");
            foreach($anchors as $an) {

                $col = $an->getElementsByTagName("from")->item(0)->getElementsByTagName("col")->item(0)->nodeValue;
                $col = ExcelUtil::intToColumn((int) $col);
                $row = $an->getElementsByTagName("from")->item(0)->getElementsByTagName("row")->item(0)->nodeValue;

                $cell = "$col:$row";
                $return["sheet".$sheet_index][] = array(
                    "index"    => $an->getElementsByTagName("cNvPr")->item(0)->getAttribute("id"),
                    "location" => $cell,
                );
            }
        }

        return $return;
    }

    /**
     * move the location of the current image
     *
     * @param $index        image index
     * @param $attributes   new image location
     * @param $drawingObj   ExcelXMLObjectDrawing
     * @return bool
     */
    public function moveImage($index, $attributes, $drawingObj) {
        if (!$drawingObj->drawingXMLDom) return false;

        foreach($drawingObj->drawingXMLDom as $sheet_index => $dom) {
            $anchors = $dom->getElementsByTagName("twoCellAnchor");
            foreach($anchors as $an) {
                $id = $an->getElementsByTagName("cNvPr")->item(0)->getAttribute("id");
                if ($index == $id) {
                    //from
                    if (isset($attributes['from']['cell']) && $attributes['from']['cell']) {
                        $from = ExcelUtil::splitColumnRowFromCell($attributes['from']['cell']);
                        $from['col'] = ExcelUtil::columnToInt($from['col']);
                        $an->getElementsByTagName("from")->item(0)->getElementsByTagName("col")->item(0)->nodeValue = $from['col'];
                        $an->getElementsByTagName("from")->item(0)->getElementsByTagName("row")->item(0)->nodeValue = $from['row'];
                    }

                    if (isset($attributes['from']['colOff'])) {
                        $an->getElementsByTagName("from")->item(0)->getElementsByTagName("colOff")->item(0)->nodeValue = $attributes['from']['colOff'] * 10000;
                    }
                    if (isset($attributes['from']['rowOff'])) {
                        $an->getElementsByTagName("from")->item(0)->getElementsByTagName("rowOff")->item(0)->nodeValue = $attributes['from']['rowOff'] * 10000;
                    }



                    //to
                    if (isset($attributes['to']['cell']) && $attributes['to']['cell']) {
                        $to = ExcelUtil::splitColumnRowFromCell($attributes['to']['cell']);
                        $to['col'] = ExcelUtil::columnToInt($to['col']);

                        $an->getElementsByTagName("to")->item(0)->getElementsByTagName("col")->item(0)->nodeValue = $to['col'];
                        $an->getElementsByTagName("to")->item(0)->getElementsByTagName("row")->item(0)->nodeValue = $to['row'];
                    }

                    if (isset($attributes['to']['colOff'])) {
                        $an->getElementsByTagName("to")->item(0)->getElementsByTagName("colOff")->item(0)->nodeValue = $attributes['to']['colOff'] * 10000;
                    }
                    if (isset($attributes['to']['rowOff'])) {
                        $an->getElementsByTagName("to")->item(0)->getElementsByTagName("rowOff")->item(0)->nodeValue = $attributes['to']['rowOff'] * 10000;
                    }
                }
            }
        }

        return false;
    }

    /**
     * copies the new image to the new output cell media folder
     * @param $path     original image path
     * @param $to       new media folder path
     * @return string
     */
    private function copyImageToMedia($path, $to) {
        $file = pathinfo($path);
        $new_filename = strtolower(md5(microtime()).'.'.$file['extension']);
        copy($path, $to.'/'.$new_filename);
        return $new_filename;
    }

    /**
     * this set the proper to_col and to_row in image location in case
     * the you want to use the default image size.
     *
     * @param $image_path   path of the uploaded image
     * @param $attributes   the attribute that was passed from Excel::createImage $image_location
     * @param $excelObject  Excel Object
     * @return mixed        final image location
     */
    private function getImageToLocation($image_path, $attributes, $excelObject) {
        $ptSize = 1.3333333333334;
        $sizeToPixel = 10000;

        //$width, $height, are in pixel
        list($width, $height, $type, $attr) = getimagesize($image_path);
        $width = (($width * $ptSize) * $sizeToPixel) + $attributes['from_col_off'];
        $height = (($height * $ptSize) * $sizeToPixel) + $attributes['from_row_off'];

        $from_column = $excelObject->util->intToColumn($attributes['from_col']).$attributes['from_row'];
        $cell_start = $from_column;

        $firstColCellSize = $excelObject->util->getCellSize($cell_start, $excelObject);
        $firstColCellSize['width'] = $firstColCellSize['width'] * $sizeToPixel;
        $firstColCellSize['height'] = $firstColCellSize['height'] * $sizeToPixel;


        if ( $firstColCellSize['width'] < ($width + $attributes['from_col_off']) ){
            //dont include the start cell.
            $o = $excelObject->util->getAllCellFrom($cell_start, 1, "col");
            $cell_start = $o[1];

            //width col
            //In this part we are calculating every cell's width untill we reach the
            //default image size
            $widthCounter = 0;
            $colCells = array();
            while($widthCounter < $width){
                $cellSize = $excelObject->util->getCellSize($cell_start, $excelObject);
                $widthCounter += $cellSize['width'] * $sizeToPixel;
                $colCells[] = array($cell_start, $widthCounter, $cellSize);
                $o = $excelObject->util->getAllCellFrom($cell_start, 1, "col");
                $cell_start = $o[1];
            }
            $columnStop = $colCells[ count($colCells) - 2 ];
            //width offset
            $col_offset = $width - $columnStop[1];
            $attributes['to_col_off'] = round($col_offset);
        }
        else {
            //this means that the image size can be displayed in one cell only
            $attributes['to_col'] = $attributes['from_col'];
            $attributes['to_col_off'] = round( $width );
            $columnStop[0] = $cell_start;
        }

        if ( $firstColCellSize['height'] < ($height + $attributes['from_row_off']) ){
            $cell_start = $columnStop[0];
            $o = $excelObject->util->getAllCellFrom($cell_start, 1, "row");
            $cell_start = $o[1];
            //height row
            //In this part we are calculating every cell's height untill we reach the
            //default image size
            $heightCounter = 0;
            $rowCells = array();
            while($heightCounter < $height){
                $cellSize = $excelObject->util->getCellSize($cell_start, $excelObject);
                $heightCounter += $cellSize['height'] * $sizeToPixel;
                $rowCells[] = array($cell_start, $heightCounter, $cellSize);
                $o = $excelObject->util->getAllCellFrom($cell_start, 1, "row");
                $cell_start = $o[1];
            }
            $rowStop = $rowCells[ count($rowCells) - 2 ];
            //height offset
            $row_offset = $height - $rowStop[1];
            $attributes['to_row_off'] = round($row_offset);
        }
        else {
            $attributes['to_row'] = $attributes['from_row'];
            $attributes['to_row_off'] = round($height);
        }

        $_f = $excelObject->util->splitColumnRowFromCell($rowStop[0]);
        if ($attributes['to_col'] === false) {
            $attributes['to_col'] = $excelObject->util->columnToInt($_f['col']) ? $excelObject->util->columnToInt($_f['col']) : 0;
        }

        if ($attributes['to_row'] === false) {
            $attributes['to_row'] = $_f['row'];
        }

        return $attributes;
    }
}

?>