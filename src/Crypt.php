<?php

namespace eru123\NoEngine;

class Crypt {
	public static function crypt(string $str, string $key) : string {
		for ($i = 0; $i < strlen($str); $i++) {
			$str[$i] = $str[$i] ^ $key[$i % strlen($key)];
		}
		return $str;
	}
	public static function encode(string $str, string $key) : string {
		$hash = self::crypt($str,$key);
		return base64_encode($hash);
	}
	public static function decode(string $encoded, string $key) : string {
		$hash = base64_decode($encoded);
		return self::crypt($hash,$key);
	}
}