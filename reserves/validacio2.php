<?php
// Incloem els fitxers auxiliars necessaris
//require "./config_site.php";

// Mirem si l'usuari es local o no
$usr=explode("/",$_POST['user']);
$usuari=$usr[0];
$dom=$usr[1];

//if (eregi("local", $dom)) die("Usuari per validar localment");
//else if ($dom) die("Domini d'usuari incorrecte"); else die("Usuari per validar a l'LDAP");

// Si hem indicat algun domini, comprovem si es local (OK) o una altra cosa (KO)
if ($dom!=""):
	if (eregi("local", $dom)) ValidacioContraBDLocal($_POST['user'], md5($_POST['pass']));
	else {
		// Si es crida des d'un domini que no sigui el local, generem un error
		$url=explode("?",$_SERVER['HTTP_REFERER']);
		$referer_page=$url[0];
		Header("Location: ./index.php?error_login=10");
		exit;
	}
else:
	// Validacio contra l'LDAP de la UPC
	// Mirem la pagina que el crida per, si procedeix, retornar-li els errors
	$url=explode("?",$_SERVER['HTTP_REFERER']);
	$referer_page=$url[0];


	// Comprovem si estem cridant directament a l'script
	if ($_SERVER['HTTP_REFERER']==""){
		Header("Location: ./index.php?error_login=2");
		exit;
	}

	// Comprovem si l'usuari s'esta autentificant a traves del formulari
	if (isset($_POST['user']) && isset($_POST['pass'])) {
		// Capturem el nom d'usuari i la contrasenya
		$login = $_POST['user'];
		$password = $_POST['pass'];

		// Fem una connexio amb l'LDAP primari
		$ldapServer=$ldapServer_open;
		$ds = @ldap_connect($ldapServer, $ldapPort) or die ("No puc connectar");
		$login_fdn="cn=ldap.upc.alt,ou=users,dc=upc,dc=edu";
		$login_fdn2="cn=".$login.",ou=users,dc=upc,dc=edu";

		// Si falla la connexio amb el primari, ho intentem amb l'auxiliar
		if (!$ds):
			$ldapServer=$ldapServerAux_open;
			$ds = @ldap_connect($ldapServer, $ldapPort);
		endif;

		// Si s'ha realitzat la connexio, mirem de validar l'usuari amb les
		// dades que arribin del formulari
		if ($ds):
			// Si l'autenticacio no es bona, tornem a demanar validacio
			ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION, 3);
			if (!@ldap_bind($ds, $login_fdn2, $password)):
				require("validacio_fnb2.php");
				Header("Location: ./index.php?login=OK");
			/*else:
				Header("Location: $referer_page?error_login=1");*/
			endif;
		
		// Si no s'ha pogut connectar ho diem i acabem
		else:
			die (_ERR_LDAP_CONNECT_FAILED);
		endif;

		// Acabem tancant la connexio amb l'LDAP
		
		ldap_close($ds);

		// Si arribem aqui es que l'usuari s'ha validat correctament.
		// Ara cal llegir la informacio referent a l'usuari que s'ha validat
		// Ho hem de fer des del compte d'administracio de l'LDAP

		// Obrim una nova connexio
		$ds = ldap_connect($ldapServer, $ldapPort) or die (_ERR_LDAP_CONNECT_FAILED);
		ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION, 3);

		//if (@ldap_bind($ds, $login_fdn2, $password)){
		if (ldap_bind($ds, $login_fdn, $password_ldap_adm)){
		    //$login="benjamin.pleguezuelos";
			//Consultem els camps que ens calen
			$searchBase = "ou=users,dc=upc,dc=edu";
			$filtre = "(&(cn=".$login.")(|(unit=*FNB*)(unit=*CEN*)(unit=*UTGAN*)(unit=*ESAII*)(unit=*MAT*)(unit=*EEL*)(unit=*EE*)(unit=*OE*)(unit=*EQ*)(unit=*USD*)(unit=*SBPA*)(unit=*BUPC*)(unit=*SGA*)(unit=*ECA*)(unit=*FIS*)(unit=*PA*)(unit=*THATC*)(unit=*SSG*)))"; // Usuaris que es diguin com s'indica i que siguin dels esmentats departaments
			//$filtre = "(&(cn=".$login."))";
			$nomes_em_cal=array("cn","mail","typology","cnb","unit","givenname","sn1","sn","userpassword");
			//$nomes_em_cal = array("*");
			//$nomes_em_cal=array("mail","manager","cn","department","homephone");		
			$searchResult = ldap_search($ds, $searchBase, $filtre, $nomes_em_cal);
			$information = ldap_get_entries($ds, $searchResult);
			//print_r($information);exit;
			// Paranoia: Destruim les variables $login i $password
			unset($login);
			unset($password);

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
				$perfil = ConsultarPerfil($nom_usuari);
				$dni = ConsultarDNI($nom_usuari);
	//			$perfil = "CCESAII";
			else:
				// Si no torna resultats (o en torna mes d'un) donem missatge d'error!
				die(_ERR_LDAP_QUERY_RESULTS);
			endif;

			// Hem acabat de fer consultes. Ja podem tancar la connexio
			ldap_close($ds);
		}

			// En aquest punt, l'usuari ja esta autentificat i hem consultat les seves dades.
			// Nomes queda desar aquestes dades en variables de sessio per a la posterior consulta
			// Iniciem una sessio nominal (veure "config_site.php")
			session_name("$usr_session_name");
			session_start();
			session_cache_expire(15);
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
			$_SESSION['junta'] = $junta;
			$_SESSION['permanent'] = $permanent;
			$_SESSION['md5pass'] = $md5pass;

	}
	// Si no esta autentificat via formulari
	else
	{
		// (Re)iniciem una sessio amb el nom definit a "config_site.php"
		session_name("$usr_session_name");
		session_start();

		// Comprovem si ja estan creades les variables de sessio identificativa de l'usuari
		// El cas mes comu es que, un cop "matada" la sessio, s'intenti tornar enrera amb el navegador
		if (!isset($_SESSION['nom_usuari']))
		{
			// Esborrem la sessio creada per l'inici de sessio anterior
			session_destroy();
			Header("Location: ./index.php?error_login=0");
			exit;
		}
	}
	
	// (TMP) Pintem els resultats obtinguts
	//print "Nom: ".$_SESSION['nom_usuari']."<br>";
	//print "Email: ".$_SESSION['email']."<br>";
	//print "Departament: ".$_SESSION['departament']."<br>";
	//print "Manager: ".$_SESSION['pac_pas']."<br>";
	//print "UID: ".$_SESSION['uid']."<br>";
	//print "Perfil: ".$_SESSION['perfil']."<br>";
	//print "Comissio Permanent: ".$permanent."<br>";
	//print "MD5Pass: ".$_SESSION['md5pass']."<br>";
endif;
Header("Location: ./index.php?login=OK");
?>
