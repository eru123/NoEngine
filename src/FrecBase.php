<?php

namespace eru123\NoEngine;

class FrecBase extends NoEngine
{
	public 	$dir 	= "./FrecBase/"; //end with '/'
	public	$con 	= 'root', $db, $tb;

	const 	version = 'v3.0';
	const 	main 	= 'main.php';
	const 	users 	= 'users.php';

	public function __construct($dir = '')
	{
		//set_exception_handler('self::FBE');
		if ($dir == '') $dir = $this->dir;
		if ($dir != '') $this->dir = $dir;

		if (!is_dir($dir)) {
			self::_mkdir($dir);
			if (!file_exists($dir . self::users))
				self::_fwrite($dir . self::users, "<?php\n\$data = array();\n\$data['col'] = array('pass','ip','date');\n\$data['pk'] = 'id';\n\$data['fk'] = 'user';\n\$data['lid'] = 0;\n\$data['row'] = array();\n", 'w');
			if (!file_exists($dir . self::main))
				self::_fwrite($dir . self::main, "<?php\n\$data = array();\n", 'w');
			$this->add_user('root', 'root');
		}
	}
	//user
	final protected function is_user($user)
	{ //checks if user exists
		$users = $this->users('row');
		foreach ($users as $id => $u)
			if (isset($u['user']))
				if ($u['user'] == $user) {
					if (!is_dir($this->dir . $user))
						self::_mkdir($this->dir . $user);
					return TRUE;
				}
		return FALSE;
	}
	final protected function users($m)
	{ //get users info
		$p = $this->dir . self::users;
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
	final protected function user($col, $user)
	{ //get user info
		$users = $this->users('row');
		foreach ($users as $id => $u)
			if ($user == $u['user'])
				if (isset($u[$col]))
					return $u[$col];
		return NULL;
	}
	final protected function is_con()
	{ //checks if already login
		if (isset($this->con)) {
			if ($this->is_user($this->con))
				return TRUE;
		}
		return FALSE;
	}
	final protected function upath()
	{ //returns user path
		if (isset($this->con)) {
			return "{$this->dir}{$this->con}/";
		}
	}
	final protected function is_db($user, $db)
	{ //check if db exists
		if (is_dir("{$this->dir}$user/$db"))
			return TRUE;
		return FALSE;
	}
	final public function add_user($user, $pass)
	{ //add user account
		$dir = $this->dir;
		$p = $dir . self::users;
		if ($this->is_user($user) === FALSE) {
			$id = $this->users('lid') + 1;
			if (preg_match("/[\W]/u", $user))
				return FALSE;
			$pass = self::pass($pass);
			$ip = self::get_ip();
			$date = date('M d, Y', time());
			$rw = "\$data['row'][$id] = array('id'=>$id,'user'=>'$user','pass'=>'$pass','ip'=>'$ip','date'=>'$date');\n\$data['lid'] = $id;\n";
			self::_mkdir($dir . $user);
			self::_fwrite($p, $rw);
			return TRUE;
		} else return FALSE;
	}
	final public function con($user, $pass)
	{ //login to db account
		if ($this->is_user($user)) {
			$psk = $this->user('pass', $user);
			if ($this->pass($pass) == $psk) {
				$this->con = $user;
				return TRUE;
			}
		}
		return FALSE;
	}
	final public function del_user($user, $pass)
	{ //deletes a user
		if ($this->is_user($user)) {
			$psk = $this->user('pass', $user);
			if ($this->pass($pass) == $psk) {
				$id = $this->user('id', $user);
				self::_fwrite($this->dir . self::users, "unset(\$data['row'][$id]);\n");
				return TRUE;
			}
		}
		return FALSE;
	}
	//Set db & tb
	final public function db($db)
	{ //select db

		$this->db = $db;
	}
	final public function tb($tb)
	{ //select tb

		$this->tb = $tb;
	}
	final public function select($db, $tb)
	{ //select db and tb

		$this->db = $db;
		$this->tb = $tb;
	}

	//CRUD
	final public function create($m = '', $a = '', $b = '', $c = '', $d = '', $e = '')
	{
		$user = $this->con;
		$dir = $this->upath();
		$db = $this->db;
		$tb = $this->tb;

		if (self::is_str($db) && $this->is_db($user, $db)) {
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
			if (isset($a) && $a != '' && preg_match("/[\w]/", $a) && self::_mkdir($dir . $a))
				return TRUE;
		} elseif ($m == 'tb') {
			//a = tb name
			//b = col
			//c = pk
			//d = fk
			$p = "$db$a.php";
			$c = $c ?? 'id';
			if ($d != '') {
				$d = self::xpl_ra_map(',', 'trim', $d);
				if (count($d) > 0) {
					$d = json_encode($d);
					$d = "json_decode('$d',true)";
				} else $d = '[]';
			} else $d = '[]';
			if ($db_bool && !file_exists($p) && isset($a) && preg_match("/[\w]/", $a) && isset($b) && gettype($b) == 'string') {
				$b = self::xpl_ra_map(',', 'trim', $b);
				$b = json_encode($b);
				$b = "json_decode('$b',true)";
				$res = self::_fwrite($p, "<?php\n\$data['col']=$b;\n\$data['pk']='$c';\n\$data['fk']=$d;\n\$data['lid']=0;\n\$data['row']=[];\n", 'w');
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
					$cols = array_merge($col, [$pk]);
					if (count($fk) > 0) {
						$cols = array_merge($cols, $fk);
						foreach ($fk as $fkeys)
							if (isset($a[$fkeys]) && count($rows) > 0)
								if (self::ra_search_bool(array($fkeys => $a[$fkeys]), $rows))
									return FALSE;
					}
					$res_a = array();
					$a = array_merge($a, [$pk => $id]);
					foreach ($a as $k => $v)
						$a[$k] = urlencode($v);
					foreach ($cols as $c)
						$res_a[$c] = $a[$c] ?? '';
					$res = json_encode($res_a);
					$res = "json_decode('$res',true)";
					$result = NoEngine::_fwrite($tb, "\$data['row'][$id] = $res;\n\$data['lid'] = $id;\n");
					$n = $result ? TRUE : FALSE;
					return $n;
				}
			}
		}
		return FALSE;
	}
	final public function read(string $m = 'row', string $a = '', array $b = array())
	{
		$user = $this->con;
		$dir = $this->upath();
		$db = $this->db;
		$tb = $this->tb;

		if (self::is_str($db) && $this->is_db($user, $db)) {
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
			return rtrim($res, '.php');
		} elseif ($m == 'row') {
			if ($a == 'where') {
				foreach ($b as $k => $v)
					if (!is_array($v)) {
						$b[$k] = urlencode($v);
					} else return array();
				return self::ra_search($b, $this->read('row'));
			} else {
				$data = self::data($tb);
				if (count($data) > 0) {
					foreach ($data['row'] as $k => $v)
						foreach ($v as $vk => $vv)
							$data['row'][$k][$vk] = urldecode($vv);
					return $data['row'];
				}
			}
		} elseif ($m == 'pk') {
			$data = self::data($tb);
			return $data['pk'];
		} elseif ($m == 'fk') {
			$data = self::data($tb);
			return $data['fk'];
		} elseif ($m == 'lid') {
			$data = self::data($tb);
			return $data['lid'];
		} elseif ($m == 'col') {
			$data = self::data($tb);
			return $data['col'];
		}
		return array();
	}
	final public function update($m = '', $a = '', $b = '', $c = '', $d = '', $e = '')
	{
		$user = $this->con;
		$dir = $this->upath();
		$db = $this->db;
		$tb = $this->tb;

		if (self::is_str($db) && $this->is_db($user, $db)) {
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
			if (self::is_word($a) && !is_dir($dir . $a)) {
				rename($dir . $this->db, $dir . $a);
				$this->db($a);
				return TRUE;
			}
		} elseif ($m == 'tb') {
			if (self::is_word($a) && !file_exists($db . $a)) {
				rename($tb, "$db$a.php");
				$this->tb($a);
				return TRUE;
			}
		} elseif ($m == 'col' && self::is_str($a)) {
			$a = explode(',', $a);
			foreach ($a as $k => $v) {
				$v = trim($v);
				$a[$k] = $v;
				if (!self::is_word($v))
					return FALSE;
			}
			$col = self::ra2str($col);
			self::_fwrite($tb, "\$data['col'] = $col;\n");
		} elseif ($m == 'data' && is_array($a) && is_array($b)) {
			//a = rule
			//b = update
			$row = $this->read('row', 'where', $a);
			$row = self::ra_first_row($row);
			$col = $this->read('col');
			$fk = $this->read('fk');
			$pk = $this->read('pk');
			$id = $row[$pk];
			foreach ($fk as $v)
				if (isset($b[$v]))
					if (count($this->read('row', 'where', array($v => $b[$v]))) > 0)
						return FALSE;
			if (array_key_exists($pk, $b)) {
				return FALSE;
			}
			$myId = array($pk => $id);
			$data = array_merge($row, $b);
			foreach ($data as $k => $v) {
				$data[$k] = urlencode($v);
			}
			$cols = array_merge([$pk], $fk, $col);
			$res = [];
			foreach ($cols as $col) {
				if (isset($data[$col])) {
					$res[$col] = $data[$col];
				} else {
					$res[$col] = "";
				}
			}
			$data = json_encode($res);
			$res = self::_fwrite($tb, "\$data['row'][$id] = json_decode('$data',true);\n");
			if ($res)
				return TRUE;
		}
		return FALSE;
	}
	final public function delete($m = '', $a = '', $b = '', $c = '', $d = '', $e = '')
	{
		$user = $this->con;
		$dir = $this->upath();
		$db = $this->db;
		$tb = $this->tb;

		if (self::is_str($db) && $this->is_db($user, $db)) {
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
			if (is_dir($dir . $a) && self::_del($dir . $a))
				return TRUE;
		} elseif ($m == 'tb' && $db_bool) {
			if (file_exists("$db$a.php") && self::_del("$db$a.php"))
				return TRUE;
		} elseif ($m == 'data' && $tb_bool) {
			$row = self::ra_first_row($this->read('row', 'where', $a));
			$pk = $this->read('pk');
			if (is_array($row) && count($row) > 0) {
				if (isset($row[$pk])) {
					$id = $row[$pk];
					$res = self::_fwrite($tb, "unset(\$data['row'][$id]);\n");
					if ($res)
						return TRUE;
				}
			}
		} elseif ($m == 'row' && $tb_bool) {
			$res = self::_fwrite($tb, "\$data['row'] = array();\n");
			if ($res)
				return TRUE;
		}
		return FALSE;
	}
	//Utils
	final public function destroy($dir = '')
	{ //delete db data
		self::_del($this->dir);
		if ($dir == '') $dir = $this->dir;
		if ($dir != '') $this->dir = $dir;
		if (!is_dir($dir)) {
			self::_mkdir($dir);
			if (!file_exists($dir . self::users))
				self::_fwrite($dir . self::users, "<?php\n\$data = array();\n\$data['col'] = array('pass','ip','date');\n\$data['pk'] = 'id';\n\$data['fk'] = 'user';\n\$data['lid'] = 0;\n\$data['row'] = array();\n", 'w');
			if (!file_exists($dir . self::main))
				self::_fwrite($dir . self::main, "<?php\n\$data = array();\n", 'w');
			$this->add_user('root', 'root');
		}
	}
	final public function optimize()
	{ //optimize db
		$res = array();
		$b = 0;
		$a = 0;
		$t = self::ms();
		foreach ($this->users('list') as $user) {
			if (is_dir($this->dir . $user)) {
				$p = "{$this->dir}$user/";
				$dbs = self::_scandir($p);
				foreach ($dbs as $db) {
					$tbs = self::_scandir($db);
					foreach ($tbs as $file) {
						if (file_exists($file)) {
							$b += filesize($file);
							$data = self::data($file);
							$dat = json_encode($data);
							self::_fwrite($file, "<?php\n\$data = json_decode('$dat',true);\n", 'w');
							$a += filesize($file);
						}
					}
				}
			}
		}
		$t = self::ms() - $t;
		$res['size_before'] = self::autobits($b);
		$res['size_after'] = self::autobits($a);
		$res['time'] = ($t / 100) . ' sec';
		return $res;
	}
	final public function size()
	{ //db size
		$b = 0;
		foreach ($this->users('list') as $user) {
			if (is_dir($this->dir . $user))
				foreach (self::_scandir("{$this->dir}$user/") as $db)
					foreach (self::_scandir($db) as $file)
						if (file_exists($file))
							$b += filesize($file);
		}
		return self::autobits($b);
	}
	final public function fetch_time()
	{ //debug rows fetch time
		$r = '';
		$t = self::ms();
		foreach ($this->users('list') as $user) {
			if (is_dir($this->dir . $user)) {
				$p = "{$this->dir}$user/";
				$dbs = self::_scandir($p);
				foreach ($dbs as $db) {
					$tbs = self::_scandir($db);
					foreach ($tbs as $file) {
						if (file_exists($file)) {
							$data = self::data($file);
							$rows = $data['row'];
							foreach ($rows as $pk => $row) {
								$r .= self::ra2str($row);
							}
						}
					}
				}
			}
		}
		$t = self::ms() - $t;
		return ($t / 1000) . ' sec';
	}
}
