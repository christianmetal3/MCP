<?php
/* -------------
 * LIB.INTERPRET
 * Developed by Thomas Ibarra
 * http://github.com/SparksD2145
 * -------------
 */

/* BEGIN */
class Interpreter{
	private $db;
	private $compressionToken;
	private $last_result;
	
	private $options;	// Object Options
	
	public function __construct(){ }
	
	/* Option Get/Set methods */
	public function setOption($name, $value){ $this->options[$name] = $value; }
	public function getOption($name){
		try{
			if(isset($this->options)){ return $this->options[$name]; }
			else { throw new Exception("Option is invalid.", E_USER_WARNING); return FALSE;	}
		} catch (Exception $e) {
			$this->error($e);
		}
	}
	
	/* Push query to server */
	public function push($query){
		try{
			if(isset($query) && $this->verifyQuerySyntax($query)){
				DB::$user = $query['user'];
				DB::$password = $query['token'];
				DB::$dbName = $query['database'];
				$this->last_result = DB::query($query['query']);
				return $this->last_result;
			} else {
				throw new Exception("Query syntax invalid.");
			}
		} catch(Exception $e){ $this->error($e); }
	}
	
	/* Compression|Translation Handlers */
	public function compress($data, $use){
		/* Use MySQL binary compression */
		try{
			if(isset($data) && $this->sanitize($data)){
				if(isset($use) && is_bool($use) && $use){
					/* USE LAST QUERY */
					$result = $this->push($this->generateQueryFromLast("SELECT COMPRESS(" . $data . ");"));
					return $result[1]->fetch_row();
				} elseif(isset($use) && is_array($use) && $this->verifyQuerySyntax($use)) {
					$use['query'] = "SELECT COMPRESS(" . $data . ");";
					$result = $this->push($use);
					return $result[1]->fetch_row();
				} else {
					throw new Exception("Invalid Data/Use case in compression query.", E_USER_WARNING);
				}
			} else { throw new Exception("No data given to compress, or data invalid.", E_USER_WARNING); }
		} catch (Exception $e){
			$this->error($e);
		}
	}
	public function decompress($data){
		/* Use MySQL binary compression */
		try{
			if(isset($data) && $this->sanitize($data)){
				if(isset($use) && is_bool($use) && $use){
					/* USE LAST QUERY */
					$result = $this->push($this->generateQueryFromLast("SELECT DECOMPRESS(" . $data . ");"));
					return $result[1]->fetch_row();
				} elseif(isset($use) && is_array($use) && $this->verifyQuerySyntax($use)) {
					$use['query'] = "SELECT DECOMPRESS(" . $data . ");";
					$result = $this->push($use);
					return $result[1]->fetch_row();
				} else {
					throw new Exception("Invalid Data/Use case in decompression query.", E_USER_WARNING);
				}
			} else { throw new Exception("No data given to decompress, or data invalid.", E_USER_WARNING); }
		} catch (Exception $e){
			$this->error($e);
		}
	}
	
	/* Interpret from server */ 
	public function interpret($data, $regex){
		try{
			if(isset($regex)){ $this->setOption('regex', $regex); }  /* if regex is specified, change options */
			else{ /* ignore empty $regex */ }
			/* Check options to ensure regex is set */
			if($this->verify($data, $regex)){
				preg_match_all($this->getOption('regex'), $data, $resulting_array, PREG_PATTERN_ORDER);
				return $resulting_array;
			} else return FALSE;
		} catch (Exception $e){
			$this->error($e);
		}
	}
	/* Get/Set compression token methods */
	public function setCToken($data){ $this->compressionToken = $data; }
	public function getCToken($data){ return $this->compressionToken; }
	
	/* Verify: data to interpret is valid? */
	public function verify($data, $regex){
		try{
			if(isset($regex)){ $this->setOption('regex', $regex); }  /* if regex is specified, change options */
			else{ /* ignore empty $regex */ }
			if(!isset($this->options['regex'])){ throw new Exception("Invalid or empty interpreter regex.", E_USER_WARNING); } 
			if(isset($this->options['regex']) && preg_match($this->getOption('regex'), $data)){ return TRUE; }
			else{ return FALSE; }
		} catch (Exception $e){
			$this->error($e);
		}
	}
	
	/* Sanitize input queries */
	public function sanitize($data){
		if(isset($data) && !empty($data)){
			if($data->stripos($data,"drop") > -1){ return FALSE;  }
			elseif($data->stripos($data,"truncate") > -1){ return FALSE; }
			elseif($data->stripos($data,"grant") > -1){ return FALSE; }
			elseif($data->stripos($data,"create") > -1){ return FALSE; }
			elseif($data->stripos($data,"alter") > -1){ return FALSE; }
			elseif($data->stripos($data,"execute") > -1){ return FALSE; }
			elseif($data->stripos($data,"file") > -1){ return FALSE; }
			elseif($data->stripos($data,"trigger") > -1){ return FALSE; }
			else { return TRUE; /* sanitize successful */ }
			
		} else {
			/* Query is not set or empty, Reject. */
			return FALSE;
		}
	}
	
	/* Verify if query is using proper syntax */
	private function verifyQuerySyntax($query){
		/* Check required keys */
		if(isset($query) && is_array($query) &&
			isset($query['host']) &&
			isset($query['user']) &&
			isset($query['token']) &&
			isset($query['database']) &&
			isset($query['query'])
			){	return TRUE; }  // Check Succeeds.
		else{
			return FALSE;		// Check Fails.
		}
	}
	
	/* Generate proper query */
	public function generateQuery($host, $user, $token, $database, $query){
		$proper_query = array(
			'host' => $host,
			'user' => $user,
			'token' => $token,
			'database' => $database,
			'query' => $query
		);
		return $proper_query;
	}
	
	/* Generate proper query based on last query. */
	public function generateQueryFromLast($query){
		try{
			if(isset($this->last_query) && is_array($this->last_query)){
				return $this->generateQuery(
					$this->last_query[0]['host'],
					$this->last_query[0]['user'],
					$this->last_query[0]['token'],
					$this->last_query[0]['database'],
					$query);
			} else {
				throw new Exception("Last query unavailable.", E_USER_WARNING);
			}
		} catch (Exception $e){
			$this->error($e);			
		}
	}
	
	
	/* Program specific utilities */
	private function error(Exception $info){ throw $info; }
}
/* END */

/* BEGIN DEPENDENCY INTEGRATION */
class DB
{
/*
    Copyright (C) 2008-2011 Sergey Tsalkov (stsalkov@gmail.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
  public static $internal_mysql = null;
  public static $insert_id = 0;
  public static $num_rows = 0;
  public static $affected_rows = 0;
  public static $queryResult = null;
  public static $queryResultType = null;
  public static $old_db = null;
  public static $current_db = null;
  public static $dbName = '';
  public static $user = '';
  public static $password = '';
  public static $host = 'localhost';
  public static $port = null;
  public static $encoding = 'latin1';
  public static $queryMode = 'queryAllRows';
  public static $param_char = '%';
  
  public static $success_handler = false;
  public static $error_handler = true;
  public static $throw_exception_on_error = false;
  public static $nonsql_error_handler = null;
  public static $throw_exception_on_nonsql_error = false;
  
  
  public static function get() {
    $mysql = DB::$internal_mysql;
    
    if ($mysql == null) {
      if (! DB::$port) DB::$port = ini_get('mysqli.default_port');
      DB::$current_db = DB::$dbName;
      $mysql = new mysqli(DB::$host, DB::$user, DB::$password, DB::$dbName, DB::$port);
      
      if ($mysql->connect_error) {
        DB::nonSQLError('Unable to connect to MySQL server! Error: ' . $mysql->connect_error);
      }
      
      $mysql->set_charset(DB::$encoding);
      DB::$internal_mysql = $mysql;
    }
    
    return $mysql;
  }
  
  public static function nonSQLError($message) {
    if (DB::$throw_exception_on_nonsql_error) {
      $e = new MeekroDBException($message);
      throw $e;
    }
    
    $error_handler = is_callable(DB::$nonsql_error_handler) ? DB::$nonsql_error_handler : 'meekrodb_error_handler';
        
    call_user_func($error_handler, array(
      'type' => 'nonsql',
      'error' => $message
    ));
  }
  
  public static function debugMode($handler = true) {
    DB::$success_handler = $handler;
  }
  
  public static function insertId() { return DB::$insert_id; }
  public static function affectedRows() { return DB::$affected_rows; }
  public static function count() { $args = func_get_args(); return call_user_func_array('DB::numRows', $args); }
  public static function numRows() { return DB::$num_rows; }
  
  public static function useDB() { $args = func_get_args(); return call_user_func_array('DB::setDB', $args); }
  public static function setDB($dbName) {
    $db = DB::get();
    DB::$old_db = DB::$current_db;
    if (! $db->select_db($dbName)) DB::nonSQLError("Unable to set database to $dbName");
    DB::$current_db = $dbName;
  }
  
  
  public static function startTransaction() {
    DB::queryNull('START TRANSACTION');
  }
  
  public static function commit() {
    DB::queryNull('COMMIT');
  }
  
  public static function rollback() {
    DB::queryNull('ROLLBACK');
  }
  
  public static function escape($str) {
    $db = DB::get();
    return $db->real_escape_string($str);
  }
  
  public static function sanitize($value) {
    if (is_object($value) && ($value instanceof MeekroDBEval)) {
      $value = $value->text;
    } else {
      if (is_array($value) || is_object($value)) $value = serialize($value);
      
      if (is_string($value)) $value = "'" . DB::escape($value) . "'";
      else if (is_null($value)) $value = 'NULL';
      else if (is_bool($value)) $value = ($value ? 1 : 0);
    }
    
    return $value;
  }
  
  private static function formatTableName($table) {
    $table = str_replace('`', '', $table);
    if (strpos($table, '.')) {
      list($table_db, $table_table) = explode('.', $table, 2);
      $table = "`$table_db`.`$table_table`";
    } else {
      $table = "`$table`";
    }
    
    return $table;
  }
  
  private static function prependCall($function, $args, $prepend) {
    array_unshift($args, $prepend);
    return call_user_func_array($function, $args);
  }
  
  private static function wrapStr($strOrArray, $wrapChar, $escape = false) {
    if (! is_array($strOrArray)) {
      if ($escape) return $wrapChar . DB::escape($strOrArray) . $wrapChar;
      else return $wrapChar . $strOrArray . $wrapChar;
    } else {
      $R = array();
      foreach ($strOrArray as $element) {
        $R[] = DB::wrapStr($element, $wrapChar, $escape);
      }
      return $R;
    }
      
  }
  
  public static function freeResult($result) {
    if (! ($result instanceof MySQLi_Result)) return;
    return $result->free();
  }
  
  public static function update() {
    $args = func_get_args();
    $table = array_shift($args);
    $params = array_shift($args);
    $where = array_shift($args);
    $buildquery = "UPDATE " . self::formatTableName($table) . " SET ";
    $keyval = array();
    foreach ($params as $key => $value) {
      $value = DB::sanitize($value);
      $keyval[] = "`" . $key . "`=" . $value;
    }
    
    $buildquery = "UPDATE " . self::formatTableName($table) . " SET " . implode(', ', $keyval) . " WHERE " . $where;
    array_unshift($args, $buildquery);
    call_user_func_array('DB::queryNull', $args);
  }
  
  public static function insertOrReplace($which, $table, $datas, $options=array()) {
    $datas = unserialize(serialize($datas)); // break references within array
    $keys = null;
    
    if (isset($datas[0]) && is_array($datas[0])) {
      $many = true;
    } else {
      $datas = array($datas);
      $many = false;
    }
    
    foreach ($datas as $data) {
      if (! $keys) {
        $keys = array_keys($data);
        if ($many) sort($keys);
      }
      
      $insert_values = array();
      
      foreach ($keys as $key) {
        if ($many && !isset($data[$key])) DB::nonSQLError('insert/replace many: each assoc array must have the same keys!'); 
        $datum = $data[$key];
        $datum = DB::sanitize($datum);
        $insert_values[] = $datum;
      }
      
      
      $values[] = '(' . implode(', ', $insert_values) . ')';
    }
    
    $table = self::formatTableName($table);
    $keys_str = implode(', ', DB::wrapStr($keys, '`'));
    $values_str = implode(',', $values);
    
    if (isset($options['ignore']) && $options['ignore'] && strtolower($which) == 'insert') { 
      DB::queryNull("INSERT IGNORE INTO $table ($keys_str) VALUES $values_str");
      
    } else if (isset($options['update']) && $options['update'] && strtolower($which) == 'insert') {
      DB::queryNull("INSERT INTO $table ($keys_str) VALUES $values_str ON DUPLICATE KEY UPDATE {$options['update']}");
      
    } else { 
      DB::queryNull("$which INTO $table ($keys_str) VALUES $values_str");
    }
  }
  
  public static function insert($table, $data) { return DB::insertOrReplace('INSERT', $table, $data); }
  public static function insertIgnore($table, $data) { return DB::insertOrReplace('INSERT', $table, $data, array('ignore' => true)); }
  public static function replace($table, $data) { return DB::insertOrReplace('REPLACE', $table, $data); }
  
  public static function insertUpdate() {
    $args = func_get_args();
    $table = array_shift($args);
    $data = array_shift($args);
    
    if (! isset($args[0])) { // update will have all the data of the insert
      if (isset($data[0]) && is_array($data[0])) { //multiple insert rows specified -- failing!
        DB::nonSQLError("Badly formatted insertUpdate() query -- you didn't specify the update component!");
      }
      
      $args[0] = $data;
    }
    
    if (is_array($args[0])) {
      $keyval = array();
      foreach ($args[0] as $key => $value) {
        $value = DB::sanitize($value);
        $keyval[] = "`" . $key . "`=" . $value;
      }
      $updatestr = implode(', ', $keyval);
      
    } else {
      $updatestr = call_user_func_array('DB::parseQueryParams', $args);
    }
    
    return DB::insertOrReplace('INSERT', $table, $data, array('update' => $updatestr)); 
  }
  
  public static function delete() {
    $args = func_get_args();
    $table = self::formatTableName(array_shift($args));
    $where = array_shift($args);
    $buildquery = "DELETE FROM $table WHERE $where";
    array_unshift($args, $buildquery);
    call_user_func_array('DB::queryNull', $args);
  }
  
  public static function sqleval() {
    $args = func_get_args();
    $text = call_user_func_array('DB::parseQueryParams', $args);
    return new MeekroDBEval($text);
  }
  
  public static function columnList($table) {
    return DB::queryOneColumn('Field', "SHOW COLUMNS FROM $table");
  }
  
  public static function tableList($db = null) {
    if ($db) DB::useDB($db);
    $result = DB::queryFirstColumn('SHOW TABLES');
    if ($db && DB::$old_db) DB::useDB(DB::$old_db);
    return $result;
  }
  
  public static function parseQueryParamsOld() {
    $args = func_get_args();
    $sql = array_shift($args);
    $types = array_shift($args);
    $types = str_split($types);
    
    foreach ($args as $arg) {
      $type = array_shift($types);
      $pos = strpos($sql, '?');
      if ($pos === false) DB::nonSQLError("Badly formatted SQL query: $sql");  
      
      if ($type == 's') $replacement = "'" . DB::escape($arg) . "'";
      else if ($type == 'i') $replacement = intval($arg);
      else DB::nonSQLError("Badly formatted SQL query: $sql");
      
      $sql = substr_replace($sql, $replacement, $pos, 1);
    }
    return $sql;
  }
  
  public static function parseQueryParamsNew() {
    $args = func_get_args();
    $sql = array_shift($args);
    $posList = array();
    $pos_adj = 0;
    $param_char_length = strlen(DB::$param_char);
    $types = array(
      DB::$param_char . 'll', // list of literals
      DB::$param_char . 'ls', // list of strings
      DB::$param_char . 'l',  // literal
      DB::$param_char . 'li', // list of integers
      DB::$param_char . 'ld', // list of decimals
      DB::$param_char . 'lb', // list of backticks
      DB::$param_char . 's',  // string
      DB::$param_char . 'i',  // integer
      DB::$param_char . 'd',  // double / decimal
      DB::$param_char . 'b',  // backtick
      DB::$param_char . 'ss'  // search string (like string, surrounded with %'s)
    );
    
    foreach ($types as $type) {
      $lastPos = 0;
      while (($pos = strpos($sql, $type, $lastPos)) !== false) {
        $lastPos = $pos + 1;
        if (isset($posList[$pos]) && strlen($posList[$pos]) > strlen($type)) continue;
        $posList[$pos] = $type;
      }
    }
    
    ksort($posList);
    
    foreach ($posList as $pos => $type) {
      $arg = array_shift($args);
      $type = substr($type, $param_char_length);
      $length_type = strlen($type) + $param_char_length;
      
      if (in_array($type, array('s', 'i', 'd', 'b', 'l'))) {
        $array_type = false;
        $arg = array($arg);
        $type = 'l' . $type;
      } else if ($type == 'ss') {
        $result = "'%" . DB::escape(str_replace(array('%', '_'), array('\%', '\_'), $arg)) . "%'";
      } else {
        $array_type = true;
        if (! is_array($arg)) DB::nonSQLError("Badly formatted SQL query: $sql -- expecting array, but didn't get one!");
      }
      
      if ($type == 'ls') $result = DB::wrapStr($arg, "'", true);
      else if ($type == 'li') $result = array_map('intval', $arg);
      else if ($type == 'ld') $result = array_map('floatval', $arg);
      else if ($type == 'lb') $result = array_map('DB::formatTableName', $arg);
      else if ($type == 'll') $result = $arg;
      else if (! $result) DB::nonSQLError("Badly formatted SQL query: $sql");
      
      if (is_array($result)) {
        if (! $array_type) $result = $result[0];
        else $result = '(' . implode(',', $result) . ')';
      }
      
      $sql = substr_replace($sql, $result, $pos + $pos_adj, $length_type);
      $pos_adj += strlen($result) - $length_type;
    }
    return $sql;
  }
  
  public static function parseQueryParams() {
    $args = func_get_args();
    if (count($args) < 2) return $args[0];
    
    if (is_string($args[1]) && preg_match('/^[is]+$/', $args[1]) && substr_count($args[0], '?') > 0)
      return call_user_func_array('DB::parseQueryParamsOld', $args);
    else
      return call_user_func_array('DB::parseQueryParamsNew', $args);
  }
  
  public static function quickPrepare() { $args = func_get_args(); return call_user_func_array('DB::query', $args); }
  
  public static function query() {
    $args = func_get_args();
    if (DB::$queryMode == 'buffered' || DB::$queryMode == 'unbuffered') {
      return DB::prependCall('DB::queryHelper', $args, DB::$queryMode);
    } else {
      return call_user_func_array('DB::queryAllRows', $args);
    }
  }
  
  public static function queryNull() { $args = func_get_args(); return DB::prependCall('DB::queryHelper', $args, 'null'); }
  public static function queryRaw() { $args = func_get_args(); return DB::prependCall('DB::queryHelper', $args, 'buffered'); }
  public static function queryBuf() { $args = func_get_args(); return DB::prependCall('DB::queryHelper', $args, 'buffered'); }
  public static function queryUnbuf() { $args = func_get_args(); return DB::prependCall('DB::queryHelper', $args, 'unbuffered'); }
  
  public static function queryHelper() {
    $args = func_get_args();
    $type = array_shift($args);
    if ($type != 'buffered' && $type != 'unbuffered' && $type != 'null') {
      DB::nonSQLError('Error -- first argument to queryHelper must be buffered or unbuffered!');
    }
    $is_buffered = ($type == 'buffered');
    $is_null = ($type == 'null');
    
    $sql = call_user_func_array('DB::parseQueryParams', $args);
    
    $db = DB::get();
    
    if (DB::$success_handler) $starttime = microtime(true);
    $result = $db->query($sql, $is_buffered ? MYSQLI_STORE_RESULT : MYSQLI_USE_RESULT);
    if (DB::$success_handler) $runtime = microtime(true) - $starttime;
    
    if (!$sql || $error = DB::checkError()) {
      if (DB::$error_handler) {
        $error_handler = is_callable(DB::$error_handler) ? DB::$error_handler : 'meekrodb_error_handler';
        
        call_user_func($error_handler, array(
          'type' => 'sql',
          'query' => $sql,
          'error' => $error
        ));
      }
      
      if (DB::$throw_exception_on_error) {
        $e = new MeekroDBException($error, $sql);
        throw $e;
      }
    } else if (DB::$success_handler) {
      $runtime = sprintf('%f', $runtime * 1000);
      $success_handler = is_callable(DB::$success_handler) ? DB::$success_handler : 'meekrodb_debugmode_handler';
      
      call_user_func($success_handler, array(
        'query' => $sql,
        'runtime' => $runtime
      )); 
    }
    
    DB::$queryResult = $result;
    DB::$queryResultType = $type;
    DB::$insert_id = $db->insert_id;
    DB::$affected_rows = $db->affected_rows;
    
    if ($is_buffered) DB::$num_rows = $result->num_rows;
    else DB::$num_rows = null;
    
    if ($is_null) {
      DB::freeResult($result);
      DB::$queryResult = DB::$queryResultType = null;
      return null;
    }
    
    return $result;
  }
  
  public static function queryAllRows() {
    $args = func_get_args();
    
    $query = call_user_func_array('DB::queryUnbuf', $args);
    $result = DB::fetchAllRows($query);
    DB::freeResult($query);
    DB::$num_rows = count($result);
    
    return $result;
  }
  
  public static function queryAllArrays() {
    $args = func_get_args();
    
    $query = call_user_func_array('DB::queryUnbuf', $args);
    $result = DB::fetchAllArrays($query);
    DB::freeResult($query);
    DB::$num_rows = count($result);
    
    return $result;
  }
  
  public static function queryOneList() { $args = func_get_args(); return call_user_func_array('DB::queryFirstList', $args); }
  public static function queryFirstList() {
    $args = func_get_args();
    $query = call_user_func_array('DB::queryUnbuf', $args);
    $result = DB::fetchArray($query);
    DB::freeResult($query);
    return $result;
  }
  
  public static function queryOneRow() { $args = func_get_args(); return call_user_func_array('DB::queryFirstRow', $args); }
  public static function queryFirstRow() {
    $args = func_get_args();
    $query = call_user_func_array('DB::queryUnbuf', $args);
    $result = DB::fetchRow($query);
    DB::freeResult($query);
    return $result;
  }
  
  
  public static function queryFirstColumn() { 
    $args = func_get_args();
    $results = call_user_func_array('DB::queryAllArrays', $args);
    $ret = array();
    
    if (!count($results) || !count($results[0])) return $ret;
    
    foreach ($results as $row) {
      $ret[] = $row[0];
    }
    
    return $ret;
  }
  
  public static function queryOneColumn() {
    $args = func_get_args();
    $column = array_shift($args);
    $results = call_user_func_array('DB::queryAllRows', $args);
    $ret = array();
    
    if (!count($results) || !count($results[0])) return $ret;
    if ($column === null) {
      $keys = array_keys($results[0]);
      $column = $keys[0];
    }
    
    foreach ($results as $row) {
      $ret[] = $row[$column];
    }
    
    return $ret;
  }
  
  public static function queryFirstField() { 
    $args = func_get_args();
    $row = call_user_func_array('DB::queryFirstList', $args);
    if ($row == null) return null;    
    return $row[0];
  }
  
  public static function queryOneField() {
    $args = func_get_args();
    $column = array_shift($args);
    
    $row = call_user_func_array('DB::queryOneRow', $args);
    if ($row == null) { 
      return null;
    } else if ($column === null) {
      $keys = array_keys($row);
      $column = $keys[0];
    }  
    
    return $row[$column];
  }
  
  private static function checkError() {
    $db = DB::get();
    if ($db->error) {
      $error = $db->error;
      $db->rollback();
      return $error;
    }
    
    return false;
  }
  
  public static function fetchRow($result = null) {
    if ($result === null) $result = DB::$queryResult;
    if (! ($result instanceof MySQLi_Result)) return null;
    return $result->fetch_assoc();
  }
  
  public static function fetchAllRows($result = null) {
    $A = array();
    while ($row = DB::fetchRow($result)) {
      $A[] = $row;
    }
    return $A;
  }
  
  public static function fetchArray($result = null) {
    if ($result === null) $result = DB::$queryResult;
    if (! ($result instanceof MySQLi_Result)) return null;
    return $result->fetch_row();
  }
  
  public static function fetchAllArrays($result = null) {
    $A = array();
    while ($row = DB::fetchArray($result)) {
      $A[] = $row;
    }
    return $A;
  }
  
  
}

class WhereClause {
  public $type = 'and'; //AND or OR
  public $negate = false;
  public $clauses = array();
  
  function __construct($type) {
    $type = strtolower($type);
    if ($type != 'or' && $type != 'and') DB::nonSQLError('you must use either WhereClause(and) or WhereClause(or)');
    $this->type = $type;
  }
  
  function add() {
    $args = func_get_args();
    if ($args[0] instanceof WhereClause) {
      $this->clauses[] = $args[0];
      return $args[0];
    } else {
      $r = call_user_func_array('DB::parseQueryParams', $args);
      $this->clauses[] = $r;
      return $r;
    }
  }
  
  function negateLast() {
    $i = count($this->clauses) - 1;
    if (!isset($this->clauses[$i])) return;
    
    if ($this->clauses[$i] instanceof WhereClause) {
      $this->clauses[$i]->negate();
    } else {
      $this->clauses[$i] = 'NOT (' . $this->clauses[$i] . ')';
    }
  }
  
  function negate() {
    $this->negate = ! $this->negate;
  }
  
  function addClause($type) {
    $r = new WhereClause($type);
    $this->add($r);
    return $r;
  }
  
  function count() {
    return count($this->clauses);
  }
  
  function text() {
    if (count($this->clauses) == 0) return '(1)';
    
    $A = array();
    foreach ($this->clauses as $clause) {
      if ($clause instanceof WhereClause) $clause = $clause->text();
      $A[] = '(' . $clause . ')';
    }
    
    $A = array_unique($A);
    if ($this->type == 'and') $A = implode(' AND ', $A);
    else $A = implode(' OR ', $A);
    
    if ($this->negate) $A = '(NOT ' . $A . ')';
    return $A;
  }
}

class DBTransaction {
  private $committed = false;
  
  function __construct() { 
    DB::startTransaction(); 
  }
  function __destruct() { 
    if (! $this->committed) DB::rollback(); 
  }
  function commit() {
    DB::commit();
    $this->committed = true;
  }
  
  
}

class MeekroDBException extends Exception {
  protected $query = '';
  
  function __construct($message='', $query='') {
    parent::__construct($message);
    $this->query = $query;
  }
  
  public function getQuery() { return $this->query; }
}

function meekrodb_error_handler($params) {
  if (isset($params['query'])) $out[] = "QUERY: " . $params['query'];
  if (isset($params['error'])) $out[] = "ERROR: " . $params['error'];
  $out[] = "";
  
  if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
    echo implode("\n", $out);
  } else {
    echo implode("<br>\n", $out);
  }
  
  debug_print_backtrace();
  
  die;
}

function meekrodb_debugmode_handler($params) {
  echo "QUERY: " . $params['query'] . " [" . $params['runtime'] . " ms]";
  if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
    echo "\n";
  } else {
    echo "<br>\n";
  }
}

class MeekroDBEval {
  public $text = '';
  
  function __construct($text) {
    $this->text = $text;
  }
}
/* END DEPENDENCY INTEGRATION */

?>