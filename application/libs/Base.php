<?php
class Base {
	// These chars are allowed in the base 64 encoding
	static $index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';

	/**
	 * 
	 * @param object $data
	 * @param int $base The base the bytes need to be encoded in
	 * @return NULL|string
	 */
	static function encode($data, int $base) {
		if ($base > 64) {
			return NULL;
		}

		$nbytes = strlen($data);

		if ($base == 16) {
			$s = bin2hex($data);
		} elseif ($nbytes <= 6 && $base <= 36) {
			$hex = bin2hex($data);
			// Pad hexadecimal string with 0 if string length is uneven
			$length = $nbytes * 2;
			$hex = str_pad($hex, $length, '0', STR_PAD_LEFT);
			$s = base_convert($hex, 16, $base);
		} else {
			// Convert the raw binary ID to a decimal string
			// This is to work around PHP's limitations with handeling large numbers
			$n = '0';
			$hex = bin2hex($data);
			for ($i = 0; $i < strlen($hex); $i++) {
				$r = base_convert($hex[$i], 16, 10);
				$n = bcadd(bcmul($n, 16), $r);
			}

			// Convert the decimal string to the required base encoding
			if ($base == 10) {
				$s = $n;
			} else {
				$s = '';
				while (bccomp($n, '0', 0) > 0) {
					$r = intval(bcmod($n, $base));
					$s = self::$index[$r] . $s;
					$n = bcdiv($n, $base, 0);
				}
			}
		}

		// Get the length that the encoded string needs to be and left pad with 0's if shorter
		$length = self::length(strlen($data), $base);
		return str_pad($s, $length, '0', STR_PAD_LEFT);
	}

	/**
	 * Decode string to bytes
	 * @param string $s The string to be decoded
	 * @param int $base The base the string is encoded in
	 * @throws OutOfBoundsException
	 * @return NULL|object
	 */
	static function decode(string $s, int $base) {
		if ($base > 64) {
			return NULL;
		}

		$nBytes = floor((strlen($s)*log($base, 2))/8);

		if ($base == 16) {
			$hex = $s;
		} elseif ($nBytes <= 6 && $base <= 36) {
			$hex = base_convert($s, $base, 16);
		} elseif ($base == 2) {
			$hex = '';
			foreach (str_split($s, 8) as $n => $byte) {
				$hex .= sprintf('%02s', base_convert($byte, 2, 16));
			}
		} else {
			if ($base == 10) {
				$n = $s;
			} else {
				// Convert the base encoded string to a decimal string
				// This is to work around PHP's limitations with handeling large numbers
				$n = '0';
				foreach (str_split(strrev($s)) as $i => $c) {
					$p = strpos(self::$index, $c);
					if ($p >= $base) {
						throw new OutOfBoundsException('');
					}

					if ($i == 0) {
						$n = $p . '';
					} else {
						$n = bcadd(bcmul($p, bcpow($base, $i)), $n);
					}
				}
			}

			// Convert the decimal string to hexadecimal
			$hex = '';
			while (bccomp($n, '0', 0) > 0) {
				$r = intval(bcmod($n, 16));
				$hex = self::$index[$r] . $hex;
				$n = bcdiv($n, 16, 0);
			}
		}

		// Pad hexadecimal string with 0 if string length is uneven
		$length = self::length($nBytes, 16);
		$hex = str_pad($hex, $length, '0', STR_PAD_LEFT);
		return hex2bin($hex);
	}

	/**
	 * Calculate the length that the encoded string will be for the given byte length and base
	 * @param int $nBytes
	 * @param int $base The base the bytes need to be encoded in
	 */
	static function length(int $nBytes, int $base) {
		return ceil(($nBytes * 8) / log($base, 2));
	}

	/**
	 * Calculate the length that the binary will be for the given string and base
	 * @param string $s The encoded sting
	 * @param int $base The base the string is encoded in
	 * @return number
	 */
	static function bytes(string $s, int $base) {
		return floor((strlen($s) * log($base, 2))/8);
	}

	/**
	 * Get the chars the binary will be encoded in
	 * @param int $base The base the bytes need to be encoded in
	 * @return string
	 */
	static function chars(int $base) {
		return substr(self::$index, 0, $base);
	}
}