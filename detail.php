<?php
// Load dependencies
require_once "includes/HTMLFunctions.php";
require_once "includes/MySQLFunctions.php";
require_once "includes/TableFunctions.php";
require_once "includes/MapFunctions.php";
require_once "includes/LoginFunctions.php";
require_once "includes/DefaultSettings.php";
include_once "LocalSettings.php";

session_start();

$selectorPath = [];
$authorized = false; // Default: not authorized
if (isset($_SESSION['SelectorPath'])) {
	$selectorPath = $_SESSION['SelectorPath'];
	$selectorPath[] = 'detail';
	$authorized = authorizedByRoles($_SESSION['Userdata']['Roles'], $selectorPath);
}

/*
* Functions, parameters and returntype to load from type parameter ($_GET).
*  Usage: 
*    URI:     detail.php?type=CA&... 
*    Actions:
*            - call function 'oGetInfoCA' with 'code' parameter (from $_GET)
*            - set type=CA for aGetTable function (includes/TableFunctions.php)
*/
$infofunctions['CA'] = [
	'rtype' => 'CA',
	'function' => "oGetInfoCA",
	'parameters' => ['code']
];
$infofunctions['PR'] = [
	'rtype' => 'PR',
	'function' => "oGetInfoProvincia",
	'parameters' => ['code']
];
$infofunctions['RE'] = [
	'rtype' => 'RE',
	'function' => "oGetInfoRecurso",
	'parameters' => ['code']
];
$infofunctions['REs'] = [
	'rtype' => 'RE',
	'function' => "oGetRecursos",
	'parameters' => ['codCD', null, 'codPR', 'codCA'] // Needs 'null' to preserve order (oGetRecursos filter param not allowed)
];

$oMysqli = oAbrirBaseDeDatos();
$oRS = null;
if ($authorized && isset($_GET['type'])) {
	$sTipo = $_GET['type'];
	if (array_key_exists($sTipo, $infofunctions)) {
		// Load triplet from type parameter
		$userfunction = $infofunctions[$sTipo]['function'];
		$userfunctionparams = $infofunctions[$sTipo]['parameters'];
		$sTipo = $infofunctions[$sTipo]['rtype'];

		// Process function parameters (check existence and format from $_GET)
		$loadedparams = [];
		foreach ($userfunctionparams as $p) {
			if (!is_null($p) && isset($_GET[$p]) && preg_match('/^[a-zA-Z\d]+$/', $_GET[$p])) {
				$loadedparams[] = $_GET[$p];
			} else {
				// Preserve order
				$loadedparams[] = null;
			}
		}

		// Finallty, execute query
		$oRS = call_user_func_array($userfunction, $loadedparams);
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Visualización de resultados de búsqueda</title>
	<link rel="shortcut icon" type="image/png" href="images/logo.png" />
	<link href="css/aattbw.css" rel="stylesheet" type="text/css" />
	<link href="css/aattbw_ArcGIS.css" rel="stylesheet" type="text/css" />
	<link href="css/detail.css" rel="stylesheet" type="text/css" />

	<link rel="stylesheet" href="https://js.arcgis.com/3.26/esri/css/esri.css">
	<!-- ArcGIS API for JavaScript library references -->
	<script src="https://js.arcgis.com/3.26/"></script>
	<!-- Terraformer reference -->
	<script src="js/terraformer.min.js"></script>
	<script src="js/terraformer-arcgis-parser.min.js"></script>
</head>

<body>
	<div id="dockbar">
		<img id="logo" src="images/logo.png" />
		<span>Aplicaciones Telemáticas Basadas en Web :: 2019-20</span>
	</div>
	<div id="bodycontent">
		<div id="data">
			<?php
			if (!$authorized) {
				// Show 'not authorized' message
				echo '<div id="error-container">';
				echo ErrorHTML("Permiso denegado", "Solicite permisos para visualizar este apartado.");
				echo '</div>';
			} else if (is_null($oRS)) {
				// Query was not executed correctly -> There is not data available
				echo sPintarError();
			} else {
				$count = $oRS->num_rows;

				// There is data to show
				if ($count > 0) {
					// 1. Format json for javascript map printing
					$aInfo = aGetTable($oRS, $sTipo);
					if ($count == 1) $json = sGetGeoJson($aInfo[T_DETALLE]);
					else $json = sGetGeoJson($aInfo[T_DETALLE], 'code', 'RE', false);
					file_put_contents($workingdir . '/AATTbW_GeoJson.json', $json);

					// 2. Print data sheet in a table (diferenciate between multiple rows / one row)
					if (count($aInfo[T_DATOS][T_DATOS_INFO]) > 1) echo sPintarDatos($aInfo[T_DATOS], true);
					else echo sPintarDatos($aInfo[T_DETALLE], false);

					// 3. Print map from client side thought javascript (json will be requested)
					echo '</div> 
					<div id="mapArcGIS">
					</div>
					<script src="js/apiArcGIS.js" type="text/javascript"></script>';
				} else {
					// Query was executed correctly but there are no results -> Simply print: "0 results founded"
					echo sPintarDatos($aInfo[T_DETALLE], false);
				}
				$oRS->free();
			}

			cerrarBaseDeDatos($oMysqli);

			?>
		</div>
</body>

</html>

<?php
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
	$endingmsg = ($registros != 1) ? 'registros encontrados' : 'registro encontrado';
	$sS = '<div id=inforegistros><p>' . $registros . ' ' . $endingmsg . '</p></div>';

	if ($registros > 0) {
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
					if ($i == 0)
						$sS .= '<td class=fixedwidth>' . $d . '</td>';
					else
						$sS .= '<td>' . $d . '</td>';

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
	}
	return $sS;
}

/* *****************************************************************************************
 * sPintarError
 *****************************************************************************************/
function sPintarError()
{
	$sS = '<p id=errormsg>¡¡Error!! No se han recibido datos</p>';
	return $sS;
}

?>