<?php

/*the hdwiki entrance */
//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
error_reporting(0);

//解决pathinfo不支持中文的问题
setlocale(LC_ALL, "zh_CN.UTF-8");

//@set_magic_quotes_runtime(0);
$mtime = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

function getCount($array,$mode = COUNT_NORMAL){
    if(is_array($array) || is_object($array)){
        return count($array, $mode);
    }else{
        return 0;
    }
}

define('IN_HDWIKI', TRUE);
define('HDWIKI_ROOT', dirname(__FILE__));

include HDWIKI_ROOT.'/model/hdwiki.class.php';

$hdwiki = new hdwiki();
$hdwiki->run();
?>