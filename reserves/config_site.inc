<?php
//Definim variables
function config_site(){
	//Variables validacio LDAP
	$ldapServer_open = "ldaps://ldap.upc.edu";
	$ldapServer_pri = "ldaps://mundy.upc.es";			//Servidor LDAP primari
	$ldapServer_aux = "ldaps://intranet.upc.es";		// Servidor LDAP auxiliar
	$ldapServer_fnb = "ldap://nereis2.upc.es";			// Servidor LDAP de la FNB
	$sso = "https://sso.upc.edu/CAS";
	$usuari_ldap_adm="cn=ldap.upc.alt,ou=users,dc=upc,dc=edu";
	$password_ldap_adm="XsZ8BxVluzBN2_T74UFX";
	$usuari_ldap_adm_fnb="cn=ldap_proxy,dc=upc,dc=es";
	$password_ldap_adm_fnb="conldapnexio";
	$ldapPort=636;
	$domini = "upc";
	//Definimos variables
	$dbhost   = "localhost";		// Servidor on esta la BD
	$dbserver = "localhost";
	$dbname_oracle = "pruebas-oracle";
	$dbname_borsa = "web";
	$dbname_drupal7 = "drupal7";
	$dbname   = "aules"; 		// Nom de la BD
	$dbuname  = "fnb";				// Username MySQL
	$dbuser  = "fnb";				// Username MySQL
	$dbpass   = "mosquis";			// Contrasenya
	// Definicio de colors
	$BGCOLOR_HEADER      = "#6699cc";	// Per a les pagines publiques
	$BGCOLOR_MENU        = "#707070";
	$BGCOLOR_INFO        = "#ffffff";
	$BGCOLOR_FOOTER      = "#f0f0f0";
	$BGCOLOR_SHADOWED    = "#f0f0f0";
	$BGCOLOR_YELLOW      = "#fffff0";
	// Configuracio de l'aspecte del lloc
	$path_to_template    = "./templates/high";           // Indica quin template hem d'utilitzar
	$url_logo            = "./pics/logos/esaii.gif"; // Indica quin logo fem anar
	$height_logo         = "44";                     // Indica les mides del logo utilitzat
	$width_logo          = "318";
	$default_lang        = "ca";
	$project_date        = "04/05/2004";
	// Altres aspectes de configuracio
	$nomweb              = "http://www.fnb.upc.es/intrafnb/aules/reserves/";
	$webadmin            = "webmaster@fnb.upc.edu";
	$esaii               = "webmaster@fnb.upc.edu";
	$usr_session_name    = "intraFNB";	
	$template_dir="templates/high";
	$url_reserves="https://www.fnb.upc.edu/intrafnb/aules/reserves/index.php";
	$url_intranet="https://www.fnb.upc.edu/intrafnb/index.php";
	$url_intranet_iso="https://www.fnb.upc.edu/intrafnb/iso/index.php";
	
	$sysadmin="webmaster@fnb.upc.edu";
	$hores_antelacio_x_eliminar_reserva = 24*60*60; //24 hores
	$hora_maxima=22;
	$hora_minima=8;
	$hora_maxima_vaixell=24;
	$hora_minima_vaixell=0;
	$hores_antelacio=6;
	
	

	$config_site = array(
            '#dbserver' => $dbserver,
            '#dbname_oracle' => $dbname_oracle,
			'#dbname_borsa' => $dbname_borsa,
			'#dbname_drupal7' => $dbname_drupal7,
			'#dbname' => $dbname,
			'#dbuname' => $dbuname,
			'#dbuser' => $dbuser,
			'#dbpass' => $dbpass,
			'#template_dir' => $template_dir,
			'#url_reserves' => $url_reserves,
			'#url_intranet' => $url_intranet,
			'#url_intranet_iso' => $url_intranet_iso,
			'#usr_session_name' => $usr_session_name,
			'#ldapServer_open' => $ldapServer_open,
			'#ldapServer_pri' => $ldapServer_pri,
			'#ldapServer_aux' => $ldapServer_aux,
			'#ldapServer_fnb' => $ldapServer_fnb,
			'#usuari_ldap_adm' => $usuari_ldap_adm,
			'#password_ldap_adm' => $password_ldap_adm,
			'#usuari_ldap_adm_fnb' => $usuari_ldap_adm_fnb,
			'#password_ldap_adm_fnb' => $password_ldap_adm_fnb,
			'#ldapPort' => $ldapPort,
			'#domini' => $domini,
			'#BGCOLOR_HEADER' => $BGCOLOR_HEADER,
			'#BGCOLOR_MENU' => $BGCOLOR_MENU,
			'#BGCOLOR_INFO' => $BGCOLOR_INFO,
			'#BGCOLOR_FOOTER' => $BGCOLOR_FOOTER,
			'#BGCOLOR_SHADOWED' => $BGCOLOR_SHADOWED,
			'#BGCOLOR_YELLOW' => $BGCOLOR_YELLOW,
			'#path_to_template' => $path_to_template,
			'#url_logo' => $url_logo,
			'#height_logo' => $height_logo,
			'#width_logo' => $width_logo,
			'#default_lang' => $default_lang,
			'#nomweb' => $nomweb,
			'#webadmin' => $webadmin,
			'#esaii' => $esaii,
			'#sysadmin' => $sysadmin,
			'#hores_antelacio_x_eliminar_reserva' => $hores_antelacio_x_eliminar_reserva,
			'#hora_maxima' => $hora_maxima,
			'#hora_minima' => $hora_minima,
			'#hora_maxima_vaixell' => $hora_maxima_vaixell,
			'#hora_minima_vaixell' => $hora_minima_vaixell,
			'#hores_antelacio' => $hores_antelacio,
	        '#sso' => $sso,
        );
	return $config_site;
}
?>
