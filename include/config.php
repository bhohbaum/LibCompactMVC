<?php
include_once('libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Application config
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 11.02.2014
 * @link		http://www.adrodev.de
 */

// Set global constants
// Debug messages & logging
// 0	off
// 1	on
define('DEBUG', 1);
define('LOG_LEVEL', Log::LOG_LVL_DEBUG);


// DB Server:

// DB Server (IP or hostname)
define("MYSQL_HOST", "localhost");

// default DB
define("MYSQL_DB", "libcompactmvc");

// DB user
define("MYSQL_USER", "botho");

// DB password
define("MYSQL_PASS", "Mausi_303");

define("DBA_DEFAULT_CLASS", "DBA");

// populate controllers with POST/GET variables?
define('REGISTER_HTTP_VARS', true);

// Session
define('SESSION_DYNAMIC_ID_DISABLED', true);
define('SESSION_TIMEOUT', 1200);

// Further constants to be set at installation time
define('UPLOAD_BASE_DIR', './files/upload');				// relative to $_SERVER['DOCUMENT_ROOT']
define('IMAGES_BASE_DIR', './files/images');				// relative to $_SERVER['DOCUMENT_ROOT']
define('TEMP_DIR', './files/temp');							// relative to $_SERVER['DOCUMENT_ROOT']
define('CSV_BASE_DIR', './files/csv');						// relative to $_SERVER['DOCUMENT_ROOT']
define('LOG_FILE', '/var/log/php/cmvc.log');				// relative to $_SERVER['DOCUMENT_ROOT']
define('BASE_URL', 'http://libcompactmvc.local');
define('DEFAULT_TIMEZONE', 'CET');

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('CACHING_ENABLED', DEBUG != 1 || true);

define('REDIS_KEY_PRAEFIX', 'CMVC_');
define('REDIS_KEY_RCACHE_PFX', REDIS_KEY_PRAEFIX . 'RENDERCACHE_');
define('REDIS_KEY_RCACHE_TTL', '600');
define('REDIS_KEY_TBLDESC_PFX', REDIS_KEY_PRAEFIX . 'TBLDESC_');
define('REDIS_KEY_FKINFO_PFX', REDIS_KEY_PRAEFIX . 'FKINFO_');

define('CAPTCHA_RES_PATH', "./include/resources");			// relative to $_SERVER['DOCUMENT_ROOT']
define('CAPTCHA_SESS_VAR', "captcha");

define('SMTP_SERVER', '127.0.0.1');
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_SENDER', 'test@libcompactmvc.local');
define('SMTP_SENDER_NAME', 'LibCompactMVC');
// Send method: MAIL (mail() function) or SMTP
define('MAIL_TRANS_TYPE', 'SMTP');
define('MAIL_DEBUG_ADDR', 'b.hohbaum@googlemail.com');

// allowed users (login)
// to get the hash on a terminal, type:
// php -r 'echo(md5("your-password-goes-here")."\n");'
define('LOGIN_USERS', '{"adrodev": 	"c0e8bf3bdd9583f9650fefd8ec590b5c",
						"admin": 	"64ab07516f3e7adb0a79cc5ec9ca529d"}');

// special DB content
define('MPT_TEXT_ONLY', 		"text_only");
define('MPT_TEXT_WITH_IMAGE', 	"text_with_image");
define('MPT_TYPE_IMAGE_ONLY', 	"image_only");

define('TRACKING_MAIL_SENT', 	"mail_sent");
define('TRACKING_MAIL_OPENED', 	"mail_opened");
define('TRACKING_LINK_CLICKED',	"link_clicked");

define('TBL_EVENT_TYPES', 'event_types');
define('TBL_IMAGES', 'images');
define('TBL_MAILINGS', 'mailings');
define('TBL_MAILINGS_HAS_RECEIVERS', 'mailings_has_receivers');
define('TBL_MAILPARTS', 'mailparts');
define('TBL_MAILPART_TYPES', 'mailpart_types');
define('TBL_RECEIVERS', 'receivers');
define('TBL_TEXTS', 'texts');
define('TBL_TRACKING_EVENTS', 'tracking_events');

define('TBLV_NEXT_RECEIVER', 'next_receiver');
define('TBLV_SEND_LIST', 'send_list');
define('TBLV_TODAYS_MAILINGS', 'todays_mailings');
define('TBLV_TRACKING_COMBINED', 'tracking_combined');

define('TBLP_TRACKING_OVERVIEW', 'tracking_overview');

?>
