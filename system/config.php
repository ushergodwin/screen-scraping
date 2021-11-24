<?php
/**
 * @category  Bluefaces PHP DB Model
 * 
 * @package   DB MODEL
 * 
 * @author Tumuhimbise Usher Godwin
 * 
 * @copyright Copyright (c) 2020-2021
 * 
 * @version   2.0
 */

 /**
 *
  * @return array $config
 */
$config["HOST"] = 'localhost';

/**
 * @var array $config USERNAME The name of the current account
 */
$config["USERNAME"] = 'root';

/**
 * @var array $config PASSWORD The password of the current user
 */
$config["PASSWORD"] = '';

/**
 * @var array $config DBNAME The database name to connect to
 */

$config["DBNAME"] = 'scrapping';

/*load Models
Models / Class names should be the same as file names
Add your models seperated with commas after the default
All Your models should be in the models/ directory
*/
$config["models"] = array("User_Model");

//Only one of the bellow 2 configurations should be turned on

/*autoload sesion
Disabled by default. Change to TRUE if required;
*/

$config["session"] = FALSE;

/*
Set the sission to cut across domain and subdmains
Replace SESSION_NAME with the name of your session
Set to  FALSE by default
Turn it on by setting it to TRUE
*/

$config["cross_site_session"] = array("SESSION_NAME", FALSE);

//Do not alter the settings bellow
$config["default"] = array("BL_Controller","Db_Model");

return $config;
