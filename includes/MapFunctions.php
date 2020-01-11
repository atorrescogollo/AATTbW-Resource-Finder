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
function sGetGeoJson($aFeatures, $codename = null, $detailtype = null)
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
        if (!is_null($codename) && !is_null($detailtype)) {

            $detailhrefargs = '?type=' . $detailtype . '&' . $codename . '=' . $cod;

            $sSFeatures .= ', "Detalle": "<a href=\"detail.php' . $detailhrefargs . '\">Ver en mapa</a>"';
        }
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
