<?php

namespace eru123\NoEngine;

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
		        $res = fwrite($handle,$data);
		        fclose ($handle);
		        return $res;
	    	} else return self::_fwrite($f,$data,'w');
	    } elseif ($m == 'w') {
	    	if (file_exists($f)) unlink($f);
	    	touch($f);
	        $handle =  fopen($f, "w" ) ;
	        $res = fwrite($handle,$data);
		    fclose ($handle);
		    return $res;
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
		$d = $d ?? __DIR__;
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
	final static function ra_rem($key, array $arr){
		$r = [];
		$kf = self::xpl_ra_map(',','trim',$key);
		if (count($kf) > 1) {
			$s = $arr;
			foreach ($kf as $rk) {
				$s = self::ra_rem($rk,$arr);
			}
			$r = $s;
		} else {
			foreach ($arr as $k => $v){
				if ($key != $k) {
					$r[$k] = $v;
				}
			}
		}
		return $r;
	}
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
		foreach ($a as $k => $v) $h[] = $k;
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
	final static function ms() {// get time in millisecods
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
	final static function m2sec(int $m){
		return ($m*30*24*60*60);
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
	final static function dts(){

		return date("m d Y H:i:s");
	}
	final static function dts_diff($old){
		//old param must be a result of dts() function
		//date format must be "m d Y H:i:s"
		$new 	= self::dts();
		$od 	= explode(' ', $old);
		$ot 	= explode(':', $od[3]);
		$nd 	= explode(' ', $new);
		$nt 	= explode(':', $nd[3]);

		$res 	= 0;

		$res 	+= ($nd[2] > $od[2]) ? self::y2sec	 ($nd[2] - $od[2]) : 0;
		$res 	+= ($nd[0] > $od[0]) ? self::m2sec 	 ($nd[0] - $od[0]) : 0;
		$res 	+= ($nd[1] > $od[1]) ? self::d2sec 	 ($nd[1] - $od[1]) : 0;
		$res 	+= ($nt[0] > $ot[0]) ? self::hr2sec  ($nt[0] - $ot[0]) : 0;
		$res 	+= ($nt[1] > $ot[1]) ? self::min2sec ($nt[1] - $ot[1]) : 0;
		$res 	+= ($nt[2] > $ot[2]) ? 				 ($nt[2] - $ot[2]) : 0;

		return $res;
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
	final static function toAlpha($data){
	    $alphabet =   array('a','b','c','d','e',
	    					'f','g','h','i','j',
	    					'k','l','m','n','o',
	    					'p','q','r','s','t',
	    					'u','v','w','x','y',
	    					'z'
	    					);
	    $alpha_flip = array_flip($alphabet);
	    if($data <= 25){
	      return $alphabet[$data];
	    }
	    elseif($data > 25){
	      $dividend = ($data + 1);
	      $alpha = '';
	      $modulo;
	      while ($dividend > 0){
	        $modulo = ($dividend - 1) % 26;
	        $alpha = $alphabet[$modulo] . $alpha;
	        $dividend = floor((($dividend - $modulo) / 26));
	      } 
	      return $alpha;
	    }
	}
	final static function toNum($data) {
	    $alphabet = array( 'a', 'b', 'c', 'd', 'e',
	                       'f', 'g', 'h', 'i', 'j',
	                       'k', 'l', 'm', 'n', 'o',
	                       'p', 'q', 'r', 's', 't',
	                       'u', 'v', 'w', 'x', 'y',
	                       'z'
	                       );
	    $alpha_flip = array_flip($alphabet);
	    $return_value = -1;
	    $length = strlen($data);
	    for ($i = 0; $i < $length; $i++) {
	        $return_value +=
	            ($alpha_flip[$data[$i]] + 1) * pow(26, ($length - $i - 1));
	    }
	    return $return_value;
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