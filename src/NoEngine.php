<?php

namespace NoEngine;

class NoEngine {
	//File System
	final static function _mkdir($dir,$m=0700){
		if (is_array($dir)) {
			foreach ($dir as $k => $v) $dir[$k] = self::_mkdir($v);
			return $dir;
		} else {
			if (is_dir($dir))	return FALSE;
			if (mkdir($dir,$m))	return TRUE;
		}
	}
	final static function _scandir($dir,$p='n'){
		$path = rtrim($dir,'/').'/';
		if(!is_dir($dir)) return array();
		$dir = scandir($dir);
		$res = array();
		$c = 0;
		$c = 0;
		for ($i=2; $i < (count($dir)); $i++) {
			$res[$c] = $path.$dir[$i];
			$c++;
		}
		return $res;
	}
	final static function _scandir2($dir,$p='n'){
		$path = rtrim($dir,'/').'/';
		if(!is_dir($dir)) return array();
		$dir = scandir($dir);
		$res = array();
		$c = 0;
		for ($i=2; $i < (count($dir)); $i++) {
			$res[$c] = $dir[$i];
			$c++;
		}
		return $res;
	}
	final static function _scandir3(string $dir){
		$base = self::_scandir($dir);
		$tmp = $base;
		foreach ($base as $v) {
			if (is_dir($v)) {
				$a = self::_scandir3($v);
				foreach ($a as $vc) {
					$tmp[] = $vc;
				}
			}
		}
		return $tmp;
	}
	final static function _del($p){
		if (is_array($p)) {
			foreach ($p as $key => $value) $p[$key] = self::_del($value);
			return $p;
		} else {
			if (is_dir($p)) {
				$dir = self::_scandir($p);
				foreach ($dir as $key => $value) self::_del($value);
				if (rmdir($p)) return TRUE;
			} elseif (file_exists($p)) {
				if (unlink($p)) return TRUE;
			}
		}
		return FALSE;
	}
	final static function _fwrite($f,$data='',$m='a'){
		if ($m == 'a') {
			if (file_exists($f)) {
	    		$handle =  fopen($f, "a" ) ;
		        return fwrite($handle,$data);
		        fclose ($handle);
	    	} else return self::_fwrite($f,'w',$data);
	    } elseif ($m == 'w') {
	    	if (file_exists($f)) unlink($f);
	    	touch($f);
	        $handle =  fopen($f, "w" ) ;
	        return fwrite($handle,$data);
	        fclose ($handle);
	    }
	}
	final static function youtube_dl(string $link){
		$q = new YTLinks;
		return $q->get($link);
	}
	final static function get_filename($f,$ext=''){
		$a = basename($f,$ext);
		if ($a == $ext)
			return 'No Filename';
		return $a;
	}
	final static function get_ext($f){
		$a = explode('/', $f);
		$b = count($a);
		$c = $a[($b-1)];
		$d = explode('.', basename($f));
		if (count($d)>1) {
			$e = array_slice($d, (count($d)-1));
			return implode('', $e);
		} else {
			return 'unknown';
		}
	}
	final static function index_dir(string $dir='.'){
		$tmp = array();
		if (is_dir($dir)) {
			$folders = self::_scandir3($dir);
			$c = 0;
			$tmp = array();
			foreach ($folders as $k => $v) {
				if (is_dir($v)) {
					$tmp[$c]['type'] = 'dir';
					$tmp[$c]['path'] = $v;
				} elseif (file_exists($v)) {
					$tmp[$c]['name'] = self::get_filename($v,'.'.self::get_ext($v));
					$tmp[$c]['type'] = self::get_ext($v);
					$tmp[$c]['size'] = filesize($v);
					$tmp[$c]['path'] = $v;
				} else {
					$c--;
				}
				$c++;
			}
		}
		return $tmp;
	}
	final static function dir_size(string $dir){
		$i = self::index_dir($dir);
		$size = 0;
		foreach ($i as $v)
			if (isset($v['size']))
				$size += $v['size'];
		return self::autobits($size);
	}
	final static function index($t,$d=null){
		$d = $d ?? $_SERVER['DOCUMENT_ROOT'];
		$i = self::index_dir($d);
		$tmp = array();
		$t = $t = self::xpl_ra_map(',','trim',$t);
		foreach ($i as $v)
			if (array_search($v['type'], array_merge(array(''),$t)))
				$tmp[] = $v;
		return $tmp;
	}
	final static function index_types($dir){
		$i = self::index_dir($dir);
		foreach ($i as $v)
			$r[] = $v['type'];
		$r = array_unique($r);
		foreach ($r as $v)
			$res[] = $v;
		sort($res);
		return $res;
	}
	//Array
	final static function ra2str($a,$ks='\'',$vs='\''){
		if (!is_array($a)) return FALSE;
		$r='';
		$c=count($a);
		$l=0;
		foreach ($a as $k => $v) {
			$l++;
			if (is_array($v)) {
				$v=self::ra2str($v);
				if (gettype($k)=='string') {
					$d="$ks$k$ks=>$v";
				} else $d="$k=>$v";
			} else {
				if (gettype($v)=='string') {
					$v=addslashes($v);
					if (gettype($k)=='string') {
						$d="$ks$k$ks=>$vs$v$vs";
					} else $d="$k=>$vs$v$vs";
				} else {
					if (gettype($k)=='string') {
						$d="$ks$k$ks=>$v";
					} else $d="$k=>$v";
				}
			}
			if ($l==$c) {
				$r.=$d;
			} else $r.=$d.",\n	";
		}
		return "array(\n	$r\n)";
	}
	final static function ra_search(array $w,array $a){
		/**
			only works on below array format
			array(
				array(key=>val,key=val),
				array(key=>val,key=val),
				array(key=>val,key=val),
				array(key=>val,key=val
			)

		**/
		$r=array();
		foreach ($a as $ak => $av) {
			$l=0;
			foreach ($w as $wk => $wv)
				if (isset($av[$wk]))
					if ($av[$wk]==$wv)
						$l++;
			if ($l==count($w))
				$r[$ak]=$av;
		}
		return $r;
	}
	final static function ra_search_bool(array $w,array $a){
		if (count(self::ra_search($w,$a))>0) 
			return TRUE;
		return FALSE;
	}
	final static function ra_first_row(array $a){
		/**
		if (count($a) > 0)
			foreach ($a as $v)
				return $v;
		**/
		foreach ($a as $k => $v)
			return $v;
		return FALSE;
	}
	final static function ra_last_row(array $a){
		$c = 0;
		$ac = count($a);
		if ($ac > 0) {
			foreach ($a as $k => $v) {
				$c++;
				if ($c == $ac)
					return $v;
			}
		}
		return NULL;
	}
	final static function ra_search_uniq(array $a,array $w){
		$c = 0;
		foreach ($w as $k => $v)
			foreach ($a as $ak => $av)
				if (isset($av[$k]) && $av[$k] == $v)
					return FALSE;
		return TRUE;
	}
	final static function ra_key(string $key,$a=array()){
		/**
			only works on below array format
			array(
				array(key=>val,key=val),
				array(key=>val,key=val),
				array(key=>val,key=val),
				array(key=>val,key=val
			)

		**/
		$r= array();
		foreach ($a as $row) if(isset($row[$key])) $r[]=$row[$key];
		return $r;
	}
	final static function ra_index($a=array(),$n,$p){
		//n = number of items to return
		//p = page of items to return
		$r=array();
		$e=($p*$n);
		$s=$e-($n-1);
		$i=count($a)/$n;
		if ($p>0) {
			$l=0;
			foreach ($a as $k => $v) {
				$l++;
				if ($l>=$s && $l<=$e) $r[$k] = $v;
			}
		}
		return $r;
	}
	final static function ra_strict_mode($a=array(),$b=array(),$s=array()){
		//a = {0={k=>v},2={k=>v}}
		//b = {k=>v}
		//s = k
		$c = count($s);
		foreach ($a as $row) {
			$l = 0;
			foreach ($s as $v) {
				$x = @$row[$v];
				$y = @$b[$v];
				if ($x==$y) $l++;
			}
			if ($l==$c) return TRUE;
		}
		return FALSE;
	}
	final static function ra_is_data($a=array(),$w=array()){
		$c = 0;
		foreach ($w as $k => $v)
			foreach ($a as $ak => $av)
				if (isset($av[$k]) && $av[$k] == $v)
					$c++;
		return $c;
	}
	final static function ra_is_data_bool($a=array(),$w=array()){
		$c = 0;
		foreach ($w as $k => $v)
			foreach ($a as $ak => $av)
				if (isset($av[$k]) && $av[$k] == $v)
					$c++;
		if ($c > 0)
			return TRUE;
		return FALSE;
	}
	final static function ra_get_val(array $a,array $w){
		$res = array();
		foreach ($w as $v)
			if (isset($a[$v])) {
				$res[$v] = $a[$v];
			}
		return $res;
	}
	final static function rsort_by_keys(array $a){
		$h = $r = array();
		foreach ($a as $k => $v) $h[] = $k;
		rsort($h);
		foreach ($h as $k => $v) $r[$v] = $a[$v];
		return $r;
	}
	final static function sort_by_keys(array $a){
		$h = $r = array();
		foreach ($a as $k => $v) $h[] = $sk;
		sort($h);
		foreach ($h as $k => $v) $r[$v] = $a[$v];
		return $r;
	}
	final static function ra_map(string $c,array $a){
		$c = explode(',', $c);
		$c = array_map('trim', $c);
		foreach ($c as $callback) {
			$a = array_map($callback, $a);
		}
		return $a;
	}
	final static function xpl_ra_map(string $d, string $c,string $str){
		$r = explode($d, $str);
		$r = self::ra_map($c,$r);
		return $r;
	}
	final static function ra_pagination(array $a,int $p,int $i,string $m = ''){
		// a - array
		// a - format : {"key":[value_array],"key":[value_array],"key":[value_array]}
		// p - page
		// i - items to show per page


		
		$b = array();
		foreach ($a as $k => $v)
			$b[] = $v;



		//total items
		$ti = count($b);

		//proccess array 
		//reformat array keys for ordering items
		if ($m == 'invert') {
			$tmp = array();
			for ($if=($ti-1); $if > 0; $if--) { 
				$tmp[] = $b[$if];
			}
			$b = $tmp;
			//rsort($a);
		}
		//total page
		$tp = $ti/$i;
		if (($ti%$i) > 0)
			$tp += 1;
		//ready result
		$res = array();
		//return empty array if request page greater than total pages
		if ($p > $tp) {
			return $res;
		} else {
			//last key id of the requested page
			$e = $p * $i;
			//firs key id of the req page
			$s = ($e - $i) + 1;

			$res = array();
			for ($i=$s; $i < ($e+1); $i++)
				if (isset($b[($i-1)]))
					$res[] = $b[($i-1)];
			return $res;
		}


	}
	//Time
	final static function ms() {
	    $mt = explode(' ', microtime());
	    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
	}
	final static function ms2sec(int $ms){
		return ($ms/1000);
	}
	final static function min2sec(int $m){
		return (60*$m);
	}
	final static function hr2sec(int $h){
		return ($h*60*60);
	}
	final static function d2sec(int $d){
		return ($d*24*60*60);
	}
	final static function y2sec(int $y){
		return ($y*365*24*60*60);
	}
	final static function ly2sec(int $ly){
		return ($ly*366*24*60*60);
	}
	final static function sec2ms(int $sec){
		return ($sec*1000);
	}
	final static function sec2min(int $sec){
		return ($sec/60);
	}
	final static function sec2hr(int $sec){
		return ($sec/60/60);
	}
	final static function sec2d(int $sec){
		return ($sec/24/60/60);
	}
	final static function sec2y(int $sec){
		return ($sec/365/24/60/60);
	}
	final static function sec2ly(int $sec){
		return ($sec/366/24/60/60);
	}
	//Bytes and bits
	final static function autobits($bits){
		$b = 1;
		$B = 8;
		$KB = 1000;
		$MB = 1000000;
		$GB = 1000000000;
		$TB = 1000000000000;
		$PB = 1000000000000000;
		$r = '0 bits';
		if ($bits>=$b) $r = number_format(($bits/$b), 2, '.', ',').' bits';
		if ($bits>=$B) $r = number_format(($bits/$B), 2, '.', ',').' B';
		if ($bits>=$KB) $r = number_format(($bits/$KB), 2, '.', ',').' KB';
		if ($bits>=$MB) $r = number_format(($bits/$MB), 2, '.', ',').' MB';
		if ($bits>=$GB) $r = number_format(($bits/$GB), 2, '.', ',').' GB';
		if ($bits>=$TB) $r = number_format(($bits/$TB), 2, '.', ',').' TB';
		if ($bits>=$PB) $r = number_format(($bits/$PB), 2, '.', ',').' PB';
		return $r;
	}
	final static function number_format_short( $n, $precision = 1 ) {
		if ($n < 900) {
			// 0 - 900
			$n_format = number_format($n, $precision);
			$suffix = '';
		} else if ($n < 900000) {
			// 0.9k-850k
			$n_format = number_format($n / 1000, $precision);
			$suffix = 'K';
		} else if ($n < 900000000) {
			// 0.9m-850m
			$n_format = number_format($n / 1000000, $precision);
			$suffix = 'M';
		} else if ($n < 900000000000) {
			// 0.9b-850b
			$n_format = number_format($n / 1000000000, $precision);
			$suffix = 'B';
		} else {
			// 0.9t+
			$n_format = number_format($n / 1000000000000, $precision);
			$suffix = 'T';
		}
	  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
	  // Intentionally does not affect partials, eg "1.50" -> "1.50"
		if ( $precision > 0 ) {
			$dotzero = '.' . str_repeat( '0', $precision );
			$n_format = str_replace( $dotzero, '', $n_format );
		}
		return $n_format . $suffix;
	}
	//Encryption
	final static function se(string $str,$l=1){
		for ($i=0; $i < $l; $i++) $str = base64_encode($str);
		return $str;
	}
	final static function sd(string $str,$l=1){
		for ($i=0; $i < $l; $i++) $str = base64_decode($str);
		return $str;
	}
	final static function pass(string $pass,$k='skidd'){
		$s = self::se(md5($pass),3);
		$s = md5($k.$s.$k);
		$s = self::se($s,1);
		return md5($s);
	}
	//Internet Protocol
	final static function get_ip() {
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
	                if (self::validate_ip($ip))
	                    return $ip;
	            }
	        } else {
	            if (self::validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
	                return $_SERVER['HTTP_X_FORWARDED_FOR'];
	        }
	    }
	    if (!empty($_SERVER['HTTP_X_FORWARDED']) && self::validate_ip($_SERVER['HTTP_X_FORWARDED']))
	        return $_SERVER['HTTP_X_FORWARDED'];
	    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
	        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
	        return $_SERVER['HTTP_FORWARDED_FOR'];
	    if (!empty($_SERVER['HTTP_FORWARDED']) && self::validate_ip($_SERVER['HTTP_FORWARDED']))
	        return $_SERVER['HTTP_FORWARDED'];

	    // return unreliable ip since all else failed
	    return $_SERVER['REMOTE_ADDR'];
	}
	final static function validate_ip($ip) {
	    if (strtolower($ip) === 'unknown')
	        return false;

	    // generate ipv4 network address
	    $ip = ip2long($ip);

	    // if the ip is set and not equivalent to 255.255.255.255
	    if ($ip !== false && $ip !== -1) {
	        // make sure to get unsigned long representation of ip
	        // due to discrepancies between 32 and 64 bit OSes and
	        // signed numbers (ints default to signed in PHP)
	        $ip = sprintf('%u', $ip);
	        // do private network range checking
	        if ($ip >= 0 && $ip <= 50331647) return false;
	        if ($ip >= 167772160 && $ip <= 184549375) return false;
	        if ($ip >= 2130706432 && $ip <= 2147483647) return false;
	        if ($ip >= 2851995648 && $ip <= 2852061183) return false;
	        if ($ip >= 2886729728 && $ip <= 2887778303) return false;
	        if ($ip >= 3221225984 && $ip <= 3221226239) return false;
	        if ($ip >= 3232235520 && $ip <= 3232301055) return false;
	        if ($ip >= 4294967040) return false;
	    }
	    return true;
	}
	//Exception
	final static function FBE($e){
		$str = $e->__toString();
		$trace = explode("\n", $str);
		$execf = trim(str_replace('#0 ', '', $trace[2]));
		$prcs = explode(' ', $execf);
		$prcsc = count($prcs);
		$resp = array();
		for ($i=0; $i < $prcsc; $i++) if ($i!=($prcsc-1)) $resp[] = $prcs[$i];
		$res = substr(implode(' ', $resp), 0,-1);
		$srvw = str_replace('/', '\\', $_SERVER['DOCUMENT_ROOT']);
		$res = str_replace($srvw,'',$res);
		$res = substr($res, 1);
		$msg = $e->getMessage();
		$res = "<b>NoEngine: </b> $msg on $res";
		if (substr($res, -4,-1) == ' on')
			$res = substr($res, 0,-4);
		echo $res;
	}
	//String
	final static function bool2str($a){
		$b = gettype($a);

		if ($b=='bool') {
			if ($a) {
				return 'true';
			}
		}
		return 'false';
	}
	final static function is_str($a){
		if (isset($a) && $a != '' && !is_array($a) && strlen($a)>0 && $a != NULL &&  gettype($a)=='string');
			return TRUE;
		return FALSE;
	}
	final static function is_word(string $str){
		if (!preg_match('[\W]', $str))
			return TRUE;
		return FALSE;
	}
	final static function clean(string $str){
		$str = str_replace("\\", "\\\\", $str);
		return str_replace("'", "\'", $str);
	}
	//Math
	final static function int_diff(int $a){
		return ($a <=> 0);
	}
	final static function int_sign(int $a){
		$int = self::int_diff($a);
		if ($int == 1) {
			return '+';
		} elseif ($int == -1) {
			return '-';
		} elseif ($int == 0){
			return 0;
		}
		return FALSE;
	}
	final static function int_sign_str(int $a){
		$int = self::int_diff($a);
		if ($int == 1) {
			return 'positive';
		} elseif ($int == -1) {
			return 'negative';
		} elseif ($int == 0){
			return 'zero';
		}
		return FALSE;
	}
	final static function is_zero(int $a){
		if (int_diff($a)==0)
			return TRUE;
		return FALSE;
	}
	final static function is_positive(int $a){
		if (int_diff($a)==1)
			return TRUE;
		return FALSE;
	}
	final static function is_negative(int $a){
		if (int_diff($a)==-1)
			return TRUE;
		return FALSE;
	}
	final static function int2roman(int $num)  {
	    // Be sure to convert the given parameter into an integer
	    $n = intval($num);
	    $result = '';
	    // Declare a lookup array that we will use to traverse the number:
	    $lookup = array(
	        'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
	        'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
	        'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
	    );
	    foreach ($lookup as $roman => $value){
	        // Look for number of matches
	        $matches = intval($n / $value);
	        // Concatenate characters
	        $result .= str_repeat($roman, $matches);
	        // Substract that from the number
	        $n = $n % $value;
	    }
	    return $result;
	}
	final static function computeClosestToZero(array $ts) {
	    if(empty($ts)){
	        return 0;
	    }
	    
	    $closest = 0;
	    
	    for ($i = 0; $i < count($ts) ; $i++) {
	        if ($closest === 0) {
	            $closest = $ts[$i];
	        } else if ($ts[$i] > 0 && $ts[$i] <= abs($closest)) {
	            $closest = $ts[$i];
	        } else if ($ts[$i] < 0 && -$ts[$i] < abs($closest)) {
	            $closest = $ts[$i];
	        }
	    }
	    
	    return $closest;
	}
	final static function closestToZero(array $ts){
	    if (count($ts) === 0) return 0;
	    
	    $closest = $ts[0];

	    foreach ($ts as $d) 
	    {
	        $absD = abs($d);
	        $absClosest = abs($closest);
	        if ($absD < $absClosest) 
	        {
	            $closest = $d;
	        } 
	        else if ($absD === $absClosest && $closest < 0) 
	        {
	            $closest = $d;
	        }
	    }
	    
	    return $closest;
	}
	//Utils
	final static function data($p){
		if (file_exists($p)) {
			include($p);
			if (isset($data))
				return $data;
		}
		return array();
	}
	final static function genSrc($dir = FALSE){
		$dir = $dir ?? rtrim($_SERVER['DOCUMENT_ROOT'],'/').'/';
		$f = $dir.'NoEngine_Src.php';

		if (!file_exists($f)) {
			$php = self::index('php',$dir.'.');
			$php = self::ra2str($php);
			$js = self::index('js',$dir.'.');
			$js = self::ra2str($js);
			$css = self::index('css',$dir.'.');
			$css = self::ra2str($css);
			self::_fwrite($f,"<?php\n\$src['php'] = $php;\n\$src['js'] = $js;\n\$src['css'] = $css;\n",'w');
		}
	}
	final static function include($php){
		$i = self::index('php',$_SERVER['DOCUMENT_ROOT']);
		foreach ($i as $v)
			if ($v['name'] == trim($php))
				include($v['path']);
	}
	final static function img2b64(string $imagePath) {
	    $finfo = new finfo(FILEINFO_MIME_TYPE);
	    $type = $finfo->file($imagePath);
	    return 'data:'.$type.';base64,'.base64_encode(file_get_contents($imagePath));
	}
}
class Header extends NoEngine {
	public function app(string $m){
		if ($m=='json') {
			header('Access-Control-Allow-Origin: *');
			header('Content-Type: application/json');
			header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Authorization, X-Requested-With');
		}
	}
}
class FrecBase extends NoEngine {

	public 	$dir 	= "./FrecBase/"; //end with '/'
	public	$con 	= 'root',$db,$tb;

	const 	version = 'v3.0';
	const 	main 	= 'main.php';
	const 	users 	= 'users.php';

	public function __construct($dir=''){
		//set_exception_handler('self::FBE');
		if ($dir == '') $dir = $this->dir;
		if ($dir != '') $this->dir = $dir;

		if (!is_dir($dir)) {
			self::_mkdir($dir);

			if (!file_exists($dir.self::users))
			self::_fwrite($dir.self::users,"<?php\n\$data = array();\n\$data['col'] = array('pass','ip','date');\n\$data['pk'] = 'id';\n\$data['fk'] = 'user';\n\$data['lid'] = 0;\n\$data['row'] = array();\n",'w');
			if (!file_exists($dir.self::main))
			self::_fwrite($dir.self::main,"<?php\n\$data = array();\n",'w');
			$this->add_user('root','root');
		}
	}
	//user
	final protected function is_user($user){//checks if user exists
		$users = $this->users('row');
		foreach ($users as $id => $u)
			if (isset($u['user']))
				if ($u['user'] == $user){
					if (!is_dir($this->dir.$user))
						self::_mkdir($this->dir.$user);
					return TRUE;
				}
		return FALSE;
	}
	final protected function users($m){//get users info
		$p = $this->dir.self::users;
		include($p);
		if ($m == 'list') {
			$res = array();
			foreach ($data['row'] as $id => $u)
				$res[] = $u['user'];
			return $res;
		} elseif ($m == 'lid') {
			return $data['lid'];
		} elseif ($m == 'pk') {
			return $data['lid'];
		} elseif ($m == 'col') {
			return explode(',', "$data[pk],$data[col]");
		} elseif ($m == 'row') {
			return $data['row'];
		}
	}
	final protected function user($col,$user){//get user info
		$users = $this->users('row');
		foreach ($users as $id => $u)
			if ($user == $u['user'])
				if (isset($u[$col]))
					return $u[$col];
		return NULL;
	}
	final protected function is_con(){ //checks if already login
		if (isset($this->con)) {
			if ($this->is_user($this->con))
				return TRUE;
		}
		return FALSE;
	}
	final protected function upath(){ //returns user path
		if (isset($this->con)) {
			return "{$this->dir}{$this->con}/";
		}
	}
	final protected function is_db($user,$db){ //check if db exists
		if (is_dir("{$this->dir}$user/$db"))
			return TRUE;
		return FALSE;
	}
	final public function add_user($user,$pass){ //add user account
		$dir = $this->dir;
		$p = $dir.self::users;
		if ($this->is_user($user) === FALSE) {
			$id = $this->users('lid') + 1;
			if (preg_match("/[\W]/u", $user))
				return FALSE;
			$pass = self::pass($pass);
			$ip = self::get_ip();
			$date = date('M d, Y',time());
			$rw = "\$data['row'][$id] = array('id'=>$id,'user'=>'$user','pass'=>'$pass','ip'=>'$ip','date'=>'$date');\n\$data['lid'] = $id;\n";
			self::_mkdir($dir.$user);
			self::_fwrite($p,$rw);
			return TRUE;
		} else return FALSE;
	}
	final public function con($user,$pass){ //login to db account
		if ($this->is_user($user)) {
			$psk = $this->user('pass',$user);
			if ($this->pass($pass)==$psk){
				$this->con = $user;
				return TRUE;
			}
		}
		return FALSE;
	}
	final public function del_user($user,$pass){ //deletes a user
		if ($this->is_user($user)) {
			$psk = $this->user('pass',$user);
			if ($this->pass($pass)==$psk){
				$id = $this->user('id',$user);
				self::_fwrite($this->dir.self::users,"unset(\$data['row'][$id]);\n");
				return TRUE;
			}
		}
		return FALSE;
	}
	//Set db & tb
	final public function db($db){ //select db

		$this->db = $db;
	}
	final public function tb($tb){ //select tb

		$this->tb = $tb;
	}
	final public function select($db,$tb){ //select db and tb

		$this->db = $db;
		$this->tb = $tb;
	}

	//CRUD
	final public function create($m='',$a='',$b='',$c='',$d='',$e=''){
		$user = $this->con;
			$dir = $this->upath();
			$db = $this->db;
			$tb = $this->tb;

			if (self::is_str($db) && $this->is_db($user,$db)) {
				$db_bool = TRUE;
				$db = "$dir$db/";
			} else $db_bool = FALSE;

			if ($db_bool && self::is_str($tb)) {
				$tb_bool = TRUE;
				$tb = "$db$tb.php";
			} else $tb_bool = FALSE;
			if (!$this->is_con())
				return FALSE;

			self::_mkdir($dir);
		if ($m == 'db') {
			//a = dbname
			if (isset($a) && $a != '' && preg_match("/[\w]/", $a) && self::_mkdir($dir.$a))
				return TRUE;
		} elseif ($m == 'tb') {
			//a = tb name
			//b = col
			//c = pk
			//d = fk
			$p = "$db$a.php";
			$c = $c ?? 'id';
			if ($d != '') {
				$d = self::xpl_ra_map(',','trim',$d);
				if (count($d) > 0){
					$d = json_encode($d);
					$d = "json_decode('$d',true)";
				}else $d = '[]';
			} else $d = '[]';
			if ($db_bool && !file_exists($p) && isset($a) && preg_match("/[\w]/", $a) && isset($b) && gettype($b) == 'string') {
				$b = self::xpl_ra_map(',','trim',$b);
				$b = json_encode($b);
				$b = "json_decode('$b',true)";
				$res = self::_fwrite($p,"<?php\n\$data['col']=$b;\n\$data['pk']='$c';\n\$data['fk']=$d;\n\$data['lid']=0;\n\$data['row']=[];\n",'w');
				return ($res ? TRUE : FALSE);
			}
		} elseif ($m == 'data') {
			//a - array of data
			if ($tb_bool && is_array($a)) {
				$data = self::data($tb) ?? array();
				if (count($data) > 0 && isset($data['lid']) && isset($data['fk']) && is_array($data['fk']) && isset($data['pk']) && isset($data['col']) && is_array($data['col']) && isset($data['row']) && is_array($data['row'])) {
					$id = $data['lid'] + 1;
					$fk = $data['fk'];
					$pk = $data['pk'];
					$col = $data['col'];
					$rows = $data['row'];
					$cols = array_merge($col,[$pk]);
					if (count($fk) > 0) {
						$cols = array_merge($cols,$fk);
						foreach ($fk as $fkeys)
							if (isset($a[$fkeys]) && count($rows) > 0)
								if (self::ra_search_bool(array($fkeys=>$a[$fkeys]),$rows))
									return FALSE;
					}
					$res_a = array();
					$a = array_merge($a,[$pk=>$id]);
					foreach ($a as $k => $v)
						$a[$k] = urlencode($v);
					foreach ($cols as $c)
						$res_a[$c] = $a[$c] ?? '';
					$res = json_encode($res_a);
					$res = "json_decode('$res',true)";
					$result = NoEngine::_fwrite($tb,"\$data['row'][$id] = $res;\n\$data['lid'] = $id;\n");
					$n = $result ? TRUE : FALSE;
					return $n;
				} 
			}
		}
		return FALSE;
	}
	final public function read(string $m='row',string $a='',array $b = array()){
		$user = $this->con;
			$dir = $this->upath();
			$db = $this->db;
			$tb = $this->tb;

			if (self::is_str($db) && $this->is_db($user,$db)) {
				$db_bool = TRUE;
				$db = "$dir$db/";
			} else $db_bool = FALSE;

			if ($db_bool && self::is_str($tb)) {
				$tb_bool = TRUE;
				$tb = "$db$tb.php";
			} else $tb_bool = FALSE;
			if (!$this->is_con())
				return FALSE;

			self::_mkdir($dir);
		if ($m == 'db') {
			return self::_scandir2($dir);
		} elseif ($m == 'tb') {
			$res = self::_scandir2($db);
			return rtrim($res,'.php');
		} elseif ($m == 'row') {
			if ($a == 'where') {
				foreach ($b as $k => $v)
					if (!is_array($v)){
						$b[$k] = urlencode($v);
					} else return array();
				return self::ra_search($b,$this->read('row'));
			} else {
				$data = self::data($tb);
				foreach ($data['row'] as $k => $v)
					foreach ($v as $vk => $vv)
						$data['row'][$k][$vk] = urldecode($vv);
				return $data['row'];
			}
		} elseif ($m == 'pk') {
			$data = self::data($tb);
			return $data['pk'];
		}elseif ($m == 'fk') {
			$data = self::data($tb);
			return $data['fk'];
		}elseif ($m == 'lid') {
			$data = self::data($tb);
			return $data['lid'];
		}elseif ($m == 'col') {
			$data = self::data($tb);
			return $data['col'];
		}
		return array();
	}
	final public function update($m='',$a='',$b='',$c='',$d='',$e=''){
		$user = $this->con;
			$dir = $this->upath();
			$db = $this->db;
			$tb = $this->tb;

			if (self::is_str($db) && $this->is_db($user,$db)) {
				$db_bool = TRUE;
				$db = "$dir$db/";
			} else $db_bool = FALSE;

			if ($db_bool && self::is_str($tb)) {
				$tb_bool = TRUE;
				$tb = "$db$tb.php";
			} else $tb_bool = FALSE;
			if (!$this->is_con())
				return FALSE;

			self::_mkdir($dir);
		if ($m == 'db') {
			if (self::is_word($a) && !is_dir($dir.$a)) {
				rename($dir.$this->db, $dir.$a);
				$this->db($a);
				return TRUE;
			}
		} elseif ($m == 'tb') {
			if (self::is_word($a) && !file_exists($db.$a)) {
				rename($tb, "$db$a.php");
				$this->tb($a);
				return TRUE;
			}
		} elseif ($m == 'col' && self::is_str($a)) {
			$a = explode(',', $a);
			foreach ($a as $k => $v){
				$v = trim($v);
				$a[$k] = $v;
				if (!self::is_word($v))
					return FALSE;
			}
			$col = self::ra2str($col);
			self::_fwrite($tb,"\$data['col'] = $col;\n");
		} elseif ($m == 'data' && is_array($a) && is_array($b)) {
			//a = rule
			//b = update
			$row = $this->read('row','where',$a);
			$row = self::ra_first_row($row);
			$fk = $this->read('fk');
			$pk = $this->read('pk');
			$id = $row[$pk];
			foreach ($fk as $v)
				if (isset($b[$v]))
					if (count($this->read('row','where',array($v=>$b[$v])))>0)
						return FALSE;
			if (array_key_exists($pk, $b)) {
				return FALSE;
			}
			$myId = array($pk=>$id);
			$data = array_merge($row,$b);
			//var_dump($data);
			$data = json_encode($data);
			$res = self::_fwrite($tb, "\$data['row'][$id] = json_decode('$data',true);\n");
			if ($res)
				return TRUE;
		}
		return FALSE;
	}
	final public function delete($m='',$a='',$b='',$c='',$d='',$e=''){
		$user = $this->con;
		$dir = $this->upath();
		$db = $this->db;
		$tb = $this->tb;

		if (self::is_str($db) && $this->is_db($user,$db)) {
				$db_bool = TRUE;
				$db = "$dir$db/";
		} else $db_bool = FALSE;

		if ($db_bool && self::is_str($tb)) {
				$tb_bool = TRUE;
				$tb = "$db$tb.php";
		} else $tb_bool = FALSE;
		if (!$this->is_con())
				return FALSE;

		self::_mkdir($dir);
		if ($m == 'db') {
				if (is_dir($dir.$a) && self::_del($dir.$a))
					return TRUE;
		} elseif ($m == 'tb' && $db_bool) {
				if (file_exists("$db$a.php") && self::_del("$db$a.php"))
					return TRUE;
		} elseif ($m == 'data' && $tb_bool) {
			$row = self::ra_first_row($this->read('row','where',$a));
			$pk = $this->read('pk');
			if (is_array($row) && count($row)>0) {
				if (isset($row[$pk])) {
					$id = $row[$pk];
					$res = self::_fwrite($tb,"unset(\$data['row'][$id]);\n");
					if ($res)
						return TRUE;
				}
			}
		} elseif ($m == 'row' && $tb_bool) {
				$res = self::_fwrite($tb,"\$data['row'] = array();\n");
				if ($res)
					return TRUE;
		}
		return FALSE;
	}
	//Utils
	final public function destroy($dir=''){ //delete db data
		self::_del($this->dir);
		if ($dir == '') $dir = $this->dir;
		if ($dir != '') $this->dir = $dir;
		if (!is_dir($dir)) {
			self::_mkdir($dir);
			if (!file_exists($dir.self::users))
			self::_fwrite($dir.self::users,"<?php\n\$data = array();\n\$data['col'] = array('pass','ip','date');\n\$data['pk'] = 'id';\n\$data['fk'] = 'user';\n\$data['lid'] = 0;\n\$data['row'] = array();\n",'w');
			if (!file_exists($dir.self::main))
			self::_fwrite($dir.self::main,"<?php\n\$data = array();\n",'w');
			$this->add_user('root','root');
		}
	}
	final public function optimize(){ //optimize db
		$res = array();
		$b = 0;
		$a = 0;
		$t = self::ms();
		foreach ($this->users('list') as $user) {
			if (is_dir($this->dir.$user)) {
				$p = "{$this->dir}$user/";
				$dbs = self::_scandir($p);
				foreach ($dbs as $db) {
					$tbs = self::_scandir($db);
					foreach ($tbs as $file) {
						if (file_exists($file)) {
							$b += filesize($file);
							$data = self::data($file);
							$dat = json_encode($data);
							self::_fwrite($file,"<?php\n\$data = json_decode('$dat',true);\n",'w');
							$a +=filesize($file);
						}

					}
				}
			}
		}
		$t = self::ms() - $t;
		$res['size_before'] = self::autobits($b);
		$res['size_after'] = self::autobits($a);
		$res['time'] = ($t/100).' sec';
		return $res;
	}
	final public function size(){ //db size
		$b = 0;
		foreach ($this->users('list') as $user) {
			if (is_dir($this->dir.$user))
				foreach (self::_scandir("{$this->dir}$user/") as $db)
					foreach (self::_scandir($db) as $file)
						if (file_exists($file))
							$b += filesize($file);
		}
		return self::autobits($b);
	}
	final public function fetch_time(){ //debug rows fetch time
		$r = '';
		$t = self::ms();
		foreach ($this->users('list') as $user) {
			if (is_dir($this->dir.$user)) {
				$p = "{$this->dir}$user/";
				$dbs = self::_scandir($p);
				foreach ($dbs as $db) {
					$tbs = self::_scandir($db);
					foreach ($tbs as $file) {
						if (file_exists($file)) {
							$data = self::data($file);
							$rows = $data['row'];
							foreach ($rows as $pk => $row) {
								$r.=self::ra2str($row);
							}
						}

					}
				}
			}
		}
		$t = self::ms() - $t;
		return ($t/1000).' sec';
	}
}
class YTLinks extends NoEngine {
    private $storage_dir;
    private $cookie_dir;

    private $client;

    private $itag_info = array(
        5 => "FLV 400x240",
        6 => "FLV 450x240",
        13 => "3GP Mobile",
        17 => "3GP 144p",
        18 => "MP4 360p",
        22 => "MP4 720p (HD)",
        34 => "FLV 360p",
        35 => "FLV 480p",
        36 => "3GP 240p",
        37 => "MP4 1080",
        38 => "MP4 3072p",
        43 => "WebM 360p",
        44 => "WebM 480p",
        45 => "WebM 720p",
        46 => "WebM 1080p",
        59 => "MP4 480p",
        78 => "MP4 480p",
        82 => "MP4 360p 3D",
        83 => "MP4 480p 3D",
        84 => "MP4 720p 3D",
        85 => "MP4 1080p 3D",
        91 => "MP4 144p",
        92 => "MP4 240p HLS",
        93 => "MP4 360p HLS",
        94 => "MP4 480p HLS",
        95 => "MP4 720p HLS",
        96 => "MP4 1080p HLS",
        100 => "WebM 360p 3D",
        101 => "WebM 480p 3D",
        102 => "WebM 720p 3D",
        120 => "WebM 720p 3D",
        127 => "TS Dash Audio 96kbps",
        128 => "TS Dash Audio 128kbps"
    );

    function __construct($link=NULL)
    {
        $this->storage_dir = sys_get_temp_dir();
        $this->cookie_dir = sys_get_temp_dir();
        $this->client = null;
    }

    function setStorageDir($dir)
    {
        $this->storage_dir = $dir;
    }

    // if URL: download it
    private function toHtml($html)
    {

    }

    // what identifies each request? user agent, cookies...
    public function curl($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfname);
        //curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfname);

        //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    // TODO: remove this as it required PECL extension
    public static function head($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return http_parse_headers($result);
    }

    // accepts either raw HTML or url
    // <script src="//s.ytimg.com/yts/jsbin/player-fr_FR-vflHVjlC5/base.js" name="player/base"></script>
    public function getPlayerUrl($video_html)
    {
        if (strpos($video_html, 'http') === 0) {
            $video_html = $this->curl($video_html);
        }

        $player_url = null;

        // check what player version that video is using
        if (preg_match('@<script\s*src="([^"]+player[^"]+js)@', $video_html, $matches)) {
            $player_url = $matches[1];

            // relative protocol?
            if (strpos($player_url, '//') === 0) {
                $player_url = 'http://' . substr($player_url, 2);
            } elseif (strpos($player_url, '/') === 0) {
                // relative path?
                $player_url = 'http://www.youtube.com' . $player_url;
            }
        }

        return $player_url;
    }

    // Do not redownload player.js everytime - cache it
    public function getPlayerHtml($video_html)
    {
        $player_url = $this->getPlayerUrl($video_html);

        $cache_path = sprintf('%s/%s', $this->storage_dir, md5($player_url));

        if (file_exists($cache_path)) {
            $contents = file_get_contents($cache_path);
            //return unserialize($contents);
        }

        $contents = $this->curl($player_url);

        // cache it too!
        file_put_contents($cache_path, serialize($contents));

        return $contents;
    }

    /*
     * Youtube Sep2018 Changes
    deDE:
        var aL={NI:function(a,b){a.splice(0,b)},jl:function(a){a.reverse()},l5:function(a,b){var c=a[0];a[0]=a[b%a.length];a[b%a.length]=c}}
        bL=function(a){a=a.split("");aL.jl(a,58);aL.NI(a,2);aL.l5(a,35);aL.NI(a,2);aL.jl(a,45);aL.l5(a,4);aL.jl(a,46);return a.join("")};
    ->$L=function(a,b,c){b=void 0===b?"":b;c=void 0===c?"":c;var d=new g.cL(a);a.match(/https:\/\/yt.akamaized.net/)||d.set("alr","yes");c&&d.set(b,bL(c));return d};
    */
    public function getSigDecodeFunctionName($player_html)
    {
        $pattern = '@yt\.akamaized\.net\/\)\s*\|\|\s*.*?\s*c\s*&&\s*d\.set\([^,]+\s*,\s*\([^\)]+\)\(([a-zA-Z0-9$]+)@is';

        if (preg_match($pattern, $player_html, $matches)) {
            $func_name = $matches[1];
            $func_name = preg_quote($func_name);

            return $func_name;
        }

        return null;
    }

    // convert JS code for signature decipher to PHP code
    public function getSigDecodeInstructions($player_html, $func_name)
    {
        // extract code block from that function
        // single quote in case function name contains $dollar sign
        // xm=function(a){a=a.split("");wm.zO(a,47);wm.vY(a,1);wm.z9(a,68);wm.zO(a,21);wm.z9(a,34);wm.zO(a,16);wm.z9(a,41);return a.join("")};
        if (preg_match('/' . $func_name . '=function\([a-z]+\){(.*?)}/', $player_html, $matches)) {

            $js_code = $matches[1];

            // extract all relevant statements within that block
            // wm.vY(a,1);
            if (preg_match_all('/([a-z0-9]{2})\.([a-z0-9]{2})\([^,]+,(\d+)\)/i', $js_code, $matches) != false) {

                // must be identical
                $obj_list = $matches[1];

                //
                $func_list = $matches[2];

                // extract javascript code for each one of those statement functions
                preg_match_all('/(' . implode('|', $func_list) . '):function(.*?)\}/m', $player_html, $matches2, PREG_SET_ORDER);

                $functions = array();

                // translate each function according to its use
                foreach ($matches2 as $m) {

                    if (strpos($m[2], 'splice') !== false) {
                        $functions[$m[1]] = 'splice';
                    } elseif (strpos($m[2], 'a.length') !== false) {
                        $functions[$m[1]] = 'swap';
                    } elseif (strpos($m[2], 'reverse') !== false) {
                        $functions[$m[1]] = 'reverse';
                    }
                }

                // FINAL STEP! convert it all to instructions set
                $instructions = array();

                foreach ($matches[2] as $index => $name) {
                    $instructions[] = array($functions[$name], $matches[3][$index]);
                }

                return $instructions;
            }
        }

        return null;
    }

    public function decodeSignature($signature, $video_html)
    {
        $player_html = $this->getPlayerHtml($video_html);

        $func_name = $this->getSigDecodeFunctionName($player_html);

        // PHP instructions
        $instructions = (array)$this->getSigDecodeInstructions($player_html, $func_name);

        foreach ($instructions as $opt) {

            $command = $opt[0];
            $value = $opt[1];

            if ($command == 'swap') {

                $temp = $signature[0];
                $signature[0] = $signature[$value % strlen($signature)];
                $signature[$value] = $temp;

            } elseif ($command == 'splice') {
                $signature = substr($signature, $value);
            } elseif ($command == 'reverse') {
                $signature = strrev($signature);
            }
        }

        return trim($signature);
    }

    // this is in beta mode!!
    // TODO: move this to its own HttpClient class
    public function stream($id)
    {
        $links = $this->getDownloadLinks($id, "mp4");

        if (count($links) == 0) {
            die("no url found!");
        }

        // grab first available MP4 link
        $url = $links[0]['url'];

        // request headers
        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64; rv:49.0) Gecko/20100101 Firefox/49.0'
        );

        if (isset($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

        // we deal with this ourselves
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // whether request to video success
        $headers = '';
        $headers_sent = false;
        $success = false;

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $data) use (&$headers, &$headers_sent) {

            $headers .= $data;

            // this should be first line
            if (preg_match('@HTTP\/\d\.\d\s(\d+)@', $data, $matches)) {
                $status_code = $matches[1];

                // status=ok or partial content
                if ($status_code == 200 || $status_code == 206) {
                    $headers_sent = true;
                    header(rtrim($data));
                }

            } else {

                // only headers we wish to forward back to the client
                $forward = array('content-type', 'content-length', 'accept-ranges', 'content-range');

                $parts = explode(':', $data, 2);

                if ($headers_sent && count($parts) == 2 && in_array(trim(strtolower($parts[0])), $forward)) {
                    header(rtrim($data));
                }
            }

            return strlen($data);
        });

        // if response is empty - this never gets called
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$headers_sent) {

            if ($headers_sent) {
                echo $data;
                flush();
            }

            return strlen($data);
        });

        $ret = @curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        // if we are still here by now, return status_code
        return true;
    }

    // extract youtube video_id from any piece of text
    public function extractVideoId($str)
    {
        if (preg_match('/[a-z0-9_-]{11}/i', $str, $matches)) {
            return $matches[0];
        }

        return false;
    }

    // selector by format: mp4 360,
    private function selectFirst($links, $selector)
    {
        $result = array();
        $formats = preg_split('/\s*,\s*/', $selector);

        // has to be in this order
        foreach ($formats as $f) {

            foreach ($links as $l) {

                if (stripos($l['format'], $f) !== false || $f == 'any') {
                    $result[] = $l;
                }
            }
        }

        return $result;
    }

    // some of the data may need signature decoding
    public function parseStreamMap($video_html, $video_id)
    {
        $stream_map = array();
        $result = array();

        // http://stackoverflow.com/questions/35608686/how-can-i-get-the-actual-video-url-of-a-youtube-live-stream
        if (preg_match('@url_encoded_fmt_stream_map["\']:\s*["\']([^"\'\s]*)@', $video_html, $matches)) {
            $stream_map = $matches[1];
        } else {

            $gvi = $this->curl("https://www.youtube.com/get_video_info?el=embedded&eurl=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D" . urlencode($video_id) . "&video_id={$video_id}");

            if (preg_match('@url_encoded_fmt_stream_map=([^\&\s]+)@', $gvi, $matches_gvi)) {
                $stream_map = urldecode($matches_gvi[1]);
            }
        }

        if ($stream_map) {
            $parts = explode(",", $stream_map);

            foreach ($parts as $p) {
                $query = str_replace('\u0026', '&', $p);
                parse_str($query, $arr);

                $result[] = $arr;
            }

            return $result;
        }

        // TODO:
        // elseif (strpos($html, 'player-age-gate-content') !== false) { // age-gate
        // youtube must have changed something
        return $result;
    }

    // options | deep_links | append_redirector
    // TODO: make it accept video_html too
    public function getDownloadLinks($video_id, $selector = false)
    {
        // you can input HTML of /watch? page directory instead of id
        $video_id = $this->extractVideoId($video_id);

        $video_html = $this->curl("https://www.youtube.com/watch?v={$video_id}");

        $result = array();
        $url_map = $this->parseStreamMap($video_html, $video_id);

        foreach ($url_map as $arr) {
            $url = $arr['url'];

            if (isset($arr['sig'])) {
                $url = $url . '&signature=' . $arr['sig'];

            } elseif (isset($arr['signature'])) {
                $url = $url . '&signature=' . $arr['signature'];

            } elseif (isset($arr['s'])) {

                $signature = $this->decodeSignature($arr['s'], $video_html);
                $url = $url . '&signature=' . $signature;
            }

            // redirector.googlevideo.com
            //$url = preg_replace('@(\/\/)[^\.]+(\.googlevideo\.com)@', '$1redirector$2', $url);

            $itag = $arr['itag'];
            $format = isset($this->itag_info[$itag]) ? $this->itag_info[$itag] : 'Unknown';

            $result[$itag] = array(
                'url' => $url,
                'format' => $format
            );
        }

        // do we want all links or just select few?
        if ($selector) {
            return $this->selectFirst($result, $selector);
        }

        return $result;
    }

    public function get($link){
		$meta = get_meta_tags($link);
		$res['title'] = @$meta['title'] ?? 'Unknown title';
		$res['description'] = @$meta['description'] ?? 'Unknown description';
		$res['img'] = @$meta['twitter:image'] ?? NULL;
		$res['links'] = $this->getDownloadLinks($link);
		foreach ($links as $key => $val) {
			$tits = urlencode($res['title']);
			$links[$key]['url'].="&title=$tits";
		}
		return $res;
	}
}
class Browser {
	protected $accept;
	protected $userAgent;

	protected $isMobile     = false;
	protected $isAndroid    = null;
	protected $isBlackberry = null;
	protected $isIphone     = null;
	protected $isIpad       = null;
	protected $isOpera      = null;
	protected $isPalm       = null;
	protected $isWindows    = null;
	protected $isGeneric    = null;

	protected $devices = array(
		"android" => "android",
		"blackberry" => "blackberry",
		"iphone" => "(iphone|ipod)",
		"ipad" => "ipad",
		"opera" => "opera mini",
		"palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
		"windows" => "windows ce; (iemobile|ppc|smartphone)",
		"generic" => "(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap)"
	);

	public function __construct() {
		$this->userAgent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$this->accept    = isset( $_SERVER['HTTP_ACCEPT'] ) ? $_SERVER['HTTP_ACCEPT'] : '';

		if (isset($_SERVER['HTTP_X_WAP_PROFILE'])|| isset($_SERVER['HTTP_PROFILE'])) {
			$this->isMobile = true;
		} elseif (strpos($this->accept,'text/vnd.wap.wml') > 0 || strpos($this->accept,'application/vnd.wap.xhtml+xml') > 0) {
			$this->isMobile = true;
		} else {
			foreach ($this->devices as $device => $regexp) {
				if ($this->isDevice($device)) {
					$this->isMobile = true;
				}
			}
		}
	}

	public function __call($name, $arguments) {
		$device = strtolower(substr($name, 2));
		if ($name == "is" . ucfirst($device)) {
			return $this->isDevice($device);
		} else {
			trigger_error("Method $name not defined", E_USER_ERROR);
		}
	}

	public function isMobile() {
		return $this->isMobile;
	}

	protected function isDevice($device) {
		$var    = "is" . ucfirst($device);
		$return = $this->$var === null ? (bool) preg_match("/" . $this->devices[$device] . "/i", $this->userAgent) : $this->$var;

		if ($device != 'generic' && $return == true) {
			$this->isGeneric = false;
		}

		return $return;
	}
}
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
