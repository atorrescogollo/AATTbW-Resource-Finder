<?php ?>
<div id="bodycontent">
	<div id="data">
	<?php
    
    require_once('P2_funcionesTable.php');

    $oMysqli = oAbrirBaseDeDatos();


    /* para probar con los diferentes tipos descomentar la invocación correspondiente */

    /**** COMUNIDADES AUTONOMAS ***/
    // Listado de las Comunidades Autónomas que empiezan por 'Com'
    /*
    $oRS = oGetCCAA('Com');
    $sTipo = 'CA';
    */
    // Información Detallada de la Comunidad Autónoma Madrid(13)
    /*
    $oRS = oGetInfoCA('13');
    $sTipo = 'CA';
    */

    /**** PROVINCIAS ***/
    // Listado de las Provincia de Aragón(02)
    /*
    $oRS = oGetProvincias(null,'02');
    $sTipo = 'PR';
    */

    // Información Detallada de  Huesca(22)
    /*
    $oRS = oGetInfoProvincia('22');
    $sTipo = 'PR';
    */

    /**** RECURSOS ***/
    // Listado de los Recursos del Conjunto de Datos Espacios Naturales Protegidos (DS0006)
    /*
    $oRS = oGetRecursos('DS0005');
    $sTipo = 'RE';
    */

    // Listado de los Recursos del Conjunto de Datos Espacios naturales y playas de Euskadi (DS0007)
    
    $oRS = oGetRecursos('DS0007');
    $sTipo = 'RE';
    

    // Listado de los Recurso asociados al Conjunto de Datos 'DS0016' y cuyo nombre contenga la cadena 'Navacerrada'
    /*
    $oRS = oGetRecursos('DS0016', 'Navacerrada');
    $sTipo = 'RE';
    */

    // Información del Recurso con código 'DS001600005058' (Cuerda larga)
    /*
    $oRS = oGetInfoRecurso('DS001600005058');
    $sTipo = 'RE';
    */

    // Información del Recurso con código 'DS000700002836' (Urdarbai)
    /*
    $oRS = oGetInfoRecurso('DS000700002836');
    $sTipo = 'RE';
    */

    if (!is_null($oRS)) {
        if ($oRS->num_rows > 0) {
            $bData = true;
            $aInfo = aGetTable($oRS, $sTipo);

            file_put_contents('js/AATTbW_GeoJson.json', sGetGeoJson($aInfo[T_DETALLE]));
            if (count($aInfo[T_DATOS][T_DATOS_INFO]) > 1) {
                echo sPintarDatos($aInfo[T_DATOS], true);
            } else {
                echo sPintarDatos($aInfo[T_DETALLE], false);
            }
            echo '
				</div> 
				<div id="mapArcGIS"></div>
				<script src="js/apiArcGIS.js" type="text/javascript"></script>	
				';
        }
        $oRS->free();
    } else {
        echo sPintarError();
    }

    cerrarBaseDeDatos($oMysqli);

    ?>
</div>


<?php
/* *****************************************************************************************
 * FUNCIONES UTILIZADAS PARA EXPORTAR A GeoJson
 * *****************************************************************************************/

/* *****************************************************************************************
 * sGetGeoJson
 *
 * @param $aFeatures array asociativo con la estructura correspondiente al campo T_DETALLE
 *			del array devuelvo por la funcion aGetTable.
 *
 * @return
 * 		- cadena de caracteres con la serialización de los Registros del array a formato GeoJSON
 *			Consultar http://geojson.org/
 * *****************************************************************************************/
function sGetGeoJson($aFeatures)
{
    $sS = '
	{
		"type" : "FeatureCollection",
		"features": [ ';

    $sSFeatures = '';
    foreach ($aFeatures as $cod => $data) {
        if (!empty($sSFeatures)) {
            $sSFeatures .= ',';
        }
        $sSFeatures .= '
		{
			"type": "Feature",
			"properties": {	';

        $sSFeatures .= ' "Nombre": "' . $data[T_DETALLE_NOMBRE] . '"';
        foreach ($data[T_DETALLE_RASGOS] as $rasgo => $valor) {
            $sSFeatures .= ', "' . $rasgo . '": "' . $valor . '"';
        }

        $sSFeatures .= ' },
		    "geometry" : ' . $data[T_DETALLE_GEO][T_DETALLE_GEO_COOR] . '
		}';
    }
    $sS .= $sSFeatures;

    $sS .= '
					]
	}
	';
    return $sS;
}

/* *****************************************************************************************
 * FUNCIONES UTILIZADAS PARA PRESENTAR la Información
 * ****************************************************************************************/

/* *****************************************************************************************
 * sPintarDatos
 *
 * @param $aInfo array asociativo con la estructura correspondiente al campo T_DETALLE
 *                T_DATOS del array devuelto por la función aGetTable
 *
 * @param $bEsTDATA valor lógico:
 *		- si toma valor true, $aInfo se corresponde con el campo T_DATA devuelto por aGetTable
 *		- en otro caso $aInfo se corresponde con el campo T_DETALLE devuelto por aGetTable
 *		- valor por defecto 'true'
 *****************************************************************************************/
function sPintarDatos($aInfo, $bEsTDATA = true)
{
    $registros = count(($bEsTDATA) ? $aInfo[T_DATOS_INFO] : $aInfo);
    $endingmsg = ($registros>1)?'registros encontrados':'registro encontrado';
    $sS = '<div id=inforegistros><p>' . count($registros) . ' '.$endingmsg.'</p></div>';
    $sS .= '
	<div id=parrilladiv>
	  <table class="parrilla">';
      
    if ($bEsTDATA) {
        $sS .= '<tr>';
        foreach ($aInfo[T_DATOS_CAMPOS] as $campo) {
            $sS .= '<th>' . $campo . '</th>';
        }
        $sS .= '</tr>';
        foreach ($aInfo[T_DATOS_INFO] as $cod => $data) {
            $sS .= '<tr>';
            $i = 0;
            foreach ($data as $d) {
                if ($i == 0) {
                    $sS .= '<td class=fixedwidth>' . $d . '</td>';
                } else {
                    $sS .= '<td>' . $d . '</td>';
                }

                $i++;
            }
            $sS .= '</tr>';
        }
    } else {
        $sS .= '<tr>';
        $sS .= '<p class=th>' . reset($aInfo)[T_DETALLE_NOMBRE] . '</p>';
        $sS .= '</tr>';
        
        foreach (reset($aInfo)[T_DETALLE_RASGOS] as $rasgo => $val) {
            $sS .= '<tr>';
            $sS .= '<td class="fixedwidth tablaresaltada">' . $rasgo . '</td>';
            $sS .= '<td>' . $val . '</td>';
            $sS .= '</tr>';
        }
    }

    $sS .= '
	  </table>
	</div>';
    return $sS;
}

/* *****************************************************************************************
 * sPintarError
 *****************************************************************************************/
function sPintarError()
{
    $sS='<p id=errormsg>¡¡Error!! No se han recibido datos</p>';
    return $sS;
}

?>