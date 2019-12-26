<?php
$workingdir = "working/";

/* DB Settings */
$dbServer = 'localhost';
$dbPort = 3306;
$dbDatabase = 'db';
$dbUser = 'user';
$dbPass = 'pass';

$siteMap_IdNames = [
  0 => 'IdSection',
  1 => 'IdOperation'
];

$modulesPath = "modules/";

$siteMap[0] = [
  "Name" => "Inicio",
];
$siteMap[1] = [
  "Name" => 'Soluciones',
  "Children" => [
    0 => [
      "Name" => 'Compartición privada de datos',
    ],
    1 => [
      "Name" => 'Open Data',
    ],
    2 => [
      "Name" => 'Smart City & IoT',
    ]
  ]
];
$siteMap[2] = [
  "Name" => 'Casos de uso',
  "Children" => [
    0 => [
      "Name" => 'Sector público',
    ],
    1 => [
      "Name" => 'Energía',
    ],
    2 => [
      "Name" => 'Movilidad y transporte',
    ],
    3 => [
      "Name" => 'Banca y seguros',
    ],
    4 => [
      "Name" => 'Industria',
    ]
  ]
];
$siteMap[3] = [
  "Name" => 'Conjuntos de datos',
  "Children" => [
    0 => [
      "Name" => 'Recursos por Comunidad Autónoma',
      "ModulePath" => $modulesPath . '/recursos_ccaa.php'
    ],
    1 => [
      "Name" => 'Recursos por Provincia',
      "ModulePath" => $modulesPath . '/recursos_provincia.php'
    ],
    2 => [
      "Name" => 'Buscador de Conjuntos de Datos',
      "ModulePath" => $modulesPath . '/buscador_recursos.php'
    ],
    3 => [
      "Name" => 'Explorador de recursos',
    ]
  ]
];


// TODO: MySQL Statement instead
define("USU_PASSW", 0);
define("USU_NOMBRE", 1);
define("USU_ROLES", 2);
$aUsuarios = array();


define('PASSWD_MIN_LENGTH', 6);
define('PASSWD_MAX_LENGTH', 10);


// Default Role hierarchy based on sitemap
/*
User role is allowed if:
  1.- $roles[IdSection]==true for first layer
  2.- $roles[IdSection][IdOperation]==true for second layer
*/

$roles = array(
  "All" => [ // Only allowed to see first
    0 => true,
    //1 => [],
    //2 => [],
    //3 => []
    1 => true,
    2 => true,
    3 => true
  ],
  "Owner" => true, // All to see everything
  "Authenticated" => [ // Allowed to see first and last sections
    0 => true,
    1 => [],
    2 => [],
    3 => [
      0 => true,
      1 => false,
      2 => true,
      3 => false
    ]
  ]
);
