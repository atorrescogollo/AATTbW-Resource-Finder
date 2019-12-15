<?php
$filter = "";
if (array_key_exists('filter', $_GET)) {
    $filter = $_GET['filter'];

    // Sanitize SQLi
    if (!preg_match('/^[a-z0-9 ]*$/i', $filter)) {
        // Clean filter parameter
        header("Location: index.php?IdSection=" . $idSection . "&IdOperation=" . $idOperation);
        die();
    }
}
?>
<h5 class=step-container-title>Paso 1</h5>
<div id=step1 class=step-container>
    <form action="index.php">
        <?php
        echo '<input type="hidden" name="IdSection" value="' . $idSection . '" />';
        echo '<input type="hidden" name="IdOperation" value="' . $idOperation . '" />';
        ?>
        <label for="filter">Filtro: </label>
        <input name="filter" value="<?php echo $filter ?>" />
        <button type="submit">Buscar</button>
    </form>
</div>

<h5 class=step-container-title>Paso 2</h5>
<div id=step2 class=step-container>
    <?php
    $oMysqli = oAbrirBaseDeDatos();
    $oRS = oGetCCAA($filter);
    $sTipo = 'CA';
    $aInfo = aGetTable($oRS, $sTipo);
    cerrarBaseDeDatos($oMysqli);


    echo "<ul>";
    $k_Name = array_search("Nombre", $aInfo[T_DATOS][T_DATOS_CAMPOS]);
    foreach ($aInfo[T_DATOS][T_DATOS_INFO] as $cod => $array_info) {
        $name = $array_info[$k_Name];
        $resources = $aInfo[T_DETALLE][$cod][T_DETALLE_RASGOS]["NumRecursos"];
        echo "<li>" . $name . " <a>(Recursos: " . $resources . ")</a></li>";
    }
    echo "</ul>";

    // For map displaying through GET
    file_put_contents($workingdir . '/AATTbW_GeoJson.json', sGetGeoJson($aInfo[T_DETALLE]));

    ?>
</div>


<h5 class=step-container-title>Mapa</h5>
<div id=map class=step-container>
    <div id="mapArcGIS"></div>
    <script src="js/apiArcGIS.js" type="text/javascript"></script>
</div>