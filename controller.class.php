<?php 
class Controller
{
	protected $_controller;
	protected $_model;
	protected $_action;
	protected $_template;


	public $doNotRenderHeader;
	public $render;
	public $data = array();
	public $params = array();
	public $layout = 'default';
	public $helpers = array();

	
	
	function __construct($model = NULL, $action=NULL) {
		$this->_controller = $model . '_controller';
		$this->_model = $model;
		$this->_action = $action;
		$this->params = array('model'=>$this->_model, 'action'=>$this->_action);
		$this->doNotRenderHeader = 0;
		$this->render = 1;
		// post data
		if(isset($_POST[$this->_model])){
			$this->data = $_POST[$this->_model];
		}
		// load models
		if(!empty($this->model)){
			foreach($this->model as $m){
				$this->$m = new $m;
			}
		}
		// load plugins
		if(!empty($this->plugins))
		{
			foreach($this->plugins as $p){
				$this->$p = new $p;
			}
		}
		//initailise templete
		$this->_template = new Template($model,$action, $this->helpers /* Helper class for view */);
	}
	
	
	// sets variable for use in view through template Class
	function set($name,$value) {
		$this->_template->set($name,$value);
	}

	function __destruct() {
		if ($this->render) {
			$this->_template->render($this->doNotRenderHeader, $this->layout);
		}
	}
	
	function redirect($location)
	{
		$this->render = 0;
		header("Location: ".checkUrl($location));
	}
}
?>