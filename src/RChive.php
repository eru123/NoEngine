<?php


namespace NoEngine;


class RChive extends NoEngine{

	const KEY = 'NoEngine';

	static function FF(string $f) : string {
		$file = $dir = array();
		if (is_file($f)) {
			$file[] = ['path'=>basename($f),'content'=>BloCrypt::E(file_get_contents($f),self::KEY)];
		} elseif (is_dir($f)) {
			$dir[] = $f;
			$scan = self::_scandir3($f);
			foreach ($scan as $item) {
				if (is_file($item)) {
					$file[] = ['path'=>$item,'content'=>BloCrypt::E(file_get_contents($item),self::KEY)];
				} else $dir[] = $item;
			}
		}
		return BloCrypt::A(json_encode([$dir,$file]),self::KEY);
	}

	static function C(string $f,string $p = NULL) : bool {
		//destination
		$p = $p ?? $f;
		$p = (substr($p, -1) == '/') ? ($p .= $f) : $p;
		$p = rtrim($p,'.').'.ne';

		//compile
		$e = self::FF($f);
		if (self::_fwrite($p,$e,'w'))
			return TRUE;
		return FALSE;
	}

	static function D(string $f,string $d = NULL) : bool  {

		//destination folder
		$d = $d ?? '.';
		$d = ($d == '/') ? $d : rtrim($d,'/').'/';
		
		//compiled file/data
		$file = is_file($f) ? @file_get_contents($f) : $f;
		$file = json_decode(BloCrypt::A($file,self::KEY),true);

		//decompiling folders
		if (isset($file[0]) && is_array($file[0]) && count($file[0]) > 0)
			for ($i=0; $i < 5; $i++) 
				foreach ($file[0] as $dir) 
					self::_mkdir("$d$dir");
			
		//decompiling $ creating files
		if (isset($file[1]) && is_array($file[1]) && count($file[1]) > 0){
			foreach ($file[1] as $item)
				if (is_array($item) && isset($item['path']) && isset($item['content']))
					self::_fwrite("$d$item[path]",BloCrypt::D($item['content'],self::KEY),'w');
				
			return TRUE;
		}
		
		return FALSE;
	}
}