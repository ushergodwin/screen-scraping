<?php
include_once '../system/config.php';
/**
 * @param string $file File name to check its existance
 * @return bool
 */
function file_does_exist(string $file) {
    $bool = false;
    if (file_exists('../models/'.$file.'.php'))
    $bool = true;
    return $bool;
}
//autoload models
$models = $config['models'];
    $m = 0;
    $m_len = count($models) - 1;
    while ($m <= $m_len) {
        if (file_does_exist($models[$m])):
            include "../models/".$models[$m].".php";
        endif;
        $m++;
    }
