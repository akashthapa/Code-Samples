<?php 
class Megamillion extends DomParser
{
	/*
	* string
	*/
	private $_date;
	/*
	* string
	*/
	private $_drawing;
	/*
	* Int
	*/
	private $_megaball;
	/*
	* string
	*/
	private $_numbers;
	/*
	* string
	*/
	private $_jackpot;
	/*
	* string
	*/
	private $_megapiler;
	
	/*
	$url - site url required
	$table - name of table required
	$find_dom = optional
	*/
	function __construct($url, $table, $find_dom='table#mytable')
	{
		parent::__construct($url, $table, $find_dom);
		if(!empty($this->_dom))
		{
			/*
			numbers
			*/
			$numbers = strip_tags($this->_dom->find('div.mm_numbers', 0)->innertext);
			$this->_numbers = $this->getNumbers($numbers);
			/*
			 megaball
			*/
			$this->_megaball = $this->_dom->find('div.mm_megaball', 0)->innertext;
			/*
			Next drawing
			*/
			$this->_drawing = $this->getDate($this->_dom->find('div.mm_nextdrawing', 0)->innertext);
			/*
			date
			*/
			$this->_date = $this->_dom->find('span.drawings_header', 0)->innertext;
			/*
			next jackpot
			*/
			$this->_jackpot = $this->regex($this->_dom->find('div.mm_nextdrawing', 0)->innertext, "/[\$](.*)/");
			/*
			megapiler
			*/
			if(!empty($this->_dom->find('div.megaplier_numbers2', 0)->innertext)){
				$this->_megapiler = 'x2';
			}elseif(!empty($this->_dom->find('div.megaplier_numbers3', 0)->innertext)){
				$this->_megapiler = 'x3';
			}elseif(!empty($this->_dom->find('div.megaplier_numbers4', 0)->innertext)){
				$this->_megapiler = 'x4';
			}else{
				$this->_megapiler = 'NA';
			}
			// latest result
			$this->get_latest_result = "Mega Millions\n".$this->addSpace($this->get_numbers())." \nMegaball: {$this->get_megaball()}  Megaplier: {$this->get_megapiler()}";
		/*
		* if dom object is empty or not created
		*/
		}else
		{
			die("Data is empty");
		}
	}
	/*
	* returns in array
	*/
	public function get_numbers()
	{
		return $this->_numbers;
	}
	/*
	# mega ball returns integer
	*/
	public function get_megaball()
	{
		return (int) $this->_megaball;
	}
	
	/*
	# string date 'Mar. 4' converts to 2011-03-04 format
	*/
	public function get_date($dateformate='Y-m-d')
	{
		
		// array month
		$months = array('jan'=>1,'feb'=>2,'mar'=>3,'apr'=>4,'may'=>5,'jun'=>6,'jul'=>7,'aug'=>8,'sep'=>9,'oct'=>10,'nov'=>11,'dec'=>12);
		// get date string
		$dateStr = trim(str_replace(', Winning Numbers', '', $this->_date));
		
		//get month
		if(!preg_match("/^[a-zA-Z]*/", $dateStr, $m)) return "";
		$month = $months[strtolower($m[0])];		
		// get date
		if(!preg_match("/[0-9]+/", $dateStr, $d)) return "";
		$date = $d[0];
		// convert date to mysql date format
		$newDate = $this->mysql_date_format($month.'/'.$date.'/'.date('Y'));
		// returns e.g. 2011-03-04
		return $newDate;
	}
	/*
	# jackpot
	*/
	public function get_jackpot()
	{
		return $this->_jackpot;
	}
	/*
	# mega piler number
	*/
	public function get_megapiler()
	{
		return $this->_megapiler;
	}
	
	/*
	* save game
	*/
	public function save()
	{
		$d = $this->get_date();
		if(!$this->compare('date', $d))
		{
			if($this->insert("NULL, 
								'$d',
								'{$this->_drawing}',  
								{$this->get_megaball()}, 
								'{$this->get_megapiler()}', 
								'{$this->get_jackpot()}', 
								'{$this->get_numbers()}'"
								))
			{
				return true;
			}else{
				return false;
			}
		}else{
			if($this->isCompleteData($d))
			{
				$sql = "UPDATE {$this->table} SET
				date = '$d',
				drawing = '{$this->_drawing}',
				megaball = {$this->get_megaball()},
				megapiler = '{$this->get_megapiler()}',
				jackpot = '{$this->get_jackpot()}',
				numbers = '{$this->get_numbers()}'
				WHERE id = {$this->isCompleteData($d)}
				";
				$this->db->Execute($sql);
			}
			return false;
		}
	}
	// get last 7 data :important - call this function before executing xml
	private function _results()
	{
		if($this->compare('date', $this->get_date()))
			$last = 7;
		else
			$last = 6;
		$r = $this->db->Execute("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 0, $last");
		if ($r === false){
			return false;
		}else{
			$results = array();
			 while (!$r->EOF) {
			 	$results[] = $r->fields;
				$r->MoveNext();
			 }
			 if($last == 6)
			 {
			 	$results[] = array(
					'date' => $this->get_date(),
					'megaball' => $this->get_megaball(),
					'megapiler' => $this->get_megapiler(),
					'jackpot' => $this->get_jackpot(),
					'numbers' => $this->get_numbers()
				);
			 }
			 return $results;
		 }
	}
	//
	public function initResult()
	{
		$this->_results = $this->_results();
	}

	
}
?>