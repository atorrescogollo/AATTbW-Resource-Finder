<?php
$codCA = "";
$oMysqli = oAbrirBaseDeDatos();
// Retrieve CCAAs
$oRS = oGetCCAA(null, $codCA);
$sTipo = 'CA';
$aInfo = aGetTable($oRS, $sTipo);

if (isset($_GET['codCA'])) {
    $codCA = sprintf("%02d", $_GET['codCA']); // Parse from integer with two digits

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
        /*
        * Step1 : Filter select
        */
        echo '<input type="hidden" name="IdSection" value="' . $idSection . '" />';
        echo '<input type="hidden" name="IdOperation" value="' . $idOperation . '" />';
        ?>
        <label for="codCA">Comunidad Autónoma: </label>
        <select name="codCA">
            <?php
            foreach ($aInfo[T_DATOS][T_DATOS_INFO] as $cod => $avalue) {
                $name = $avalue[0];
                // Each CCAA
                echo '<option value="' . $cod . '" ' . (($cod == $codCA) ? 'selected="selected"' : '') . '>' . $name . '</option>';
            }
            ?>
        </select>
        <button type="submit">Buscar</button>
    </form>
</div>
<?php
// Check if there is any filter
if (!empty($codCA)) {
    // Retrieve Provinces filtered
    $oRS = oGetProvincias(null, $codCA);
    $sTipo = 'PR';
    $aInfo = aGetTable($oRS, $sTipo);

    if (count($aInfo) > 0) {
        /*
        * Step 2 : List Provinces
        */
        echo '<h5 class=step-container-title>Paso 2</h5>';
        echo '<div id=step2 class=step-container>';
        echo "<ul>";
        // Get all provinces names
        $k_Name = array_search("Nombre", $aInfo[T_DATOS][T_DATOS_CAMPOS]);
        foreach ($aInfo[T_DATOS][T_DATOS_INFO] as $cod => $array_info) {
            $name = $array_info[$k_Name];
            $resources = $aInfo[T_DETALLE][$cod][T_DETALLE_RASGOS]["NumRecursos"];
            // Print province and its resources
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
            file_put_contents($workingdir . '/AATTbW_GeoJson.json', sGetGeoJson($aInfo[T_DETALLE], 'codPR', 'REs'));
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

    cerrarBaseDeDatos($oMysqli);
}
?>