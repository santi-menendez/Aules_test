<?php
include "./locales.php";
include "./class.FastTemplate.php3";
include "./funcions.php";
include "./funcions-extra.php";
include "./config.php";
include "./reserves.php";
include "./forms.php";
include "./listados.php";


// Si no s'ha fet abans, hem de demanar la validacio de l'usuari
//session_name($usr_session_name);
//session_start();
//if (!isset($_SESSION['uid'])):
//	$_SESSION['url_demanada']=$_SERVER['REQUEST_URI'];
//	Header("Location: ../index.htm");
//	die();
//endif;

//// Si no es membre del CCESAII, no ha de poder accedir a la pagina...
//if ($_SESSION['perfil']!='CCESAII') :
//	Header("Location: ../intrafnb.php");
//	die();
//endif;

//print "Nom: ".$_SESSION['nom_usuari']."<br>";
//print "Email: ".$_SESSION['email']."<br>";
//print "Departament: ".$_SESSION['departament']."<br>";
//print "Manager: ".$_SESSION['pac_pas']."<br>";
//print "UID: ".$_SESSION['uid']."<br>";
//print "Perfil: ".$_SESSION['perfil']."<br>";


// Capturem totes les variables que arriben per la linia de comandes
while (list($var, $value)=each($_GET)):
	$$var=$value;
	//echo "$var -> $value<br>";
endwhile;

$tpl = new FastTemplate(".");

// Pintem les opcions del menu
PintarMenu();

// Calculem quin dia es avui i pintem la ocupació de les aules de informàtica
	if(($mes=="")&&($any=="")):
	$date=getdate();
		$any=$date['year'];
		$mes=$date['mon'];
		$dia_semana=$date['wday'];
		$dia=$date['mday'];
	endif;
PintarOcupacioAulaInformatica($mes,$any);


	Printpage("Reservator v.1.1", $res_login);
	/*}else{
		printf("S'esta fent un mal us dels parametres");
	}*/

?>