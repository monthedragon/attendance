<?php
/**
 * A subpackage of the ExcelXML. This is a utility class in ExcelXML. This serves as a helper in generating xlsx class.
 *
 * @copyright  Copyright (c) Technopian Corporation (http://www.technopian.com)
 * @author    Leo Escarcha
 * @since    2015-05-25
 */

/**
 * @todo remove some functions when integrated to the sms (like: zip, unzip)
 */

if (!class_exists("PclZip")) {
    require_once('include/pclzip/pclzip.lib.php');
}
/**
 * Class ExcelUtil
 */
class ExcelUtil
{
    /**
     * this is the default cell width when you open an excel template
     */
    const defaultExcelCellWidth = 64; //pixel

    /**
     * this is the default cell height when you open an excel template
     */
    const defaultExcelCellHeight = 20; //pixel

    /**
     * @var string $defaultExcelTemplate    holds the default template path
     */
    public $defaultExcelTemplate;

    public $defaultExcelTemplateFolder;

    public function __construct() {
        $this->defaultExcelTemplate = 'include/Excel/excel/utils/templates/default.xlsx';
        $this->defaultExcelTemplateFolder = 'include/Excel/excel/utils/templates/';
    }

    /**
     * @param $zip_archive  the full path of the new archive
     * @param $zip_dir      the directory where you want to unzip
     */
    function unzip( $zip_archive, $zip_dir ){
        if( !is_dir( $zip_dir ) ){
			mkdir($zip_dir,0777);
            // die( "Specified directory '$zip_dir' for zip file '$zip_archive' extraction does not exist.sss" );
        }

        $archive = new PclZip( $zip_archive );

        if( $archive->extract( PCLZIP_OPT_PATH, $zip_dir ) == 0 ){
            die( "Error: " . $archive->errorInfo(true) );
        }
    }

    /**
     * @param $zip_archive  the archive file path
     * @param $archive_file the file in the archive you want to extract
     * @param $to_dir       the directory where you want the file to be extracted
     */
    function unzip_file( $zip_archive, $archive_file, $to_dir ){
        if( !is_dir( $to_dir ) ){
            die( "Specified directory '$to_dir' for zip file '$zip_archive' extraction does not exist.AAAA" );
        }

        $archive = new PclZip( "$zip_archive" );
        if( $archive->extract(  PCLZIP_OPT_BY_NAME, $archive_file,
                PCLZIP_OPT_PATH,    $to_dir         ) == 0 ){
            die( "Error: " . $archive->errorInfo(true) );
        }
    }

    /**
     * @param $zip_dir          the directory you want to zip
     * @param $zip_archive      the archive full path
     */
    function zip_dir( $zip_dir, $zip_archive ){
        $archive    = new PclZip( "$zip_archive" );
        $v_list     = $archive->create( "$zip_dir" );
        if( $v_list == 0 ){
            die( "Error: " . $archive->errorInfo(true) );
        }
    }

    /**
     * this sends a signal to the browser that you want to download the generated
     *
     * @param string $file      the full path of the xlsx file
     * @param $filename         filename of the file
     */
    private function sendToBrowser($file, $filename,$path) {
		$info = pathinfo($file);
		global $current_language;
		if ($current_language == 'ja') {
			$savename= mb_convert_encoding($filename, 'SJIS-WIN','UTF-8');
		} 
		else {
			$savename = $filename;
		}
		$savename = $savename.".xlsx";

        //clean all levels of buffers
        while (ob_get_level()) {
            ob_end_clean();
        }


        ob_clean();
        ob_start();
        readfile($file);
        header("Pragma: public");
        header("Content-Disposition: attachment; filename=$savename");
        header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
        header("Cache-Control: max-age=0");
        header("Content-Length: ".ob_get_length());
        header('Content-Type: application/xlsx');
        ob_end_flush();

        $this->cleanExcelTempFolder($path,$info['basename']);

        exit;
    }

    /**
     * archive the edited xml files then rename to xlsx
     *
     * @param $path         the path where you want to put zip file
     * @param $filename     the filename you want to set when you download the file
     */
    public function zipToXLSX($path, $filename) {
        $zip_file = $this->zipExcel( $path );
        if (file_exists($zip_file)) {
            $info = pathinfo($zip_file);
            $new = $info['dirname'] . DIRECTORY_SEPARATOR . md5("excel_".microtime(true)).'.xlsx';
            rename($zip_file, $new);
            $this->sendToBrowser($new, $filename,$path);
        }
    }

    /**
     * archive the xml files
     *
     * @param $folder   the full path of all the xml files
     * @return bool     return true if succesfully zipped
     */
    private function zipExcel($folder) {
        $source = $folder;
        $destination = $folder.'.zip';

        @unlink($destination);

        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            $absolute_source_path = realpath($source);

            $new_file = array();
            foreach ($files as $file) {
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..'))) continue;
                $path_to_copy = realpath($file);
                $r = explode($absolute_source_path, $path_to_copy);
                if (isset($r[1]) && !empty($r[1]) && !in_array($r[1], $new_file)) {
                    $new_file[] = $r[1];
                }
            }

            foreach ($new_file as $file_name) {
                if (is_dir($absolute_source_path.$file_name)){
                    $f = explode('\\', $file_name);
                    foreach ($f as $e => $i) {
                        if (empty($i)) unset($f[$e]);
                    }
                    $file_name = implode('/', $f);
                    $file_name = explode(DIRECTORY_SEPARATOR, $file_name);

                    if ($file_name[0] == "") {
                        unset($file_name[0]);
                    }
                    $file_name = implode(DIRECTORY_SEPARATOR, $file_name);
                    $zip->addEmptyDir($file_name);
                }

                if (is_file($absolute_source_path.$file_name)) {
                    $f = explode('\\', $file_name);
                    foreach ($f as $e => $i) {
                        if (empty($i)) unset($f[$e]);
                    }

                    $new_file_name = implode(DIRECTORY_SEPARATOR, $f);
                    $new_file_name = explode(DIRECTORY_SEPARATOR, $new_file_name);

                    if ($new_file_name[0] == "") {
                        unset($new_file_name[0]);
                    }
                    $new_file_name = implode(DIRECTORY_SEPARATOR, $new_file_name);

                    $zip->addFile($absolute_source_path.$file_name, $new_file_name);
                }
            }
        }
        $file = $zip->filename;
        $zip->close();
        return $file;
    }

    /**
     * a basic creation of the domdocument
     *
     * @param $xml_file     path or string of the xml
     * @param $is_file      true = file, false = xml string
     * @return DomDocument  the new domdocument
     * @throws Exception    throw error if the file not exist
     */
    public function createDomObject($xml_file, $is_file){
        $dom = new DomDocument();
        if ($is_file) {
            if (file_exists($xml_file)){
                $dom->load($xml_file);
            }
            else {
                throw new Exception("$xml_file not exist");
            }
        } else {
            $dom->loadXML($xml_file);
        }

        return $dom;
    }

    /**
     * a helper that splits the column and rows
     *
     * @param $cell     the cell id you want to process
     * @return array    array("row" => '' , "col" => '')
     */
    public function splitColumnRowFromCell($cell) {
        $row = preg_replace("/[^0-9]/","",$cell);
        $col = preg_replace('/[0-9]+/',"", $cell);
        return array('col' => $col, 'row' => (int) $row);
    }

    /**
     * this makes a simple numeric representation of all the cells in the excel
     *
     * @param string $pString  column id (Example: "A", "AA", AB)
     * @return mixed  the numeric representation of the column
     * @throws Exception    when more than 3 chars
     */
    public function columnToInt($pString = 'A') {
        if (count(str_split($pString)) > 3) {
            throw new Exception("Oops! this cannot process more than 3 chars");
        }

        static $_indexCache = array();

        if (isset($_indexCache[$pString]))
            return $_indexCache[$pString];

        $_columnLookup = array(
            'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'M' => 13,
            'N' => 14, 'O' => 15, 'P' => 16, 'Q' => 17, 'R' => 18, 'S' => 19, 'T' => 20, 'U' => 21, 'V' => 22, 'W' => 23, 'X' => 24, 'Y' => 25, 'Z' => 26,
            'a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6, 'g' => 7, 'h' => 8, 'i' => 9, 'j' => 10, 'k' => 11, 'l' => 12, 'm' => 13,
            'n' => 14, 'o' => 15, 'p' => 16, 'q' => 17, 'r' => 18, 's' => 19, 't' => 20, 'u' => 21, 'v' => 22, 'w' => 23, 'x' => 24, 'y' => 25, 'z' => 26
        );

        if (isset($pString{0})) {
            if (!isset($pString{1})) {
                $_indexCache[$pString] = $_columnLookup[$pString];
                $return = $_indexCache[$pString];
            } elseif(!isset($pString{2})) {
                $_indexCache[$pString] = $_columnLookup[$pString{0}] * 26 + $_columnLookup[$pString{1}];
                $return = $_indexCache[$pString];
            } elseif(!isset($pString{3})) {
                $_indexCache[$pString] = $_columnLookup[$pString{0}] * 676 + $_columnLookup[$pString{1}] * 26 + $_columnLookup[$pString{2}];
                $return = $_indexCache[$pString];
            }
        }
        return $return;
    }

    /**
     * this converts the integer to a column value.
     *
     * @param int $pColumnIndex     integer to convert
     * @return mixed
     */
    public function intToColumn($pColumnIndex = 0) {
        $pColumnIndex = $pColumnIndex - 1; //I want the start index will start to 1

        static $_indexCache = array();

        if (!isset($_indexCache[$pColumnIndex])) {
            // Determine column string
            if ($pColumnIndex < 26) {
                $_indexCache[$pColumnIndex] = chr(65 + $pColumnIndex);
            } elseif ($pColumnIndex < 702) {
                $_indexCache[$pColumnIndex] = chr(64 + ($pColumnIndex / 26)) .
                    chr(65 + $pColumnIndex % 26);
            } else {
                $_indexCache[$pColumnIndex] = chr(64 + (($pColumnIndex - 26) / 676)) .
                    chr(65 + ((($pColumnIndex - 26) % 676) / 26)) .
                    chr(65 + $pColumnIndex % 26);
            }
        }
        return $_indexCache[$pColumnIndex];
    }

    /**
     * checks if a partcular cell is in the merged cell
     *
     * @param $cell         the cell to be check
     * @param $mergedCell   the range of the merged cell
     * @return bool         return the upper right corner of the merged cell if the $cell is in the merged cell otherwise return false if not
     */
    public function isInMergedCell($cell, $mergedCell){
        $_cell = $cell;
        $cell =  self::splitColumnRowFromCell($cell);
        $cell['col'] = self::columnToInt($cell['col']);

        $range = explode(':', $mergedCell);
        $range[0]           = self::splitColumnRowFromCell($range[0]);
        $range[0]['col']    = self::columnToInt($range[0]['col']);

        $range[1]           = self::splitColumnRowFromCell($range[1]);
        $range[1]['col']    = self::columnToInt($range[1]['col']);

        if( ( $cell['col'] >= $range[0]['col']) && ($cell['col'] <= $range[1]['col']) ) {
            if( ( $cell['row'] >= $range[0]['row']) && ($cell['row'] <= $range[1]['row']) ) {
                $mergedCell = explode(':', $mergedCell);
                return $mergedCell[0];
            }
        }

        return false;
    }

    /**
     * this returns all the side of the merged cell
     *
     * @param $range    string A9:B12
     * @return array    array of all the sides
     */
    public function getAllSidesInMergedCell($range) {
        $_range = explode(':', $range);

        $from = $_range[0];
        $to  = $_range[1];

        $from = self::splitColumnRowFromCell($from);
        $from['col'] = self::columnToInt($from['col']);

        $to = self::splitColumnRowFromCell($to);
        $to['col'] = self::columnToInt($to['col']);

        $sides = array();
        //top
        $top = array();
        $p = $from['col'];
        while($p <= $to['col']){
            $top[] = self::intToColumn($p).$from['row'];
            $p++;
        }
        $sides["top"] = $top;

        //bottom
        $t = array();
        $p = $to['col'];
        while($p >= $from['col']){
            $t[] = self::intToColumn($p).$to['row'];
            $p--;
        }
        krsort($t);
        $sides["bottom"] = array_values($t);

        //left
        $t = array();
        $p = $from['row'];
        $_from_col = self::intToColumn($from['col']);
        while($p <= $to['row']){
            $t[] = $_from_col.$p;
            $p++;
        }
        $sides["left"] = $t;

        //right
        $t = array();
        $p = $to['row'];
        $_to_col = self::intToColumn($to['col']);
        while($p >= $from['row']){
            $t[] = $_to_col.$p;
            $p--;
        }
        krsort($t);
        $sides["right"] = array_values($t);

        return $sides;
    }

    /**
     * check if the path already exist
     * @param $path the path to check
     */
    public function createIfNotExistFolder($path) {
        if (!file_exists($path)) {
            mkdir($path);
        }
    }

    /**
     * this retrieve all the files in a particular folder by using
     * the file extension
     *
     * @param $path         folder path
     * @param string $ext   extension to search
     * @return array        array of files
     */
    public function getFilesInDirByExt($path, $ext = "xml") {
        $return = array();
        foreach (new DirectoryIterator($path) as $file) {
            $filename = $file->getFilename();
            $info = pathinfo($path.'/'.$filename);
            if ($info['extension'] == $ext) {
                $return[] = $filename;
            }
        }

        return $return;
    }

    /**
     * this generates all the cells in a range.
     * can be a range of columns or rows
     *
     * @param $cell     From what cell the search will begin
     * @param $num      The range of cells
     * @param string $what  Is it a column or a row
     * @return array    array of cells
     */
    public function getAllCellFrom($cell, $num, $what = "col") {
        $c = self::splitColumnRowFromCell($cell);

        $range = array();

        if ($what == 'col') {
            $c['col'] = self::columnToInt($c['col']);
            for($i = $c['col']; $i <= $c['col']+$num ; $i++ ) {
                $range[] = self::intToColumn($i).$c['row'];
            }
        }
        else {
            for($i = $c['row']; $i <= $c['row']+$num ; $i++ ) {
                $range[] = $c['col'].$i;
            }
        }

        return $range;
    }

    /**
     * This function cleans only the cache temp folder files before creating
     * a new xlsx file. This results to, every download of th excel file
     * the old xlsx files will be deleted first. Only the last excel file
     * generated will be seen on the cache temp folder
     *
     * @param $dir  the directory of the cache temp for excel ( get_cache_temp_path()."excel"; )
     */
    public function cleanExcelTempFolder($dir,$excel_name) {
        $dirs = array();
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir."/"), RecursiveIteratorIterator::CHILD_FIRST);

        //deletes the files inside the folder of the excel output
        foreach ($files as $file) {
            if( in_array($file->getFilename(), array('.', '..'))) continue;
            $doc_path = $file->getRealPath();
            if (is_dir($doc_path)) {
                $dirs[] =  $doc_path;
            }
            if (is_file($doc_path)) {
                @unlink($doc_path);
            }
        }

        if ($dirs) {
            foreach($dirs as $d) {
                @rmdir($d);
            }
        }

        //deletes the generated excel file
        @unlink($this->excelFolder."/".$excel_name);

        //deletes the folder
        @rmdir($dir);

    }

    /**
     * this is for a temporary storage of all the cell's sizes that came from getCellSize() function.
     * this is to optimize the getCellSize() function.
     *
     * @var     Temporary storage
     */
    private $tempCellSizesStorage;


    /**
     * this function return's the current cell width and height from the DOM
     *
     * @param $cell
     * @param $excelObject
     * @return array
     */
    public function getCellSize($cell, $excelObject) {
        $activeSheet = $excelObject->worksheet->getCurrentWorkingSheet();
        $cellSizes = $this->tempCellSizesStorage[$activeSheet] ? $this->tempCellSizesStorage[$activeSheet] : array();

        $_cell = $excelObject->util->splitColumnRowFromCell($cell);
        $column = $excelObject->util->columnToInt($_cell['col']);
        $row = $_cell['row'];

        if (isset($cellSizes['column'][$column]) && isset($cellSizes['row'][$row])) {
            return array("width" => $cellSizes['column'][$column], "height" => $cellSizes['row'][$row]);
        }

        $width = 0;
        $height = 0;

        //get height and width
        //col
        if (!isset($cellSizes['column'][$column])) {
            $cols = $excelObject->worksheet->getCurrentWorkSheetDom()->getElementsByTagName('col');
            foreach($cols as $col) {
                $min = $col->getAttribute("min");
                if ($min == $column) {
                    $width = $col->getAttribute("width");
                }
            }
        }

        //row
        if (!isset($cellSizes['row'][$row])) {
            $rows = $excelObject->worksheet->getCurrentWorkSheetDom()->getElementsByTagName('row');
            foreach($rows as $_row) {
                $r = $_row->getAttribute("r");
                if ($r == $row) {
                    $height = $_row->getAttribute("ht");
                }
            }
        }

        $cellSizes['column'][$column] = ($width == 0) ? self::defaultExcelCellWidth : round($width * 7.591933570581257); //the width is multiplied by 7.591933570581257 so that we can get the exact width in pixel
        $cellSizes['row'][$row] = ($height == 0) ? self::defaultExcelCellHeight : round($height * 1.3333333333334); //also the height is multiplied to 1.3333333333334 for pixel convertion. Width and Height has a different calculation in Excel

        $this->tempCellSizesStorage[$activeSheet] = $cellSizes;

        return array("width" => $cellSizes['column'][$column], "height" => $cellSizes['row'][$row]);
    }

    /**
     * Since we dont have the exact formula how the pixel size of excel is being computed we
     * will just use this formula. This formula makes the closest width calculation so far.
     * A matter of -+1 pixel difference.
     *
     * Alternate Solution:
     * For the exact width size you can use Excel::getColumnWidth function then set it to the
     * desired column.
     *
     * Note:
     * self::ExcelUtil::defaultExcelCellWidth (64) using this formula ExcelUtil::defaultExcelCellWidth is being computed exactly
     * so need to worry if we will use the default excel column width
     *
     * @param $width
     * @return float
     */
    function convertToColumnWidth($width) {
        //it uses a pattern of 7 series numbers.
        $patterns = array(  0.140625,   0.285156, 0.425781,
                            0.570312,  0.710937,  0.855468, 1
                        );

        //Formula
        if ($width <= 7) {
            return $patterns[$width - 1];
        }
        else if ($width % 7 == 0) {
            return $width / 7;
        }
        else {
            $w = floor($width / 7);
            return ($patterns[($width % 7) - 1] + $w);
        }
    }

    /**
     * converts int to Excel pixel size
     *
     * @param $height
     * @return string
     */
    public function convertToRowHeight($height) {
        $h = $height * 0.75;
        return substr($h, 0, 5);
    }

    /**
     * converts the excel pixel size to int
     *
     * @param $height
     * @return float
     */
    public function convertRowHeightToInt($height) {
        return $height / 0.75;
    }

    /**
     * we need to follow certain elements order in xlsx xml.
     * this will determine the location of mergeCells element where to insert it.
     * We cannot just insert the mergeCells element in the xml.
     *
     * in the function $heirarchy_of_elements is the container of the order of elements.
     * $heirarchy_of_elements[0] will be the first priority. if $heirarchy_of_elements[0] exists insert the mergeCells
     * before that and so on.
     *
     * TO DO's add new elements if you have found new one that affects the ordering of the mergeCell.
     * But be sure that you test that first.
     *
     * @param bool $XmlDom
     * @return mixed
     */
    public function mergeCellElementLocation($XmlDom = false) {
        //this is the order of elements on where the mergecell is to be inserted
        //incase that you found new element insert the element in the right order.
        //please test the scenarios first before you finalize the order of the new element.
        $heirarchy_of_elements = array(
            "phoneticPr",
            "printOptions",
            "pageMargins"
        );

        if (!$XmlDom) return $heirarchy_of_elements[count($heirarchy_of_elements) - 1]; //just return the last element

        $xpath = new DOMXPath($XmlDom);
        $xpath->registerNamespace("atom", "http://schemas.openxmlformats.org/spreadsheetml/2006/main");
        foreach($heirarchy_of_elements as $element) {
            $cellExists = $xpath->query('//atom:worksheet/atom:'.$element);
            if ($cellExists->length) return $element;
        }

    }
}

?>