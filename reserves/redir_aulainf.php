<?php
header("Content-Type: text/html; charset=utf-8");
error_reporting(0);
for ($recurs=29;$recurs<=30;$recurs++):
?>
<html>

 <head>

  <meta http-equiv="Refresh" content="5;url=http://www.fnb.upc.edu/intrafnb/aules/reserves/redir_aulainf.php?recurs=<? echo $recurs; ?>">

 </head>

 <body>

 <?php
//
include "./locales.php";
include "./config.php";
include "./class.FastTemplate.php3";
include "./forms.php";
include "./funcions.php";
include "./funcions-extra.php";
include "./reserves.php";
// Capturem totes les variables que arriben per la linia de comandes
while (list($var, $value)=each($_GET)):
	$$var=$value;
	//echo "$var -> $value<br>";
endwhile;

$tpl = new FastTemplate(".");
// Pintem les opcions del menu
PintarMenu_Consulta();
//@mysqli_query($conn,"SET NAMES 'utf8'");
// Per al recurs seleccionat, calculem quin dia es dilluns i pintem la setmana indicada
if ($p_dia==NULL || $u_dia==NULL || $mes==NULL || $any==NULL): extract(Calcula_dilluns_actual()); endif;
if ($recurs<=28):
	PintarSetmana($p_dia, $u_dia, $mes, $any, $recurs);
else:
	include "./listados.php";
	PintarSetmanaAulaInformatica($p_dia, $u_dia, $mes, $any, $recurs);
endif;
Printpage("Llistat Aules", $res_login);
 ?>

 </body>

</html>
<?
endfor;
?>