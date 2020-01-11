<?php
$resetfilters = (isset($_GET['reset_filters']) && $_GET['reset_filters']);
// Init filters
if ($resetfilters || !isset($_SESSION['filter']['Cat'])) {
    $_SESSION['filter']['Cat'] = array();
}
if ($resetfilters || !isset($_SESSION['filter']['KW'])) {
    $_SESSION['filter']['KW'] = array();
}
if ($resetfilters || (isset($_GET['remCD']) && $_GET['remCD']) || !isset($_SESSION['filter']['CD'])) {
    $_SESSION['filter']['CD'] = null;
}

// Get flags and update filters
$flags = ['addCat', 'addKW', 'remCat', 'remKW'];
foreach ($flags as $flag) {
    if (isset($_GET[$flag])) {
        $type = substr($flag, 3, strlen($flag) - 3);
        switch (substr($flag, 0, 3)) {
            case 'add':
                if (!in_array($_GET[$flag], $_SESSION['filter'][$type])) {
                    $_SESSION['filter'][$type][] = $_GET[$flag];
                }
                break;
            case 'rem':
                if (in_array($_GET[$flag], $_SESSION['filter'][$type])) {
                    $key = array_search($_GET[$flag], $_SESSION['filter'][$type]);
                    unset($_SESSION['filter'][$type][$key]);
                }
                break;
        }
    }
}

// Get select resource
if (isset($_GET['codCD'])) {
    $_SESSION['filter']['CD'] = $_GET['codCD'];
}


$href = "index.php?IdSection=" . $idSection . "&IdOperation=" . $idOperation;
$oMysqli = oAbrirBaseDeDatos();
?>
<div class=borderlayoutout>
    <div class=borderlayoutin>
        <div id=filter-container class=westpanel>
            <p class="step-container-title">Filtros</p>
            <div id=filter-data class="step-container">
                <p>Categorías</p>
                <div class="filter-list-container container">
                    <ul id=categories-filter class=filter-list>
                        <?php
                        $oRS = oGetCategorias($_SESSION['filter']['Cat'], $_SESSION['filter']['KW']);
                        while ($row = $oRS->fetch_assoc()) {
                            $codeCategoria = $row['Codigo'];
                            $nombreCategoria = $row['Nombre'];
                            $numCD = $row["NumCD"];
                            $_SESSION['cache']['Cat'][$codeCategoria]['name'] = $nombreCategoria;
                            echo '<li><a title="Filtrar por \'Categorías\'::' . $nombreCategoria . '" href="' . $href . '&addCat=' . $codeCategoria . '">' . $nombreCategoria . ' <span>' . $numCD . '</span></a></li>';
                        }
                        ?>
                    </ul>
                </div>
                <p>Palabras Clave</p>
                <div class="filter-list-container container">
                    <ul id=keywords-filter class=filter-list>
                        <?php
                        $oRS = oGetPalabrasClave($_SESSION['filter']['Cat'], $_SESSION['filter']['KW']);
                        while ($row = $oRS->fetch_assoc()) {
                            $codeKW = $row['Codigo'];
                            $nombreKW = $row['Nombre'];
                            $numCD = $row["NumCD"];

                            $_SESSION['cache']['KW'][$codeKW]['name'] = $nombreKW;
                            echo '<li><a title="Filtrar por \'Palabras Clave\'::' . $nombreKW . '" href="' . $href . '&addKW=' . $codeKW . '">' . $nombreKW . ' <span>' . $numCD . '<span></a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <div id=data-template class=centerpanel>
            <h5 class=step-container-title>Filtros Activos
                <?php
                if (count($_SESSION['filter']['Cat']) > 0 || count($_SESSION['filter']['KW']) > 0 || !is_null($_SESSION['filter']['CD']))
                    echo '<a href="' . $href . '&reset_filters=true"><img src="images/delete-icon.png" style="height: 12px;margin-left:25px"/></a>';
                ?>
            </h5>
            <div id=active-filters class=step-container>
                <table>
                    <tr>
                        <td>Categorias:</td>
                        <td>
                            <?php
                            foreach ($_SESSION['filter']['Cat'] as $codCat) {
                                echo '<span class=filterselectedbox><a href="' . $href . '&remCat=' . $codCat . '"><img src="images/delete-icon.png" style="height: 12px;"/></a>' . $_SESSION['cache']['Cat'][$codCat]['name'] . '</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Palabras Clave:</td>
                        <td>
                            <?php
                            foreach ($_SESSION['filter']['KW'] as $codKW) {
                                echo '<span class=filterselectedbox><a href="' . $href . '&remKW=' . $codKW . '"><img src="images/delete-icon.png" style="height: 12px;"/></a>' . $_SESSION['cache']['KW'][$codKW]['name'] . '</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    if (!is_null($_SESSION['filter']['CD'])) {
                        echo '<tr>';
                        echo '  <td>Conjunto de Datos:</td>';
                        echo '  <td>';
                        $codCD = $_SESSION['filter']['CD'];
                        echo '    <span class=filterselectedbox><a href="' . $href . '&remCD=true"><img src="images/delete-icon.png" style="height: 12px;"/></a>' . $_SESSION['cache']['CD'][$codCD]['name'] . '</span>';
                        echo '  </td>';
                        echo '</tr>';
                    }
                    ?>
                </table>
            </div>
            <?php
            $oRS = oGetConjuntosDatos($_SESSION['filter']['Cat'], $_SESSION['filter']['KW'], $_SESSION['filter']['CD']);
            $count = $oRS->num_rows;
            if ($count != 1) {
                echo '<div id=flexdatabox-container class=step-container>';
            } else {
                echo '<div class=step-container>';
            }

            echo '<ul class=flexdataboxes>';
            while ($row = $oRS->fetch_assoc()) {
                $codigoCD = $row['Codigo'];
                $nombreCD = $row['Nombre'];
                $descripcion = $row['Descripcion'];
                $cat = str_replace(';', ', ', $row['Categorias']);
                $kw = str_replace(';', ', ', $row['PalabrasClave']);
                $numRecursos = str_replace(';', ', ', $row['NumRecursos']);

                $_SESSION['cache']['CD'][$codigoCD]['name'] = $nombreCD;

                echo '<li class=flexdatabox-item>';
                echo '<a class=flexdatabox-item-link href="' . $href . '&codCD=' . $codigoCD . '">';
                echo '<h4><img src="images/item.png" height=10px/>' . $nombreCD . '</h4>';
                echo '<p style="font-size: 12px;margin: 0px 5px; margin-top: 10px">' . $descripcion . '</p>';
                echo '<table>';
                echo '<tr>';
                echo '<td>Categorias:</td>';
                echo '<td>' . $cat . '</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td>Palabras Clave:</td>';
                echo '<td>' . $kw . '</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td>Numero de recursos:</td>';
                echo '<td>' . $numRecursos . '</td>';
                echo '</tr>';
                echo '</table>';
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';

            if (!is_null($_SESSION['filter']['CD'])) {
                $siteMapPath[] = 'detail';
                $showexternallink = authorizedByRoles($rolesUser, $siteMapPath);
                $showexternallink = ($showexternallink && $numRecursos>0);

                echo '<div class=step-container style="max-height: 220px; overflow: auto">';

                echo '<h5 style="display: inline; padding: 0 10px 0 30px" class=step-container-title>Listado de recursos (' . $numRecursos . ')</h5>';
                if ($showexternallink) {
                    echo '<a href="javascript:popupdetail(\'' . $externallinkhost . $externallinkpath . '?type=REs&codCD=' . $_SESSION['filter']['CD'] . '\')"><img style="height: 15px; margin: 0 6px" src="images/location.png" /></a>';
                }
                echo '<ul id=resources-list>';

                $oRS = oGetRecursos($codigoCD);
                while ($row = $oRS->fetch_assoc()) {
                    $codigoR = $row['Codigo'];
                    $nombreR = $row['Nombre'];
                    $nombreProvincias = $row['Provincias'];
                    echo '<li>';
                    echo $nombreR;
                    echo '<span>(' . $nombreProvincias . ')</span>';
                    if ($showexternallink) {
                        echo '<a href="javascript:popupdetail(\'' . $externallinkhost . $externallinkpath . '?type=RE&code=' . $codigoR . '\')"><img style="height: 13px; margin: 0 6px" src="images/location.png" /></a>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
<?php
cerrarBaseDeDatos($oMysqli);
?>