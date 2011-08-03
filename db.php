<?php 
// dom parser class
require_once('adodb_lite/adodb.inc.php');
// records in associative array
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
require_once('db_info.php');

class db
{
	private $host = HOST;
	private $user = USER;
	private $pass = PASS;
	private $database = DB;
	private $db;
	private $table = "";
	
	public function __construct($table){
		$this->db = NewADOConnection('mysql');
		$this->db->Connect($this->host, $this->user, $this->pass, $this->database);
		$this->table = $table;
		//$this->db->debug = true;
	}
	
	public function getData(){
		$r = $this->db->Execute("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 0, 3");
		if ($r === false){
			return false;
		}else{
			$results = array();
			 while (!$r->EOF) {
			 	$results[] = $r->fields;
				$r->MoveNext();
			 }
			 return $results;
		}	
	}
	
	public function latestResult(){
		$r = $this->db->Execute("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 0, 1");
		if ($r === false){
			return false;
		}else{
			return $r->fields;
		}	
	}
	
	public function findField($table, $select, $field, $value)
	{
		$r = $this->db->Execute("SELECT $select FROM {$table} WHERE $field='$value' LIMIT 0, 1");
		if ($r === false){
			return false;
		}else{
			return $r->fields[$select];
		}
		
	}
	
	public function allSubscribers()
	{
		$subscribers = array();
		$r = $this->db->Execute("SELECT * from subscriptions WHERE suspended = 0");
		if ($r !== false){
			while (!$r->EOF) {
			 	$subscribers[] = $r->fields;
				$r->MoveNext();
			 }
		}
		return $subscribers;
	}
	
	public function getDbObj()
	{
		return $this->db;
	}
	
	public function insert($str){
		$this->db->Execute("INSERT INTO {$this->table} VALUE(
		{$str}
		)");
	}
	public function update($str, $where)
	{
		$this->db->Execute("UPDATE {$this->table} SET $str WHERE $where");
	}
	
	
}
?>