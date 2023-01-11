<?php
header("Content-Type: text/html; charset=utf-8");
include "./locales.php";
include "./class.FastTemplate.php3";
include "./forms.php";
include "./funcions.php";
include "./funcions-extra.php";
include "./config.php";
include "./reserves.php";
include "./listados.php";

// Capturem totes les variables que arriben per la linia de comandes
while (list($var, $value)=each($_GET)):
	$$var=$value;
	//echo "$var -> $value<br>";
endwhile;

$tpl = new FastTemplate(".");
// Pintem les opcions del menu
PintarMenu_Consulta();
// Per al recurs seleccionat, calculem quin dia es dilluns i pintem la setmana indicada
if ($p_dia==NULL || $u_dia==NULL || $mes==NULL || $any==NULL): extract(Calcula_dilluns_actual()); endif;
PintarSetmanaAulaInformatica($p_dia, $u_dia, $mes, $any, $recurs);
Printpage("Llistat Aules", $res_login);
?>