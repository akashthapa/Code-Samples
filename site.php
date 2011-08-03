<?php 
class Site
{
	private $xml;
	public function __construct(){
		$this->xml = simplexml_load_file(CONFIG."config.xml");
	}
	public function __set($name, $value)
	{
		$this->$name = $value;
	}
	public function __get($name)
	{
		return $this->$name;
	}
	public function getXml()
	{
		return $this->xml;
	}
	public function save($d){
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$root = $dom->createElement('config');
		
		
		$site = $dom->createElement('site');
		foreach($d as $k => $v)
		{
			$v = trim($v);
			$ele = $dom->createElement($k);
			$ele->appendChild($dom->createTextNode($v));
			$site->appendChild($ele);
		}
		$root->appendChild($site);
		$dom->appendChild($root);
		echo $dom->save(CONFIG."config.xml");
	}
}
?>