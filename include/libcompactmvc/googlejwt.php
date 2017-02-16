<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Google JSON web token
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
use Emarref\Jwt\Claim;
class GoogleJWT {
	private $token;
	private $encryption;
	private $tz;
	private $jwt;

	public function __construct($issuer, $pk) {
		DLOG();
		$this->tz = date_default_timezone_get();
		date_default_timezone_set('UTC');
		
		$this->token = new Emarref\Jwt\Token();
		
		// Standard claims are supported
		$this->token->addClaim(new Claim\Audience('https://www.googleapis.com/oauth2/v3/token'));
		$this->token->addClaim(new Claim\Expiration(new DateTime('60 minutes')));
		$this->token->addClaim(new Claim\IssuedAt(new DateTime('now')));
		$this->token->addClaim(new Claim\Issuer($issuer));
		
		$this->jwt = new Emarref\Jwt\Jwt();
		$algorithm = new Emarref\Jwt\Algorithm\Rs256('notasecret');
		$this->encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);
		if (file_exists($pk)) {
			$pk = file_get_contents($pk);
		}
		$this->encryption->setPrivateKey($pk);
		
		date_default_timezone_set($this->tz);
	}

	public function add_scope($scope) {
		$this->token->addClaim(new Claim\PublicClaim('scope', $scope));
		return $this;
	}

	public function get_token() {
		date_default_timezone_set('UTC');
		$serializedToken = $this->jwt->serialize($this->token, $this->encryption);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v3/token");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "&access_type=offline&grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=" . $serializedToken . "'");
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($ch, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($ch, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		$this->token = json_decode(curl_exec($ch), true);
		date_default_timezone_set($this->tz);
		return $this->token;
	}

}
