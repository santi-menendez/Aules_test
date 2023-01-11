<?php
include "./config.php";
$data_reserva=$_REQUEST["data_reserva"];
$data_final=$_REQUEST["data_final"];
$recurs=$_REQUEST["recurs"];
$qui=utf8_decode($_REQUEST["qui"]);
$motiu=utf8_decode($_REQUEST["motiu"]);
$hora_inici=$_REQUEST["hora_inici"];
$hora_final=$_REQUEST["hora_final"];
$patro=utf8_decode($_REQUEST["patro"]);
$dni_patro=$_REQUEST["dni_patro"];
$titol=$_REQUEST["titol"];
$titulacio_patro=$_REQUEST["titulacio_patro"];
$embarcats=$_REQUEST["embarcats"];
$motiu=utf8_decode($_REQUEST["motiu"]);
$altres=utf8_decode($_REQUEST["altres"]);
$filename="pdf_bot_de_rescat";
   $var = "<html>
	 <head><title></title>
	 </head>
	 <body>";
		$var .= "<strong><h1>Formulari de reserva del Bot de rescat Rissaga-SOLAS</h1></strong>\n";
		
		$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">&nbsp;</td></tr>\n";
		if ($data_reserva==$data_final):
			$var .= "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">El ".$data_reserva." des de les  ".substr($hora_inici, 0, 5)." hores fins a les ".substr($hora_final, 0, 5)." hores</td></tr>\n";
	    else:  	
			$var .= "<tr><td width=\"38%\"><b>Data de la reserva:</b></td><td width=\"62%\">Des del ".$_REQUEST["data_reserva"]." a les  ".substr($hora_inici, 0, 5)." hores fins al ".$data_final." a les ".substr($hora_final, 0, 5)." hores</td></tr>\n";
		endif;
		$var .= "<tr><td width=\"37%\"><b>Professor responsable de la reserva:</b></td><td>  ".utf8_encode($qui)."</td></tr>\n";
	    $var .= "<tr><td width=\"37%\"><b>Nom i DNI del Patr&oacute;/ona responsable:</b></td><td>  ".utf8_encode($patro."&nbsp;-&nbsp;".$dni_patro)."</td></tr>\n";
	    $var .= "<tr><td width=\"37%\"><b>T&iacute;tol igual o superior a Patr&oacute; de iot:</b></td><td> SI&nbsp;-&nbsp;".utf8_encode($titulacio_patro)."</td></tr>\n";
	    $var .= "<tr><td width=\"37%\"><b>N&deg; total d'embarcats igual o inferior a 12:</b></td><td> SI</td></tr>\n";
		$var .= "<tr><td width=\"37%\"><b>Motiu de la reserva:</b></td><td>  ".utf8_encode($motiu)."</td></tr>\n";
		$var .= "<tr><td width=\"37%\"><b>Assignatura/Activitat:</b></td><td>  ".utf8_encode($altres)."</td></tr>\n";
		$var .= "</table> \n";
		
		$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td width=\"70%\"><b>Relaci&oacute; de persones embarcades:</b></td><td width=\"30%\"></td></tr>\n";
		$var .= "<tr><td width=\"80%\">Nom i Cognoms</td><td width=\"20%\">DNI</td></tr>\n";
			$var .= "<table border=\"1px\"> \n";
			$var .= "<tr><td width=\"70%\" height=\"30px\"> 1.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			$var .= "<tr><td width=\"70%\" height=\"30px\"> 2.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			$var .= "<tr><td width=\"70%\" height=\"30px\"> 3.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			$var .= "<tr><td width=\"70%\" height=\"30px\"> 4.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			$var .= "<tr><td width=\"70%\" height=\"30px\"> 5.-</td><td width=\"30%\">&nbsp;</td></tr>\n";
			$var .= "</table> \n";
		$var .= "</table> \n";
		$var .= "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
		$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td>&nbsp;</td></tr>\n";
		$var .= "<tr><td width=\"50%\"><b>Estat de l'embarcaci&oacute; a la recepci&oacute;:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		$var .= "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		$var .= "</table> \n";
			$var .= "<br> \n";
		$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td width=\"50%\"><b>Ordre i neteja de l'embarcaci&oacute; a la recepci&oacute;:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		$var .= "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		$var .= "</table> \n";
			$var .= "<br> \n";
		$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td width=\"50%\"><b>Estat de l'embarcaci&oacute; al finalitzar l'&uacute;s:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		$var .= "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		$var .= "</table> \n";
			$var .= "<br> \n";
		$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td width=\"50%\"><b>Ordre i neteja de l'embarcaci&oacute; al finalitzar l'&uacute;s:</b></td><td width=\"50%\">( ) Favorable</td></tr>\n";
		$var .= "<tr><td width=\"50%\">&nbsp;</td><td width=\"50%\">( ) Desfavorable. Especificar:</td></tr>\n";
		$var .= "</table> \n";
			$var .= "<br> \n";
			$var .= "<table border=\"0px\"> \n";
			$var .= "<tr><td width=\"100%\" height=\"2%\">&nbsp;</td></tr>\n";
			$var .= "</table> \n";
			
		/*$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td width=\"50%\"><b>Contador d'hores de funcionament del motor:</b></td><td width=\"50%\">Inici:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		$var .= "<tr><td width=\"50%\"><b>&nbsp;</b></td><td width=\"50%\">Final:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;hores</td></tr>\n";
		$var .= "</table> \n";*/
		$var .= "<br> \n";
		$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td><b>Signatura del patr&oacute; i del sol&middot;licitant. Barcelona a&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;de&nbsp;&nbsp;&nbsp;&nbsp;20__</b></td></tr>\n";
		$var .= "</table> \n";

			$var .= "<table border=\"1px\"> \n";
			$var .= "<tr><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Sol&middot;licitant<br><br><br><br><br></td><td width=\"50%\" height=\"10%\" align=\"center\" valign=\"top\">Nom i Cognoms del Patr&oacute;/ona</td></tr>\n";
			$var .= "</table></body></html> \n";
		$var .= "<table border=\"0px\"> \n";
		$var .= "<tr><td width=\"100%\" height=\"7%\"><em>S'ha de lliurar una copia complimentada i signada de l'activitat a l'Administraci&oacute; del Centre.</em></td></tr>\n";
		$var .= "</table> \n";

//============================================================+
// File name   : example_006.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 006 for TCPDF class
//               WriteHTML and RTL support
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
 * @abstract TCPDF - Example: WriteHTML and RTL support
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('/web/public_html_new/intranet/tfg/tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Centre Calcul FNB');
$pdf->SetTitle('TCPDF Vaixell Barcelona');
$pdf->SetSubject('TCPDF Reserva Espais i Recursos');
$pdf->SetKeywords('TCPDF, PDF, curs, especialitat, acta');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO_FNB_UPC, PDF_HEADER_LOGO_WIDTH, NULL, NULL);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/spa.php')) {
    require_once(dirname(__FILE__).'/lang/spa.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', '', 10);

// add a page
$pdf->AddPage();

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
//$pdf->Ln(5);
// create some HTML content

// output the HTML content
$pdf->writeHTML($var, true, false, true, false, '');

//Close and output PDF document
$pdf->Output($filename, 'I');

//============================================================+
// END OF FILE
//============================================================+

?>