<?php
//============================================================+
// File name   : example_062.php
// Begin       : 2010-08-25
// Last Update : 2013-05-14
//
// Description : Example 062 for TCPDF class
//               XObject Template
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: XObject Template
 * @author Nicola Asuni
 * @since 2010-08-25
 */

// Include the main TCPDF library (search for installation path).
tcpdf();

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('AUTHOR');
#$pdf->SetTitle('INVITATION LETTER');
$pdf->SetSubject('SUBJECT');
$pdf->SetKeywords('INVITATION');

// set default header data 
$pdf->setPrintHeader(false);
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH);
$pdf->setPrintFooter(false);
//$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', 'B', 25);

// add a page
$pdf->AddPage();

//set as header
$pdf->Image(dirname(__FILE__) .'/../../../assets/images/logo.PNG', 170, 3, 30, 30, '', '', '', true, 72, '', false, false, 0, false, false, false);

$subject = array('Mr.'=>'He','Ms.'=>'She'); 
$possessive = array('Mr.'=>'His','Ms.'=>'Her'); 

$customerName = $contact_firstname . ' ' . $contact_middlename . ' ' .$contact_lastname;
$screening_date = date('M d,Y',strtotime($screening_date));

$html  = " 	<p>
			<b>{$contact_salutation} {$customerName}</b>
			<br><br>
			
			{$contact_position}<br>
			{$contact_address}
			
			<br><br>
			Dear {$contact_salutation} {$contact_nick_name} ,
			
			<br><br>
			On behalf of the sourcing team of First GPRS, we are pleased to endorse to you {$salutation} {$firstname} {$middlename} {$lastname} (“{$nick_name}”) for your {$projects[$project]} requirement. {$nick_name} is a product of the {$college}. {$subject[$salutation]} can speak fluent English as ". strtolower($possessive[$salutation])."  primary language and conversational {$sec_language} as a minor. 
			
			<br><br>
			We trust that given ".strtolower($possessive[$salutation])."  background and relevant experience, {$nick_name} would be an asset to {$client_name}. We have scheduled ".strtolower($possessive[$salutation])." screening with you on {$screening_date}. Kindly extend ".strtolower($possessive[$salutation])."  the assistance that  ".strtolower($subject[$salutation])." will need. 
			Please contact me if I can provide any furt".strtolower($possessive[$salutation])."  details. I can be reached at {$user_mobile}.
			
			<br><br>
			Sincerely, 
			
			<br><br>
			SGD.<br>
			__________________________<br>
			{$user_name}<br>
			First  GPRS Inc. <br>
			<br>

			</p>

			";

$footer = " <hr style='background-color:gray;'> 
			F I R S T   G R E A T   P A C I F I C    R I M   S E R V I C E S,    I N C.
			301 Guildford Bldg.  The Manors at Celebrity Place, Capitol Hills, Quezon City 1119 
		";
			
// set font
$pdf->SetFont('helvetica', '', 11);
// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
 
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTMLCell(0, 0, '', 260, $footer, 0, 1, 0, true, 'C', true);
 

// draw jpeg image to be clipped
#$pdf->Image(dirname(__FILE__) .'/../../../assets/images/stop.png', 80, 200, 60, 50, '', '', '', true, 72, '', false, false, 0, false, false, false);
  

//Close and output PDF document		
$filename = dirname(__FILE__) .'/../../../uploads/pdf/'.$customerName.'.pdf';		
$pdf->Output($filename, 'F');
//$pdf->Output($filename, 'I');

#$pdf->Output('example_0621.pdf', 'I');
#$attch = $pdf->Output('example.pdf','E');
#$pdf->Output("/var/www/html/cms/uploads/example_062.pdf");

//============================================================+
// END OF FILE
//============================================================+


//send EMAIL
if(SEND_EMAIL)
	echo email($contact_email,array($filename));
?>
