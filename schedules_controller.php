<?php
class SchedulesController extends AppController {

	var $name = 'Schedules';
	var $uses = array('Schedule', 'School', 'Teacher', 'Standard', 'User');
	
	
	function index($month = null, $year = null) {
		$this->layout = 'ajax';
		//$this->autoRender = false;
		$day_list = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
		
		if(!$year || !$month){
			$year = date('Y'); // returns 2011
			$month = date('m'); // returns Month name like 'June'
		}
		$today = time();
		$total_days = date('t', mktime(0,0,0,$month,1,$year)); // returns days in a month
		$first_day = date('D', mktime(0,0,0,$month,1,$year)); // returns fisrt date falls on what day
		$title = date('F', mktime(0,0,0,$month,1,$year));
		$next = date('m/Y', mktime(0,0,0,$month+1,1,$year));
		$prev = date('m/Y', mktime(0,0,0,$month-1,1,$year));
		//How many Blank days
		for($i=0; $i<count($day_list); $i++)
		{
			if($first_day == $day_list[$i]){ // ex: if 
				$beforeBlank = $i;
			}
		}
		$afterBlank = 34-(($total_days-1)+$beforeBlank);
		//make array of dates
		$calendar = array();
		/*
		 * GEt teacher id through user id
		 *
		 */
		$this->Teacher->recursive = -1;
		$t = $this->Teacher->findByUser_id($this->Auth->user('id'), 'id');
		// loop
		for($i=1; $i<$total_days+1; $i++)
		{
			$d = mktime(0,0,0,$month,$i,$year);
			$day = date('D', mktime(0,0,0,$month,$i,$year)); // returns day like 'Sun' or 'Mon' on particular day/month/year
			$today = time();
			$current_date = date('Y-m-d', $d);
			$this->Schedule->recursive = 1;
			$sdl = $this->Schedule->find('first', array('conditions'=>array('Schedule.date'=>$current_date, 'Schedule.teacher_id'=>$t['Teacher']['id'])));
			
			$sdl['active'] = 0;
			if($d > $today){
				$sdl['active'] = 1;
			}
			$sdl['date'] = $current_date;
			$sdl['day'] = $day;
			$calendar[] = $sdl;
		}
		$this->set(compact('beforeBlank', 'afterBlank', 'calendar', 'month', 'title', 'year', 'total_days', 'first_days', 'day_list', 'next', 'prev'));
		
	}
	
	function calendar()
	{
		
	}
	
	function isAuthorized()
	{
		return true;
	}

	function view() {
		// School id
		$this->School->recursive = -1;
		$s = $this->School->findByUser_id($this->Auth->user('id'), 'id');
		$this->set('schedule', $this->Schedule->find('all', array('conditions'=>array('School.id'=>$s['School']['id']))));
	}
	
	function details($id)
	{
		$this->layout = 'ajax';
		$this->School->recursive = -1;
		/*$s = $this->School->findByUser_id($this->Auth->user('id'), 'id');*/
		$this->set('schedule', $this->Schedule->read(null, $id));
	}
	
	function search()
	{	
		//
		$countries = $this->Teacher->Country->find('list');
		$standards = $this->Teacher->Standard->find('list');
		$this->set(compact('countries', 'standards'));
	}
	
	function available_teachers(){
		$this->layout = 'ajax';
		if(!empty($this->data))
		{
			$date = $this->data['Schedule']['key'];
			if($date >= date('Y-m-d'))
			{
				$custom = "SELECT * 
				FROM teachers as Teacher
				LEFT JOIN schedules AS Schedule ON Schedule.teacher_id = Teacher.id 
				LEFT JOIN standards_teachers as st ON st.standard_id = {$this->data['Schedule']['standard']} AND st.teacher_id = Teacher.id			
				WHERE Schedule.date = '$date' AND Schedule.hired = 0			
				";
				
				$this->set('result', $this->Schedule->query($custom));
		}else{
				$this->Session->setFlash('Sorry! selected date is older, please select newer date and try again');
			}
		}
	}
	/* 
	 * School hires teacher
	 * @id = teacher id
	 */
	function hire($id = null, $date=null)
	{
		$this->autoRender = false;
		if(!$id || !$date)
		{
			$this->redirect($this->referer());
		}
		
		$res = $this->Schedule->find('first', array('conditions'=>array(
													'Schedule.hired'=>0,
													'Schedule.teacher_id'=>$id,
													'Schedule.date'=>$date
													)
												)
											);
		// find teacher email address from User table
		$teacher = $this->User->findById($res['Teacher']['user_id'], 'username');
		
		if(empty($res)){
			$this->Session->setFlash('Teacher is not available on this date');
			$this->redirect($this->referer());
		}
		// School id
		$this->School->recursive = -1;
		$s = $this->School->findByUser_id($this->Auth->user('id'), 'id');

		$this->data['Schedule'] = $res['Schedule'];
		$this->data['Schedule']['hired'] = 1;
		$this->data['Schedule']['school_id'] = $s['School']['id'];
		if($this->Schedule->save($this->data)){
			// Send NOTIFICATION to user
				// value for template
				//$this->School->recursive = 2;
				$this->set('result', $this->Schedule->find('first', array('conditions'=>array('Schedule.id'=>$res['Schedule']['id']))));
				$webmaster = Configure::read('Config.webmaster');
				$this->Email->to = $teacher['User']['username']; // username is email address
				$this->Email->replyTo = $webmaster;
				$this->Email->from = $webmaster;
				$this->Email->subject = 'New Job Offer';
				$this->Email->template = 'hire_notification';
				$this->Email->sendAs = 'both';
				//$this->Email->delivery = 'debug';
				$this->Email->send();
			//
			$this->Session->setFlash('Success! you have hired teacher for date '.$date);
			$this->redirect(array('action'=>'search'));
		}
		
	}
	/* 
	 * Make Available in bulk
	 */
	function addByDateRange($month=null, $year=null)
	{
		$dates = array();
		if($month && $year){
			$td = date('t', mktime(0,0,0,$month,1,$year)); // total days in $month
			for($e=1; $e<=$td; $e++){
				$day = date('D', mktime(0,0,0,$month,$e,$year));
				if($day != 'Sun' && $day != 'Sat'){
					$d = ($e < 10) ? '0'.$e : $e;
					$dates[] = $year.'-'.$month.'-'.$d;
				}
			}
		}else if($this->data){
			$start = date($this->data['Schedule']['from']);
			$end = date($this->data['Schedule']['to']);
			// Start
			$ms1 = strtotime($this->data['Schedule']['from']);
			$ms2 = strtotime($this->data['Schedule']['to']);
			//Start Date
			$m1 = date('m', $ms1);
			$d1 = date('d', $ms1);
			$y1 = date('Y', $ms1);
			$td1 = date('t', $ms1);
			//End Date
			$m2 = date('m', $ms2);
			$d2 = date('d', $ms2);
			$y2 = date('Y', $ms2);
			$td2 = date('t', $ms2);
			// Append start date
			// If start and end month are equal
			// Loop will run till the last date of end
			$loop_limit = ($m1 == $m2) ? $d2 : $td1;
			for(; $d1 <= $loop_limit; $d1++){
				$day = date('D', mktime(0,0,0,$m1,$d1,$y1));
				if($day != 'Sun' && $day != 'Sat'){
					$withzero = ($d1 < 10) ? '0'.$d1 : $d1;
					$dates[] = $y1.'-'.$m1.'-'.$withzero;
				}
			}
			// Append to end date
			// If start month is less than end 
			// loop from 1 to till the last date of end
			if($m1 < $m2){
				for($i=1; $i <= $d2; $i++){
					$day = date('D', mktime(0,0,0,$m2,$i,$y2));
					if($day != 'Sun' && $day != 'Sat'){
						$withzero = ($i < 10) ? '0'.$i : $i;
						$dates[] = $y2.'-'.$m2.'-'.$withzero;
					}
				}
			}
		}
		// Remove dates which is less than today
		
		
		$dates2 = array();
		foreach($dates as $v){
			if($v > date('Y-m-d')){
				$dates2[] = $v;
			}
		}
		$dates = null;
		if($dates2){
			$this->add(null, $dates2);
		}
		$this->autoRender = false;
	}
	/* 
	 * Make Available teacher on @date
	 * @param : date
	 */
	function add($date, $multi=null) {
		$save = false;
		$this->Teacher->recursive = -1;
		$tid = $this->Auth->user('id');
		$t = $this->Teacher->findByUser_id($tid, 'id');
		//Find all data from
		$this->data['Schedule']['teacher_id'] = $t['Teacher']['id'];
		if(!$multi){
			// School id
			if(!$this->Schedule->dateExist($date, $tid)){
				$this->data['Schedule']['date'] = $date;
				if (!empty($this->data)) {
					$this->Schedule->create();
					$this->Schedule->save($this->data);
				}
			}
		}else{
			foreach($multi as $k=>$d){
				if(!$this->Schedule->dateExist($d, $tid)){
					$this->data['Schedule']['date'] = $d;
					if (!empty($this->data)) {
						$this->Schedule->create();
						$this->Schedule->save($this->data);
					}
				}
			}
		}
		$this->autoRender = false;
		
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid schedule', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->Schedule->save($this->data)) {
				$this->Session->setFlash(__('The schedule has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The schedule could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Schedule->read(null, $id);
		}
		$teachers = $this->Schedule->Teacher->find('list');
		$schools = $this->Schedule->School->find('list');
		$this->set(compact('teachers', 'schools'));
	}

	function delete($id = null) {
		$this->Schedule->delete($id);
		$this->redirect($this->referer());	
	}
}
?>