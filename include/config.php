<?php 
include_once('libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

// Set global constants

// DB Server:

// DB Server (IP or hostname)
define("MYSQL_HOST", "localhost");
//define("MYSQL_HOST", "www.impressionstool.de");

// default DB
define("MYSQL_DB", "botho_birkhit");
//define("MYSQL_DB", "jan_birkhit");
//define("MYSQL_DB", "birkhit");

// DB user
define("MYSQL_USER", "botho");
//define("MYSQL_USER", "jan");
//define("MYSQL_USER", "birkhit");

// DB password
define("MYSQL_PASS", "mausi303");
//define("MYSQL_PASS", "adrodevrockt");
//define("MYSQL_PASS", "8F73V940qVRs");



// Further constants to be set at installation time
define('UPLOAD_BASE_DIR', './files/upload');				// relative to $_SERVER['DOCUMENT_ROOT']
define('IMAGES_BASE_DIR', './files/images');				// relative to $_SERVER['DOCUMENT_ROOT']
define('CM_IMAGES_BASE_DIR', './files/cmimgs');				// relative to $_SERVER['DOCUMENT_ROOT']
define('GEN_IMAGES_BASE_DIR', './files/genimgs');			// relative to $_SERVER['DOCUMENT_ROOT']
define('PDFS_BASE_DIR', './files/pdfs');					// relative to $_SERVER['DOCUMENT_ROOT']
define('TEMP_DIR', './files/temp');							// relative to $_SERVER['DOCUMENT_ROOT']
define('DB_EXPORT_FILE', './files/database.sqlite');		// relative to $_SERVER['DOCUMENT_ROOT']
define('LOG_FILE', '/var/www/botho/log/birkhit.log');		// relative to $_SERVER['DOCUMENT_ROOT']

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);

define('CAPTCHA_RES_PATH', "./include/resources");			// relative to $_SERVER['DOCUMENT_ROOT']
define('CAPTCHA_SESS_VAR', "captcha");

define('SMTP_SERVER', '127.0.0.1');
define('SMTP_USER', '');
define('SMTP_PASS', '');
// Send method: MAIL (mail() function) or SMTP
define('MAIL_TRANS_TYPE', 'SMTP');
define('MAIL_DEBUG_ADDR', 'b.hohbaum@compactmvc.de');

// allowed users (login)
define('LOGIN_USERS', '{"adrodev": 	"c0e8bf3bdd9583f9650fefd8ec590b5c", 
						"admin": 	"64ab07516f3e7adb0a79cc5ec9ca529d"}');

// filter IDs in database that require special handling in the code
define('ID_FLT_IMPRESSIONEN', 22);

// filter types have matching entries in the filter_type table.
define('FLT_TYPE_FROM_TO', 		"from-to");
define('FLT_TYPE_IMG_MULTISEL', "img-multiselect");

// Session
define('SESSION_DYNAMIC_ID_DISABLED', true);

// Debug messages
// 0	off
// 1	on
define('DEBUG', 1);


// Internal constants
define("LOG_LVL_ERROR", 0);
define("LOG_LVL_WARNING", 1);
define("LOG_LVL_NOTICE", 2);



?>
