<?php 
// receive user configurations
require 'config.php';

//autload session is set to TRUE
if ($config["session"] == TRUE) {
    session_start();
}

//autoload cross site session
if ($config["cross_site_session"][1] == TRUE) {
    session_name($config["cross_site_session"][0]);
    session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST']);
    session_start();
}
//autload user models / classes
$default = $config["default"];

//get number of models loaded

$default_Length = count($default) - 1;

$df = 0;

//autoload default models
while ($df <= $default_Length) {
    include $default[$df].'.php';
    $df++;
    if ($df > $default_Length):
        break;
    endif;
}
//end autoload

class Model extends BL_Controller
 {

    function __construct(){
        parent::__construct();
    }
}
class Database extends Db_model
{
    public function __construct()
    {
        parent::__construct();
    }
}