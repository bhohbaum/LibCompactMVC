#!/opt/local/bin/php55
<?php
include_once('../../include/libcompactmvc.php');

/**
 * Import and send script
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 19.02.2014
 * @link		http://www.adrodev.de
 */

/**
 * Read parameters
 */
$maxloop = 30;
$sendenabled = false;
$importenabled = false;
$debugenabled = false;
foreach ($argv as $key => $val) {
	if ((isset($argv[$key])) && (is_numeric($argv[$key]))) {
		$maxloop = $argv[$key];
	}
	if ((isset($argv[$key])) && (($argv[$key] == "--send") || ($argv[$key] == "-s") || ($argv[$key] == "-si") || ($argv[$key] == "-is"))) {
		$sendenabled = true;
	}
	if ((isset($argv[$key])) && (($argv[$key] == "--import") || ($argv[$key] == "-i") || ($argv[$key] == "-is") || ($argv[$key] == "-si"))) {
		$importenabled = true;
	}
	if ((isset($argv[$key])) && (($argv[$key] == "--debug") || ($argv[$key] == "-d"))) {
		$debugenabled = true;
	}
}

$db = DbAccess::get_instance("DBA");
$mailings = $db->get_todays_mailings();

echo("\nToday's mailings:\n");
echo(var_export($mailings, true)."\n");

if ($importenabled) {
	foreach ($mailings as $mailing) {
		if ($mailing["data_url"] == "") {
			echo("\n\nMailing '".$mailing["name"]."' with id ".$mailing["id"]." has an empty import URL. No mails will be sent for this mailing.\n");
		} else {
			echo("\n\nImporting from ".$mailing["data_url"]." :\n");
			if (($fh = fopen($mailing["data_url"], "r")) !== false) {
				while (($data = fgetcsv($fh, 1024, "\t")) !== false) {
					if (count($data) == 4) {
						echo(var_export($data, true)."\n");
						$db->create_receiver($data[0], $data[1], $data[2], $data[3]);
						$receiver = $db->get_receiver_by_email($data[0]);
						if ($receiver != null) {
							$db->update_receiver($receiver["id"], $data[0], $data[1], $data[2], $data[3]);
							$db->create_mhr($mailing["id"], $receiver["id"]);
						}
					}
				}
			}
		}
	}
}

if ($sendenabled) {
	echo("\n\nSending ".$maxloop." mails if available.\n");
	$counter = 0;
	$receiver = $db->get_next_receiver();
	if ($receiver == null) {
		echo("No more mails to send at the moment.\n\n");
		exit(0);
	}
	$db->create_tracking_event($receiver["mhr_id"], "mail_sent", null);
	while ($receiver != null) {
		if ($counter++ >= $maxloop) {
			exit(0);
		}
		echo(var_export($receiver, true)."\n");
		if (($fh = fopen(BASE_URL."/app/mail/byident/".$receiver["ident"], "r")) !== false) {
			$contents = '';
			while (!feof($fh)) {
				$contents .= fread($fh, 8192);
			}
			fclose($fh);
			$mail = new HTMLMail(HTMLMail::MAIL_TYPE_HTML);
			$matches = array();
			$baseurl = str_replace("http://", "http:\/\/", BASE_URL);
			preg_match_all('/'.$baseurl.'([^"]+)/', $contents, $matches);
			if ($debugenabled) {
				echo("Found links in mail body:\n");
				echo(var_export($matches[0], true)."\n");
			}
			foreach ($matches[0] as $url) {
				$pos1 = strpos($url, "trackingstats/tp");
				$pos2 = strpos($url, "app/mail/byident");
				if (($pos1 === false) && ($pos2 === false)) {
					if (substr($url, 0, 4) == "http") {
						$mail->add_inline($url);
						$contents = str_replace($url, "cid:".basename($url), $contents);
					}
				}
			}
			if ($debugenabled) {
				echo(var_export($contents, true)."\n");
			}
			$mail->set_transfer_type(HTMLMail::TRANS_TYPE_SMTP);
			$mail->set_smtp_access(SMTP_SERVER, SMTP_USER, SMTP_PASS);
			$mail->set_sender(SMTP_SENDER, SMTP_SENDER_NAME);
			$mail->set_receiver($receiver["email"], $receiver["firstname"]." ".$receiver["lastname"]);
			$mail->set_subject($receiver["subject"]);
			$mail->set_html_body($contents);
			$mail->send();
		}
		$receiver = $db->get_next_receiver();
		if ($receiver == null) {
			echo("No more mails to send at the moment.\n\n");
			exit(0);
		}
		$db->create_tracking_event($receiver["mhr_id"], "mail_sent", null);
	}
}


