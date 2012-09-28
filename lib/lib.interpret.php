<?php
/* REQUIRED LIBRARIES */
require("lib.db.php");
require("lib.query.php");

/* BEGIN */
class Interpreter{
	private $db;
	private $compressionToken;
	private $last_result;
	
	private $options;	// Object Options
	
	public function Interpreter($options){
		/* INTERPRETER OPTIONS */
		if(!isset($options) && !empty($options) && is_array($options)){ $this->options = $options; }
		else{
			/* Define Default Options */
			$this->options = array(
				'delimiter' => ';',
				'textIdentifier' => '^',
				'useDelimiterSet' => FALSE,
				'delimiterSet' => '');
		}
		/* BEGIN CONSTRUCT */
		/* TODO: define startup routines. */ 
		/* END CONSTRUCT */
	}
	
	/* Set Database and Connect */
	public function setDB($host, $user, $token, $database){
		try{
			/* If a prior connection exists, disconnect and GC. */
			if(isset($this->db)){ $this->db->close(); unset($this->db);	}
			
			/* create connection */
			$this->db = new DB($host, $user, $token);
			if(!$this->db->set_db($database)) throw new Exception("Database Connection Unsuccessful.", E_USER_ERROR);
		} catch(Exception $e){
			$this->error($e);
		}
	}
	
	/* Return currently connected database object */
	public function getDB(){
		try{
			if(isset($this->options)){ return $this->options[$name]; }
			else { throw new Exception("Option is invalid.", E_USER_WARNING); return FALSE;	}
		} catch (Exception $e) {
			$this->error($e);
		}
	}
	
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
			if($this->verifyQuerySyntax($query) && sanitize($query['query'])){
				$this->setDB(
					$query['host'],
					$query['user'],
					$query['token'],
					$query['database']);
				$this->last_query = array($query, new Query(
					$this->getDB(), 
					$query['query']));
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
				} else if(isset($use) && is_array($use) && $this->verifyQuerySyntax($use)) {
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
				} else if(isset($use) && is_array($use) && $this->verifyQuerySyntax($use)) {
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
	
	/* Translate to server */
	public function translate($data){ /* TODO: Build translate */ }
	
	/* Interpret from server */ 
	public function interpret($data){ /* TODO: Build interpret */ }
	
	/* Get/Set compression token methods */
	public function setCToken($data){ $this->compressionToken = $data; }
	public function getCToken($data){ return $this->compressionToken; }
	
	/* Verify: data to interpret is valid? */
	public function verify($data, $pattern){ /* TODO: Build Verify */ }
	
	/* Sanitize input queries */
	public function sanitize($data){
		if(isset($data) && !empty($data)){
			/* TODO: MAKE SANITIZER NOT FLAG INNOCENT VALUES */
			if($data->stripos($data,"drop") > -1){ return FALSE;  }
			else if($data->stripos($data,"truncate") > -1){ return FALSE; }
			else if($data->stripos($data,"grant") > -1){ return FALSE; }
			else if($data->stripos($data,"insert") > -1){ return FALSE; }
			else if($data->stripos($data,"delete") > -1){ return FALSE; }
			else if($data->stripos($data,"create") > -1){ return FALSE; }
			else if($data->stripos($data,"alter") > -1){ return FALSE; }
			else if($data->stripos($data,"execute") > -1){ return FALSE; }
			else if($data->stripos($data,"file") > -1){ return FALSE; }
			else if($data->stripos($data,"trigger") > -1){ return FALSE; }
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
			
		}
	}
	
	
	/* Program specific utilities */
	private function error($info){ echo $info->getMessage(); }
	private function notify($info){ /* TODO: Build Notifier */ }
	private function logf($info){ /* TODO: Build Logging System */ }
}
/* END */
?>