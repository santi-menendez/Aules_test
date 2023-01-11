<?
	// Si es crida directament, redireccionem cap a "index.php" i acabem
	/*if(preg_match("/config_site.php/i", $PHP_SELF)):
		Header("Location: ./index.php");
		die();
	endif;*/

	//Variables validacio LDAP
	$ldapServer_open = "ldaps://ldap.upc.edu";
	$ldapServer_pri = "ldaps://mundy.upc.es";			//Servidor LDAP primari
	$ldapServer_aux = "ldaps://intranet.upc.es";		// Servidor LDAP auxiliar
	$ldapServer_fnb = "ldap://nereo.upc.es";			// Servidor LDAP de la FNB
	$usuari_ldap_adm="cn=ldap.upc.alt,o=upc";
	$password_ldap_adm="XsZ8BxVluzBN2_T74UFX";
	$ldapServer_fnb2 = "ldap://nereis2.upc.es";			// Servidor LDAP de la FNB
	$usuari_ldap_adm2="cn=ldap_proxy";
	$password_ldap_adm2="conldapnexio";
	$ldapPort=636;

	$domini = "upc";

	// Variables referents a la Base de dades i a la sessio
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
	$nomweb              = "http://www.fnb.upc.es/intrafnb/reserves/reserves/";
	$webadmin            = "webmaster@fnb.upc.edu";
	$esaii               = "webmaster@fnb.upc.edu";

	$usr_session_name    = "intraFNB";	

?>
