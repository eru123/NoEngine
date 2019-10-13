<?php

namespace NoEngine;

class BloCrypt {
	//PHP5+
	private static $gpk; 

	private static function set($str){
		self::$gpk = (string) $str;
	}
	private static function get(){
		if (is_null(self::$gpk)) {
			static::set(static::rk());
		}
		return self::$gpk;
	}
	private static function rk(){
		if (function_exists('random_bytes')) {
			return bin2hex(random_bytes(rand(32, 64)));
		} else {
			return sha1(uniqid());
		}
	}
	public static function A($str, $key = NULL){
		$key = $key ?: static::get();
		for ($i = 0; $i < strlen($str); $i++) {
			$str[$i] = $str[$i] ^ $key[$i % strlen($key)];
		}
		return $str;
	}
	public static function E($str, $key = NULL){
		return base64_encode(static::A($str, $key));
	}
	public static function D($str, $key = NULL){
		return static::A(base64_decode($str), $key);
	}
}