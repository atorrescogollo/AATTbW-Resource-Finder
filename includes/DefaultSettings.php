<?php

    /* DB Settings */
    $dbServer='localhost';
    $dbPort=3306;
    $dbDatabase='db';
    $dbUser='user';
    $dbPass='pass';

    /* SiteMap
      -----------
        Inicio (0)
        Soluciones (1)
            Compartición privada de datos (1,0)
            Open Data (1,1)
            Smart City & IoT (1,2)
        Casos de uso (2)
            Sector público (2,0)
            Energía (2,1)
            Movilidad y transporte (2,2)
            Banca y seguros (2,3)
            industria (2,4)
        Conjuntos de datos (3)
            Recursos por Comunidad Autónoma (3,0)
            Recursos por Provincia (3,1)
            Buscador de Conjuntos de Datos (3,2)
            Explorador de recursos (3,3)
    */

    $siteMap_IdNames=[
        0 => 'IdSection',
        1 => 'IdOperation'
    ];

    $modulesPath="modules/";

    $siteMap[0]=[
        "Name" => "Inicio",
    ];

    $siteMap[1]=[
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
      $siteMap[2]=[
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
            "Name" => 'industria',
          ]
        ]
      ];
      $siteMap[3]=[
        "Name" => 'Conjuntos de datos',
        "Children" => [
          0 => [
            "Name" => 'Recursos por Comunidad Autónoma',
            "ModulePath" => $modulesPath.'recursos_ccaa.php'
          ],
          1 => [
            "Name" => 'Recursos por Provincia',
          ],
          2 => [
            "Name" => 'Buscador de Conjuntos de Datos',
          ],
          3 => [
            "Name" => 'Explorador de recursos',
          ]
        ]
      ];
