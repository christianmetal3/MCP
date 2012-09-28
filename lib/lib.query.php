<?php 
class Query 
    { 
	private $result; 
	private $affected_rows = 0; 
	private $numrows; 
	private $numfields; 
	private $row_data = array(); 
	private $row_obj; 
	private $field_name; 
	private $field_type; 
	private $field_len; 
	private $field_flags; 
	private $last_query = array(); 
	 
	public function Query($db,$value) {
	    if (!isset($db) || empty($db)) $this->killClass(); 
	    else {
		$this->last_query["start_time"] = $this->getmicrotime(); 
		if (!$this->result = mysql_query($value)){ 
			return false;
		} 
		$this->last_query["end_time"] = $this->getmicrotime();
		 
		$this->affected_rows = mysql_affected_rows($db->linkID); 
		 
		if (eregi("^SELECT", $value)){ 
		    $this->numrows = mysql_num_rows($this->result); 
		    $this->numfields = mysql_num_fields($this->result); 
		} 
		else{ 
		    $this->numrows = 0; 
		    $this->numfields = 0;     
		} 
		$this->last_query["sql"] = $value; 
	    } 
	} 
	
	public function fetch_array() {
	    if ($this->affected_rows <> 0) {
		$this->row_data = mysql_fetch_array($this->result);	 
	    } 
	} 
	 
	public function fetch_row() {
	    if ($this->affected_rows <> 0) {
		$this->row_data = mysql_fetch_row($this->result); 
	    } 
	} 
	 
	public function fetch_object() {
	    if ($this->affected_rows <> 0) {
		$this->row_obj = mysql_fetch_object($this->result); 
	    } 
	} 
	 
	public function field_info($id) {
	    if (empty($this->result)) return false; 
	    $this->field_name = mysql_field_name($this->result,$id); 
	    $this->field_type = mysql_field_type($this->result,$id); 
	    $this->field_len = mysql_field_len($this->result,$id); 
	    $this->field_flags = mysql_field_flags($this->result,$id); 
	} 
	 
	/* THIS FUNCTION IS FOR TESTING PURPOSES ONLY */ 
	public function query_info() {
	    echo "<u>Your Previous Query Consisted of:</u><br>"; 
	    echo "SQL = '".$this->last_query["sql"]."'<br>"; 
	    $temp = ($this->last_query["end_time"] - $this->last_query["start_time"]); 
	    $temp *= 1000; 
	    $temp = number_format($temp, 3); 
	    echo "Time Elapsed: ".$temp."(ms)<br>"; 
	    echo "Number of Records: ".$this->numrows."<br>"; 
	    echo "Number of Rows Affected: ".$this->affected_rows; 
	} 
	/* THIS FUNCTION IS FOR TESTING PURPOSES ONLY */ 
	public function print_results() {
	    if ($this->affected_rows == 0) return; 
	    for ($i = 0; $i < $this->numrows; $i++) {
		$this->fetch_row(); 
		echo "+"; 
		for ($j = 0; $j < $this->numfields; $j++){ 
		    echo "-".$this->row_data[$j]."-"; 
		} 
		echo "+<br>"; 
	    }     
	}
	 
	public function getmicrotime() { 
	    list($usec, $sec) = explode(" ",microtime());  
	    return ((float)$usec + (float)$sec);  
	} 
	 
	public function close() {
	    mysql_free_result($this->result); 
	}	     
}  
?> 
