<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * General error messages.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class ErrorMessages {
	const ERR_NO_CONNECTION = "Verbindung zum SQL-Server nicht m&ouml;glich";
	const ERR_NO_AUTHORIZATION = "Die Authentifizierung ist fehlgeschlagen";
	const ERR_METHOD_NOT_FOUND = "Die Methode wurde nicht gefunden";
	const ERR_UTF8_NOT_SUPPORTED = "UTF8 wird von ihrer Datenbank nicht unterst&uuml;tzt";
	const ERR_ENTRY_NOT_FOUND = "Der Eintrag wurde nicht gefunden";
	const ERR_NO_VALID_DATA = "Die Daten sind ung&uuml;ltig";
	const ERR_NOT_IMPLEMENTED = "Funktion nicht implementiert";
	const ERR_GENERAL_ERROR = "Genereller Fehler";
	const ERR_DB_QUERY_ERROR = "Fehler bei Datenbankabfrage: ";


}
