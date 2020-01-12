<?php
/*
* ------------------------
* CCAA Resources Module
* ------------------------
*/

// Retrieve filter parameter
$filter = "";
if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];

    // Sanitize SQLi
    if (!preg_match('/^[a-z]+[a-z -]*$/i', $filter)) {
        // Clean filter parameter
        header("Location: index.php?IdSection=" . $idSection . "&IdOperation=" . $idOperation);
        die();
    }
}
?>
<h5 class=step-container-title>Paso 1</h5>
<div id=step1 class=step-container>
    <form id=filterform action="index.php" onsubmit="return validateFilter();">
        <?php
        /*
        * Step1 : Filter form
        */
        echo '<input type="hidden" name="IdSection" value="' . $idSection . '" />';
        echo '<input type="hidden" name="IdOperation" value="' . $idOperation . '" />';
        ?>
        <label for="filter">Filtro: </label>
        <input name="filter" value="<?php echo $filter ?>" />
        <button type="submit">Buscar</button>
    </form>
</div>
<?php
// Check if there is any filter
if (!empty($filter)) {
    // Retrieve CCAAs filtered
    $oMysqli = oAbrirBaseDeDatos();
    $oRS = oGetCCAA($filter);
    $sTipo = 'CA';
    $aInfo = aGetTable($oRS, $sTipo);
    cerrarBaseDeDatos($oMysqli);

    if (count($aInfo) > 0) {
        /*
        * Step 2 : List CCAAs
        */
        echo '<h5 class=step-container-title>Paso 2</h5>';
        echo '<div id=step2 class=step-container>';
        echo "<ul>";
        // Get all CCAA names
        $k_Name = array_search("Nombre", $aInfo[T_DATOS][T_DATOS_CAMPOS]);
        foreach ($aInfo[T_DATOS][T_DATOS_INFO] as $cod => $array_info) {
            $name = $array_info[$k_Name];
            $resources = $aInfo[T_DETALLE][$cod][T_DETALLE_RASGOS]["NumRecursos"];
            // Print CCAA and its resources
            echo "<li>" . $name . " <span>(Recursos: " . $resources . ")</span></li>";
        }
        echo "</ul>";
        echo '</div>';

        
        $selectorPath = [];
        $authorized = false; // Default: not authorized
        if (isset($_SESSION['SelectorPath'])) {
            $selectorPath = $_SESSION['SelectorPath'];
            $selectorPath[] = 'detail';
            $authorized = authorizedByRoles($_SESSION['Userdata']['Roles'], $selectorPath);
        }
        // For map displaying through GET
        if ($authorized) // Show detail link
            file_put_contents($workingdir . '/AATTbW_GeoJson.json', sGetGeoJson($aInfo[T_DETALLE], 'codCA', 'REs'));
        else  // Do not show detail link
            file_put_contents($workingdir . '/AATTbW_GeoJson.json', sGetGeoJson($aInfo[T_DETALLE]));

        // Print map from client side thought javascript (json will be requested)
        echo '<h5 class=step-container-title>Mapa</h5>
            <div id=map class=step-container>
            <div id="mapArcGIS"></div>
            <script src="js/apiArcGIS.js" type="text/javascript"></script>
            </div>';
    } else {
        // Filter is OK but there is no results
        echo NotificationHTMLCondensed("No se encontraron resultados");
    }
}
?>