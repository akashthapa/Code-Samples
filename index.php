<?php 
session_start();
include('core/basic.php');

$url = isset($_GET['url']) ? trim($_GET['url']) : 'home';
$page = "";
// url 
$urlArr = explode('/', $url);
if(count($urlArr) > 1){
	if(!empty($urlArr[0]) && !empty($urlArr[1])){
		$page = PAGE_PATH.$urlArr[0].'/'.$urlArr[1];
	}
}else{
	if(!empty($urlArr[0])){
		$page = PAGE_PATH.$urlArr[0];
	}
}

// layout inclusion
function content(){
	global $page;
	include($page.'.php');
}
if(file_exists($page.'.php') && file_exists($page.'.ini')){
	$metas = parse_ini_file($page.'.ini');
	foreach($metas as $k=>$v){
		${$k} = $v;
	}
	$pagelayout = 'page_'.$layout.'.php';
	
}else{
	$page = PAGE_PATH.'notfound';
	$pagelayout = 'page_inner.php';
}

function replace($buffer){
	$buffer = str_replace('%img_path%', IMAGE_PATH, $buffer);
	return $buffer;
}

ob_start("replace");
include(THEME_PATH.$pagelayout);
ob_end_flush();
//
?>