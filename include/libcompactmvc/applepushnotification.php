<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Class for sending Apple push notifications
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Media Impression Unit 08
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ApplePushNotification {
	private $certpath;
	private $device_token;

	public function __construct($certpath) {
		DLOG();
		$this->certpath = $certpath;
	}

	public function alert($device_token, $message, $badge = 1, $sound = "default") {
		DLOG();
		// Payload erstellen und JSON codieren
		$payload['aps'] = array(
				'alert' => $message,
				'badge' => $badge,
				'sound' => $sound
		);
		$this->send($payload, $device_token);
	}

	private function send($payload, $device_token) {
		DLOG();
		$payload = json_encode($payload);
		
		$apnsHost = 'gateway.sandbox.push.apple.com';
		$apnsPort = 2195;
		
		// Stream erstellen
		$streamContext = stream_context_create();
		stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->certpath);
		
		$apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		if ($apns) {
			// Nachricht erstellen und senden
			$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $device_token)) . chr(0) . chr(strlen($payload)) . $payload;
			fwrite($apns, $apnsMessage);
			
			// Verbindung schliessen
			fclose($apns);
		} else {
			throw new Exception($errorString, $error);
		}
	}

}
