<?php

namespace NoEngine;


class Header extends NoEngine {
	public function app(string $m){
		if ($m=='json') {
			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Authorization, X-Requested-With');
		}
	}
}