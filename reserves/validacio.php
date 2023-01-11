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
		Header("Location: ./index.php?error_login=0");
		exit;
	}

	// Comprovem si l'usuari s'esta autentificant a traves del formulari
	if (isset($_POST['user']) && isset($_POST['pass'])) {
		// Capturem el nom d'usuari i la contrasenya
		$login = $_POST['user'];
		$password = $_POST['pass'];

		// Fem una connexio amb l'LDAP primari
		$ldapServer=$ldapServer_fnb2;
		$ds = @ldap_connect($ldapServer, $ldapPort) or die ("No puc connectar");
		$login_fdn="cn=ldap_proxy,dc=upc,dc=es";
		$login_fdn2="cn=".$login.",ou=becaris,ou=fnb,dc=upc,dc=es";

		// Si falla la connexio amb el primari, ho intentem amb l'auxiliar
		if (!$ds):
			$ldapServer=$ldapServerAux_open;
			$ds = @ldap_connect($ldapServer, $ldapPort);
		else:
		endif;

		// Si s'ha realitzat la connexio, mirem de validar l'usuari amb les
		// dades que arribin del formulari
		if ($ds):
			// Si l'autenticacio no es bona, tornem a demanar validacio
			//ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION, 3);
			if (!@ldap_bind($ds, $login_fdn2, $password)):
				echo $usuari.$login_fdn2.$password;exit;
				//require("validacio_fnb.php");
				Header("Location:  $referer_page?error_login=1");
			else:
				//echo $usuari.$login_fdn2;exit;
				Header("Location:  $referer_page?error_login=1");
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

		if (@ldap_bind($ds, $login_fdn2, $password)){
			
			//Consultem els camps que ens calen
			$searchBase = "ou=fnb,dc=upc,dc=es";
			$filtre = "(&(cn=".$login.")(|(o=*FNB*)(o=*CEN*)(o=*ESAII*)(o=*MAT*)(o=*EEL*)(o=*EE*)(o=*OE*)(o=*EQ*)(o=*ITT*)(o=*SBPA*)(o=*BUPC*)(o=*THATC*)(o=*ECA*)(o=*FIS*)(o=*UTGAEIB*)(o=*UTGAN*)(o=*SSG*)))"; // Usuaris que es diguin com s'indica i que siguin dels esmentats departaments
			$nomes_em_cal=array("Email","departmentNumber","displayName","o","cn","sn1","sn");
			//$nomes_em_cal = "";
			//$nomes_em_cal=array("mail","manager","cn","department","homephone");		
			$searchResult = ldap_search($ds, $searchBase, $filtre, $nomes_em_cal);
			$information = ldap_get_entries($ds, $searchResult);
			// Paranoia: Destruim les variables $login i $password
			unset($login);
			unset($password);

			// Per a que tot vagi be hi ha d'haver uun (un i nomes un) sol element.
			// Si es aixi, recollim les dades a la nostra conveniencia
			if ($information['count']==1):
				$addrEmail=strtolower($information[0]['Email'][0]);
				$pac_pas=$information[0]['departmentNumber'][0];
				$dept=$dept[0];
				$uid=$information[0]['cn'][0];
				$nom_usuari=$information[0]['displayName'][0];
				$junta = ConsultarJUNTA($nom_usuari);
				$permanent = ConsultarCOMISSIO_PERMANENT($nom_usuari);
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
			
			echo "<script> alert(".$uid.");</script>";

			// Si haviem demanat d'accedir a una altra pagina diferent de l'index de la
			// intrafnb, redireccionem l'usuari cap aquella pagina en concret.
			if (isset($_SESSION['url_demanada'])):
				Header("Location: ".$_SESSION['url_demanada']);
				exit;
			else:
				// Fem una crida al mateix script per a que quedin disponibles les variables de sessio
				//Header("Location: $PHP_SELF");
				//exit;
			endif;
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
	//print "Nom: ".$_SESSION['sn']."<br>";
	//print "Email: ".$_SESSION['mail']."<br>";
	//print "Departament: ".$_SESSION['unit']."<br>";
	//print "Manager: ".$_SESSION['pac_pas']."<br>";
	//print "UID: ".$_SESSION['sn']."<br>";
	//print "Perfil: ".$_SESSION['perfil']."<br>";
endif;
?>
