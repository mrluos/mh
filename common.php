<?php
/**
 * http post 请求
 *
 * @param  [string] $url        [description]
 * @param  [string] $parameters [description]
 * @param array $headers [description]
 *
 * @return [obj]             [description]
 */
function gf_http_post($url, $parameters = null, $headers = [], $upwd = '')
{
	return gf_http($url, 'post', $parameters, $headers, $upwd);
}

/**
 * http get 请求
 *
 * @param  [string] $url        [description]
 * @param  [string] $parameters [description]
 * @param array $headers [description]
 *
 * @return [obj]             [description]
 */
function gf_http_get($url, $parameters = null, $headers = [], $upwd = '')
{
	return gf_http($url, 'get', $parameters, $headers, $upwd);
}

/**
 * [gf_http description]
 *
 * @param  [type] $url        [description]
 * @param  [type] $method     [description]
 * @param  [type] $parameters [description]
 * @param array $headers [Authorization验证的账号密码user:pwd格式]
 * @param  [string] $upwd []
 * @return [type]             [description]
 */
function gf_http($url, $method, $parameters = null, $headers = [], $upwd = '')
{
	if (empty($url)) {
		return null;
	}
	$ci = curl_init();
	/* Curl settings */
	curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ci, CURLOPT_TIMEOUT, 3000);
	curl_setopt($ci, CURLOPT_HEADER, false);
	curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
	curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);     // 从证书中检查SSL加密算法是否存在
	switch (strtolower($method)) {
		case 'post':
//			$urlParams = http_build_query($url);
			$tmp = explode('?', $url);
			if (isset($tmp[1])) {
				parse_str($tmp[1], $postParame);
				$parameters = array_merge($postParame, $parameters);
			}
			curl_setopt($ci, CURLOPT_POST, true);
			if (!empty($parameters)) {
				curl_setopt($ci, CURLOPT_POSTFIELDS, $parameters);
			}
//			exit;
			break;
		case 'get':
			if (!empty($parameters)) {
				$url .= strpos($url, '?') === false ? '?' : '';
				$url .= strpos($url, '&') === false ? '' : '&';
				$url .= http_build_query($parameters);
			}
			break;
		default:
			# code...
			break;
	}
	if ('' != $upwd) {
		curl_setopt($ci, CURLOPT_USERPWD, $upwd);
	}
	curl_setopt($ci, CURLOPT_URL, $url);
	curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ci, CURLINFO_HEADER_OUT, true);
	$response = curl_exec($ci);
	if (curl_error($ci)) {
		$response = curl_error($ci);
	}
	curl_close($ci);
	return $response;
}

/**
 * Yaf PDO class.
 * @Author: Carl
 * @Since: 2017/4/7 15:42
 * Created by PhpStorm.
 */
class DbEXClass
{
	private static $dbLink;
	private static $lastSql;
	private static $lastInsertId;
	private static $errMessage;
	private static $linkMap;

	public static function getInstance($dsn, $username, $password)
	{
		$opts = array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			// Cancel one specific SQL mode option that RackTables has been non-compliant
			// with but which used to be off by default until MySQL 5.7. As soon as
			// respective SQL queries and table columns become compliant with those options
			// stop changing @@SQL_MODE but still keep SET NAMES in place.
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8", @@SQL_MODE = REPLACE(@@SQL_MODE, "NO_ZERO_DATE", "")',
		);
		if (!is_array(self::$linkMap)) {
			self::$linkMap = array();
		}
		if (isset($pdo_bufsize)) {
			$opts[PDO::MYSQL_ATTR_MAX_BUFFER_SIZE] = $pdo_bufsize;
		}

		if (isset($pdo_ssl_key)) {
			$opts[PDO::MYSQL_ATTR_SSL_KEY] = $pdo_ssl_key;
		}

		if (isset($pdo_ssl_cert)) {
			$opts[PDO::MYSQL_ATTR_SSL_CERT] = $pdo_ssl_cert;
		}

		if (isset($pdo_ssl_ca)) {
			$opts[PDO::MYSQL_ATTR_SSL_CA] = $pdo_ssl_ca;
		}

		try {

			self::$dbLink = self::getDBLink($dsn, $username, $password, $opts);
			// var_dump($dsn, $username, $password, $opts);
			// if(self::$dbLink===null){
			//     // echo 'init...';
			//     self::$dbLink = new PDO ($dsn, $username, $password, $opts);
			// }
		} catch (Exception $e) {
			self::$errMessage = "Database connect failed:\n\n" . $e->getMessage();
			die(self::$errMessage);
		}
		return self::$dbLink;
	}

	public static function changeDB($rs)
	{
		self::$dbLink = $rs;
	}

	private static function getDBLink($dsn, $username, $password, $opts)
	{
		$md5 = md5($dsn);
		if (isset(self::$linkMap[$md5])) {
			return self::$linkMap[$md5];
		}
		$rs = new PDO($dsn, $username, $password, $opts);
		self::$linkMap[$md5] = $rs;
		return $rs;

	}

	private static function checkDataType($val)
	{

		if (is_bool($val)) {
			return PDO::PARAM_BOOL;
		} elseif (is_numeric($val)) {
			$test = $val * 1;
			if (is_int($test)) {
				return PDO::PARAM_INT;
			}
			return PDO::PARAM_STR;
		} elseif (is_null($val)) {
			return PDO::PARAM_NULL;
		} else {
			return PDO::PARAM_STR;
		}

	}

	public static function isConnectOk()
	{
		return !!self::$dbLink;
	}

	public static function execute($sql, $param = array())
	{
		try {
			$pre = self::$dbLink->prepare($sql);
			foreach ($param as $key => $value) {
				$pre->bindValue($key + 1, $value, self::checkDataType($value));
			}
			$pre->execute();
			self::$lastSql = $pre->queryString;
			//echo self::$lastSql;
			return $pre;
		} catch (PDOException $e) {
			self::$errMessage = $e->getMessage();
			die($e);
		}
	}

	public static function insert($table, $columns)
	{
		$sql = " INSERT INTO {$table} (`" . implode('`, `', array_keys($columns));
		$sql .= '`) VALUES (' . self::questionMarks(count($columns)) . ')';
		// Now the query should be as follows:
		// INSERT INTO table (c1, c2, c3) VALUES (?, ?, ?)
		$res = self::execute($sql, array_values($columns))->rowCount();
		if ($res > 0) {
			return self::$dbLink->lastInsertId();
		} else {
			return false;
		}
	}

	public static function ignore_insert($table, $columns)
	{
		$sql = " INSERT IGNORE INTO {$table} (`" . implode('`, `', array_keys($columns));
		$sql .= '`) VALUES (' . self::questionMarks(count($columns)) . ')';
		// Now the query should be as follows:
		// INSERT INTO table (c1, c2, c3) VALUES (?, ?, ?)
		$res = self::execute($sql, array_values($columns))->rowCount();
		if ($res > 0) {
			return self::$dbLink->lastInsertId();
		} else {
			return false;
		}
	}

	/**
	 * 插入多条语句
	 * @Author:吴世聪
	 * @param $table
	 * @param $data 二维数组
	 * @return bool
	 * @throws WSException
	 */
	public static function inserts($table, $data)
	{

		$sql = ' INSERT INTO ' . $table . ' (`' . implode('`, `', array_keys($data[0])) . '`)';
		$values = ' (' . self::questionMarks(count($data[0])) . ')';
		$params = [];
		foreach ($data as $key => $columns) {
			if ($key === 0) {
				$sql .= ' VALUES' . $values;
			} else {
				$sql .= ',' . $values;
			}
			$params = array_merge($params, array_values($columns));
		}

		$res = self::execute($sql, $params)->rowCount();

		if ($res > 0) {
			return self::$dbLink->lastInsertId();
		} else {
			return false;
		}
	}

	public static function update($table, $param, $where, $conjunction = 'AND')
	{
		if (!count($param)) {
			self::$errMessage = 'update must have set.';
			die('update must have set.');
		}
		if (!count($where)) {
			self::$errMessage = 'update must have where.';
			die('update must have where.');
		}
		$whereValues = array();
		$sql = " UPDATE $table SET " . self::makeSetSQL($param) . ' WHERE ' . self::makeWhereSQL($where, $conjunction, $whereValues);
		return self::execute($sql, array_merge(array_values($param), $whereValues))->rowCount();
	}

	public static function delete($table, $where, $conjunction = 'AND')
	{
		if (!count($where)) {
			self::$errMessage = 'delete must have where.';
			die('delete must have where.');
		}
		$whereValues = array();
		$sql = " DELETE FROM $table WHERE " . self::makeWhereSQL($where, $conjunction, $whereValues);
		// print_r($sql);die;
		return self::execute($sql, $whereValues)->rowCount();
	}

	/**
	 * 开启事务
	 * @return bool
	 */
	public static function begin()
	{
		return self::$dbLink->beginTransaction();
	}

	/**
	 * 事务提交
	 * @return bool
	 */
	public static function commit()
	{
		return self::$dbLink->commit();
	}

	/**
	 * 事务回滚
	 * @return bool
	 */
	public static function rollBack()
	{
		return self::$dbLink->rollBack();
	}

	public static function getColumn($sql, $param = array(), $col = 0)
	{
		return self::execute($sql, $param)->fetchColumn($col);
	}

	public static function getKeyValue($sql, $param = array())
	{
		return self::execute($sql, $param)->fetchAll(PDO::FETCH_KEY_PAIR);
	}

	public static function getCount($sql, $param = array())
	{
		return self::execute($sql, $param)->rowCount();
	}

	public static function getAll($sql, $param = array())
	{
		return self::execute($sql, $param)->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function getRow($sql, $param = array())
	{
		return self::execute($sql, $param)->fetch(PDO::FETCH_ASSOC);
	}

	public static function makeSetSQL($columns)
	{
		if (!count($columns)) {
			die('columns must not be empty');
		}
		$tmp = array();
		// Same syntax works for NULL as well.
		foreach ($columns as $col => $val) {
			$tmp[] = "`${col}`=?";
		}
		return implode(', ', $tmp);
	}

	public static function makeWhereSQL($where_columns, $conjunction, &$params = array())
	{
		if (!in_array(strtoupper($conjunction), array('AND', '&&', 'OR', '||', 'XOR'))) {
			die('conjunction' . $conjunction . 'invalid operator');
		}
		if (!count($where_columns)) {
			return '';
			// die ('where_columns must not be empty');
		}
		$params = array();
		$tmp = array();
		foreach ($where_columns as $colName => $colValue) {
			if ($colValue === null) {
				$tmp[] = "$colName IS NULL";
			} elseif (is_array($colValue)) {
				// Suppress any string keys to keep array_merge() from overwriting.
				$params = array_merge($params, array_values($colValue));
				$tmp[] = sprintf('%s IN(%s)', $colName, self::questionMarks(count($colValue)));
			} else {
				$tmp[] = "${colName}=?";
				$params[] = $colValue;
			}
		}

		return implode(" ${conjunction} ", $tmp);
	}

	public static function questionMarks($count)
	{
		if ($count <= 0) {
			die('count must be greater than zero');
		}
		return implode(', ', array_fill(0, $count, '?'));
	}

	public static function getLastSQL()
	{
		return self::$lastSql;
	}

	public static function getLastInsertId()
	{
		return self::$dbLink->lastInsertId();
	}

	public static function getError()
	{
		return self::$errMessage;
	}
}

class DbClass
{
	private $dbLink;

	private $lastSql;
	private $lastInsertId;
	private $errMessage;


	public function __construct($dsn, $username, $password)
	{
		$opts = array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			// Cancel one specific SQL mode option that RackTables has been non-compliant
			// with but which used to be off by default until MySQL 5.7. As soon as
			// respective SQL queries and table columns become compliant with those options
			// stop changing @@SQL_MODE but still keep SET NAMES in place.
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8", @@SQL_MODE = REPLACE(@@SQL_MODE, "NO_ZERO_DATE", "")',
		);
		if (isset ($pdo_bufsize))
			$opts[PDO::MYSQL_ATTR_MAX_BUFFER_SIZE] = $pdo_bufsize;
		if (isset ($pdo_ssl_key))
			$opts[PDO::MYSQL_ATTR_SSL_KEY] = $pdo_ssl_key;
		if (isset ($pdo_ssl_cert))
			$opts[PDO::MYSQL_ATTR_SSL_CERT] = $pdo_ssl_cert;
		if (isset ($pdo_ssl_ca))
			$opts[PDO::MYSQL_ATTR_SSL_CA] = $pdo_ssl_ca;
		try {
			$this->dbLink = new PDO ($dsn, $username, $password, $opts);
		} catch (Exception $e) {
			$this->errMessage = "Database connect failed:\n\n" . $e->getMessage();
			die($this->errMessage);
		}
	}

	public function isConnectOk()
	{
		return !!$this->dbLink;
	}

	private function checkDataType($val)
	{

		if (is_bool($val)) {
			return PDO::PARAM_BOOL;
		} elseif (is_numeric($val)) {
			$test = $val * 1;
			if (is_int($test)) {
				return PDO::PARAM_INT;
			}
			return PDO::PARAM_STR;
		} elseif (is_null($val)) {
			return PDO::PARAM_NULL;
		} else {
			return PDO::PARAM_STR;
		}

	}

	public function execute($sql, $param = array())
	{
		try {
			$pre = $this->dbLink->prepare($sql);
			foreach ($param as $key => $value) {
				$pre->bindValue($key + 1, $value, $this->checkDataType($value));
			}
			$pre->execute();
			$this->lastSql = $pre->queryString;
			// echo $this->lastSql;
			return $pre;
		} catch (PDOException $e) {
			$this->errMessage = $e->getMessage();
			die($e);
		}
	}

	public function insert($table, $columns)
	{
		$sql = " INSERT INTO {$table} (`" . implode('`, `', array_keys($columns));
		$sql .= '`) VALUES (' . $this->questionMarks(count($columns)) . ')';
		// Now the query should be as follows:
		// INSERT INTO table (c1, c2, c3) VALUES (?, ?, ?)
		$res = $this->execute($sql, array_values($columns))->rowCount();
		if ($res > 0) {
			return $this->dbLink->lastInsertId();
		} else {
			return FALSE;
		}
	}

	public function update($table, $param, $where, $conjunction = 'AND')
	{
		if (!count($param)) {
			$this->errMessage = 'update must have set.';
			die('update must have set.');
		}
		if (!count($where)) {
			$this->errMessage = 'update must have where.';
			die('update must have where.');
		}
		$whereValues = array();
		$sql = " UPDATE $table SET " . $this->makeSetSQL($param) . ' WHERE ' . $this->makeWhereSQL($where, $conjunction, $whereValues);
		return $this->execute($sql, array_merge(array_values($param), $whereValues))->rowCount();
	}

	public function delete($table, $where, $conjunction = 'AND')
	{
		if (!count($where)) {
			$this->errMessage = 'delete must have where.';
			die('delete must have where.');
		}
		$whereValues = array();
		$sql = " DELETE FROM $table WHERE " . $this->makeWhereSQL($where, $conjunction, $whereValues);
		// print_r($sql);die;
		return $this->execute($sql, $whereValues)->rowCount();
	}

	/**
	 * 开启事务
	 * @return bool
	 */
	public function begin()
	{
		return $this->dbLink->beginTransaction();
	}

	/**
	 * 事务提交
	 * @return bool
	 */
	public function commit()
	{
		return $this->dbLink->commit();
	}

	/**
	 * 事务回滚
	 * @return bool
	 */
	public function rollBack()
	{
		return $this->dbLink->rollBack();
	}

	public function getColumn($sql, $param = array(), $col = 0)
	{
		return $this->execute($sql, $param)->fetchColumn($col);
	}

	public function getKeyValue($sql, $param = array())
	{
		return $this->execute($sql, $param)->fetchAll(PDO::FETCH_KEY_PAIR);
	}

	public function getCount($sql, $param = array())
	{
		return $this->execute($sql, $param)->rowCount();
	}

	public function getAll($sql, $param = array())
	{
		return $this->execute($sql, $param)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getRow($sql, $param = array())
	{
		return $this->execute($sql, $param)->fetch(PDO::FETCH_ASSOC);
	}

	public function makeSetSQL($columns)
	{
		if (!count($columns)) {
			die ('columns must not be empty');
		}
		$tmp = array();
		// Same syntax works for NULL as well.
		foreach ($columns as $col => $val) {
			$tmp[] = "`${col}`=?";
		}
		return implode(', ', $tmp);
	}

	public function makeWhereSQL($where_columns, $conjunction, &$params = array())
	{
		if (!in_array(strtoupper($conjunction), array('AND', '&&', 'OR', '||', 'XOR'))) {
			die ('conjunction' . $conjunction . 'invalid operator');
		}
		if (!count($where_columns)) {
			die ('where_columns must not be empty');
		}
		$params = array();
		$tmp = array();
		foreach ($where_columns as $colName => $colValue)
			if ($colValue === NULL)
				$tmp[] = "$colName IS NULL";
			elseif (is_array($colValue)) {
				// Suppress any string keys to keep array_merge() from overwriting.
				$params = array_merge($params, array_values($colValue));
				$tmp[] = sprintf('%s IN(%s)', $colName, $this->questionMarks(count($colValue)));
			} else {
				$tmp[] = "${colName}=?";
				$params[] = $colValue;
			}
		return implode(" ${conjunction} ", $tmp);
	}

	public function questionMarks($count)
	{
		if ($count <= 0) {
			die('count must be greater than zero');
		}
		return implode(', ', array_fill(0, $count, '?'));
	}

	public function getLastSQL()
	{
		return $this->lastSql;
	}

	public function getLastInsertId()
	{
		return $this->dbLink->lastInsertId();
	}

	public function getError()
	{
		return $this->errMessage;
	}

	///*********tom*******///

	/**
	 * @param string $database 数据库名字
	 * @param string $table 数据库表名
	 * @return array 所有表的字段值
	 */

	public function getTableFields($database = '', $table = '')
	{
		//$column_name = $this -> getAll("select column_name from information_schema.`COLUMNS` where TABLE_SCHEMA='".$database."' and TABLE_NAME='".$table."'");
		$column_name = $this->getAll("select column_name from information_schema.`COLUMNS` where TABLE_SCHEMA=? and TABLE_NAME=?", array($database, $table));
		return $column_name;
	}

}

class DbModel
{
	// table name
	public $table = '';
	// primary key
	public $pk = 'id';

	public $confName = 'mysql';
	private $db;

	public function __construct($table = '')
	{
//		mysql.dsn = "mysql:host=127.0.0.1;dbname=ws_cmdb;port=3306"
//mysql.username = "root"
//mysql.password = "123456"
		$this->db = DBEXClass::getInstance('mysql:host=127.0.0.1;dbname=mh;port=3306', 'ws_cmdb', 'ws_cmdbwscmdb');
		$this->table = empty($table) ? $this->table : $table;
		if (empty($this->table)) {
			$this->table = strtolower(str_replace('Model', '', get_class($this)));
		}
	}

	public function getLinkRs()
	{
		return $this->db;
	}

	public function get($id)
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getRow("SELECT * FROM {$this->table} WHERE {$this->pk}=?", array($id));
	}

	/**
	 * @param string $col
	 * @param string $table
	 * 查询表的字段
	 * @return mixed
	 */
	public function getColInfo($col, $table = '')
	{
		if (empty($table)) {
			$table = $this->table;
		}
		DBEXClass::changeDB($this->db);
		return DBEXClass::getRow("SHOW COLUMNS FROM {$table} WHERE FIELD LIKE ?", array($col));
	}

	/**
	 * @param string $col
	 * @param string $table
	 * 快速获取枚举类型列表
	 * @return array
	 */
	public function getColEnum($col, $table = '')
	{
		$col_info = $this->getColInfo($col, $table);
		$enum = explode(',', preg_replace('/^enum\((.*)\)$/i', '$1', $col_info['Type']));
		return array_map(function ($v) {
			return trim($v, '\'');
		}, $enum);
	}

	public function add($data)
	{
		return $this->insert($this->table, $data);

	}

	public function insert($table, $data)
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::insert($table, $data);
	}

	public function edit($id, $data)
	{
		return $this->mod($id, $data);
	}

	public function mod($id, $data)
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::update($this->table, $data, array($this->pk => $id));
	}

	public function set($ids, $field, $value)
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::update($this->table, array($field => $value), array($this->pk => $ids));
	}

	public function update($table, $data = array(), $where = array(), $conjunction = 'AND')
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::update($table, $data, $where, $conjunction);
	}

	public function execute($sql, $param = array())
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::execute($sql, $param);
	}

	public function del($ids)
	{
		// DBEXClass::changeDB($this->db);
		return $this->delete($this->table, array($this->pk => $ids));
	}

	public function delete($table, $param = array(), $join = 'AND')
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::delete($table, $param, $join);
	}

	public function isConnectOk()
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::isConnectOk();
		// return !!self::$dbLink;
	}

	/**
	 * 开启事务
	 * @return bool
	 */
	public function begin()
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::begin();
	}

	/**
	 * 事务提交
	 * @return bool
	 */
	public function commit()
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::commit();
	}

	/**
	 * 事务回滚
	 * @return bool
	 */
	public function rollBack()
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::rollBack();
	}

	public function getColumn($sql, $param = array(), $col = 0)
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getColumn($sql, $param, $col);
	}

	public function getKeyValue($sql, $param = array())
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getKeyValue($sql, $param);
	}

	public function getCount($sql, $param = array())
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getCount($sql, $param);
	}

	public function getAll($sql, $param = array())
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getAll($sql, $param);
	}

	public function getRow($sql, $param = array())
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getRow($sql, $param);
	}

	public function getLastSQL()
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getLastSQL();
	}

	public function getLastInsertId()
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getLastInsertId();
	}

	public function getError()
	{
		DBEXClass::changeDB($this->db);
		return DBEXClass::getError();
	}

	/**
	 * @param string $database 数据库名字
	 * @param string $table 数据库表名
	 * @return array 所有表的字段值
	 */

	public function getTableFields($database = '', $table = '')
	{
		//$column_name = $this -> getAll("select column_name from information_schema.`COLUMNS` where TABLE_SCHEMA='".$database."' and TABLE_NAME='".$table."'");
		DBEXClass::changeDB($this->db);
		$column_name = DBEXClass::getAll("select column_name from information_schema.`COLUMNS` where TABLE_SCHEMA=? and TABLE_NAME=?", array($database, $table));
		return $column_name;
	}

}

class MH extends DbModel
{
	public $table = 'mh';
	public $tableZj = 'mh_zj';

	//不需要模糊搜索字段
	public function insert($table, $data)
	{
		return parent::insert($table, $data); // TODO: Change the autogenerated stub
	}

	public function getOne($id)
	{
		return $this->getAll('select * from ' . $this->table . ' where id = ?', [$id]);
	}

	public function getOneZJ($id)
	{
		return $this->getAll('select * from ' . $this->tableZj . ' where id = ?', [$id]);
	}

	public function getAllMH()
	{
		return $this->getAll('select * from ' . $this->table . ' order by ticai desc', []);
	}
}

function getPage($page = 1, $type = 1)
{
	$db = new MH();
	if ($type == 1) {
		$rs = gf_http_get('http://www.xiximh.vip/home/api/getpage/tp/1-vip-' . $page);
	}
	if ($type == 2) {
		$rs = gf_http_get('http://www.xiximh.vip/home/api/getpage/tp/1-competitive-' . $page);
	}
	if ($type == 3) {
		$rs = gf_http_get('http://www.xiximh.vip/home/api/getpage/tp/1-newest-' . $page);
	}


	$rs = json_decode($rs, true);

	if ($rs['code'] == 1) {
		$list = $rs['result']['list'];
		var_dump($page, count($list));
		foreach ($list as $item) {
			if (empty($db->getOne($item['id']))) {
				echo "\r\nadd news {$item['id']} {$item['title']}\r\n";
				$db->add($item);
			}

		}
		if ($rs['result']['lastPage'] == false) {
			getPage($page + 1, $type);
		}
	}
}

//getPage(2);
function getAllZJ()
{

	$db = new MH();
	$all = $db->getAllMH();
	foreach ($all as $item) {
		$rs = gf_http_get('http://www.xiximh.vip/home/api/chapter_list/tp/' . $item['id'] . '-1-1-1000');

		$rs = json_decode($rs, true);

		if ($rs['code'] == 1) {
			$list = $rs['result']['list'];
			var_dump($item['id'], count($list));
			foreach ($list as $i) {
				if (isset($i['image'])) {
					preg_match('/\/bookimages\/(\d+)\//si', $i['image'], $match);
					if (isset($match[1])) {
						$i['dir_str'] = '/bookimages/' . $match[1] . '/' . $i['id'] . '/';
					}
				}
				if (empty($db->getOneZJ($i['id']))) {
					$db->insert('mh_zj', $i);
				}

			}
		}
	}
}

function getAllZJMaxImg()
{

	$db = new MH();
	$all = $db->getAll('select * from mh_zj');
	foreach ($all as $k => $item) {
		echo $item['id'], ' start!!', "\r\n";
		if ($item['pic_count'] > 0) {
			continue;
		}
		$l = explode('/', $item['dir_str']);
		$l[3] = $item['cjid'];
		$item['dir_str'] = implode('/', $l);
//		$item['image'] = "/bookimages/15700/29651/eaa49764-a896-43af-9364-4f4630f43059.png";
		preg_match('/(\..*)/si', $item['image'], $m);
		$item['image_suffix'] = isset($m[0]) ? $m[0] : '';
		$flag = 100;
		$db->update('mh_zj', [
			'dir_str' => $item['dir_str']
		], ['id' => $item['id']]);
		$init = 30;
		$picCount = 30;
		$goOn = true;

		$suffix = ($item['image_suffix'] ? $item['image_suffix'] : '');
		$type = 0;
		$picSuffix = ['', '.jpg', '.png', '.jpeg', '.gif'];
		while (1) {
			if (!isset($picSuffix[$type])) {
				break;
			}
			$suffix = $picSuffix[$type];
			$imgUrl = 'http://www.xiximh.vip/' . $item['dir_str'] . '1' . $suffix;
			getimagesize($imgUrl, $rs);
			if ($rs) {
				break;
			}
			$type++;
		}
		echo ' pic suffix:', $suffix, "\r\n";
		while ($goOn) {

			$imgUrl = 'http://www.xiximh.vip/' . $item['dir_str'] . $picCount . $suffix;
			getimagesize($imgUrl, $rs);
//			$rs = file_get_contents('http://www.xiximh.vip/' . $item['dir_str'] . $picCount . $item['image_suffix']);
//			var_dump($imgUrl, $rs);
			if ($picCount <= $init) {
				if ($picCount == $init && $rs) {
					$picCount += 2;
					continue;
				}
				if ($picCount == 0) {
					$goOn = false;
					continue;
				}
				if (!$rs) {
					$picCount -= 2;
					continue;
				}
				if ($picCount < $init) {
					$goOn = false;
				}
			} else {
				if (!$rs) {
					$goOn = false;
					continue;
				}
				$picCount += 2;
			}

//			exit();

		}
		$imgUrl = 'http://www.xiximh.vip/' . $item['dir_str'] . $picCount . $suffix;
		getimagesize($imgUrl, $rs);
		if (!$rs) {
			$picCount - 1;
		}
		$db->update('mh_zj', [
			'image_suffix' => $suffix,
			'pic_count' => $picCount - 1,
		], ['id' => $item['id']]);
		echo $item['id'], ',count:', $picCount - 1, "\r\n";
	}
}

function getAllZJEx($id)
{

	$rs = gf_http_get('http://www.xiximh.vip/home/api/chapter_list/tp/' . $id . '-1-1-1000');
	$db = new MH();
	$rs = json_decode($rs, true);
//	print_r($rs);
	$list = [];
	if ($rs['code'] == 1) {
		$list = $rs['result']['list'];
//		var_dump($item['id'], count($list));
		foreach ($list as $i) {
			if (isset($i['image'])) {
				preg_match('/\/bookimages\/(\d+)\/(\d+)/si', $i['image'], $match);
//				var_dump($match);
				if (isset($match[0])) {
					$i['dir_str']=$match[0].'/';
//					$i['dir_str'] = '/bookimages/' . $match[1] . '/' . $i['id'] . '/';
				}
			}
			if (empty($db->getOneZJ($i['id']))) {
				echo "\r\n", "add new Id " . $i['id'], "\r\n";
				$db->insert('mh_zj', $i);
			}

		}
	}
	return $list;
}

/**
 * 获取文件夹下文件的数量
 * @param $url 传入一个url如：/apps/web
 * @return int 返回文件数量
 */
function getFileNumber($url)
{
	$num = 0;
	$arr = glob($url);
	foreach ($arr as $v) {
		if (is_file($v)) {
			$num++;
		} else {
			$num += getFileNumber($v . "/*");
		}
	}
	return $num;
}

function getFileName($url)
{
	$dir = [];
	$arr = glob($url);
	print_r($arr);
	foreach ($arr as $v) {
		if (is_file($v)) {
			$dir[] = $v;
		} else {
			$dir = array_merge($dir, getFileNumber($v . "/*"));
		}
	}
	return $dir;
}

function getPicCount($item, $suffix = '-1')
{
	$remoteDir = $item['dir_str'];
	if ($suffix == -1) {
		$suffix = testForImgSuffix($item['image_suffix'], $item['dir_str']);
	}
	$testPic = 0;
	$imgUrl = 'http://www.xiximh.vip/' . $remoteDir . $testPic . $suffix;
	$startIndex = 1;
	$rs = @file_get_contents($imgUrl);
	if ($rs) {
		$startIndex = 0;
	}
	$flag = true;
	$step = 10;
	$cur = $step;
	$count = 0;
	$map = [];
	$falseFlag = false;
	$lastV = 0;
	do {
		$imgUrl = 'http://www.xiximh.vip/' . $remoteDir . $cur . $suffix;
		$rs = @file_get_contents($imgUrl);
		$count++;
//		$rs = false;

//		$map[] = [$cur, $rs];
		if ($rs) {
			if ($falseFlag) {
				break;
			}
			$lastV = $cur;
			$cur += $step;

		} else {
			$falseFlag = true;
			$s = intval(($cur - $lastV) / 2);
			$s = $s <= 1 ? 1 : $s;
			$cur -= $s;
		}

		if ($cur <= 0) {
			$flag = false;
		}
		if ($cur <= 0) {
			$cur = 0;
			break;
		}

	} while ($flag);
	return [$cur, $startIndex];
//	echo $cur, ' ## ', $count, "##", $startIndex, "\r\n";
//	print_r($map);
}

function testForImgSuffix($locSuffix, $remoteDir = '', $testPic = '2', $isDebug = true)
{
	$suffix = ($locSuffix ? $locSuffix : '');
	$count = 0;
	$picSuffix = ['.jpg', '.png', '.jpeg', '.gif', ''];
	$rs = null;
	while (1) {
		if (!isset($picSuffix[$count])) {
			break;
		}
		$suffix = $picSuffix[$count];

		$imgUrl = 'http://www.xiximh.vip/' . $remoteDir . $testPic . $suffix;
		$rs = @file_get_contents($imgUrl);
		if ($isDebug) {
			echo ' suffix test for [' . $suffix, '] ', $imgUrl, ' #----> ', boolval($rs) ? 'true' : 'false', "\r\n";
		}
		if ($rs) {
			break;
		}
		$count++;
	}
	if ($rs) {
		$suffix = $picSuffix[$count];
	} else {
		$suffix = '';
	}
	return $suffix;
}

function updateImgSuffix($page = 1, $pageSize = 100)
{

	$db = new MH();
	$limit = '';
	$offset = ($page - 1) * $pageSize;
	$limit = " limit {$offset},{$pageSize}";
	$all = $db->getAll('select * from mh_zj where image_start_index =-1 ' . $limit);
	foreach ($all as $k => $item) {
		if ($item['image_start_index'] != -1) {
			continue;
		}
		echo $item['id'], ' start!!', "\r\n";
		$suffix = testForImgSuffix($item['image_suffix'], $item['dir_str']);
		list($count, $index) = getPicCount($item, $suffix);
//		echo ' pic suffix:', $suffix, "\r\n";
		$db->update('mh_zj', [
			'image_suffix' => $suffix,
			'image_start_index' => $index,
			'pic_count' => $count
		], ['id' => $item['id']]);
		echo "suffix[{$suffix}],index[{$index}],count[{$count}]", "\r\n";
	}
}

function getImgToLoc($id = null, $sonId = null, $page = 0, $pagesize = 0, $desc = 'desc')
{
	$db = new MH();
	$limit = '';
	if ($page > 0) {
		$offset = ($page - 1) * $pagesize;
		$limit = " limit {$offset},{$pagesize}";
	}
	if ($id == null) {

		$all = $db->getAll('
SELECT j.* FROM (SELECT * FROM mh ORDER BY pingfen*1 DESC ' . $limit . ' )AS t 
JOIN mh_zj j ON j.manhua_id = t.id 
', []);
	} else {

		$all = $db->getAll('select * from mh_zj where manhua_id = ? order  by sort*1 ' . $desc, [$id]);
	}
	$total = 0;
	foreach ($all as $k => $item) {
		if ($id == null) {
			$id = $item['manhua_id'];
		}
		if ($sonId) {
			if ($sonId !== $item['id']) {
				continue;
			}
		}

		echo 'manhua_id:', $id, ' zhangjie:', $item['id'], ' start!!', "\r\n";
		$dirPath = 'img/' . $id . '/' . $item['id'];
		if (!file_exists($dirPath)) {
			@mkdir($dirPath, 777, true);
		} else {
			$dirList = scandir($dirPath);
			print_r($dirList);
			if (count($dirList) > 2) {
				continue;
			}
		}
		if ($item['type'] == 100) {
			$url = str_replace('//manga', '/manga', $item['dir_str']);
			if ($item['id'] == '') {
				$db->update('mh_zj', [
					'dir_str' => $url,
					'id' => $k + 1 + ($item['manhua_id'] + 1),
				], ['title' => $item['title']]);
			}
			getType100Img($item, 'img/' . $id . '/' . $item['id'] . '/');

			continue;
		}
		$l = explode('/', $item['dir_str']);
		$l[3] = $item['cjid'];
		$item['dir_str'] = implode('/', $l);
//		$item['image'] = "/bookimages/15700/29651/eaa49764-a896-43af-9364-4f4630f43059.png";
		preg_match('/(\..*)/si', $item['image'], $m);
		$item['image_suffix'] = isset($m[0]) ? $m[0] : '';
		$flag = 100;
		$db->update('mh_zj', [
			'dir_str' => $item['dir_str']
		], ['id' => $item['id']]);
		$init = 30;
		$goOn = true;
		$suffix = testForImgSuffix($item['image_suffix'], $item['dir_str']);
		echo ' pic suffix:', $suffix, "\r\n";
		$picCount = 0;
		while ($goOn) {
			$imgUrl = 'http://www.xiximh.vip/' . $item['dir_str'] . $picCount . $suffix;
			echo 'get img ', $imgUrl, "\r\n";
			$rs = @file_get_contents($imgUrl);
			if (!$rs) {
				if ($picCount == 0) {
					$total++;
					$picCount++;
					continue;
				}
				$goOn = false;
			}
			if ($rs) {
				$rs = file_put_contents('img/' . $id . '/' . $item['id'] . '/' . $picCount . '.jpg', $rs);
			}
			$picCount++;
			$total++;
		}
	}
}

function getMHByHZW()
{

	$db = new MH();
	$rs = $db->getAll('select max(id)as max from mh ;', []);
	$max = $rs[0]['max'];

	$url = 'http://www.hanhande.net/manga/94/';
	$content = file_get_contents($url);
	preg_match_all('/\<li\>(.*?)\<\/li\>/six', $content, $matchs);
//	print_r($matchs[1]);exit;
	$all = [];

	foreach ($matchs[1] as $key => $val) {
		$val = trim($val);
//		var_dump($val);
		preg_match_all('/\<a.*?href=\"(.*?)\".*?\>.*?\<\/a\>/six', $val, $a);

		if (isset($a[1][0])) {
//			print_r($a[1][0]);
//			echo "\r\n";
			if (is_string($a[1][0]) && preg_match('/^\/manga\/.*?\.html/six', $a[1][0])) {
				$zj = $a[1][0];
//				echo "#---------------------------------------------------------#\r\n";
//				print_r($a[0][0]);
//				echo "\r\n----\r\n";
				preg_match('/\<span\>(.*?)\<\/span\>/six', $a[0][0], $rs);
//				print_r($zj);
//				echo "\r\n----\r\n";
//				print_r($rs);
				if (isset($rs[1])) {
					$all[] = ['name' => $rs[1], 'url' => 'http://www.hanhande.net/' . $zj];
				}
//				echo "\r\n";
//				exit;
			}
		}
	}
	print_r($all);

	$row = $db->getAll("select * from mh  where title like '%海贼王%'", []);
	if (empty($row)) {
		$id = $db->insert('mh', [
			'id' => $max + 1,
			'title' => '海贼王'
		]);
	} else {
		$id = $row[0]['id'];
	}
	foreach ($all as $k => $val) {
		$row = $db->getAll("select * from mh_zj  where manhua_id=? and type=100 and dir_str=?", [$id, $val['url']]);
		print_r($row);
		if (empty($row)) {
			$rs = $db->insert('mh_zj', [
				'manhua_id' => $id,
				'title' => $val['name'],
				'dir_str' => $val['url'],
				'id' => $id + $k + 1,
				'type' => 100,
			]);
			print_r($rs);
		} else {
//			$id = $row[0]['id'];
		}
	}
}

function getType100Img($obj, $savePath)
{
	$url = str_replace('//manga', '/manga', $obj['dir_str']);

	$rs = file_get_contents($url);
//	print_r($url);
//	print_r($rs);
	if ($rs) {
		preg_match('/chapterImages\s\=\s\[(.*?)\]/six', $rs, $m);
//		print_r($m);
		$rs = json_decode('[' . $m[1] . ']', true);
//		print_r($rs);
		echo $savePath;
//		exit;
		if ($rs) {
			foreach ($rs as $k => $r) {
				echo 'get img ', $r, "\r\n";
				$fs = file_get_contents($r);
				if ($fs) {
					file_put_contents($savePath . $k . '.jpg', $fs);
				}

			}
		}
	}
}

function jsonOutput($data)
{
	ob_clean();;
	$rs = json_encode($data, 1);
	exit($rs);
}

function jsonSuccess($data=[])
{
	jsonOutput(['code' => 0, 'data' => $data, 'msg' => '']);
}

function jsonError($msg='', $data=[])
{
	jsonOutput(['code' => -1, 'data' => $data, 'msg' => $msg]);
}
