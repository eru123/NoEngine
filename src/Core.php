<?php

namespace NoEngine;

class Core {
	public function time_diff(int $od): int {

		return time() - $od;
	}
	public function get_date_alt(): int {

		return date("YmdHis");
	}
	public function get_date(string $timezone = "Asia/Manila"): string{
		date_default_timezone_set($timezone);
		return (string) date(DATE_ATOM);
	}
	public function get_display_date(string $date = ""): string{
		$date = trim(str_replace("  ", " ", $date));
		if ($date == "") {
			$date = self::get_date();
		}
		// Correct format
		$res = date_format(date_create($date), "F d, Y");
		return $res;
	}
	public function gravatar(string $email, int $size = 100, string $default = ""): string{
		$email = trim($email); // "MyEmailAddress@example.com"
		$email = strtolower($email); // "myemailaddress@example.com"
		$hash = md5($email);
		$d = "";
		if ($default != "") {
			$d = "&d=" . urlencode($default);
		}

		return "https://www.gravatar.com/avatar/$hash?s=$size$d";
	}
	public function valid_alias(string $name): bool {
		if (!preg_match("/[^a-zA-Z0-9._-]/", $name)) {
			return TRUE;
		}
		return FALSE;
	}
	public function valid_name(string $name): bool {
		if (!preg_match("/[^a-zA-Z. -]/", $name)) {
			return TRUE;
		}
		return FALSE;
	}
	public function valid_mobile(string $phone) : bool {
		
		$formatted = str_replace(" ","",$phone);
		$formatted = str_replace("-","",$formatted);
		$formatted = str_replace("+","",$formatted);

		return (!preg_match("/[^0-9]/", $formatted) && strlen($formatted) >= 10 && strlen($formatted) <= 15) ?? FALSE;
		

		return FALSE;
	}
	public static function se(string $str, $l = 1) {
		for ($i = 0; $i < $l; $i++) {
			$str = base64_encode($str);
		}

		return $str;
	}
	public static function sd(string $str, $l = 1) {
		for ($i = 0; $i < $l; $i++) {
			$str = base64_decode($str);
		}

		return $str;
	}
	public static function random(string $pass, $k = 'debcoco') {
		$s = self::se(md5($pass), 3);
		$s = md5($k . $s . $k);
		$s = self::se($s, 1);
		return md5($s);
	}
	public static function get_ip() {
		// check for shared internet/ISP IP
		if (!empty($_SERVER['HTTP_CLIENT_IP']) && valsidate_ip($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		}

		// check for IPs passing through proxies
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// check if multiple ips exist in var
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
				$iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($iplist as $ip) {
					if (self::validate_ip($ip)) {
						return $ip;
					}
				}
			} else {
				if (self::validate_ip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					return $_SERVER['HTTP_X_FORWARDED_FOR'];
				}

			}
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED']) && self::validate_ip($_SERVER['HTTP_X_FORWARDED'])) {
			return $_SERVER['HTTP_X_FORWARDED'];
		}

		if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
			return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		}

		if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::validate_ip($_SERVER['HTTP_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_FORWARDED_FOR'];
		}

		if (!empty($_SERVER['HTTP_FORWARDED']) && self::validate_ip($_SERVER['HTTP_FORWARDED'])) {
			return $_SERVER['HTTP_FORWARDED'];
		}

		// return unreliable ip since all else failed
		return $_SERVER['REMOTE_ADDR'];
	}
	public static function validate_ip($ip) {
		if (strtolower($ip) === 'unknown') {
			return false;
		}

		// generate ipv4 network address
		$ip = ip2long($ip);

		// if the ip is set and not equivalent to 255.255.255.255
		if ($ip !== false && $ip !== -1) {
			// make sure to get unsigned long representation of ip
			// due to discrepancies between 32 and 64 bit OSes and
			// signed numbers (ints default to signed in PHP)
			$ip = sprintf('%u', $ip);
			// do private network range checking
			if ($ip >= 0 && $ip <= 50331647) {
				return false;
			}

			if ($ip >= 167772160 && $ip <= 184549375) {
				return false;
			}

			if ($ip >= 2130706432 && $ip <= 2147483647) {
				return false;
			}

			if ($ip >= 2851995648 && $ip <= 2852061183) {
				return false;
			}

			if ($ip >= 2886729728 && $ip <= 2887778303) {
				return false;
			}

			if ($ip >= 3221225984 && $ip <= 3221226239) {
				return false;
			}

			if ($ip >= 3232235520 && $ip <= 3232301055) {
				return false;
			}

			if ($ip >= 4294967040) {
				return false;
			}

		}
		return true;
	}
	public static function get_request($f) {

		return ($_REQUEST[$f] ?? false);
	}
	public static function validName($name) {
		if (preg_match("/[^a-zA-Z'. -]/", $name) || strlen($name) < 2 || strlen($name) > 36) {
			return false;
		}
		return true;
	}
	public static function validAlias($alias) {
		if (preg_match("/[^a-zA-Z0-9._-]/", $alias) || strlen($alias) < 3 || strlen($alias) > 36) {
			return false;
		}
		return true;
	}
	public static function array_pagination(array $array, int $offset, int $items, bool $invert = false) {
		$res = [];

		if ($invert === true) {
			rsort($array);
		}

		$childs_count = count($array);

		if ($childs_count > 0) {
			$min_items = 1;
			$max_items = $childs_count;

			$items = ($items < $min_items) ? $min_items : (($items > $max_items) ? $max_items : $items);

			$min_offset = 1;
			$max_offset = ceil($childs_count / $items);

			$offset = ($offset < $min_offset) ? $min_offset : (($offset > $max_offset) ? $max_offset : $offset);

			$ending_index = ($items * $offset);
			$starting_index = ($items * $offset) - $items;

			for ($i = $starting_index; $i < $ending_index; $i++) {
				if (isset($array[$i])) {
					$res[] = $array[$i];
				}
			}
		}

		return $res;
	}
}
