<?php

header("Content-Type: text/html; charset=utf-8");
include "./locales.php";
include "./class.FastTemplate.php3";
include "./funcions.php";
include "./funcions-extra.php";
include "./config_site.inc";
include "./reserves.php";
include "./forms.php";
include "./listados.php";
include "./forms_vaixell.php";
include "./forms_taller.php";

$config_site = config_site();
$dbuser = $config_site['#dbuser'];
$dbpass = $config_site['#dbpass'];
$dbserver = $config_site['#dbserver'];
$dbname = $config_site['#dbname'];
$template_dir = $config_site['#template_dir'];
$url_reserves = $config_site['#url_reserves'];
$url_intranet = $config_site['#url_intranet'];
$url_intranet_iso = $config_site['#url_intranet_iso'];
$usr_session_name = $config_site['#usr_session_name'];
$ldapServer_open = $config_site['#ldapServer_open'];
$ldapServer_pri = $config_site['#ldapServer_pri'];
$ldapServer_aux = $config_site['#ldapServer_aux'];
$ldapServer_fnb = $config_site['#ldapServer_fnb'];
$usuari_ldap_adm = $config_site['#usuari_ldap_adm'];
$password_ldap_adm = $config_site['#password_ldap_adm'];
$ldapPort = $config_site['#ldapPort'];
$domini = $config_site['#domini'];
$BGCOLOR_HEADER = $config_site['#BGCOLOR_HEADER'];
$BGCOLOR_MENU = $config_site['#BGCOLOR_MENU'];
$BGCOLOR_INFO = $config_site['#BGCOLOR_INFO'];
$BGCOLOR_FOOTER = $config_site['#BGCOLOR_FOOTER'];
$BGCOLOR_SHADOWED = $config_site['#BGCOLOR_SHADOWED'];
$BGCOLOR_YELLOW = $config_site['#BGCOLOR_YELLOW'];
$path_to_template = $config_site['#path_to_template'];
$url_logo = $config_site['#url_logo'];
$height_logo = $config_site['#height_logo'];
$width_logo = $config_site['#width_logo'];
$default_lang = $config_site['#default_lang'];
$nomweb = $config_site['#nomweb'];
$webadmin = $config_site['#webadmin'];
$esaii = $config_site['#esaii'];
$sysadmin = $config_site['#sysadmin'];
$hores_antelacio_x_eliminar_reserva = $config_site['#hores_antelacio_x_eliminar_reserva'];
$hora_maxima = $config_site['#hora_maxima'];
$hora_minima = $config_site['#hora_minima'];
$hora_maxima_vaixell = $config_site['#hora_maxima_vaixell'];
$hora_minima_vaixell = $config_site['#hora_minima_vaixell'];
$hores_antelacio = $config_site['#hores_antelacio'];
$sso = $config_site['#sso'];


// Si no s'ha fet abans, hem de demanar la validacio de l'usuari
//session_name($usr_session_name);
//session_start();
//$_SESSION['url_demanada']=$_SERVER['REQUEST_URI'];

if (!isset($_SESSION['uid']) || !session_id()):

	
	/**
	 *   Example for a simple cas 2.0 client
	 *
	 * PHP Version 5
	 *
	 * @file     example_simple.php
	 * @category Authentication
	 * @package  PhpCAS
	 * @author   Joachim Fritschi <jfritschi@freenet.de>
	 * @author   Adam Franco <afranco@middlebury.edu>
	 * @license  http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
	 * @link     https://wiki.jasig.org/display/CASC/phpCAS
	 */
	
	// Load the settings from the central config file
	require_once '/web/public_html_new/intranet/phpCAS/config.php';
	// Load the CAS lib
	require_once $phpcas_path . '/CAS.php';
	
	// Enable debugging
	phpCAS::setDebug();
	// Enable verbose error messages. Disable in production!
	phpCAS::setVerbose(true);
	
	// Initialize phpCAS
	phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
	
	// For production use set the CA certificate that is the issuer of the cert
	// on the CAS server and uncomment the line below
	// phpCAS::setCasServerCACert($cas_server_ca_cert_path);
	
	// For quick testing you can disable SSL validation of the CAS server.
	// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
	// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
	phpCAS::setNoCasServerValidation();
	
	// force CAS authentication
	phpCAS::forceAuthentication();
	
	// at this step, the user has been authenticated by the CAS server
	// and the user's login name can be read with phpCAS::getUser().
	
	// logout if desired
	if(isset($_REQUEST['logout'])):
	    phpCAS::logout();
	endif;
	// for this test, simply print that the authentication was successfull
	
	// Si arribem aqui es que l'usuari s'ha validat correctament.
	// Ara cal llegir la informacio referent a l'usuari que s'ha validat
	// Ho hem de fer des del compte d'administracio de l'LDAP
	
	// Obrim una nova connexio
	$login = phpCAS::getUser();
	$ds = ldap_connect($ldapServer_open, $ldapPort) or die (_ERR_LDAP_CONNECT_FAILED);
	ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION, 3);
	// Fem la validacio com a usuari administrador
	if ($ds) {
	    // binding to ldap server
	    $ldapbind = ldap_bind($ds,$usuari_ldap_adm,$password_ldap_adm) or die (_ERR_LDAP_ADM_NOT_VALID);
	}
	
	//Consultem els camps que ens calen
	$searchBase = "ou=users,dc=upc,dc=edu";
	$filtre = "(&(cn=".$login.")(|(unit=*FNB*)(unit=*CEN*)(unit=*UTGAN*)(unit=*ESAII*)(unit=*MAT*)(unit=M)(unit=*EEL*)(unit=*EE*)(unit=*OE*)(unit=*EQ*)(unit=*USD*)(unit=*SBPA*)(unit=*BUPC*)(unit=*SGA*)(unit=*ECA*)(unit=*FIS*)(unit=*PA*)(unit=*THATC*)(unit=*SSG*)))"; // Usuaris que es diguin com s'indica i que siguin dels esmentats departaments
	//$filtre = "(&(cn=".$login."))";
	$nomes_em_cal=array("cn","mail","typology","cnb","unit","givenname","sn1","sn","userpassword");
	//$nomes_em_cal = array("*");
	//$nomes_em_cal=array("mail","manager","cn","department","homephone");
	$searchResult = ldap_search($ds, $searchBase, $filtre, $nomes_em_cal);
	$information = ldap_get_entries($ds, $searchResult);
	//print_r($information)."--";echo $login;exit;
	// Per a que tot vagi be hi ha d'haver uun (un i nomes un) sol element.
	// Si es aixi, recollim les dades a la nostra conveniencia
	if ($information['count']==1):
    	$addrEmail=strtolower($information[0]['mail'][0]);
    	$pac_pas=$information[0]['typology'][0];
    	$dept=$information[0]['unit'][0];
    	$uid=$information[0]['cn'][0];
    	$nom_usuari=$information[0]['cnb'][0];
    	//$junta = ConsultarJUNTA($nom_usuari);
    	$md5pass = $information[0]['userpassword'][0];
    	//$permanent = ConsultarCOMISSIO_PERMANENT($nom_usuari);
    	// A mes, consultem a la taula d'usuaris per saber el perfil de l'usuari.
    	// D'entrada, hi haura 3 perfils (Usuari, Administracio i CCESAII)
    	// Consultem el perfil per la seva adreca de correu
    	// Connectem amb el servidor i seleccionem la BD corresponent
    	CheckPerfil($nom_usuari);
    	$perfil = ConsultarPerfil($nom_usuari,$uid);
    	$dni = ConsultarDNI($nom_usuari);
    	//			$perfil = "CCESAII";
	else:
    	// Si no torna resultats (o en torna mes d'un) donem missatge d'error!
    	die(_ERR_LDAP_QUERY_RESULTS);
	endif;
	
	// Hem acabat de fer consultes. Ja podem tancar la connexio
	ldap_close($ds);
	
	// En aquest punt, l'usuari ja esta autentificat i hem consultat les seves dades.
	// Nomes queda desar aquestes dades en variables de sessio per a la posterior consulta
	// Iniciem una sessio nominal (veure "config_site.php")
	session_name("$usr_session_name");
	session_start();
	session_cache_expire(30);
	// Paranoia: Li diem al navegador que no posi la pagina a la cache
	session_cache_limiter('nocache,private');
	
	// Assignem les variables de l'usuari a variables de sessio
	$_SESSION['nom_usuari'] = $nom_usuari;
	$_SESSION['email'] = $addrEmail;
	$_SESSION['addrEmail'] = $addrEmail;
	$_SESSION['departament'] = $dept;
	$_SESSION['pac_pas'] = $pac_pas;
	$_SESSION['uid'] = $uid;
	$_SESSION['perfil'] = $perfil;
	$_SESSION['dni'] = $dni;
	$_SESSION['md5pass'] = $md5pass;	
	$_SESSION['url_demanada']=$_SERVER['REQUEST_URI'];
else:
    phpCAS::logout();
    Header("Location: ./index.php");
	die();
endif;

// Si no es membre del CCESAII, no ha de poder accedir a la pagina...
if($_SESSION["nom_usuari"]=='becari.ga') $_SESSION['perfil'] = 'CCESAII';
//$_SESSION['perfil']=ConsultarPerfil($_SESSION["nom_usuari"]);
if ($_SESSION['perfil']!='CCESAII' && $_SESSION['perfil']!='Usuari PDI' && $_SESSION['perfil']!='Usuari Sales' && $_SESSION['perfil']!='Usuari PDI VAIXELL' && $_SESSION['perfil']!='Usuari NT3') :
die("No teniu privilegis per accedir-hi.<br> Usuari: {".$_SESSION['nom_usuari']."}.<br> Perfil: {".$_SESSION['perfil']."}.<br> <a href=\'".$_SERVER['REQUEST_URI']."'?&op=100\">Sortir</a>");
endif;

// Capturem totes les variables que arriben per la linia de comandes
while (list($var, $value)=each($_GET)):
$$var=$value;
//echo "$var -> $value<br>";
endwhile;

$tpl = new FastTemplate(".");

// Pintem les opcions del menu
$op=$_REQUEST["op"];
$dia=$_REQUEST["dia"];
$mes=$_REQUEST["mes"];
$any=$_REQUEST["any"];
$recurs=$_REQUEST["recurs"];
$tipus=$_REQUEST["tipus"];
$qui=$_REQUEST["qui"];
$motiu=$_REQUEST["motiu"];
$hora_inici=$_REQUEST["hora_inici"];
$hora_final=$_REQUEST["hora_final"];
$patro=$_REQUEST["patro"];
$alum=$_REQUEST["alum"];
$quin=$_REQUEST["quin"];
$maquina=$_REQUEST["maquina"];
$fungible=$_REQUEST["fungible"];
$qquant=$_REQUEST["qquant"];
$assig=$_REQUEST["assig"];
$projector=$_REQUEST["projector"];
$data_inici=$_REQUEST["data_inici"];
$data_final=$_REQUEST["data_final"];
$patro=$_REQUEST["patro"];
$dni_patro=$_REQUEST["dni_patro"];
$titol=$_REQUEST["titol"];
$titulacio_patro=$_REQUEST["titulacio_patro"];
$embarcats=$_REQUEST["embarcats"];
$altres=$_REQUEST["altres"];
$f_tipus_reserva=$_REQUEST["f_tipus_reserva"];
$_1=$_REQUEST["_1"];
$_2=$_REQUEST["_2"];
$_3=$_REQUEST["_3"];
$_4=$_REQUEST["_4"];
$_5=$_REQUEST["_5"];
$_6=$_REQUEST["_6"];
$_7=$_REQUEST["_7"];
$aula=$_REQUEST["aula"];
$id_reserva=$_REQUEST["id_reserva"];

PintarMenu();

switch($op){
    case 11: // Per a un recurs determinat, mostra el llistat de reserves per a un dia concret
        PintarReservesDia($dia,$mes,$any,$recurs);
        break;
    case 12:	//
        oneClassResources($dia,$mes,$any,$tipus);
        break;
    case 13:	//Obre finestra per fer una llista de les aules on fa docencia un professor
        //Buit
        break;
    case 14:	//Obre finestra per fer una llista de les aules on fa docencia un professor
        PintarOcupacioAulaInformatica($mes,$any,$recurs);
        break;
    case 15: // Per a un recurs determinat, mostra el llistat de reserves per a un dia concret
        PintarReservesDia_Vaixell($dia,$mes,$any,$recurs);
        break;
    case 16: // Per a un recurs determinat, mostra el llistat de reserves per a un dia concret
        PintarReservesDia_Taller($dia,$mes,$any,$recurs);
        break;
    case 48: // Li arriben dades (nomes ID) per validar-la
        ValidarReservaID($id);
        break;
    case 49:	// Li arriben les dades d'una reserva i ha de validar-la
        ValidarReserva_Taller($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$alum,$quin,$maquina,$fungible,$qquant);
        break;
    case 50:	// Obre una finestra per a realitzar una reserva puntual
        FerReservaPuntual($dia,$mes,$any,$recurs,$hora_inici);
        break;
    case 51:	// Li arriben les dades d'una reserva i ha de validar-la
        ValidarReserva($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$motius,$projector);
        break;
    case 52:	// Obre una finestra per a realitzar una reserva puntual del Vaixell
        FerReservaPuntual_Vaixell($dia,$mes,$any,$recurs,$hora_inici);
        break;
    case 53:	// Li arriben les dades d'una reserva i ha de validar-la
        ValidarReserva_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres);
        break;
    case 54:	// Obre una finestra per a realitzar una reserva puntual del Vaixell
        FerReservaPuntual_Taller($dia,$mes,$any,$recurs,$hora_inici);
        break;
    case 55:	// Permet crear reserves periodiques
        FerReservaPeriodica($dia,$mes,$any,$recurs,$hora_inici);
        break;
    case 56:	// Reserves periodiques
        if(!$_1 && !$_2 && !$_3 && !$_4 && !$_5 && !$_6 && !$_7):
        //no han marcat cap dia
        error_periodic_reservation();
        else:
        $resultat=0;
        $i=0;
        if(($_1)&&($resultat==0)): // Periodica en dilluns
        $resultat=$resultat + CheckPeriodicReservation($_GET['data_inici'],$data_final,1,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $t[$i]=1;
        $i++;
        endif;
        if(($_2)&&($resultat==0)): // Periodica en dimarts
        $resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,2,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $t[$i]=2;
        $i++;
        endif;
        if(($_3)&&($resultat==0)): // Periodica en dimecres
        $resultat=$resultat + CheckPeriodicReservation($_GET['data_inici'],$_GET['data_final'],3,$recurs,$_GET['qui'],$motiu,$hora_inici,$hora_final,0,$projector);
        $t[$i]=3;
        $i++;
        endif;
        if(($_4)&&($resultat==0)): // Periodica en dijous
        $resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,4,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $t[$i]=4;
        $i++;
        endif;
        if(($_5)&&($resultat==0)): // Periodica en divendres
        $resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,5,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $t[$i]=5;
        $i++;
        endif;
        if(($_6)&&($resultat==0)): // Periodica en dissabte
        $resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,6,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $t[$i]=6;
        $i++;
        endif;
        if(($_7)&&($resultat==0)): // Periodica en diumenge
        $resultat=$resultat + CheckPeriodicReservation($data_inici,$data_final,7,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $t[$i]=7;
        $i++;
        endif;
        
        if($resultat==0):
        // Si no hi ha hagut cap error en les comprovacions, es pot acceptar la reserva periodica
        $i--;
        while($i>=0):
        accept_periodic_reservation($data_inici,$data_final,$t[$i],$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,0,$projector);
        $i--;
        endwhile;
        else:
        //no es poden realitzar
        //accept_periodic_reservation($data_inici,$data_final,$t[$i-1],$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,0,$projector);
        error_periodic_reservation();
        endif;
        endif;
        //accept_periodic_reservation($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
        break;
    case 57:	// Validar Reserves de més de un dia del Vaixell
        extract(CheckDiesSetmana_Vaixell($data_inici,$data_final,$hora_inici,$hora_final));
        if(!$_1 && !$_2 && !$_3 && !$_4 && !$_5 && !$_6 && !$_7):
        //no han marcat cap dia
        error_periodic_reservation();
        else:
        $resultat=0;
        $i=0;
        if(($_1)&&($resultat==0)): // Periodica en dilluns
        $resultat=$resultat + CheckPeriodicReservation_Vaixell($_GET['data_inici'],$data_final,1,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,1);
        $hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,1);
        $t[$i]=1;
        $i++;
        endif;
        if(($_2)&&($resultat==0)): // Periodica en dimarts
        $resultat=$resultat + CheckPeriodicReservation_Vaixell($data_inici,$data_final,2,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,2);
        $hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,2);
        $t[$i]=2;
        $i++;
        endif;
        if(($_3)&&($resultat==0)): // Periodica en dimecres
        $resultat=$resultat + CheckPeriodicReservation_Vaixell($_GET['data_inici'],$_GET['data_final'],3,$recurs,$_GET['qui'],$motiu,$hora_inici,$hora_final,0,$projector);
        $hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,3);
        $hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,3);
        $t[$i]=3;
        $i++;
        endif;
        if(($_4)&&($resultat==0)): // Periodica en dijous
        $resultat=$resultat + CheckPeriodicReservation_Vaixell($data_inici,$data_final,4,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,4);
        $hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,4);
        $t[$i]=4;
        $i++;
        endif;
        if(($_5)&&($resultat==0)): // Periodica en divendres
        $resultat=$resultat + CheckPeriodicReservation_Vaixell($data_inici,$data_final,5,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,5);
        $hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,5);
        $t[$i]=5;
        $i++;
        endif;
        if(($_6)&&($resultat==0)): // Periodica en dissabte
        $resultat=$resultat + CheckPeriodicReservation_Vaixell($data_inici,$data_final,6,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,6);
        $hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,6);
        $t[$i]=6;
        $i++;
        endif;
        if(($_7)&&($resultat==0)): // Periodica en diumenge
        $resultat=$resultat + CheckPeriodicReservation_Vaixell($data_inici,$data_final,7,$recurs,$qui,$motiu,$hora_inici,$hora_final,0,$projector);
        $hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,0);
        $hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,0);
        $t[$i]=7;
        $i++;
        endif;
        
        if($resultat==0):
        // Si no hi ha hagut cap error en les comprovacions, es pot acceptar la reserva periodica
        if ($validar_de_nuevo==1):
        ValidarReserva_Vaixell_2($data_inici,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres);
        endif;
        if($gestio==1 && ($_SESSION['perfil']=='CCESAII' OR $_SESSION['perfil']=='Usuari PDI VAIXELL')):
        $i--;
        $id_periodic = microtime(true);
        while($i>=0):
        accept_periodic_reservation_vaixell($data_inici,$data_final,$t[$i],$recurs,$qui,$motiu,$hora_inici_j[$i],$hora_final_j[$i],$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$i,$hora_final,$altres,$id_periodic);
        $i--;
        endwhile;
        elseif($gestio==2):
        EnviarCorreuDenegacioReserva_Vaixell_2($data_inici,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva,$motius);
        echo "<script>alert(\"Correu enviat.\");</script>";
        exit;
        else:
        GestioReserva_Vaixell_2($data_inici,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva);
        endif;
        else:
        //no es poden realitzar
        //accept_periodic_reservation_vaixell($data_inici,$data_final,$t[$i-1],$recurs,$qui,$motiu,$hora_inici_j[$i],$hora_final_j[$i],$patro,$dni_patro,$titulacio_patro,0,$hora_final,$altres);
        error_periodic_reservation();
        endif;
        endif;
        //accept_periodic_reservation($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
        break;
    case 58:	// Reserves periodiques
        //extract(CheckDiesSetmana_Taller($data_inici,$data_final,$hora_inici,$hora_final));
        if(!$_1 && !$_2 && !$_3 && !$_4 && !$_5 && !$_6 && !$_7):
        //no han marcat cap dia
        error_periodic_reservation();
        else:
        $resultat=0;
        $i=0;
        if(($_1)&&($resultat==0)): // Periodica en dilluns
        $resultat=$resultat + CheckPeriodicReservation_Taller($_GET['data_inici'],$data_final,1,$hora_inici,$hora_final,$recurs,$qui,$motiu,$patro,$alum,$quin,$maquina,$fungible,$qquant);
        //$hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,1);
        //$hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,1);
        $t[$i]=1;
        $i++;
        endif;
        if(($_2)&&($resultat==0)): // Periodica en dimarts
        $resultat=$resultat + CheckPeriodicReservation_Taller($data_inici,$data_final,2,$hora_inici,$hora_final,$recurs,$qui,$motiu,$patro,$alum,$quin,$maquina,$fungible,$qquant);
        //$hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,2);
        //$hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,2);
        $t[$i]=2;
        $i++;
        endif;
        if(($_3)&&($resultat==0)): // Periodica en dimecres
        $resultat=$resultat + CheckPeriodicReservation_Taller($_GET['data_inici'],$_GET['data_final'],3,$hora_inici,$hora_final,$recurs,$qui,$motiu,$patro,$alum,$quin,$maquina,$fungible,$qquant);
        //$hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,3);
        //$hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,3);
        $t[$i]=3;
        $i++;
        endif;
        if(($_4)&&($resultat==0)): // Periodica en dijous
        $resultat=$resultat + CheckPeriodicReservation_Taller($data_inici,$data_final,4,$hora_inici,$hora_final,$recurs,$qui,$motiu,$patro,$alum,$quin,$maquina,$fungible,$qquant);
        //$hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,4);
        //$hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,4);
        $t[$i]=4;
        $i++;
        endif;
        if(($_5)&&($resultat==0)): // Periodica en divendres
        $resultat=$resultat + CheckPeriodicReservation_Taller($data_inici,$data_final,5,$hora_inici,$hora_final,$recurs,$qui,$motiu,$patro,$alum,$quin,$maquina,$fungible,$qquant);
        //$hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,5);
        //$hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,5);
        $t[$i]=5;
        $i++;
        endif;
        if(($_6)&&($resultat==0)): // Periodica en dissabte
        $resultat=$resultat + CheckPeriodicReservation_Taller($data_inici,$data_final,6,$hora_inici,$hora_final,$recurs,$qui,$motiu,$patro,$alum,$quin,$maquina,$fungible,$qquant);
        //$hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,6);
        //$hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,6);
        $t[$i]=6;
        $i++;
        endif;
        if(($_7)&&($resultat==0)): // Periodica en diumenge
        $resultat=$resultat + CheckPeriodicReservation_Taller($data_inici,$data_final,7,$hora_inici,$hora_final,$recurs,$qui,$motiu,$patro,$alum,$quin,$maquina,$fungible,$qquant);
        //$hora_inici_j[$i]=CalculaHoraInici($data_inici,$data_final,$hora_inici,0);
        //$hora_final_j[$i]=CalculaHoraFinal($data_inici,$data_final,$hora_final,0);
        $t[$i]=7;
        $i++;
        endif;
        
        if($resultat==0):
        // Si no hi ha hagut cap error en les comprovacions, es pot acceptar la reserva periodica
        $i--;
        while($i>=0):
        accept_periodic_reservation_taller($data_inici,$data_final,$t[$i],$hora_inici, $hora_final, $recurs, $qui, $motiu, $patro, $alum, $quin, $maquina, $fungible, $qquant);
        $i--;
        endwhile;
        else:
        //no es poden realitzar
        accept_periodic_reservation_taller($data_inici,$data_final,$t[$i-1],$hora_inici, $hora_final, $recurs, $qui, $motiu, $patro, $alum, $quin, $maquina, $fungible, $qquant);
        endif;
        endif;
        //accept_periodic_reservation($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,0);
        break;
    case 59: // Acceptem una petició de reserva directament (des del enllaç que s'envia per email)
        accept_periodic_reservation($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector);
        break;
    case 60: // Acceptem una petició de reserva directament (des de el enllaç que s'envia per email)
        if($gestio==1):
        ValidarReserva($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$motius,$projector);
        elseif($gestio==2):
        EnviarCorreuDenegacioReserva($dia,$mes,$any,$recurs,$qui,$assig,$motiu,$hora_inici,$hora_final,$aula,$projector, $f_tipus_reserva,$motius);
        echo "<script>alert(\"Correu enviat.\");</script>";
        exit;
        GestioReservaPuntual($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector);
        else:
        GestioReservaPuntual($dia,$mes,$any,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector);
        endif;
        break;
    case 61: // Acceptem una petició de reserva directament (des de el enllaç que s'envia per email)
        if($gestio==1):
        accept_periodic_reservation($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$motius,$projector);
        elseif($gestio==2):
        EnviarCorreuDenegacioReservaPeriodica($data_inici,$data_final,$recurs,$qui,$assig,$motiu,$hora_inici,$hora_final,$aula,$projector,$dia,$f_tipus_reserva,$motius);
        echo "<script>alert(\"Correu enviat.\");</script>";
        GestioReservaPeriodica($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector);
        else:
        GestioReservaPeriodica($data_inici,$data_final,$dia,$recurs,$qui,$motiu,$hora_inici,$hora_final,$assig,$uid,$projector);
        endif;
        break;
    case 62: // Acceptem una petició de reserva directament (des de el enllaç que s'envia per email)
        if($gestio==1):
        ValidarReserva_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva);
        elseif($gestio==2):
        EnviarCorreuDenegacioReserva_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva,$motius);
        echo "<script>alert(\"Correu enviat.\");</script>";
        exit;
        GestioReservaPuntual_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva);
        else:
        GestioReservaPuntual_Vaixell($dia,$mes,$any,$data_final,$recurs,$qui,$motiu,$hora_inici,$hora_final,$patro,$dni_patro,$titol,$titulacio_patro,$embarcats,$motiu,$altres,$f_tipus_reserva);
        endif;
        break;
    case 30: // Per al recurs seleccionat, pinta el calendari del mes i any indicat
        PintarMes($mes, $any, $recurs);
        break;
    case 31: // Per al recurs seleccionat, pinta el calendari del mes i any indicat
        PintarMesVaixell($mes, $any, $recurs);
        break;
    case 32: // Per al recurs seleccionat, pinta el calendari del mes i any indicat
        PintarMesTaller($mes, $any, $recurs);
        break;
    case 35: // Per al recurs seleccionat, pinta la setmana indicada
        PintarSetmana($p_dia, $u_dia, $mes, $any, $recurs);
        break;
    case 36: // Per al recurs seleccionat, pinta la setmana indicada
        PintarSetmanaxhores($p_dia, $u_dia, $mes, $any, $recurs);
        break;
    case 36: // Per al recurs seleccionat, pinta la setmana indicada
        //Buit
        break;
    case 73: // Abans de procedir a eliminar una reserva, demanem la confirmacio
        ConfirmarEliminacioReserva($id_reserva, $recurs, $dia, $mes, $any);
        break;
    case 74: // S'ha confirmat que es vol esborrar la reserva i es procedeix
        EliminarReserva($id_reserva, $recurs);
        break;
    case 75: // S'ha confirmat que es vol esborrar la reserva i es procedeix
        EliminarDia_ReservaPeriodica($id_reserva, $recurs, $dia, $mes, $any);
        break;
    case 80: // Mostra la informació de la reserva d'un recurs
        MostrarInfoReserva($id_reserva,$dia,$mes,$any);
        break;
    case 81: // Mostra la informació de la reserva d'un recurs
        MostrarInfoReserva_Vaixell($id_reserva);
        break;
    case 82: // Mostra la informació de la reserva d'un recurs
        MostrarInfoReserva_Taller($id_reserva);
        break;
    case 100:// Sortim del programa
        Logout($sso);
        break;
    case 1:  // Afegir nous recursos al sistema
        MantenimentRecursos();
        break;
    case 2:	// Afegir Responsables de recursos
        MantenimentResponsables();
        break;
    case 3:	// Ens arriben els parametres $nom_usuari i $perfil. Ara donem d'alta l'usuari
        AltaResponsable($nom_usuari, $perfil);
        break;
    case 5:  // Demanar la creacio d'un nou recurs
        DemanarNouRecurs();
        break;
    case 6:  // Envia la peticio de creacio del recurs
        send_resource($nom_recurs, $descripcio, $responsable);
        break;
    case 7:	// Mostra en una finestra les variables "oficials" registrades de la sessio
        PintarVariablesRegistrades();
        break;
    case 40: // Llistem tots els recursos del sistema (agrupats per edifici)
        PintaLlistaRecursos();
        break;
    case 45: // Llistem tots els recursos d'un edifici determiniat
        PintaLlistaRecursosEdifici($edifici);
        break;
    default: // Llistem tots els edificis que tenen recursos
        PintaEdificisAmbRecursos();
        break;
}

Printpage("Reserva Aules", $res_login);

?>