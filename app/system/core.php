<?php

declare(strict_types=1);
/**
 * @author suconghou
 * @blog http://blog.suconghou.cn
 * @link https://github.com/suconghou/mvc
 * @version 1.2.2
 */


class app
{
	private static $global;

	public static function start(array $config)
	{
		$err = null;
		self::$global = $config;
		error_reporting(self::get('debug') ? E_ALL : E_ALL & ~E_NOTICE);
		try {
			if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && (count($param = explode('-', ltrim($_SERVER['HTTP_IF_NONE_MATCH'], 'W/'))) === 2)) {
				list($expire, $t) = $param;
				if ($expire > $_SERVER['REQUEST_TIME'] || (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '') + intval($t) > $_SERVER['REQUEST_TIME'])) {
					header('Cache-Control: public, max-age=' . ($expire - $_SERVER['REQUEST_TIME']));
					return header('Expires: ' . gmdate('D, d M Y H:i:s', intval($expire)) . ' GMT', true, 304);
				}
			}
			$cli = PHP_SAPI === 'cli';
			$errHandler = function (throwable $e) use ($cli) {
				if ($cli) {
					echo $e, PHP_EOL;
				} else {
					$err = $e->getTraceAsString();
					$errMsg = $e->getMessage();
					$errCode = $e->getCode();
					$errs = str_replace(PHP_EOL, '</p><p>', $err);
					echo "<div style='margin:2% auto;width:80%;box-shadow:0 0 5px #f00;padding:1%;font:italic 14px/20px Georgia,Times New Roman;word-wrap:break-word;'><p>ERROR({$errCode}) {$errMsg}</p><p>{$errs}</p></div>";
				}
			};
			if ($cli) {
				if (($name = getenv('name')) && ($entry = getenv('entry'))) {
					return self::createPhar($name, $entry);
				}
				$uri = implode('/', $GLOBALS['argv']);
			} else {
				list($uri) = explode('?', $_SERVER['REQUEST_URI'], 2);
			}
			if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
				$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
			}
			$varPath = $config['var_path'] ?? (__DIR__ . DIRECTORY_SEPARATOR);
			if (substr($varPath, 0, 7) === 'phar://') {
				$varPath = str_replace('/' . $_SERVER['SCRIPT_NAME'], '', substr($varPath, 7));
			}
			define('VAR_PATH_LOG', $varPath . 'log' . DIRECTORY_SEPARATOR);
			define('VAR_PATH_HTML', $varPath . 'html' . DIRECTORY_SEPARATOR);
			$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
			$file = self::file($request_method . $uri);
			self::$global['sys.cachefile'] = $file;
			if (is_file($file)) {
				$expire = filemtime($file);
				if ($_SERVER['REQUEST_TIME'] < $expire) {
					$t = $expire - $_SERVER['REQUEST_TIME'];
					header('Expires: ' . gmdate('D, d M Y H:i:s', $expire) . ' GMT');
					header('Cache-Control: public, max-age=' . $t);
					header('X-Cache: Hit');
					header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME']) . ' GMT');
					header('ETag: W/' . ($_SERVER['REQUEST_TIME'] + $t) . '-' . $t);
					return readfile($file);
				}
				unlink($file);
			}
			// 普通路由执行器,交由app::run执行,app::run只能执行普通路由
			$execHandler = function (array $r) {
				return self::run($r);
			};
			set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
				throw new Exception(sprintf('%s%s', $errstr, $errfile ? (' in file ' . $errfile . ($errline ? "({$errline})" : '')) : ''), $errno);
			});
			route::register(...$config['lib_path'] ?? [__DIR__ . DIRECTORY_SEPARATOR]);
			// 进行正则路由匹配,未匹配到fallback到普通路由
			route::notfound($execHandler);
			return route::run($uri, $request_method);
		} catch (Throwable $e) {
			$err = $e;
			$errfound = self::get('errfound');
			$errno = $e->getCode();
			$errstr = substr($err->getMessage(), 0, 200);
			try {
				headers_sent() || header('Error-At:' . preg_replace('/\s+/', ' ', $errstr), true, in_array($errno, [400, 401, 403, 404, 500, 502, 503, 504], true) ? $errno : 500);
				if ($errno === 404) {
					$notfound = self::get('notfound');
					return ($notfound ?? $errfound ?? $errHandler)($e, $cli);
				}
				return ($errfound ?? $errHandler)($e, $cli);
			} catch (Throwable $e) {
				$err = $e;
				echo $e;
			}
		} finally {
			if ($err) {
				$errstr = $err->getMessage();
				$errfile = $err->getFile();
				$errline = $err->getLine();
				$errno = $err->getCode();
				$errormsg = sprintf('ERROR(%d) %s%s%s', $errno, $errstr, $errfile ? " in {$errfile}" : '', $errline ? " on line {$errline}" : '');
				$errno === 404 ? self::log($errormsg, 'DEBUG', strval($errno)) : self::log($errormsg, 'ERROR');
			}
		}
	}

	private static function createPhar(string $name, string $entry)
	{
		$path = ROOT . $name;
		$phar = new Phar($path, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::SKIP_DOTS, $name);
		$phar->startBuffering();
		$dirObj = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ROOT)), '/^[\w\/\-\\\.:]+\.php$/i');
		foreach ($dirObj as $file => $fileinfo) {
			$phar->addFromString(substr($file, strlen(ROOT)), php_strip_whitespace($file));
		}
		$phar->setStub("#!/usr/bin/env php" . PHP_EOL . "<?php Phar::mapPhar('$name');require 'phar://{$name}/{$entry}';__HALT_COMPILER();");
		$phar->stopBuffering();
		echo "{$phar->count()} files stored in {$path}" . PHP_EOL;
	}

	//参数必须是数组,第一个为控制器,第二个为方法,后面的为参数,他确保了被调用的控制器是单例的,不重复实例化
	public static function run(array $r)
	{
		if (empty($r[0])) {
			$r[0] = 'home';
		} else if (!preg_match('/^[a-z][\w\\\-]{0,20}$/i', $r[0])) {
			throw new Exception(sprintf('request controller %s error', $r[0]), 404);
		}
		if (empty($r[1])) {
			$r[1] = 'index';
		} else if (!preg_match('/^[a-z][\w\\\-]{0,20}$/i', $r[1])) {
			throw new Exception(sprintf('request action %s:%s error', $r[0], $r[1]), 404);
		}
		if (!method_exists($r[0], $r[1]) || !method_exists($r[0], '__invoke')) {
			throw new Exception(sprintf('request action %s:%s not exist', $r[0], $r[1]), 404);
		}
		if (empty(self::$global['sys.' . $r[0]])) {
			self::$global['sys.' . $r[0]] = new $r[0]($r);
		}
		$instance = self::$global['sys.' . $r[0]];
		if (!is_callable([$instance, $r[1]])) {
			throw new Exception(sprintf('request action %s:%s not callable', $r[0], $r[1]), 404);
		}
		try {
			return call_user_func_array([$instance, $r[1]], array_slice($r, 2));
		} catch (Throwable $e) {
			return $instance($e);
		}
	}

	public static function log($msg, string $type = 'DEBUG', string $file = '')
	{
		if (is_writable(VAR_PATH_LOG) && (self::get('debug') || (($type = strtoupper($type)) === 'ERROR'))) {
			$path = VAR_PATH_LOG . ($file ? $file : date('Y-m-d')) . '.log';
			$msg = $type . '-' . date('Y-m-d H:i:s') . ' ==> ' . (is_scalar($msg) ? $msg : PHP_EOL . print_r($msg, true)) . PHP_EOL;
			return error_log($msg, 3, $path);
		}
	}

	public static function file(string $r = "", bool $delete = false)
	{
		$file = sprintf('%s%u.html', VAR_PATH_HTML, crc32(strtolower($r)));
		return $delete ? (is_file($file) && unlink($file)) : $file;
	}
	public static function cache(int $s = 0)
	{
		header('Expires: ' . gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME'] + $s) . ' GMT');
		header("Cache-Control: public, max-age={$s}");
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $_SERVER['REQUEST_TIME']) . ' GMT');
		return header('ETag: W/' . ($_SERVER['REQUEST_TIME'] + $s) . '-' . $s);
	}
	public static function get(string $key, $default = null)
	{
		return self::$global[$key] ?? $default;
	}
	public static function set(string $key, $value)
	{
		self::$global[$key] = $value;
		return self::$global;
	}
	public static function conf(string $key = '', $default = null, string $cfgfile = 'config.php')
	{
		$config = self::$global[$cfgfile] ?? (self::$global[$cfgfile] = include $cfgfile);
		if ($key = array_filter(explode('.', $key, 9), 'strlen')) {
			foreach ($key as $item) {
				if (is_array($config) && isset($config[$item])) {
					$config = $config[$item];
				} else {
					return $default;
				}
			}
		}
		return $config;
	}
	public static function on(string $event, closure $task)
	{
		return self::$global['event'][$event] = $task;
	}
	public static function off(string $event)
	{
		unset(self::$global['event'][$event]);
	}
	public static function emit(string $event, $args = [])
	{
		return empty(self::$global['event'][$event]) ?: call_user_func_array(self::$global['event'][$event], is_array($args) ? $args : [$args]);
	}
	public static function __callStatic(string $fn, array $args)
	{
		if (isset(self::$global['event'][$fn])) {
			return call_user_func_array(self::$global['event'][$fn], $args);
		}
		throw new BadMethodCallException("call error static method {$fn}", 500);
	}
}

class route
{
	private static $routes = [];
	private static $notfound;
	static function u(string $path = '', $query = null, $host = null): string
	{
		$prefix = '';
		if ($host === true) {
			$protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? "https" : "http";
			$host =  $_SERVER['HTTP_HOST'] ?? '';
			$prefix = "{$protocol}://{$host}";
		} else if ($host) {
			$prefix = $host;
		}
		if (is_array($query)) {
			$query = http_build_query($query);
		}
		if ($query) {
			return "{$prefix}{$path}?{$query}";
		}
		return "{$prefix}{$path}";
	}
	static function to(string $url, int $timeout = 0)
	{
		if (in_array($timeout, [0, 301, 302, 303, 307, 308], true)) {
			header("Location:{$url}", true, $timeout);
		} else {
			header("Refresh:{$timeout};url={$url}", true, 302);
		}
		exit(header('Cache-Control:no-cache, no-store, max-age=0, must-revalidate'));
	}
	static function get(string $regex, $fn)
	{
		self::add($regex, $fn, ['GET']);
	}
	static function post(string $regex, $fn)
	{
		self::add($regex, $fn, ['POST']);
	}
	static function put(string $regex, $fn)
	{
		self::add($regex, $fn, ['PUT']);
	}
	static function delete(string $regex, $fn)
	{
		self::add($regex, $fn, ['DELETE']);
	}
	static function head(string $regex, $fn)
	{
		self::add($regex, $fn, ['HEAD']);
	}
	static function options(string $regex, $fn)
	{
		self::add($regex, $fn, ['OPTIONS']);
	}
	static function any(string $regex, $fn, array $methods = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'])
	{
		self::add($regex, $fn, $methods);
	}
	static function add(string $regex, $fn, array $methods)
	{
		self::$routes[] = [$regex, $fn, $methods];
	}
	static function notfound($fn)
	{
		self::$notfound = $fn;
	}
	public static function register(string ...$dirs)
	{
		spl_autoload_register(function ($name) use ($dirs) {
			$name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
			foreach ($dirs as $dir) {
				$file = "{$dir}{$name}.php";
				if (is_file($file)) {
					require_once $file;
					if (class_exists($name, false)) {
						return true;
					}
				}
			}
			return false;
		});
	}
	// 调用此方法,上层需try
	static function run(string $uri, string $m)
	{
		$r = array_values(array_filter(explode('/', $uri, 9), 'strlen'));
		$uri = '/' . implode('/', $r);
		$ret = self::match($uri, $m);
		self::$routes = [];
		if ($ret) {
			list($url, $params, $fn) = $ret;
			return self::call($fn, [], $params);
		}
		if (!self::$notfound) {
			self::$notfound = function () {
				throw new Exception('Not Found', 404);
			};
		}
		return self::call(self::$notfound, [$r], []);
	}
	private static function match(string $uri, string $m)
	{
		foreach (self::$routes as list($regex, $fn, $methods)) {
			if (in_array($m, $methods, true) && preg_match("/^{$regex}$/", $uri, $matches)) {
				$url = array_shift($matches);
				return [$url, $matches, $fn];
			}
		}
		return false;
	}
	// fn 可能是个字符串函数名,可能是个closure,可能是个数组
	private static function call($fn, array $ctx, array $params)
	{
		if (is_array($fn)) {
			return app::run(array_merge($fn, $ctx, $params));
		}
		return call_user_func_array($fn, array_merge($ctx, $params));
	}
}

class request
{
	public static function post($key = null, $default = null, string $clean = '')
	{
		return self::getVar($_POST, $key, $default, $clean);
	}
	public static function get($key = null, $default = null, string $clean = '')
	{
		return self::getVar($_GET, $key, $default, $clean);
	}
	public static function param($key = null, $default = null, string $clean = '')
	{
		return self::getVar($_REQUEST, $key, $default, $clean);
	}
	public static function server($key = null, $default = null, string $clean = '')
	{
		return self::getVar($_SERVER, $key, $default, $clean);
	}
	public static function cookie($key = null, $default = null, string $clean = '')
	{
		return self::getVar($_COOKIE, $key, $default, $clean);
	}
	public static function session($key = null, $default = null, string $clean = '')
	{
		isset($_SESSION) || session_start();
		return self::getVar($_SESSION, $key, $default, $clean);
	}
	public static function input(bool $json = true, $key = null, $default = null)
	{
		$str = file_get_contents('php://input');
		$json ? ($data = json_decode($str, true)) : parse_str($str, $data);
		return $key ? ($data[$key] ?? $default) : $data;
	}
	public static function ip($default = null)
	{
		return  $_SERVER['REMOTE_ADDR'] ?? $default;
	}
	public static function ua($default = null)
	{
		return  $_SERVER['HTTP_USER_AGENT'] ?? $default;
	}
	public static function refer($default = null)
	{
		return  $_SERVER['HTTP_REFERER'] ?? $default;
	}
	public static function https()
	{
		return isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off');
	}
	public static function is(string $m = null, closure $callback = null)
	{
		$t = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		return $m ? (($t === strtoupper($m)) ? ($callback ? $callback() : true) : false) : $t;
	}
	public static function verify(array $rule, $callback = false, $post = true)
	{
		$keys = [];
		$data = $post === true ? $_POST : (is_array($post) ? $post : $_REQUEST);
		foreach ($rule as $key => $value) {
			$keys[] = is_int($key) ? $value : $key;
		}
		foreach ($data as $key => $value) {
			if (!in_array($key, $keys)) {
				unset($data[$key]);
			}
		}
		foreach ($keys as $key) {
			$data[$key] =  $data[$key] ?? null;
		}
		return validate::verify($rule, $data, $callback);
	}
	public static function getVar(&$origin, $var, $default = null, string $clean = '')
	{
		if ($var) {
			if (is_array($var)) {
				$data = [];
				foreach ($var as $k) {
					$data[$k] = isset($origin[$k]) ? ($clean ? self::clean($origin[$k], $clean) : $origin[$k]) : $default;
				}
				return $data;
			}
			return isset($origin[$var]) ? ($clean ? self::clean($origin[$var], $clean) : $origin[$var]) : $default;
		}
		return $origin;
	}
	public static function clean($val, string $type = '')
	{
		switch ($type) {
			case 'int':
				return intval($val);
			case 'float':
				return floatval($val);
			case 'string':
				return trim(strval($val));
			case 'xss':
				return filter_var(htmlspecialchars(strip_tags($val), ENT_QUOTES), FILTER_SANITIZE_STRING);
			case 'html':
				return trim(strip_tags($val));
			case 'en':
				return preg_replace('/[\x80-\xff]/', '', $val);
			default:
				return $type ? sprintf($type, $val) : trim($val);
		}
	}
}

class validate
{
	public static function verify(array $rule, array $data, $callback = false)
	{
		try {
			$switch = [];
			foreach ($rule as $k => &$item) {
				if (isset($data[$k])) {
					if (is_array($item)) {
						foreach ($item as $type => $msg) {
							if ($msg instanceof closure) {
								$data[$k] = $msg($data[$k], $k);
							} else if (is_array($msg)) {
								if (!in_array($data[$k], $msg, true)) {
									throw new Exception($type ?: "{$k} error", -22);
								}
							} else if (is_int($type)) {
								$switch[$k] = $msg;
							} else if (!self::check($data[$k], $type)) {
								throw new Exception($msg ?: "{$k} error", -23);
							}
						}
					} else if ($item instanceof closure) {
						$data[$k] = $item();
					} else {
						$data[$k] = $item;
					}
				} else if ($item instanceof closure) {
					$data[$k] = $item();
				} else if (!is_array($item)) {
					$data[$k] = $item;
				} else if (isset($item['require'])) {
					throw new Exception($item['require'] ?: "{$k} is required", -20);
				} else if (isset($item['required'])) {
					throw new Exception($item['required'] ?: "{$k} is required", -21);
				} else if (isset($item['default'])) {
					$data[$k] = $item['default'] instanceof closure ? $item['default']() : $item['default'];
				}
			}
		} catch (Throwable $e) {
			if ($callback === false) {
				throw $e;
			}
			$data = ['code' => $e->getCode(), 'msg' => $e->getMessage()];
			return $callback ? (($callback instanceof closure) ? $callback($data, $e) : json($data)) : false;
		}
		foreach ($switch as $from => $to) {
			$data[$to] = $data[$from];
			unset($data[$from]);
		}
		return $data;
	}
	private static function check($item, string $type)
	{
		if (strpos($type, '=') && (list($key, $val) = explode('=', $type, 2))) {
			switch ($key) {
				case 'minlength':
					return strlen($item) >= $val;
				case 'maxlength':
					return strlen($item) <= $val;
				case 'length':
					return strlen($item) == $val;
				case 'eq':
					return trim($item) === trim($val);
				case 'eqs':
					return strtolower(trim($item)) === strtolower(trim($val));
				case 'set':
					return in_array($item, explode(',', $val), true);
				default:
					return preg_match($type, $item);
			}
		} else {
			switch ($type) {
				case 'required':
					return $item;
				case 'require':
					return $item === 0.0 || $item === 0 || $item === '0' || $item;
				case 'default':
					return true;
				case 'int':
					return filter_var($item, FILTER_VALIDATE_INT) !== false;
				case 'number':
					return is_numeric($item);
				case 'scalar':
					return is_scalar($item);
				case 'email':
					return self::email($item);
				case 'username':
					return self::username($item);
				case 'password':
					return self::password($item);
				case 'phone':
					return self::phone($item);
				case 'url':
					return self::url($item);
				case 'ip':
					return self::ip($item);
				case 'idcard':
					return self::idcard($item);
				default:
					return preg_match($type, $item);
			}
		}
	}
	public static function email(string $email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	public static function phone(string $phone)
	{
		return preg_match("/^1\d{10}$/", $phone);
	}
	public static function url(string $url)
	{
		return filter_var($url, FILTER_VALIDATE_URL);
	}
	public static function ip(string $ip)
	{
		return filter_var($ip, FILTER_VALIDATE_IP);
	}
	//中国大陆身份证号(15位或18位)
	public static function idcard(string $id)
	{
		return preg_match('/^\d{15}(\d\d[0-9xX])?$/', $id);
	}
	//字母数字汉字,不能全是数字
	public static function username(string $username)
	{
		return is_numeric($username) ? false : preg_match('/^[\w\x{4e00}-\x{9fa5}]{3,20}$/u', $username);
	}
	//数字/大写字母/小写字母/标点符号组成，四种都必有，8位以上
	public static function password(string $pass)
	{
		return preg_match('/^(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/', $pass);
	}
}

class db
{
	final private static function id(): int
	{
		static $id = 0;
		return ++$id;
	}

	final public static function getVar(string $sql)
	{
		return self::exec($sql, [], 'fetchColumn');
	}

	final public static function getLine(string $sql)
	{
		return self::exec($sql, [], 'fetch');
	}

	final public static function getData(string $sql)
	{
		return self::exec($sql, [], 'fetchAll');
	}

	final public static function runSql(string $sql)
	{
		return self::exec($sql, [], '');
	}

	final public static function insert(array $data, string $table = '', bool $ignore = false, bool $replace = false)
	{
		$sql = sprintf('%s %sINTO %s %s', $replace ? 'REPLACE' : 'INSERT', $ignore ? 'IGNORE ' : '', $table ?: static::table(), self::values($data));
		return self::exec($sql, $data);
	}

	final public static function replace(array $data, string $table = '')
	{
		return self::insert($data, $table, false, true);
	}

	final public static function delete(array $where = [], string $table = '')
	{
		$sql = sprintf('DELETE FROM %s%s', $table ?: static::table(), self::condition($where));
		return self::exec($sql, $where);
	}

	final public static function find(array $where = [], string $table = '', string $col = '*', array $orderLimit = [], $fetch = 'fetchAll')
	{
		$sql = sprintf('SELECT %s FROM %s%s%s', $col, $table ?: static::table(), self::condition($where), $orderLimit ? self::orderLimit($orderLimit) : '');
		return self::exec($sql, $where, $fetch);
	}

	final public static function findOne(array $where = [], string $table = '', string $col = '*', array $orderLimit = [1], string $fetch = 'fetch')
	{
		return self::find($where, $table, $col, $orderLimit, $fetch);
	}

	final public static function findVar(array $where = [], string $table = '', string $col = 'COUNT(1)', array $orderLimit = [1])
	{
		return self::find($where, $table, $col, $orderLimit, 'fetchColumn');
	}

	final public static function findPage(array $where = [], string $table = '', string $col = '*', int $page = 1, int $limit = 20, array $order = [])
	{
		$total = intval(self::findVar($where, $table));
		$pages = ceil($total / $limit);
		$list = self::find($where, $table, $col, [($page - 1) * $limit => intval($limit)] + $order);
		return ['list' => $list, 'pages' => $pages, 'total' => $total, 'current' => $page, 'prev' => min($pages, max(1, $page - 1)), 'next' => min($pages, $page + 1)];
	}

	final public static function update(array $where, array $data, string $table = '')
	{
		$sql = sprintf('UPDATE %s SET %s%s', $table ?: static::table(), self::values($data, true), self::condition($where));
		return self::exec($sql, $data + $where);
	}

	final public static function query(array ...$v)
	{
		return array_map(function ($item) {
			return self::exec(...$item);
		}, $v);
	}

	final public static function init(array $dbConfig): PDO
	{
		$options = [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_TIMEOUT => 3, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_STRINGIFY_FETCHES => false];
		return new PDO($dbConfig['dsn'], $dbConfig['user'] ?? '', $dbConfig['pass'] ?? '', $options);
	}

	final public static function lastId()
	{
		return static::ready()->lastInsertId();
	}

	public static function ready(): PDO
	{
		static $_pdo;
		if (empty($_pdo)) {
			$_pdo = self::init(app::get('db'));
		}
		return $_pdo;
	}

	final public static function exec(string $sql, array $params = [], $fetch = '')
	{
		$pdo = static::ready();
		if (empty($params)) {
			return $fetch ? (is_string($fetch) ? $pdo->query($sql)->$fetch() : $pdo->query($sql)) : $pdo->exec($sql);
		}
		$stm = $pdo->prepare($sql);
		$rs = $stm->execute($params);
		return $fetch ? (is_string($fetch) ? $stm->$fetch() : $stm) : $rs;
	}

	public static function table(): string
	{
		return static::class;
	}

	final public static function condition(array &$where, string $prefix = 'WHERE'): string
	{
		$keys = [];
		foreach (array_filter(array_keys($where)) as $item) {
			$x = array_values(array_filter(explode(' ', $item)));
			$n = $x[0];
			$verb = array_slice($x, 1);
			$a = is_array($where[$item]);
			$marks = [];
			if ($x[0] === '!') {
				$n = $x[1];
				$verb = array_slice($x, 2);
				$v = $where[$item];
				$marks = $v;
			} elseif ($x[0][0] === '!') {
				$n = substr($x[0], 1);
				$v = $where[$item];
				$marks = $v;
			} else {
				if ($a) {
					$i = 0;
					foreach ($where[$item] as $t) {
						$q = "_{$n}_" . $i++;
						$marks[] = ":{$q}";
						$where[$q] = $t;
					}
				} else {
					$t = $n . '_' . self::id();
					$v = ":{$t}";
					$where[$t] = $where[$item];
				}
			}
			unset($where[$item]);
			$keys[] = sprintf('`%s` %s %s', $n, $verb ? implode(' ', $verb) : ($a ? 'IN' : '='), $a ? sprintf('(%s)', implode(',', $marks)) : $v);
		}
		$condition = $keys ? implode(sprintf(' %s ', $where[0] ?? 'AND'), $keys) : '';
		unset($where[0], $keys);
		return $condition ? sprintf('%s(%s)', $prefix ? " {$prefix} " : '', $condition) : '';
	}

	final public static function values(array &$data, bool $set = false, string $table = ''): string
	{
		$keys = [];
		foreach (array_keys($data) as $item) {
			$k = trim($item);
			$n = ltrim($k, '!');
			$v = trim($n);
			if ($k !== $n) {
				$keys[] = [$v, $data[$item]];
			} else {
				$v_ = "{$v}_" . self::id();
				$keys[] = [$v, ":{$v_}"];
				$data[$v_] = $data[$item];
			}
			unset($data[$item]);
		}
		return $set ? implode(',', array_map(function ($x) {
			return sprintf('`%s` = %s', $x[0], $x[1]);
		}, $keys)) : sprintf('%s(%s) VALUES (%s)', $table ? " `{$table}` " : '', implode(',', array_map(function ($x) {
			return sprintf('`%s`', $x[0]);
		}, $keys)), implode(',', array_map(function ($x) {
			return $x[1];
		}, $keys)));
	}

	final public static function orderLimit(array $orderLimit, array $limit = []): string
	{
		$orderLimit = array_filter($orderLimit, function ($x) use ($orderLimit, &$limit) {
			if (is_int($x) || ctype_digit($x)) {
				$k = array_search($x, $orderLimit, true);
				$limit = [$k, $x];
				return false;
			} else {
				return true;
			}
		});
		$limit = $limit ? " LIMIT " . implode(',', $limit) : '';
		$orderLimit ? (array_walk($orderLimit, function (&$v, $k) {
			$v = sprintf('%s %s', $k, is_string($v) ? $v : ($v ? 'ASC' : 'DESC'));
		})) : '';
		return sprintf('%s%s', $orderLimit ? ' ORDER BY ' . implode(',', $orderLimit) : '', $limit);
	}

	final public function __call(string $fn, array $args)
	{
		return self::__callStatic($fn, $args);
	}

	final public static function __callStatic(string $fn, array $args)
	{
		$pdo = static::ready();
		if (method_exists($pdo, $fn)) {
			return call_user_func_array([$pdo, $fn], $args);
		}
		throw new BadMethodCallException("method {$fn} not found in class " . static::class, 500);
	}
}

function template(string $v, array $_data_ = null, $callback = null, string $_path_ = '')
{
	$_path_ = $_path_ ?: app::get('view_path', '');
	if (is_int($callback) && $callback > 1) {
		$t = $callback;
		$callback = function ($buffer) use ($t) {
			echo $buffer;
			if (is_writable(VAR_PATH_HTML)) {
				if ($file = app::get('sys.cachefile')) {
					file_put_contents($file, $buffer) && touch($file, $_SERVER['REQUEST_TIME'] + $t);
				}
			}
		};
	}
	if ((is_file($_v_ = $_path_ . $v . '.php')) || (is_file($_v_ = $_path_ . $v))) {
		(is_array($_data_) && !empty($_data_)) && extract($_data_);
		if ($callback) {
			ob_start() && include $_v_;
			$contents = ob_get_contents();
			return (ob_end_clean() && ($callback instanceof closure)) ? $callback($contents) : $contents;
		}
		return include $_v_;
	}
	throw new Exception("file {$_v_} not found", 404);
}

function session($key, $val = null, bool $delete = false)
{
	isset($_SESSION) || session_start();
	if (is_null($val)) {
		return $delete ? (bool) array_map(function ($k) {
			unset($_SESSION[$k]);
		}, is_array($key) ? $key : [$key]) : request::session($key, null);
	}
	return $_SESSION[$key] = $val;
}
function cookie($key, $val = null)
{
	if (is_null($val)) {
		return request::cookie($key, null);
	}
	return setcookie(...func_get_args());
}
function json(array $data, string $callback = '')
{
	$data = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	$data = $callback ? $callback . "(" . $data . ")" : $data;
	headers_sent() || header('Content-Type: application/' . ($callback ? 'javascript' : 'json') . ';charset=utf-8');
	exit($data);
}
