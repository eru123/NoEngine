<?php

namespace Inc;


class Inc extends NoEngine {
	final static function php(string $f,$d = null){

		$d = $d ?? $_SERVER['DOCUMENT_ROOT'];
		$p = "$_SERVER[DOCUMENT_ROOT]/NE_PHPIndex.json";
		//check if php index file is exists
		if (!file_exists($p)) {
			//search all php file
			$i = self::index('php',$d);
			//create php index file
			self::_fwrite($p,json_encode($i),'w');
		}
		//ready index file
		$i = @file_get_contents($p);
		$i = json_decode($i, true);
		//process request file
		$f = self::xpl_ra_map(',','trim',$f);
		//magic happens here
		$res = array();
		if (is_array($i) && count($i)>0) {
			//get path
			foreach ($i as $v) {
				foreach ($f as $file) {
					$res[$file]['path'] = array();
					$res[$file]['count'] = 0;
					if ($v['name'] == $file) {
						$res[$file]['count']++;
						$path = $v['path'];
						$res[$file]['path'][$path] = 0;
					}
				}
			}
			//count non existing php file
			$nec = 0;
			foreach ($res as $file => $info)
				if ($info['count'] == 0)
					$nec++;
			//attempt to research again the non existing php file
			if ($nec>0) {
				$i = self::index('php',$d);
				self::_fwrite($p,json_encode($i),'w');
				$i = @file_get_contents($p);
				$i = json_decode($i,true);
				foreach ($i as $v) {
					foreach ($f as $file) {
						if ($v['name'] == $file) {
							$path = $v['path'];
							$res[$file]['path'][$path] = 0;
						}
						unset($res[$file]['count']);
					}
				}
			}
			//include the file
			foreach ($i as $v) {
				foreach ($f as $file) {
					
					if ($v['name'] == $file) {
						$path = $v['path'];
						$rs = 0;						
						if (file_exists($path)) {
							$rs = 1;
							include($path);
							
						}
						$res[$file]['path'][$path] = $rs;
					}
				}
			}
			return $res;
		}
	}
	final static function css(string $css,$m = true,string $d = null){
		$d = $d ?? $_SERVER['DOCUMENT_ROOT'];
		$p = "$_SERVER[DOCUMENT_ROOT]/NE_CSSIndex.json";
		//check if css index file exists
		if (!file_exists($p)) {
			//search all css in the directory
			$i = self::index('css',$d);
			//create css index file
			self::_fwrite($p,json_encode($i),'w');
		}
		//ready index file
		$i = @file_get_contents($p);
		$i = json_decode($i,true);

		//ready request files
		$css = self::xpl_ra_map(',','trim',$css);
		
		//check non existing request files
		$c = 0;
		foreach ($css as $req)
			foreach ($i as $index)
				if ($req == $index['name'])
					$c++;

		//attemp to index again to verify the non existence of the file
		if ($c>0) {
			//search all css in the directory
			$i = self::index('css',$d);
			//create css index file
			self::_fwrite($p,json_encode($i),'w');
			//ready index file again
			$i = @file_get_contents($p);
			$i = json_decode($i,true);
		}

		//process
		$res = '';
		foreach ($i as $file)
			foreach ($css as $name)
				if ($file['name'] == $name)
					$res .= "<link rel='stylesheet' type='text/css' href='$file[path]'>";
		//if mode is false
		if ($m == false) {
			foreach ($i as $file){
				foreach ($css as $name){
					if ($file['name'] == $name){
						$file['tag'] = "<link rel='stylesheet' type='text/css' href='$file[path]'>";
						$tmp[] = $file; 
					}
				}
			}
			//return all existing array
			return $tmp;
		}
		//return all existing css in link tags;
		return $res;
	}
	final static function js(string $js,$m = true,string $d = null){
		$d = $d ?? $_SERVER['DOCUMENT_ROOT'];
		$p = "$_SERVER[DOCUMENT_ROOT]/NE_JSIndex.json";

		//check if js index file exists
		if (!file_exists($p)) {

			//search all js in the directory
			$i = self::index('js',$d);

			//create js index file
			self::_fwrite($p,json_encode($i),'w');
		}

		//ready index file
		$i = @file_get_contents($p);
		$i = json_decode($i,true);

		//ready request files
		$js = self::xpl_ra_map(',','trim',$js);
		
		//check non existing request files
		$c = 0;
		foreach ($js as $req)
			foreach ($i as $index)
				if ($req == $index['name'])
					$c++;
				
		//attemp to index again to verify the non existence of the file
		if ($c>0) {

			//search all js in the directory
			$i = self::index('js',$d);

			//create js index file
			self::_fwrite($p,json_encode($i),'w');

			//ready index file again
			$i = @file_get_contents($p);
			$i = json_decode($i,true);

		}

		//process
		$res = '';

		foreach ($i as $file)
			foreach ($js as $name)
				if ($file['name'] == $name)
					$res .= "<script type='text/javascript' src='$file[path]'></script>";
		
		//if mode is false
		if ($m == false) {
			foreach ($i as $file){
				foreach ($js as $name){
					if ($file['name'] == $name){
						$file['tag'] = "<script type='text/javascript' src='$file[path]'></script>";
						$tmp[] = $file; 
					}
				}
			}

			//return all existing array
			return $tmp;

		}

		//return all existing js in script tags;
		return $res;
	}
	final static function img($a,$ext='jpg,jpeg,png',$d = null){

		//check directory
		$d = $d ?? $_SERVER['DOCUMENT_ROOT'];

		//assign default value of result variable
		$res = false;

		//ready index file
		$p = "$_SERVER[DOCUMENT_ROOT]/NE_IMGIndex.json";

		$i = self::index($ext,$d);
		return $i;
	}
	final static function phpLib($dir){
		$dirIndex = self::index('php',$dir);
		$interface = $trait = $abstract = $class = array();
		foreach ($dirIndex as $file) {
			if (isset($file['path']) && file_exists($file['path'])) {
				$code = file($file['path']);
				foreach ($code as $line) {
					$line = trim($line);
					$line = explode(' ', $line);
					if ($line[0] == 'interface' || ($line[0] == 'final' && $line[1] == 'interface')) {
						$interface[] = $file;
					} elseif ($line[0] == 'class') {
						$class[] = $file;
					} elseif ($line[0] == 'abstract') {
						$abstract[] = $file;
					} elseif ($line[0] == 'trait') {
						$trait[] = $file;
					} elseif ($line[0] == 'final' && $line[1] == 'class') {
						$class[] = $file;
					}
				}
			}
		}
		//$libraries = ['interface'=>$interface,'class'=>$class,'abstract'=>$abstract,'trait'=>$trait];
		$idx_if = json_encode($interface);
		$idx_tt = json_encode($trait);
		$idx_at = json_encode($abstract);
		$idx_cl = json_encode($class);
		$idx_fd = json_encode($dirIndex);
		//$idx = str_replace('"path"', "\n	\"path\"", $idx);
		//$idx = str_replace("},","},\n", $idx);

		$data = "<?php
			\$idx_if = json_decode('$idx_if',true);\n\n
			\$idx_fd = json_decode('$idx_fd',true);\n\n
			\$idx_tt = json_decode('$idx_tt',true);\n\n
			\$idx_at = json_decode('$idx_at',true);\n\n
			\$idx_cl = json_decode('$idx_cl',true);\n\n

			foreach(\$idx_if as \$interface)\n
				if(file_exists(\$interface['path']))\n
					include_once(\$interface['path']);\n\n

			foreach(\$idx_tt as \$trait)\n
				if(file_exists(\$trait['path']))\n
					include_once(\$trait['path']);\n\n

			foreach(\$idx_at as \$abstract)\n
				if(file_exists(\$abstract['path']))\n
					include_once(\$abstract['path']);\n\n

			foreach(\$idx_cl as \$class)\n
				if(file_exists(\$class['path']))\n
					include_once(\$class['path']);\n\n

			foreach(\$idx_fd as \$class)\n
				if(file_exists(\$class['path']))\n
					include_once(\$class['path']);\n\n

		";
		//$data = "<?php \$data=json_decode('$idx',true); foreach(\$data as \$file){if(isset(\$file['path']) && file_exists(\$file['path'])){include_once(\$file['path']);}}";
		if (FrecBase::_fwrite(__DIR__.'/autoload.php',$data,'w'));
			return TRUE;
		return FALSE;
	}
}