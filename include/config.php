<?php 
include_once('libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

// Set global constants

// DB Server:

// DB Server (IP or hostname)
define("MSSQL_HOST", "10.10.2.3");
define("MYSQL_HOST", "localhost");

// default DB
define("MSSQL_DB", "VWA-REPLIKATION");
define("MYSQL_DB", "vwa3_1");

// DB user
define("MSSQL_USER", "sa");
define("MYSQL_USER", "vwa3");

// DB password
define("MSSQL_PASS", "cola!light4167");
define("MYSQL_PASS", "Siz1I4mgsFmBwRkxDSWBXijpBDhFINny");



// Further constants to be set at installation time
define('UPLOAD_BASE_DIR', '/newsletter/upload');		// relative to $_SERVER['DOCUMENT_ROOT']
define('SMTP_SERVER', '127.0.0.1');
define('SMTP_USER', '');
define('SMTP_PASS', '');
// Send method: MAIL (mail() function) or SMTP
define('MAIL_TRANS_TYPE', 'SMTP');
define('MAIL_DEBUG_ADDR', 'b.hohbaum@compactmvc.de');


// valid IPs that may access the page (VALID_IP_1 .. VALID_IP_8)
define('VALID_IP_1', '46.237.202.6');			// 
define('VALID_IP_2', '46.4.83.136');			// Server's IP, required to start mssql -> mysql replication in background
define('VALID_IP_3', '149.172.31.122');
define('VALID_IP_4', '');
define('VALID_IP_5', '');
define('VALID_IP_6', '');
define('VALID_IP_7', '');
define('VALID_IP_8', '');


// Log level
// 0	off
// 1	on
define('DEBUG', 1);


// Internal constants
define("LOG_LVL_ERROR", 0);
define("LOG_LVL_WARNING", 1);
define("LOG_LVL_NOTICE", 2);


// root dir
// /var/www/vwa1/html/newsletter/

?>
