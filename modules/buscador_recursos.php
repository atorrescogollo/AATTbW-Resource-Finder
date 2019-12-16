<?php

function displaySelectedFilters($type)
{
    if (isset($_SESSION['Filters'][$type])) {
        $sS = '';
        foreach ($_SESSION['Filters'][$type] as $item) {
            $sS .= '<span class=filterselectedbox>' . $item . '</span>';
        }
        return $sS;
    } else {
        $_SESSION['Filters'][$type] = [];
        return '';
    }
}

$self_href = 'index.php?IdSection=' . $idSection . '&IdOperation=' . $idOperation;
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
                            echo '<li><a href="' . $self_href . '">' . $nombreCategoria . ' (' . $numCD . ')</a></li>';
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
                            echo '<li><a href="' . $self_href . '">' . $nombreKW . ' (' . $numCD . ')</a></li>';
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
                        <td>Conjunto de Datos:</td>
                        <td><?php echo displaySelectedFilters('Dataset'); ?></td>
                    </tr>
                </table>
            </div>
            <div id=flexdatabox-container class=step-container>
                <ul class=flexdataboxes>
                    <?php
                    $oRS = oGetConjuntosDatos();
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