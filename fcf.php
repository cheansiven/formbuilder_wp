<?php
/*
Plugin Name: First Form Builder
Description: Form Builder example
*/

define('FCF_FILE',__FILE__);
define('FCF_DIR',dirname(__FILE__));
define('FCF_URL',plugins_url('',__FILE__));

require_once 'include/Core.class.php';
require_once 'include/Helper.class.php';

new FCS_Core();

if(file_exists(FCF_DIR.'/include/Extended.php')){
require_once 'include/Extended.php';
}else{
    define('FCF_IRTATE',TRUE);
}
