<?php

// Load selected filters from URL
foreach (['Categories', 'Keywords', 'Dataset'] as $type) {
    if (isset($_GET['filter_' . strtolower($type)])) {
        $_SESSION['Filters'][$type] = $_GET['filter_' . strtolower($type)];
    } else {
        $_SESSION['Filters'][$type] = [];
    }
}

function displaySelectedFilters($type)
{
    if (!in_array($type, ['Categories', 'Keywords', 'Dataset'])) {
        return '';
    }
    if (isset($_SESSION['Filters'][$type])) {
        $sS = '';
        foreach ($_SESSION['Filters'][$type] as $item) {
            $sS .= '<span class=filterselectedbox><a href="' . prepareHRef('remove', $type, $item) . '"><img src="images/delete-icon.png" style="height: 12px;"/></a>' . $item . '</span>';
        }
        return $sS;
    } else {
        $_SESSION['Filters'][$type] = [];
        return '';
    }
}

function prepareHRef($action, $type, $item)
{
    global $idSection, $idOperation;

    if (!in_array($type, ['Categories', 'Keywords', 'Dataset']) or !in_array($action, ['add', 'remove'])) {
        return '';
    }
    $sS = 'index.php?IdSection=' . $idSection . '&IdOperation=' . $idOperation;
    foreach (array_diff(['Categories', 'Keywords'], [$type]) as $i) {
        if (isset($_SESSION['Filters'][$i]) and !empty($_SESSION['Filters'][$i])) {
            $sS .= '&' . http_build_query(
                [
                    'filter_' . strtolower($i) => $_SESSION['Filters'][$i]
                ]
            );
        }
    }
    $selectedFilters = $_SESSION['Filters'][$type];
    $arrayPostAction = [];
    switch ($action) {
        case 'add':
            $arrayPostAction = array_merge($selectedFilters, [$item]);
            break;
        case 'remove':
            $arrayPostAction = array_diff($selectedFilters, [$item]);
            break;
    }
    $sS .= '&' . http_build_query(
        [
            'filter_' . strtolower($type) => $arrayPostAction
        ]
    );
    return $sS;
}
$categoriesInfo = [];
$keywordsInfo = [];
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
                            $oRS = oGetCategorias();
                            while ($row = $oRS->fetch_assoc()) {
                                $codeCategoria = $row['Codigo'];
                                $nombreCategoria = $row['Nombre'];
                                $numCD = $row["NumCD"];
                                $categoriesInfo[$nombreCategoria] = [
                                    'codCat' => $codeCategoria,
                                    'numCD' => $numCD
                                ];
                                if (in_array($nombreCategoria, $_SESSION['Filters']['Categories'])) { // Already selected
                                    $href = prepareHRef('remove', 'Categories', $nombreCategoria); // Remove from last search array
                                } else { // Not selected
                                    $href = prepareHRef('add', 'Categories', $nombreCategoria); // Append to last search array
                                }
                                echo '<li><span class=filterselectedbox><img src="images/item-icon.png" style="height: 12px;"/><a href="' . $href . '">' . $nombreCategoria . ' (' . $numCD . ')</a></span></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <p>Palabras Clave</p>
                    <div class="filter-list-container container">
                        <ul id=keywords-filter class=filter-list>
                            <?php
                            $oRS = oGetPalabrasClave();
                            while ($row = $oRS->fetch_assoc()) {
                                $codeKW = $row['Codigo'];
                                $nombreKW = $row['Nombre'];
                                $numCD = $row["NumCD"];

                                $keywordsInfo[$nombreKW] = [
                                    'codPC' => $codeKW,
                                    'numCD' => $numCD
                                ];
                                if (in_array($nombreKW, $_SESSION['Filters']['Keywords'])) { // Already selected
                                    $href = prepareHRef('remove', 'Keywords', $nombreKW);
                                } else { // Not selected
                                    $href = prepareHRef('add', 'Keywords', $nombreKW);
                                }

                                echo '<li><a href="' . $href . '">' . $nombreKW . ' (' . $numCD . ')</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div id=data-template class=centerpanel>
                <h5 class=step-container-title>Filtros Activos</h5>
                <div id=active-filters class=step-container>
                    <table>
                        <tr>
                            <td>Categorias:</td>
                            <td><?php echo displaySelectedFilters('Categories'); ?></td>
                        </tr>
                        <tr>
                            <td>Palabras Clave:</td>
                            <td><?php echo displaySelectedFilters('Keywords'); ?></td>
                        </tr>
                        <tr>
                            <?php
                            if (!empty($_SESSION['Filters']['Dataset'])) {
                                echo '<td>Conjunto de Datos:</td>';
                                echo '<td>' . displaySelectedFilters('Dataset') . '</td>';
                            }
                            ?>
                        </tr>
                    </table>
                </div>
                <div id=flexdatabox-container class=step-container>
                    <ul class=flexdataboxes>
                        <?php
                        $aCodCat=null;
                        foreach ($_SESSION["Filters"]["Categories"] as $cat) {
                            $aCodCat[] = $categoriesInfo[$cat]['codCat'];
                        }
                        $aCodPC=null;
                        foreach ($_SESSION["Filters"]["Keywords"] as $kw) {
                            $aCodPC[] = $keywordsInfo[$kw]['codPC'];
                        }
                        $oRS = oGetConjuntosDatos($aCodCat,$aCodPC);
                        while ($row = $oRS->fetch_assoc()) {
                            $nombreCD = $row['Nombre'];
                            $descripcion = $row['Descripcion'];
                            $cat = str_replace(';', ', ', $row['Categorias']);
                            $kw = str_replace(';', ', ', $row['PalabrasClave']);
                            $numRecursos = str_replace(';', ', ', $row['NumRecursos']);

                            echo '<li class=flexdatabox-item>';
                            echo '<h4><img src="images/item.png"/>' . $nombreCD . '</h4>';
                            echo '<p style="font-size: 12px;">' . $descripcion . '</p>';
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
                            echo '</li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
    cerrarBaseDeDatos($oMysqli);
    ?>