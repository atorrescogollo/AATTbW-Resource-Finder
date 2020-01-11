<?php
$codCA = "";
$oMysqli = oAbrirBaseDeDatos();
$oRS = oGetCCAA(null, $codCA);
$sTipo = 'CA';
$aInfo = aGetTable($oRS, $sTipo);
/*echo '<pre>';
print_r($aInfo[T_DETALLE]);
echo '</pre>';*/
if (array_key_exists('codCA', $_GET)) {
    $codCA = sprintf("%02d", $_GET['codCA']);

    // Sanitize SQLi
    if (!preg_match('/^[0-9]+$/i', $codCA)) {
        // Clean filter parameter
        header("Location: index.php?IdSection=" . $idSection . "&IdOperation=" . $idOperation);
        die();
    }
}
?>
<h5 class=step-container-title>Paso 1</h5>
<div id=step1 class="step-container">
    <form id=filterform action="index.php" onsubmit="return validateCCAAFilter();">
        <?php
        echo '<input type="hidden" name="IdSection" value="' . $idSection . '" />';
        echo '<input type="hidden" name="IdOperation" value="' . $idOperation . '" />';
        ?>
        <label for="codCA">Comunidad Autónoma: </label>
        <select name="codCA">
            <?php
            foreach ($aInfo[T_DATOS][T_DATOS_INFO] as $cod => $avalue) {
                $name = $avalue[0];
                echo '<option value="' . $cod . '" ' . (($cod == $codCA) ? 'selected="selected"' : '') . '>' . $name . '</option>';
            }
            ?>
        </select>
        <button type="submit">Buscar</button>
    </form>
</div>
<?php
if (!empty($codCA)) {
    $oRS = oGetProvincias(null, $codCA);
    $sTipo = 'PR';
    $aInfo = aGetTable($oRS, $sTipo);

    if (count($aInfo) > 0) {

        echo '<h5 class=step-container-title>Paso 2</h5>';
        echo '<div id=step2 class=step-container>';
        echo "<ul>";
        $k_Name = array_search("Nombre", $aInfo[T_DATOS][T_DATOS_CAMPOS]);
        foreach ($aInfo[T_DATOS][T_DATOS_INFO] as $cod => $array_info) {
            $name = $array_info[$k_Name];
            $resources = $aInfo[T_DETALLE][$cod][T_DETALLE_RASGOS]["NumRecursos"];
            echo "<li>" . $name . " <span>(Recursos: " . $resources . ")</span></li>";
        }
        echo "</ul>";

        // For map displaying through GET
        file_put_contents($workingdir . '/AATTbW_GeoJson.json', sGetGeoJson($aInfo[T_DETALLE], 'codPR', 'REs'));

        echo '</div>';

        echo '
    <h5 class=step-container-title>Mapa</h5>
    <div id=map class=step-container>
    <div id="mapArcGIS"></div>
    <script src="js/apiArcGIS.js" type="text/javascript"></script>
    </div>
    ';
    } else {
        echo NotificationHTMLCondensed("No se encontraron resultados");
    }

    cerrarBaseDeDatos($oMysqli);
}
?>