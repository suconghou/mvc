<?php

declare(strict_types=1);
/**
 * @author suconghou
 * @blog http://blog.suconghou.cn
 * @link https://github.com/suconghou/mvc
 * @version 1.2.6
 */


class app
{
	private static $global;

	public static function start(array $config)
	{
		$err = null;
		self::$global = $config;
		error_reporting(self::get('debug') ? E_ALL : E_ALL & ~E_NOTICE);
		$cli = PHP_SAPI === 'cli';
		try {
			if (!$cli && isset($_SERVER['HTTP_IF_NONE_MATCH']) && (count($param = explode('-', ltrim($_SERVER['HTTP_IF_NONE_MATCH'], 'W/'))) === 2)) {
				[$expire, $t] = $param;
				if ($expire > $_SERVER['REQUEST_TIME'] || (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '') + intval($t) > $_SERVER['REQUEST_TIME'])) {
					header('Cache-Control: public, max-age=' . ($expire - $_SERVER['REQUEST_TIME']));
					return header('Expires: ' . gmdate('D, d M Y H:i:s', intval($expire)) . ' GMT', true, 304);
				}
			}
			$varPath = $config['var_path'] ?? (__DIR__ . DIRECTORY_SEPARATOR);
			define('VAR_PATH_LOG', $varPath . 'log' . DIRECTORY_SEPARATOR);
			define('VAR_PATH_HTML', $varPath . 'html' . DIRECTORY_SEPARATOR);
			if ($cli) {
				$uri = implode('/', $GLOBALS['argv']);
			} else {
				[$uri] = explode('?', $_SERVER['REQUEST_URI'], 2);
			}
			if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
				$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
			}
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
			set_error_handler(static fn (int $errno, string $errstr, string $errfile, int $errline) => throw new ErrorException($errstr, $errno, 1, $errfile, $errline));
			route::register(...$config['lib_path'] ?? [__DIR__ . DIRECTORY_SEPARATOR]);
			// 进行正则路由匹配,未匹配到fallback到普通路由
			route::notfound(static fn (array $r) => self::run($r));
			return route::run($uri, $request_method);
		} catch (Throwable $e) {
			$err = $e;
			$errfound = self::get('errfound');
			$errno = $e->getCode();
			$errstr = substr($err->getMessage(), 0, 200);
			$errHandler = static function (Throwable $e, bool $cli) {
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
				$errfile = $err->getFile();
				$errline = $err->getLine();
				$errno = $err->getCode();
				$errormsg = sprintf('ERROR(%d) %s%s%s', $errno, $err->getMessage(), $errfile ? " in {$errfile}" : '', $errline ? " on line {$errline}" : '');
				$errno === 404 ? self::log($errormsg, 'DEBUG', strval($errno)) : self::log($errormsg, 'ERROR');
			}
		}
	}

	//参数必须是数组,第一个为控制器,第二个为方法,后面的为参数,他确保了被调用的控制器是单例的,不重复实例化
	public static function run(array $r)
	{
		if (empty($r[0])) {
			$r[0] = 'home';
		} else if (!preg_match('/^[a-z][\w\\\-]{0,20}$/i', $r[0])) {
			throw new InvalidArgumentException(sprintf('request controller %s error', $r[0]), 404);
		}
		if (empty($r[1])) {
			$r[1] = 'index';
		} else if (!preg_match('/^[a-z][\w\-]{0,20}$/i', $r[1])) {
			throw new InvalidArgumentException(sprintf('request action %s:%s error', $r[0], $r[1]), 404);
		}
		if (!method_exists($r[0], $r[1]) || !method_exists($r[0], '__invoke')) {
			throw new InvalidArgumentException(sprintf('request action %s:%s not exist', $r[0], $r[1]), 404);
		}
		if (empty(self::$global['sys.' . $r[0]])) {
			self::$global['sys.' . $r[0]] = new $r[0]($r);
		}
		$instance = self::$global['sys.' . $r[0]];
		if (!is_callable([$instance, $r[1]])) {
			throw new InvalidArgumentException(sprintf('request action %s:%s not callable', $r[0], $r[1]), 404);
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
			$path = VAR_PATH_LOG . ($file ?: date('Y-m-d')) . '.log';
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
	public static function on(string $event, callable $task)
	{
		return self::$global['event'][$event] = $task;
	}
	public static function off(string $event)
	{
		unset(self::$global['event'][$event]);
	}
	public static function emit(string $event, array $args = [])
	{
		return empty(self::$global['event'][$event]) ?: call_user_func_array(self::$global['event'][$event], $args);
	}
	public static function __callStatic(string $fn, array $args)
	{
		return call_user_func_array(self::$global['event'][$fn] ?? static fn () => throw new BadMethodCallException($fn, 500), $args);
	}
}

class route
{
	private static $routes = [];
	private static $notfound;
	public static function u(string $path = '', array|string $query = '', bool|string $host = ''): string
	{
		$prefix = '';
		if ($host === true) {
			$protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) !== 'off')) ? "https" : "http";
			$host = $_SERVER['HTTP_HOST'] ?? '';
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
	public static function to(string $url, int $timeout = 0)
	{
		if (in_array($timeout, [0, 301, 302, 303, 307, 308], true)) {
			header("Location:{$url}", true, $timeout);
		} else {
			header("Refresh:{$timeout};url={$url}", true, 302);
		}
		exit(header('Cache-Control:no-cache, no-store, max-age=0, must-revalidate'));
	}
	public static function get(string $regex, array|string|callable $fn)
	{
		self::add($regex, $fn, 'GET');
	}
	public static function post(string $regex, array|string|callable $fn)
	{
		self::add($regex, $fn, 'POST');
	}
	public static function put(string $regex, array|string|callable $fn)
	{
		self::add($regex, $fn, 'PUT');
	}
	public static function patch(string $regex, array|string|callable $fn)
	{
		self::add($regex, $fn, 'PATCH');
	}
	public static function delete(string $regex, array|string|callable $fn)
	{
		self::add($regex, $fn, 'DELETE');
	}
	public static function any(string $regex, array|string|callable $fn, array $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])
	{
		array_walk($methods, static fn (string $m) => self::add($regex, $fn, $m));
	}
	public static function add(string $regex, array|string|callable $fn, string $method)
	{
		self::$routes[$method] ??= [];
		self::$routes[$method][] = [$regex, $fn];
	}
	public static function notfound(array|string|callable $fn)
	{
		self::$notfound = $fn;
	}
	public static function register(string ...$dirs)
	{
		spl_autoload_register(static function ($name) use ($dirs) {
			$name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
			foreach ($dirs as $dir) {
				if (is_file($file = "{$dir}{$name}.php")) {
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
	public static function run(string $uri, string $m)
	{
		$r = array_values(array_filter(explode('/', $uri, 9), 'strlen'));
		$uri = '/' . implode('/', $r);
		$ret = self::match($uri, $m);
		self::$routes = [];
		if ($ret) {
			[$_, $params, $fn] = $ret;
			return self::call($fn, [], $params);
		}
		if (!self::$notfound) {
			self::$notfound = static fn () => throw new BadFunctionCallException('Not Found', 404);
		}
		return self::call(self::$notfound, [$r], []);
	}
	private static function match(string $uri, string $m)
	{
		foreach (self::$routes[$m] ?? [] as [$regex, $fn]) {
			if (preg_match("#^{$regex}$#", $uri, $matches)) {
				$url = array_shift($matches);
				return [$url, $matches, $fn];
			}
		}
		return false;
	}
	// fn 可能是个字符串函数名,可能是个closure,可能是个数组
	private static function call(array|string|callable $fn, array $ctx, array $params)
	{
		if (is_array($fn)) {
			return app::run(array_merge($fn, $ctx, $params));
		}
		return call_user_func_array($fn, array_merge($ctx, $params));
	}
}

class request
{
	public static function post(array|string $key = '', $default = null, string $clean = '')
	{
		return self::getVar($_POST, $key, $default, $clean);
	}
	public static function get(array|string $key = '', $default = null, string $clean = '')
	{
		return self::getVar($_GET, $key, $default, $clean);
	}
	public static function param(array|string $key = '', $default = null, string $clean = '')
	{
		return self::getVar($_REQUEST, $key, $default, $clean);
	}
	public static function server(array|string $key = '', $default = null, string $clean = '')
	{
		return self::getVar($_SERVER, $key, $default, $clean);
	}
	public static function cookie(array|string $key = '', $default = null, string $clean = '')
	{
		return self::getVar($_COOKIE, $key, $default, $clean);
	}
	public static function session(array|string $key = '', $default = null, string $clean = '')
	{
		isset($_SESSION) || session_start();
		return self::getVar($_SESSION, $key, $default, $clean);
	}
	public static function input(bool $json = true, string|int $key = '', $default = null)
	{
		$str = file_get_contents('php://input');
		$json ? ($data = $str ? json_decode($str, true, 32, JSON_THROW_ON_ERROR) : []) : parse_str($str, $data);
		return $key ? ($data[$key] ?? $default) : $data;
	}
	public static function ip($default = null)
	{
		return $_SERVER['REMOTE_ADDR'] ?? $default;
	}
	public static function ua($default = null)
	{
		return $_SERVER['HTTP_USER_AGENT'] ?? $default;
	}
	public static function refer($default = null)
	{
		return $_SERVER['HTTP_REFERER'] ?? $default;
	}
	public static function https()
	{
		return isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) !== 'off');
	}
	public static function is(string $m = '', callable $callback = null)
	{
		$t = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		return $m ? (($t === strtoupper($m)) ? ($callback ? $callback() : true) : false) : $t;
	}
	public static function verify(array $rule, array|bool $post = true, bool|callable $callback = false)
	{
		$data = $post === true ? $_POST : (is_array($post) ? $post : $_REQUEST);
		return validate::verify($rule, $data, $callback);
	}
	public static function getVar(array &$origin, array|string $var = '', $default = null, string $clean = '')
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
	public static function verify(array $rule, array &$data, bool|callable $callback = false)
	{
		try {
			$data = array_intersect_key($data, $rule);
			array_walk($rule, static fn ($_, $k) => $data[$k] ??= null);
			$switch = [];
			foreach ($rule as $k => $item) {
				if (isset($data[$k])) {
					if (is_array($item)) {
						foreach ($item as $type => $msg) {
							if ($type === 'default') continue;
							if ($msg instanceof closure) {
								$data[$k] = $msg($data[$k], $type, $k);
							} else if (is_array($msg)) {
								array_is_list($msg) ? (in_array($data[$k], $msg, true) or throw new InvalidArgumentException($type, -22)) : (is_array($data[$k]) ? self::verify($msg, $data[$k], $callback) : throw new InvalidArgumentException($type, -23));
							} else if (is_int($type) && is_string($msg)) {
								$switch[$k] = $msg;
							} else if (is_scalar($msg)) {
								(is_string($type) && self::check($data[$k], $type)) or throw new InvalidArgumentException(strval($msg), -24);
							} else if (is_callable($msg)) {
								$data[$k] = $msg($data[$k], $type, $k);
							}
						}
					} else {
						$data[$k] = $item instanceof closure ? $item() : $item;
					}
				} else if ($item instanceof closure) {
					$data[$k] = $item();
				} else if (!is_array($item)) {
					$data[$k] = $item;
				} else if (!empty($item['require']) || !empty($item['required'])) {
					throw new InvalidArgumentException($item['require'] ?? $item['required'], -20);
				} else if (array_key_exists('default', $item)) {
					$data[$k] = $item['default'] instanceof closure ? $item['default']() : $item['default'];
				} // else 其他情况：有规则，但是不存在值，规则$item是数组，应该按照校验规则解析，但是没要求必填，没配置默认值，则忽略校验
			}
		} catch (Throwable $e) {
			if ($callback === false) {
				throw $e;
			}
			$data = ['code' => $e->getCode(), 'msg' => $e->getMessage()];
			return is_callable($callback) ? $callback($e, $data) : json($data);
		}
		foreach ($switch as $from => $to) {
			$data[$to] = $data[$from];
			unset($data[$from]);
		}
		return $data;
	}
	public static function check($item, string $type)
	{
		if (($a = explode('=', $type)) && (count($a) === 2) && ([$key, $val] = $a)) {
			switch ($key) {
				case 'minlength':
					return is_string($item) && is_numeric($val) && strlen($item) >= intval($val);
				case 'maxlength':
					return is_string($item) && is_numeric($val) && strlen($item) <= intval($val);
				case 'length':
					return is_string($item) && ([$l, $a] = [strlen($item), explode(',', $val, 2)]) && is_numeric($a[0]) && (count($a) == 2 ? (is_numeric($a[1]) && $l >= intval($a[0]) && $l <= intval($a[1])) : ($l == intval($a[0])));
				case 'int':
					return is_int($item) && ($a = explode(',', $val, 2)) && is_numeric($a[0]) && (count($a) == 2 ? (is_numeric($a[1]) && $item >= intval($a[0]) && $item <= intval($a[1])) : ($item === intval($a[0])));
				case 'number':
					return filter_var($item, FILTER_VALIDATE_INT) !== false && ($a = explode(',', $val, 2)) && is_numeric($a[0]) && (count($a) == 2 ? (is_numeric($a[1]) && $item >= intval($a[0]) && $item <= intval($a[1])) : ($item == intval($a[0])));
				case 'numeric':
					return is_numeric($item) && ($a = explode(',', $val, 2)) && is_numeric($a[0]) && (count($a) == 2 ? (is_numeric($a[1]) && $item >= floatval($a[0]) && $item <= floatval($a[1])) : ($item <= floatval($a[0])));
				case 'eq':
					return is_string($item) && $item === $val;
				case 'eqs':
					return is_string($item) && strtolower($item) === strtolower($val);
				case 'set':
					return in_array($item, explode(',', $val), true);
			}
		}
		if (($a = explode('::', $type)) && (count($a) >= 2) && is_callable("$a[0]::$a[1]", false, $c)) {
			return call_user_func_array($c, array_merge([$item], array_slice($a, 2)));
		}
		return match ($type) {
			'array', 'bool', 'float', 'int', 'null', 'numeric', 'object', 'scalar', 'string' => "is_$type"($item),
			'alnum', 'alpha', 'cntrl', 'digit', 'graph', 'lower', 'print', 'punct', 'space', 'upper', 'xdigit' => is_string($item) && "ctype_$type"($item),
			'ip' => filter_var($item, FILTER_VALIDATE_IP) !== false,
			'url' => filter_var($item, FILTER_VALIDATE_URL) !== false,
			'number' => filter_var($item, FILTER_VALIDATE_INT) !== false,
			'email' => filter_var($item, FILTER_VALIDATE_EMAIL) !== false,
			'default' => true,
			'required' => $item,
			'require' => $item === 0.0 || $item === 0 || $item === '0' || $item,
			'phone' => (is_string($item) || is_int($item)) && preg_match("/^1\d{10}$/", $item),
			'username' => is_numeric($item) ? false : (is_string($item) && preg_match('/^[\w\x{4e00}-\x{9fa5}]{3,20}$/u', $item)), //字母数字汉字,不能全是数字
			'password' => is_string($item) && preg_match('/^(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/', $item), //数字/大写字母/小写字母/标点符号组成，四种都必有，8位以上
			'json' => is_string($item) && in_array(trim($item)[0] ?? '', ['[', '{'], true) && !is_null(json_decode($item)), //字符串是合法的JSON数组或对象
			default => is_string($item) && (strlen($type) > 2) && (ctype_punct($type[0]) && (str_ends_with(rtrim($type, 'ADJSUXimnsux'), $type[0]))) && preg_match($type, $item)
		};
	}
}

class db
{
	private static function id(): int
	{
		static $id = 0;
		return ++$id;
	}

	final public static function getVar(string $sql, array $params = [])
	{
		return self::exec($sql, $params, 'fetchColumn');
	}

	final public static function getLine(string $sql, array $params = [])
	{
		return self::exec($sql, $params, 'fetch');
	}

	final public static function getData(string $sql, array $params = [])
	{
		return self::exec($sql, $params, 'fetchAll');
	}

	final public static function runSql(string $sql, array $params = [])
	{
		return self::exec($sql, $params, '');
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

	final public static function find(array $where = [], string $table = '', string $col = '*', array $orderLimit = [], string $fetch = 'fetchAll')
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
		return array_map(static fn (array $item) => self::exec(...$item), $v);
	}

	final public static function init(array $dbConfig): PDO
	{
		$options = [PDO::ATTR_PERSISTENT => $dbConfig['persistent'] ?? true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_TIMEOUT => 3, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_STRINGIFY_FETCHES => false];
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

	final public static function exec(string $sql, array $params = [], bool|string $fetch = '')
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

	final public static function cond(array &$where, string $prefix = 'WHERE'): string
	{
		$keys = [];
		foreach (array_filter(array_keys($where)) as $item) {
			$x = array_values(array_filter(explode(' ', $item)));
			$n = $x[0];
			$verb = array_slice($x, 1);
			$a = is_array($where[$item]);
			$marks = [];
			if (is_null($where[$item])) {
				$v = 'NULL';
				if ($x[0] === '!') {
					$n = $x[1];
					$verb = array_slice($x, 2);
				} else if ($x[0][0] === '!') {
					$n = substr($x[0], 1);
				}
				$verb = $verb ?: ['IS'];
			} elseif ($x[0] === '!') {
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
					$t = str_replace(['.', '`'], '_', $n) . '_' . self::id();
					$v = ":{$t}";
					$where[$t] = $where[$item];
				}
			}
			unset($where[$item]);
			$keys[] = sprintf('%s %s %s', str_contains($n, '.') ? $n : "`$n`", $verb ? implode(' ', $verb) : ($a ? 'IN' : '='), $a ? sprintf('(%s)', implode(',', $marks)) : $v);
		}
		$condition = $keys ? implode(sprintf(' %s ', $where[0] ?? 'AND'), $keys) : '';
		unset($where[0], $keys);
		return $condition ? sprintf('%s(%s)', $prefix ? " {$prefix} " : '', $condition) : '';
	}

	final public static function condition(array &$where, string $prefix = 'WHERE'): string
	{
		if (!array_is_list($where) || empty($where)) {
			return self::cond($where, $prefix);
		}
		$parts = [];
		$verb = 'OR';
		foreach ($where as $key => $item) {
			if (is_array($item)) {
				$parts[] = self::cond($item, '');
				foreach ($item as $k => $v) {
					$where[$k] = $v;
				}
			} else if (is_string($item)) {
				$verb = $item;
			}
			unset($where[$key]);
		}
		return sprintf('%s(%s)', $prefix ? " $prefix " : '', implode(" $verb ", $parts));
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
		return $set ? implode(',', array_map(static fn (array $x) => sprintf('`%s` = %s', $x[0], $x[1]), $keys)) : sprintf('%s(%s) VALUES (%s)', $table ? " `{$table}` " : '', implode(',', array_map(static fn (array $x) => sprintf('`%s`', $x[0]), $keys)), implode(',', array_map(static fn (array $x) => $x[1], $keys)));
	}

	final public static function orderLimit(array $orderLimit): string
	{
		$limit = '';
		$orderLimit = array_filter($orderLimit, static function ($x, $k) use (&$limit) {
			if ((is_int($x) || ctype_digit(strval($x))) && (is_int($k) || ctype_digit(strval($k)))) {
				$limit = "$k,$x";
				return false;
			}
			return true;
		}, ARRAY_FILTER_USE_BOTH);
		$orderLimit ? (array_walk($orderLimit, static function (&$v, $k) {
			$v = sprintf('%s %s', $k, is_string($v) ? $v : ($v ? 'ASC' : 'DESC'));
		})) : '';
		return sprintf('%s%s', $orderLimit ? ' ORDER BY ' . implode(',', $orderLimit) : '', $limit ? " LIMIT $limit" : '');
	}
}

function template(string $v, array $data = [], callable|int $callback = 0, string $path = '')
{
	$path = $path ?: app::get('view_path', '');
	if (is_int($callback) && $callback > 1) {
		$t = $callback;
		$callback = static function ($buffer) use ($t) {
			echo $buffer;
			if (is_writable(VAR_PATH_HTML)) {
				if ($file = app::get('sys.cachefile')) {
					file_put_contents($file, $buffer) && touch($file, $_SERVER['REQUEST_TIME'] + $t);
				}
			}
		};
	}
	if ((is_file($__v__ = $path . $v . '.php')) || (is_file($__v__ = $path . $v))) {
		$__render__props__ = ['v' => $__v__, 'callback' => $callback, 'data' => $data];
		$render = static function () use ($__render__props__) {
			extract($__render__props__['data'], EXTR_SKIP);
			unset($__render__props__['data']);
			if ($__render__props__['callback']) {
				ob_start() && include $__render__props__['v'];
				$contents = ob_get_contents();
				return (ob_end_clean() && ($__render__props__['callback'] instanceof closure)) ? $__render__props__['callback']($contents) : $contents;
			}
			return include $__render__props__['v'];
		};
		return $render();
	}
	throw new InvalidArgumentException("file {$__v__} not found", 404);
}

function session(array|string $key, $val = null, bool $delete = false)
{
	isset($_SESSION) || session_start();
	if (is_null($val)) {
		return $delete ? array_filter(is_array($key) ? $key : [$key], static function ($k) {
			unset($_SESSION[$k]);
		}) : request::session($key, null);
	}
	return $_SESSION[$key] = $val;
}
function cookie(array|string $key, $val = null)
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
