<?php
/*
* --------------------
*  LocalSettings.php
* --------------------
*   Variables are edited and overwritten here. Default variables definition is available in includes/DefaultSettings.php
*/

/* 
* DB Settings
*/
//$dbServer='localhost';
//$dbPort=3306;
$dbDatabase = 'AATTbW_2019_20';
$dbUser = 'aattbw';
$dbPass = 'aattbw';

/*
* Users definition :
*  - User format:
*       < mail (key) > => [ <password>, <name>, <roles> ]
*  - Roles format: 
*       <roles> = [ <role1>, <role2>, ...]
*
* NOTES:
*  -  Roles are defined in includes/DefaultSettings.php
*/
$aUsuarios = array(
    // Owners
    "admin@tecweb.es" => array("pass_admin", "Administrador", ["Owner"]),
    "usu1@tecweb.es" => array("pass_usu1", "Pedro Levante Pérez", ["Owner"]),
    "usu2@tecweb.es" => array("pass_usu2", "Joaquín Cacho Gordo", ["Owner"]),
    "usu3@tecweb.es" => array("pass_usu3", "Pepe Roa Barata", ["Owner"]),

    // Limited
    "lim1@tecweb.es" => array("pass_lim1", "Usuario Limitado 1", ["Limited"]),
    "lim2@tecweb.es" => array("pass_lim2", "Usuario Limitado 2", ["Limited"]),
    "lim3@tecweb.es" => array("pass_lim3", "Usuario Limitado 3", ["Limited"]),
);
