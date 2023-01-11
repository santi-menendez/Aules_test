<?php
// Incloem els fitxers auxiliars necessaris
//require "./config_site.php";

// Comprovem si l'usuari s'esta autentificant a traves del formulari
if (isset($_POST['user']) && isset($_POST['pass'])) {
	// Capturem el nom d'usuari i la contrasenya
	$login = $_POST['user'];
	$password = $_POST['pass'];
		
	// Fem una connexio amb l'LDAP primari
	$ldap_server="nereo.upc.es";
	$cn=$login;
	$ds=ldap_connect($ldap_server);
		//Verifiquem que el servidor LDAP respon
		if($ds) {
			//Comprovem que l'usuari existeix i consultem els camps que volem
			if (!@ldap_bind($ds, $login, $password)){
				$solonecesito = array( "givenname", "description", "ou", "mail", "sn" , "fullname");
               	$ldap_bind=ldap_bind($ds,"ldap_proxy_user");
               	$ldap_result = ldap_search($ds,"ou=PAS,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
				$ldap_result2 = ldap_search($ds,"ou=PAC,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
				$ldap_result3 = ldap_search($ds,"ou=CC,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
				$ldap_result4 = ldap_search($ds,"ou=Becaris,ou=Docent,o=UPC-FNB","commonName=$cn",$solonecesito);
               	$entry_dn=ldap_first_entry($ds, $ldap_result);
				$information=ldap_get_entries($ds, $ldap_result);
					if ($entry_dn==false) {
	        	        $entry_dn=ldap_first_entry($ds, $ldap_result2);
						$information=ldap_get_entries($ds, $ldap_result2);
					}
					if ($entry_dn==false) {
	          	      $entry_dn=ldap_first_entry($ds, $ldap_result3);
					  $information=ldap_get_entries($ds, $ldap_result3);
					}
					if ($entry_dn==false) {
	          	      $entry_dn=ldap_first_entry($ds, $ldap_result4);
					  $information=ldap_get_entries($ds, $ldap_result4);
					}
               	if($entry_dn) {
                   	$dn=ldap_get_dn($ds, $entry_dn);
					ldap_unbind($ds);
					//then bind to make sure credentials are correct
                    $ds=ldap_connect($ldap_server);
					//use the @ symbol prefix on the ldap command to suppress errors to the browser
                    $ldap_bind=@ldap_bind($ds, $dn, $password);
                    if(ldap_errno($ds)==49) {
						Header("Location:  $referer_page?error_login=1");
						exit;
                    }
					
					if(ldap_errno($ds)==0) {
						// Paranoia: Destruim les variables $login i $password
						unset($login);
						unset($password);
					
						$addrEmail=strtolower($information[0]['mail'][0]);
						$pac_pas=($information[0]['description'][0]);
						$gn_sn=array($information[0]['givenname'][0],$information[0]['sn'][0]);
						$nom_usuari=implode(" ",$gn_sn);
						$dept=($information[0]['ou'][0]);
						$uid=$information[0]['fullname'][0];
						
						// A mes, consultem a la taula d'usuaris per saber el perfil de l'usuari.
						// D'entrada, hi haura 3 perfils (Usuari, Administracio i CCESAII)
						// Consultem el perfil per la seva adreca de correu
						//$perfil = ConsultarPerfil($addrEmail);
						$perfil = ConsultarPerfil($nom_usuari);
						// Hem acabat de fer consultes. Ja podem tancar la connexio
						ldap_close($ds);
						
						// En aquest punt, l'usuari ja esta autentificat i hem consultat les seves dades.
						// Nomes queda desar aquestes dades en variables de sessio per a la posterior consulta
						// Iniciem una sessio nominal (veure "config_site.php")
						session_name("$usr_session_name");
						session_start();

						// Paranoia: Li diem al navegador que no posi la pagina a la cache
						session_cache_limiter('nocache,private');

						// Assignem les variables de l'usuari a variables de sessio
						$_SESSION['nom_usuari'] = $nom_usuari;
						$_SESSION['email'] = $addrEmail;
						$_SESSION['departament'] = $dept;
						$_SESSION['pac_pas'] = $pac_pas;
						$_SESSION['uid'] = $uid;
						$_SESSION['perfil'] = $perfil;

						// Si haviem demanat d'accedir a una altra pagina diferent de l'index de la
						// intrafnb, redireccionem l'usuari cap aquella pagina en concret.
						if (isset($_SESSION['url_demanada'])):
							Header("Location: ".$_SESSION['url_demanada']);
							exit;
						else:
							// Fem una crida al mateix script per a que quedin disponibles les variables de sessio
							Header("Location: $PHP_SELF");
							exit;
						endif;
					
					//Header("Location: $PHP_SELF");
					}
				}
		}
	}else{	
	die (_ERR_LDAP_CONNECT_FAILED);
	}
}else	{	
die (_ERR_LDAP_INAPPROPRIATE_AUTH);
}
//Comprovem que el usuari existeix al LDAP de la FNB
$ldap_bind=@ldap_bind($ds, $login, $password);
if(ldap_errno($ds)==34) {
Header("Location:  $referer_page?error_login=1");
ldap_close($ds);
exit;
}
?> 