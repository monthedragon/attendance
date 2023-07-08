<?php
/**
 * A subpackage of the ExcelXML. This handles all sheets related functions in ExcelXML.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

/**
 * Class ExcelXMLObjectStyle
 */
class ExcelStyle {

    /**
     * @var string  holder of the full path of the styles.xml
     */
    public $styleFile = '/xl/styles.xml';

    /**
     * @var DomDocument holds the DomDocument of the style.xml
     */
    public $styleDom;

    /**** default style values ****/
    /**
     * @var array styles for borders location (sides, top, bottom)
     */
    private $borderLocations = array("left","right", "top", "bottom", "diagonal");

    /**
     * @var array text alignments (x is for horizontal alignment and y is for vertical alignment)
     */
    private $textAlign = array(
                'y' => array('bottom', 'center', 'top'),
                'x' => array('left', 'center', 'right')
    );

    public function __construct(){  }

    /**
     * this creates the  style domdocument and sets the full path of the style.xml path
     * @param $parent_obj Excel class
     */
    public function loadStyleXML($parent_obj) {
        $this->styleFile = $parent_obj->excelFolder.$this->styleFile;
        $this->styleDom = $parent_obj->util->createDomObject($this->styleFile, true);
    }

    /**
     * this create a new style element in style domdocument
     *
     * @param array $array_style    the array for the style you are creating
     * @return int                  the style id created
     */
    public function createStyle( array $array_style = array() ){
        if (!$array_style) return 0;
        $arr = $array_style;

        $has_style = array();

        //cell styles
        if (isset($arr['cell'])) {
            $has_style = $this->newCellStyle($arr['cell']);
        }

        //text styles
        if (isset($arr['text'])) {
            $has_style += $this->newTextStyle($arr['text']);
        }

        if ($has_style) {
            $this->writeXF($has_style);
            $style_id = $this->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->length - 1;
            return $style_id;
        }

        return 0;
    }

    /**
     * this is resposible for creating styles in cell
     *
     * @param $style    cell style array
     * @return array    array of ids that will be st to domsocument
     */
    private function newTextStyle($style) {
        $return = array();

        if (isset($style['align-x']) && in_array($style['align-x'], $this->textAlign['x'])) {
            //this is part of the xf tag. that's why this will be written on the self::writeXF()
            $return['align-x'] = $style['align-x'];
        }

        if (isset($style['align-y']) && in_array($style['align-y'], $this->textAlign['y'])) {
            //this is part of the xf tag. that's why this will be written on the self::writeXF()
            $return['align-y'] = $style['align-y'];
        }

        if (isset($style['wrap-text']) && $style['wrap-text']) {
            //this is part of the xf tag. that's why this will be written on the self::writeXF()
            $return['wrap-text'] = true;
        }

        if (isset($style['shrink-to-fit']) && $style['shrink-to-fit']) {
            //this is part of the xf tag. that's why this will be written on the self::writeXF()
            $return['shrink-to-fit'] = true;
        }

        if (isset($style['indent']) && $style['indent']) {
            //this is part of the xf tag. that's why this will be written on the self::writeXF()
            $return['indent'] = $style['indent'];
        }

        //font styles
        $has_font = false;
        $font_node = $this->styleDom->createElement("font");
        if (isset($style['font']) && $style['font']) {
            $_font = $this->styleDom->createElement("name");
            $_font->setAttribute("val", $style['font']);
            $font_node->appendChild($_font);
            $has_font = true;
        }

        if (isset($style['bold']) && $style['bold']) {
            $font_node->appendChild($this->styleDom->createElement("b"));
            $has_font = true;
        }

        if (isset($style['italic']) && $style['italic']) {
            $font_node->appendChild($this->styleDom->createElement("i"));
            $has_font = true;
        }

        if (isset($style['underline']) && $style['underline']) {
            $_u = $this->styleDom->createElement("u");
            if ($style['underline'] == "double") {
                $_u->setAttribute("val", $style['underline']);
            }
            $font_node->appendChild($_u);
            $has_font = true;
        }

        if (isset($style['size']) && $style['size']) {
            $_sz = $this->styleDom->createElement("sz");
            $_sz->setAttribute("val", $style['size']);
            $font_node->appendChild($_sz);
            $has_font = true;
        }

        if (isset($style['color']) && $style['color']) {
            $_color = $this->styleDom->createElement("color");
            $_color->setAttribute("rgb", $style['color']);
            $font_node->appendChild($_color);
            $has_font = true;
        }

        if (isset($style['strike-through']) && $style['strike-through']) {
            $font_node->appendChild($this->styleDom->createElement("strike"));
            $has_font = true;
        }

        if ($has_font) {
            $this->styleDom->getElementsByTagName("fonts")->item(0)->appendChild($font_node);
            $return['fontId'] = $this->styleDom->getElementsByTagName("font")->length - 1;
        }

        return $return;
    }

    /**
     * this is responsible in creating styles pertaining to texts only
     * @param $style    array of text styles
     * @return array    array of id and style values that will be set to style domdocument
     */
    private function newCellStyle($style){
        $return = array();

        /** BACKGROUND **/
        if (isset($style['background'])) {
            $fgColor = $this->styleDom->createElement("fgColor");

            if ($style['background']['color']){
                $fgColor->setAttribute("rgb", $style['background']['color']);
            }

            if ($style['background']['shade']){
                $fgColor->setAttribute("tint", $style['background']['shade']);
            }

            $patternType = 'solid';
            if(isset($style['background']['pattern-type'])){
                $patternType = $style['background']['pattern-type'];
            }

            $patternFill = $this->styleDom->createElement("patternFill");
            $patternFill->setAttribute("patternType", $patternType);
            $patternFill->appendChild($fgColor);

            $fill = $this->styleDom->createElement("fill");
            $fill->appendChild($patternFill);

            $this->styleDom->getElementsByTagName("fills")->item(0)->appendChild($fill);
            $return['fillId'] = $this->styleDom->getElementsByTagName("fill")->length - 1;
        }

        /** BORDER **/
        if (isset($style['border']) || $style['border']) {
            $border = $this->styleDom->createElement("border");

            if (isset($style['border']['all'])) {
                $_all = array();
                foreach ($this->borderLocations as $i) {
                    $_all[$i] = isset($style['border'][$i]) ? $style['border'][$i] : $style['border']['all'];
                }
                $style['border'] = $_all;
                unset($style['border']['diagonal']);
                unset($style['border']['all']);
            }

            foreach($this->borderLocations as $border_location) {
                if (isset($style['border'][$border_location])) {

                    $border_location_node = $this->styleDom->createElement($border_location);

                    //border style
                    if (isset($style['border'][$border_location]["style"])) {
                        $border_location_node->setAttribute("style", $style['border'][$border_location]["style"]);
                    }
                    else {
                        $border_location_node->setAttribute("style", "thin");
                    }

                    //border color
                    if (isset($style['border'][$border_location]["color"])) {
                        $border_color_node = $this->styleDom->createElement("color");
                        $border_color_node->setAttribute("rgb", $style['border'][$border_location]["color"]);
                        $border_location_node->appendChild($border_color_node);
                    }
                    else {
                        $border_color_node = $this->styleDom->createElement("color");
                        $border_color_node->setAttribute("indexed", 64);
                        $border_location_node->appendChild($border_color_node);
                    }

                    $border->appendChild($border_location_node);
                }
                else {
                    $border->appendChild($this->styleDom->createElement($border_location));
                }
            }

            $this->styleDom->getElementsByTagName("borders")->item(0)->appendChild($border);
            $return['borderId'] = $this->styleDom->getElementsByTagName("border")->length - 1;
        }
        return $return;
    }

    /**
     * this is the final function after validating all the style array. this creates an element "style xf"
     *
     * @param $arr  array of validated styles
     */
    private function writeXF($arr) {
        $xf = $this->styleDom->createElement("xf");

        //setting the text alignment
            $has_alignment = false;
            $alignment_node = $this->styleDom->createElement("alignment");
            if (isset($arr['align-x']) && $arr['align-x']) {
                $has_alignment = true;
                $alignment_node->setAttribute("horizontal", $arr['align-x']);
                unset($arr['align-x']);
            }

            if (isset($arr['align-y']) && $arr['align-y']) {
                $has_alignment = true;
                $alignment_node->setAttribute("vertical", $arr['align-y']);
                unset($arr['align-y']);
            }

            if (isset($arr['wrap-text']) && $arr['wrap-text']) {
                $has_alignment = true;
                $alignment_node->setAttribute("wrapText", 1);
                unset($arr['wrap-text']);
            }

            if (isset($arr['shrink-to-fit']) && $arr['shrink-to-fit']) {
                $has_alignment = true;
                $alignment_node->setAttribute("shrinkToFit", 1);
                unset($arr['shrink-to-fit']);
            }

            if (isset($arr['indent']) && $arr['indent']) {
                $has_alignment = true;
                $alignment_node->setAttribute("indent", $arr['indent']);
                unset($arr['indent']);
            }

            if ($has_alignment) {
                $xf->setAttribute("applyAlignment", 1);
                $xf->appendChild($alignment_node);
            }


        //set all the attribute
        foreach ($arr as $k => $v) {
            $xf->setAttribute($k, $v);
        }

        $this->styleDom->getElementsByTagName("cellXfs")->item(0)->appendChild($xf);
    }

    /**
     * Todo's add more attribute check
     * this needs more enhanacements.
     * for now this is used only in checking border attribute
     *
     * @param $style_id     style id
     * @param $attribute    the cell attribute
     * @return bool         true if exist
     */
    public function checkIfStyleAttributeExist($style_id, $attribute) {
        //check borders
        if ($attribute == "border") {
            return $this->checkBorderExistence($style_id);
        }
    }

    /**
     * Also this one needs more improvement
     * Currently using this for borders
     *
     * @param $style_id     style id
     * @param $attribute    attribute to extract
     * @return bool|DOMNode the attribute of the extracted node
     */
    public function extractStyleAttribute($style_id, $attribute) {
        //borders
        if ($attribute == "border") {
            return $this->extractBorderStyle($style_id);
        }
    }

    /**
     * this writes the new dom to the style.xml
     */
    public function writeStyle() {
        file_put_contents($this->styleFile, $this->styleDom->saveXML());
    }

    /**** extract specific attribute from cell *****/
    private function extractBorderStyle($style_id) {
        $xf = $this->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->item($style_id-1);
        if (!$borderId = $xf->getAttribute("borderId")) return false;

        $b = $this->styleDom->getElementsByTagName("border")->item($borderId+1);
        return $b ? $b : false;

    }

    /**
     * style existence checks
     * This only check if the style id has any border style
     *
     * @param $style_id     style id to chect
     * @return bool         return true if it has otherwise return false
     */
    private function checkBorderExistence($style_id) {
        $xf = $this->styleDom->getElementsByTagName("cellXfs")->item(0)->getElementsByTagName("xf")->item($style_id-1);
        if (!$borderId = $xf->getAttribute("borderId")) return false;

        $b = $this->styleDom->getElementsByTagName("border")->item($borderId+1);
        if (!$b) return false;

        foreach($b->childNodes as $child) {
            if ($child->getAttribute("style")) return true;
        }
        return false;
    }

}

?>