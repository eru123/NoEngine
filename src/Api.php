<?php

namespace eru123\NoEngine;

class Api
{

	final public static function listen(string $str, $callback = null): array
	{
		$keys = explode(" ", trim($str));
		$fkey = [];
		foreach ($keys as $key) {
			if (strlen(trim($key)) > 0) {
				if (count(explode(":", $key)) == 2) {
					$lkey = explode(":", $key);
					$fkey[$lkey[0]] = $lkey[1];
				} else {
					$fkey[$key] = "";
				}
			}
		}

		$params = self::match($fkey) ? self::translate($fkey) : FALSE;

		if (gettype($callback) == "object" && $params !== FALSE) {
			try {
				return $callback($params) ?? ($params ?? []);
			} catch (\Exception $e) {
				return $params ?? [];
			} catch (\Error $e) {
				return $params ?? [];
			}
		}

		return array();
	}
	final protected static function match(array $arr): bool
	{
		foreach ($arr as $k => $v) {
			if (strtolower($v) == "--r" || strtolower($v) == "-r" || strtolower($v) == "~r" || strtolower($v) == "!r") {
				if (isset($_REQUEST[$k])) {
					if (gettype($_REQUEST[$k]) == "string" && strlen($_REQUEST[$k]) <= 0)
						return FALSE;
				} else return FALSE;
			} elseif (strlen(trim($v)) > 0) {
				if (isset($_REQUEST[$k])) {
					if ($_REQUEST[$k] != $v)
						return FALSE;
				} else return FALSE;
			}
		}
		return TRUE;
	}
	final protected static function translate(array $arr): array
	{
		$res = [];
		foreach ($arr as $k => $v) {
			if (!empty($_REQUEST[$k]) && isset($_REQUEST[$k])) {
				$res[$k] = $_REQUEST[$k];
			} else {
				$res[$k] = NULL;
			}
		}
		return $res;
	}
	final protected static function json(array $a): void
	{
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
		echo json_encode($a);
	}
	public static function respond(array $res, array $default = []): void
	{
		if (count($res) <= 0)
			$res = $default;

		self::json($res);
	}
}
