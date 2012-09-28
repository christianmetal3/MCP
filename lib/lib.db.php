<?php 

class DB {
	private $host; 
	private $user; 
	private $pass; 
	private $linkID; 
	private $dbID; 
	private $numrows; 
	private $numfields; 
	private $tables; 
	private $dbs; 
	private $db; 

	public function DB($hostaddr, $user,$pass) {
		$this->user = $this->set_user($user); 
		$this->pass = $this->set_pass($pass); 
		$this->host = $hostaddr;
		$this->connect(); 
	} 
	 
	public function set_user($value){
		return $this->user = $value; 
	} 
	public function set_pass($value) {
		return $this->pass = $value; 
	} 
	 
	public function get_user() {
		return $this->user;
	} 
	 
	public function connect(){
		if (!$this->linkID = mysql_connect($this->host,$this->user,$this->pass)){
			return false;
		} else { return true; }
	} 
	 
	public function close() {
		if (empty($this->linkID)){
			return false;
		} else {
			mysql_close($this->linkID); 
			return true;
		}
	} 
	 
	public function list_dbs() {
		if (!$this->dbs = mysql_list_dbs($this->linkID)){
			return false;
		} else { return $this->dbs; } 
	} 
	 
	public function set_db($value) 
	{	 
		if (!$this->dbID = mysql_select_db($value,$this->linkID)){
			return false;
		} else { $this->db = $value; return true; }
	}
	public function list_tables() {
		if (!$this->tables = mysql_list_tables($this->db)){
			return false; 
		} else { return $this->tables; }
	} 
	 
	public function last_row($tablename) {
		if (!$max_query = mysql_query("SELECT max(id) AS max_row FROM $tablename")){
			return false;
		} else { $temp = mysql_fetch_row($max_query); return ($temp[0]); }
	} 
}  
?> 
