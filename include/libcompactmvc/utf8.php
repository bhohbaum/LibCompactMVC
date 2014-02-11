<?php 
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * UTF-8 helper class. These methods check the encoding of the input and convert it if required.
 * 
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC Mail Module
 * @license LGPL version 3
 * @link http://www.gnu.org/licenses/lgpl.html
 */
class UTF8 {
	
	/**
	 * Only static functions. No instantiation required.
	 */
	private function __construct() {
		;
	}
	
	/**
	 * Convert to UTF-8
	 * @param String $string input string
	 */
	public static function encode($string) {
		if (!is_string($string)) {
			return false;
		}
		if (self::check_utf8($string)) {
			return $string;
		} else {
			if (strlen(self::checkEncoding($string, "UTF-8")) != strlen($string)) {
				return utf8_encode($string);
			} else {
				return self::checkEncoding($string, "UTF-8");
			}
		}
	}
	
	/**
	 * Convert to ISO-8859-1
	 * @param String $string input string
	 */
	public static function decode($string) {
		if (!is_string($string)) {
			return false;
		}
		return utf8_decode(self::encode($string));
	}
	
	/**
	 * Check if the input is properly UTF-8 encoded.
	 * @param String $str string to be checked
	 */
	private static function check_utf8($str) {
		$len = strlen($str);
		for($i = 0; $i < $len; $i++){
			$c = ord($str[$i]);
			if ($c > 128) {
				if (($c > 247)) return false;
				elseif ($c > 239) $bytes = 4;
				elseif ($c > 223) $bytes = 3;
				elseif ($c > 191) $bytes = 2;
				else return false;
				if (($i + $bytes) > $len) return false;
				while ($bytes > 1) {
					$i++;
					$b = ord($str[$i]);
					if ($b < 128 || $b > 191) return false;
					$bytes--;
				}
			}
		}
		return true;
	} 
	
	/**
	 * Required to convert other formats than ISO-8859-1.
	 * @param String $string intput string
	 * @param String $string_encoding desired encoding
	 */
	private static function checkEncoding( $string, $string_encoding ) {
		$fs = $string_encoding == 'UTF-8' ? 'UTF-32' : $string_encoding;
		$ts = $string_encoding == 'UTF-32' ? 'UTF-8' : $string_encoding;
		return $string === mb_convert_encoding(mb_convert_encoding($string, $fs, $ts), $ts, $fs);
	}
	
	
	
	
	
}


?>