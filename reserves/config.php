<?php
// Variables per a l'LDAP
//$ldapServer        = "ldaps://vivaldi.upc.es";
//$usuari_ldap_adm   ="cn=usuari proves120, o=lcx";
//$password_ldap_adm ="99990120";
$ldapServer        = "ldaps://ldap.upc.edu";
$usuari_ldap_adm   = "cn=ldap.upc.alt,ou=users,dc=upc,dc=edu";
$password_ldap_adm = "XsZ8BxVluzBN2_T74UFX";


$sysadmin="webmaster@fnb.upc.edu";
$hores_antelacio_x_eliminar_reserva = 24*60*60; //24 hores
$hora_maxima=22;
$hora_minima=8;
$hora_maxima_mt = array('15','22');
$hora_minima_mt = array('8','15');
$hora_maxima_vaixell=24;
$hora_minima_vaixell=0;
$hores_antelacio=6;
$lang="ca";
$dbname="aules";
$dbuser="fnb";
$dbuname="fnb";
$dbpass="mosquis";
$dbserver="localhost";
$template_dir="templates/high";
$url_reserves="http://www.fnb.upc.edu/intrafnb/aules/reserves/index.php";
$url_intranet="http://www.fnb.upc.edu/intrafnb/index.php";
$url_intranet_iso="http://www.fnb.upc.edu/intrafnb/iso/index.php";
$usr_session_name = "intraFNB";	// Nom de la sessio d'usuari ---> NO CANVIAR!!!


$enviar_correu=1;	// 1 Envia correus de confirmacio si procedeix;   0: Mai no envia correus de confirmacio

// Definicio de les constants de les diverses operacions
//define("", "");
?>
