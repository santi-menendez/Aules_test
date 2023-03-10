<?php

// Literals generals
define("E_MES1", "Gener");
define("E_MES2", "Febrer");
define("E_MES3", "Mar&ccedil;");
define("E_MES4", "Abril");
define("E_MES5", "Maig");
define("E_MES6", "Juny");
define("E_MES7", "Juliol");
define("E_MES8", "Agost");
define("E_MES9", "Setembre");
define("E_MES10", "Octubre");
define("E_MES11", "Novembre");
define("E_MES12", "Desembre");
define("E_DILLUNS", "Dilluns");
define("E_DIMARTS", "Dimarts");
define("E_DIMECRES", "Dimecres");
define("E_DIJOUS", "Dijous");
define("E_DIVENDRES", "Divendres");
define("E_DISSABTE", "Dissabte");
define("E_DIUMENGE", "Diumenge");
define("E_DIA0", "No");
define("E_DIA1", "Dilluns");
define("E_DIA2", "Dimarts");
define("E_DIA3", "Dimecres");
define("E_DIA4", "Dijous");
define("E_DIA5", "Divendres");
define("E_DIA6", "Dissabte");
define("E_DIA7", "Diumenge");
define("E_M_DILLUNS", "Dl");
define("E_M_DIMARTS", "Dt");
define("E_M_DIMECRES", "Dc");
define("E_M_DIJOUS", "Dj");
define("E_M_DIVENDRES", "Dv");
define("E_M_DISSABTE", "Ds");
define("E_M_DIUMENGE", "Dg");


define("E_ADD", "Afegir");
define("E_DELETE", "Eliminar");
define("E_HORA", "Hora");
define("E_DATA", "Data");
define("E_DIA_SETMANA", "Dia de la setmana");
define("E_QUI", "Reservat per");
define("E_PETICIO", "Petici&oacute; de reserva de");
define("E_LLOC","Lloc");
define("E_ASSIG","Assignatura");
define("E_PROJECTOR", "Projector");
define("E_EMBARCATS", "N&deg; total d'embarcats<br>
inferior o igual a 12?");
define("E_EMBARCATS_MININA", "N&deg; total d'embarcats<br>
inferior o igual a 5?");
define("E_PATRO", "Patr&oacute; (nom i cognoms)");
define("E_DNI_PATRO", "DNI Patr&oacute;");
define("E_TITULACIO_PATRO", "Titulaci&oacute; Patr&oacute;");
define("E_RELACIO_EMBARCATS", "Relaci&oacute; de persones embarcades");
define("E_NOM_EMBARCATS", "Nom i Cognoms");
define("E_DNI_EMBARCATS", "DNI");
define("E_TITOL", "T&iacute;tol del patr&oacute;<br>igual o superior a<br>Patr&oacute; de Iot?");
define("E_ALTRES","Altres:");
define("E_ACTIVITAT","Assignatura/Activitat:");
define("E_PLA","Pla d'estudis");
//define("E_ACTIVITAT", "L'activitat pertany a l'?mbit");
define("E_FUNCIONS", "Funcions");
define("E_OCUPAT", "Ocupat");
define("E_ERR_BD_CONNECT", "Error en connectar a la base de dades");
define("E_ERR_SELECT_DB", "Error en seleccionar la base de dades");
define("E_RECURS", "Recurs");
define("E_CHECKLIST", "Checklist per la reserva del ");
define("E_DESCRIPCIO", "Descripci&oacute;");
define("E_RESPONSABLE", "Responsable");
define("E_RESP_ACTI", "Responsable activitat");
define("E_ALUM", "N&deg; alumnes/assistents");
define("E_LOCALITZACIO", "Localitzaci&oacute;");
define("E_QUIN", "Quin/a");
define("E_MAQ", "Especificar quina<br>maquinaria o eines a<br>utilitzar seran necessaries");
define("E_FUNG", "Consum de<br>material fungible?");
define("E_QQUANT", "Quin i quantitat<br>de material fungible<br>a utilitzar");
define("E_COMENTARI", "Comentari");
define("E_PROF", "Professorat");
define("E_LST_RECURSOS_PER_EDIFICIS", "Llistat de recursos de la Facultat, classificats per edifici");
define("E_EDIFICIS_AMB_RECURSOS", "Llistat d'edificis departamentals en els que s'han definit recursos compartits");
define("E_RECURSOS_EDIFICI", "Llistat de recursos comuns definits en l'edifici");
define("E_MSG_RESERVA", "Esta realitzant una reserva pel");
//define("E_MSG_RESERVA_VAIXELL", "&Eacute;s imprescindible omplir el formulari de reserva del vaixell BARCELONA i lliurar-lo degudament complimentat i signat a l'administraci&oacute; del Centre");
define("E_MSG_RESERVA_VAIXELL", "Enviada notificaci&oacute; de reserva del vaixell BARCELONA per correu electr&ograve;nic amb la informaci&oacute; de la reserva i documentaci&oacute; a complimentar i signar per l'usuari i lliurar a l'Administraci&oacute; del Centre");
define("E_MSG_RESERVA_PERIODIC", "Esta realitzant una reserva peri&ograve;dica");
define("E_MSG_DEL_RECURS", "del recurs");
define("E_HORA_INICI", "Hora inici");
define("E_HORA_FINAL", "Hora final");
define("E_DATA_INICI", "Data inici periode");
define("E_DATA_FINAL", "Data final periode");
define("E_MOTIU", "Motiu");
define("E_ERR_DATA_NO_VAL", "Les dades indicades per a la reserva no s&oacute;n v&agrave;lides.<br><br>Probablement esteu intentant fer una reserva en un dia i/o hora que ja han passat.");
define("E_ERR_DATA_48_VAL", "Les dades indicades per a la reserva no s&oacute;n v&agrave;lides.<br><br>La reserva del vaixell s'ha de fer amb 24 hores d'antelaci&oacute;.");
define("E_ERR_DATA_6H_VAL", "Les dades indicades per a la reserva no s&oacute;n v&agrave;lides.<br><br>La petici? de reserva de les aules s'ha de fer amb 6 hores d'antelaci&oacute;.");
define("E_ERR_HORA_NO_VAL", "Les hores assignades no cumpleixen els requisits.<br> hora minima $hora_minima<=inicial $hora_inici< final $hora_final<=hora maxima $hora_maxima");
define("E_ERR_HORA_VAIXELL_NO_VAL", "Les hores assignades no cumpleixen els requisits.<br> hora minima $hora_minima_vaixell<=inicial $hora_inici< final $hora_final<=hora maxima $hora_maxima_vaixell");
define("E_ERR_HI_HA_RESERVA", "Hi ha alguna reserva que es solapa amb la reserva demanada.");
define("E_ERR_HI_HA_AULA_SENSE_PROJECTOR", "La reserva de l'aula es pot realitzar, per&ograve; hi ha alguna reserva de projectors que es solapa amb la reserva demanada.");
define("E_ERR_TOTS_OCUPATS", "La reserva de l'aula es pot realitzar, per&ograve; no es pot fer la reserva del projectors perqu? tots els projectors estan ocupats a la franja horaria seleccionada.");
define("E_ERR_INSERT_DB", "S'ha produit un error en intentar inserir la reserva a la BD.<p>Torneu-ho a intentar i, si el problema persisteix, si us plau, contacteu amb <a href='mailto:$sysadmin'>$sysadmin</a>");
define("E_ERR_DELETE_DB", "S'ha produit un error en intentar eliminar la reserva seleccionada de la BD.<p>Torneu-ho a intentar i, si el problema persisteix, si us plau, contacteu amb el Centre de Calcul FNB");
define("E_ERR_MARCA_UN_DIA", "Per tal de formalitzar una reserva peri&ograve;dica, cal marcar el(s) dia(es) de la setmana en que ha de ser efectiva aquesta reserva.");
define("E_ERR_CONDICIONS_NO_ACCEPTADES", "No has acceptat les condicions de la reserva.");
define("E_MSG_RESERVA_RSLT", "La reserva s'ha realitzat satisfactoriament");
define("E_MSG_NO_RESERVA_RSLT", "No s'ha pogut realitzar la reserva");
define("E_MSG_PETICIO_RESERVA_RSLT", "S'ha enviat la petici&oacute; de reserva a la Secretaria de Direcci&oacute; del Centre. El m&eacute;s aviat possible s'enviar&agrave; la confirmaci&oacute; de la reserva per correu electr&ograve;nic.");
define("E_MSG_PETICIO_RESERVA_RSLT_AULES", "S'ha enviat la petici&oacute; de reserva a la Unitat de Gesti&oacute; Acad&egrave;mica del Centre. El m&eacute;s aviat possible s'enviar&agrave; la confirmaci&oacute; de la reserva per correu electr&ograve;nic.");
define("E_MSG_PETICIO_RESERVA_RSLT_INF", "S'ha enviat la petici&oacute; de reserva al Centre de C&agrave;lcul del Centre. El m?s aviat possible s'enviar&agrave; la confirmaci&oacute; de la reserva per correu electr&ograve;nic.");
define("E_MSG_PETICIO_RESERVA_RSLT_SIMU", "S'ha enviat la petici&oacute; de reserva del laboratori amb simulador (GMDSS, Navegaci&oacute; o M&agrave;quines). El m&eacute;s aviat possible s'enviar&agrave; la confirmaci&oacute; de la reserva per correu electr&ograve;nic.");
define("E_MSG_PETICIO_RESERVA_RSLT_VAIXELL", "S'ha enviat la petici&oacute; de reserva a la Unitat d'Administraci&oacute; del Centre. El m&eacute;s aviat possible s'enviar&agrave; la confirmaci&oacute; de la reserva per correu electr&ograve;nic. Dintre d'aquest correu electr&ograve;nic de confirmaci&oacute; trobareu l'enlla&ccedil; per imprimir el document PDF que haureu d'omplir i lliurar a l'administraci&oacute; del Centre.");
define("E_MSG_DEL_RESERVA_RSLT", "La reserva s'ha eliminat satisfactoriament");
define("E_MSG_EXISTEIX_PROJECTOR", "A les Aula11, Aula 12, Aula 23 i Aula 24 ja existeix un projector fixe. No cal fer la reserva del projector.");
define("E_MSG_NO_DEL_RESERVA_RSLT", "La reserva no s'ha pogut eliminar pel seg&uuml;ent motiu:");
define("E_MSG_DATES", "El format de la data ha de ser dd/mm/aaaa");
define("E_ERR_DATA_INICI_NO_VAL", "La data d'inici &eacute;s m&eacute;s petita que la data actual");
define("E_CREAR_RESERVA", "Crear reserva");
define("E_PERIODICA", "Peri&ograve;dica");
define("E_PRINCIPAL", "Principal");
define("E_TRIA_RECURS", "Triar recurs");
define("E_CONT_RECURS", "Controlar recurs");
define("E_ERR_DELETE_NO_ADMIN", "No pots eliminar reserva d'aquest recurs");
define("E_ERR_MOTIU_NO_VAL", "Cal indicar el motiu de la reserva");
define("E_ERR_QUI_NO_VAL", "Cal indicar qui fa la reserva");
define("E_ERR_ASSIG_NO_VAL", "Cal indicar una descripci? de l'assignatura reglada o curs extern de la reserva");
define("E_RESPONSABLE_RECURS", "Responsable del recurs");
define("E_MSG_INF_RESERVA", "Informaci&oacute; de la reserva");
define("E_MSG_GEST_RESERVA", "Gesti&oacute; de la petici&oacute; de reserva del");
define("E_ERR_ARA_NO_VAL", "Esteu intentant fer una reserva en un interval de temps que ja ha passat");
define("E_ERR_DELETE_RESERVA_PERIODICA", "La data de inici de la reserva peri&oacute;dica &eacute;s m&eacute;s antiga que la data d'avui");
define("E_ERR_DELETE_RESERVA_ANTELACIO", "La eliminaci&oacute; de reserves s'ha de fer amb una antelaci&oacute; de 24 hores laborables a la data de la reserva");
define("E_ERR_DELETE_DIA_RESERVA_PERIODICA", "La data i hora de inici del dia de la reserva peri&oacute;dica a eliminar &eacute;s m&eacute;s antiga que la data i hora d'avui");
define("E_SETMANA_DEL", "Setmana del ");
define("E_MSG_ANONYMOUS", "Accedir com a usuari gen&egrave;ric");
define("E_RESERVA", "Reserva");
define("E_LLISTAT", "Llistat");
define("E_PROFESSOR", "Professor");
define("ERR_NO_HTTP_REFER", "Acc&eacute;s Denegat. Proveu d'accedir-hi desde el Collaboratori");
define("E_SUG_RESERVA", "Si despres de fer una reserva aquesta no apareix, proveu apretant F5 (Refrescar la pantalla)");
define("E_NEW_PASSWORD", "Nova contrasenya");
define("E_PASSWORD_AGAIN", "Escriviu la contrasenya un altre cop");
define("E_ERR_PASSWORD_NOT_SAME", "Error. Les contrasenyes no coincideixen");
define("E_ERR_LOGIN", "Error. Login Incorrecte");
define("E_PASSWORD_CHG_SUCC", "S'ha canviat la contrasenya");
define("E_CHG_PASSWORD", "Canviar contrasenya");
define("E_CHANGE", "Canviar");
define("E_LOGOUT", "Sortir");
define("E_LOGIN", "Iniciar Sessi&oacute;");
define("E_PASSWORD", "Contrasenya");
define("E_USERNAME", "Usuari");
define("E_NOM_USUARI", "Username (UPC)");
define("E_EMAIL", "Email");
define("E_DATA_CADUCITAT", "Data de caducitat");
define("E_PERFIL", "Perfil de l'usuari");
define("E_MSG_RESPONSABLE_RSLT", "S'ha afegit el responsable correctament");
define("E_MSG_NO_RESPONSABLE_RSLT", "Han aparegut errors a l'afegir el responsable");
define("E_MSG_CONF_ELIMINAR_RESERVA", "Heu demanat per eliminar una reserva.<br>En el cas de que es tracti d'una reserva peri&ograve;dica l'eliminar&agrave; de tot el seu periode.");
define("E_MSG_CONF_ELIMINAR_DIA_RESERVA", "Heu demanat per eliminar un dia de una reserva peri&ograve;dica.<br>");
define("E_MSG_CONF_ELIMINAR_RESERVA2", "Esteu segur que voleu eliminar-la?");
define("E_MSG_CONF_ELIMINAR_DIA_RESERVA2", "Esteu segur que voleu eliminar nom&eacute;s aquest dia?");
define("E_YES", "Si");
define("E_NO", "No");
define("E_NOM_RECURS", "Nom del Recurs");
define("E_TANCAR", "Tancar");
define("TXT_DEMANAR_RECURS", "<p>Aquest formulari permet enviar a l'administrador del sistema una petici&oacute; de creaci&oacute; d'un nou recurs.<br><br>Per fer-ho, nom&eacute;s cal especificar les caracter&iacute;stiques del recurs (Nom, descripci&oacute; b&agrave;sica i responsable) i apretar el boto de de peticio de recurs.</p>");
define("E_DEMANAR_RECURS", "Demanar recurs");
define("E_MSG_DEMANAR_RECURS", "Caracter&iacute;stiques del recurs: ...\nUbicaci&oacute; del recurs: ...\n");
define("E_MSG_ADD_RESOURCE_RSLT", "La petici&oacute; de recurs ha estat enviada al responsable satisfact&ograve;riament");
define("E_MSG_NO_ADD_RESOURCE_RSLT", "No s'ha pogut enviar la petici&oacute; de nou recurs pel seg&uuml;ent motiu:");
define("E_MSG_ADD_RESOURCE_NOM", "No s'ha indicat el nom del recurs");
define("E_MSG_ADD_RESOURCE_RESPONSABLE", "No s'ha indicat el nom del responsable del recurs");
define("E_MSG_ADD_RESOURCE_DESCRIPCIO", "No s'han indicat les caracter&iacute;stiques del recurs que es demana");
define("E_MSG_ADD_RESOURCE_PACIENCIA", "Un cop s'hagi processat l'alta del recurs rebreu la corresponent notificaci&oacute;");

define("E_AVIS_LEGAL", "return confirm('En demanar la reserva, garantitzes que hi haur&agrave; la pres&egrave;ncia d\'un responsable docent durant el seu &uacute;s. Est&agrave;s d\'acord?');");
define("E_AVIS_LEGAL_INFO", "En confirmar la reserva, garantitzes que hi haur? la pres?ncia d'un responsable docent durant el seu ?s.");
?>