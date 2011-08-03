<?php /**
* The Front Controller for handling every request
* @coptyright : www.akashthapa.com
* @author: Akash Thapa
* @email: akashthapa7@gmail.com
*/

/**
 * Use the DS to separate the directories in other defines
 */
 //

define('WEB_ROOT', basename(dirname(dirname(__FILE__))));

 //
define('DS', '/');
/**
 * CMS Core files
 */
define('CORE', 'core'.DS);
/**
 * CMS container folder.
 */
define('ROOT', basename(dirname(__FILE__)));
/**
 * Config 
 */
define('CONFIG', 'config'.DS);
/**
/**
 #########################
 SITE CONFIG
 #########################
 * 
 */
 if(!file_exists(CONFIG.'config.xml'))
 {
 	die("Create config.xml file inside folder 'config'. This is sit's main setting file");
 }else{
 	include_once(CORE.'site.php');
	$site = new Site();
	/*
	* This sections makes $site's properties, like $site->name, $site->theme
	*/
	foreach($site->getXml()->site as $key => $value){
		foreach($value as $k=>$v){
			$site->$k = $v;
		}
	}
 }

 
/**
 #########################
 THEME FOLDER
 #########################
 * Theme 
 */
define('THEME', 'themes'.DS);
/**
 * Theme Path
 */
 if(is_dir(THEME.$site->theme)){
	define('THEME_PATH', THEME.$site->theme.DS);
}else{
	die("Missing theme '$site->theme'. Please create theme folder inside ".THEME);
}
/*
 #########################
 DATA FOLDER
 #########################
 * Data 
 */
define('DATA', 'data'.DS);
/*

*/
define('WEB_ROOT_PATH', DS.WEB_ROOT.DS);
/**
 * Page Path 
 */
define('PAGE_PATH', DATA.'pages'.DS);
/**
 * Element Path 
 *
 */
define('ELEMENT_PATH', DATA.'elements'.DS);
/**
 * Page Meta Path 
 */
define('PAGE_META_PATH', DATA.'metas'.DS);
/**
 * Css 
 */
define('CSS_PATH', DS.WEB_ROOT.DS.THEME_PATH.'css'.DS);
/**
 * Javascript 
 */
define('JS_PATH', DS.WEB_ROOT.DS.THEME_PATH.'js'.DS);
/**
 * Image 
 */
define('IMAGE_PATH', DS.WEB_ROOT.DS.THEME_PATH.'img'.DS);

function css(){
	$args = func_get_args();
	foreach($args as $name)
	{
		echo "<link href=\"".CSS_PATH.$name.".css\" rel=\"stylesheet\" type=\"text/css\" />";
	}
}
function js(){
	$args = func_get_args();
	foreach($args as $name)
	{
		echo "<script type=\"text/javascript\" src=\"".JS_PATH.$name.".js\"></script>";
	}
}
function element()
{
	$args = func_get_args();
	foreach($args as $name)
	{
		$name = str_replace(' ', '_', trim($name));
		include(ELEMENT_PATH.$name.'.php');
	}
}

?>