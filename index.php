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

// Default IdSection : index.php == index.php?IdSection=0
$idSection = 0;
if (array_key_exists('IdSection', $_GET)) {
	$idSection = $_GET['IdSection'];

	// Wrong IdSection
	if (!is_numeric($idSection) or !array_key_exists($idSection, $siteMap)) {
		// Redirect to /index.php
		header("Location: index.php");
		die();
	}
}

$current_siteMap = $siteMap[$idSection];

$hasOperation = false;
$idOperation = 0;
if (array_key_exists('IdOperation', $_GET)) {
	$idOperation = $_GET['IdOperation'];

	// Wrong IdOperation
	if (!is_numeric($idOperation) or !isset($current_siteMap["Children"][$idOperation])) {
		// Redirect to /index.php
		header("Location: index.php");
		die();
	} else {
		$hasOperation = true;
		$current_siteMap = $current_siteMap["Children"][$idOperation];
	}
}

$hasChildren = (array_key_exists('Children', $current_siteMap) and !empty($current_siteMap['Children']));

if ($hasOperation) $selectorPath = [$idSection, $idOperation];
else $selectorPath = [$idSection];

$_SESSION['SelectorPath']=$selectorPath;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Práctica 3</title>
	<link rel="shortcut icon" type="image/png" href="images/logo.png" />

	<link href="css/aattbw.css" rel="stylesheet" type="text/css" />
	<script src="js/aattbw.js"></script>
	<link href="css/modules.css" rel="stylesheet" type="text/css" />
	<script src="js/modules.js"></script>

	<link href="css/aattbw_ArcGIS.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="https://js.arcgis.com/3.26/esri/css/esri.css">
	<!-- ArcGIS API for JavaScript library references -->
	<script src="https://js.arcgis.com/3.26/"></script>
	<!-- Terraformer reference -->
	<script src="js/terraformer.min.js"></script>
	<script src="js/terraformer-arcgis-parser.min.js"></script>
</head>

<body>
	<div id="zone1" class="dockbar">
		<div id=logo-container class=container>
			<img src=images/logo.png />
		</div>
		<div id=title class=container>
			<h1>AATTbW</h1>
		</div>
		<div class="container login-container">
			<?php
			if (isset($_SESSION["Authenticated"]) and $_SESSION["Authenticated"]) {
				// When athenticated -> Show name and logout option
				echo '
					<form id=logoutform method=\'POST\' action=\'login.php\'>
						<input type="hidden" name="action" value="logout">
						<label class="labelbox">Hola, ' . $_SESSION["Userdata"]["Nombre"] . '</label>
						<button type="submit">Salir</button>
					</form>
				';
			} else {
				// When NOT athenticated -> Show login form and check auth errors
				echo '
					<form id=loginform method=\'POST\' action=\'login.php\' onsubmit="return validateLogin()">
						<input type="hidden" name="action" value="login">
						<label class="labelbox" for="username">Login</label>
						<input type="text" name="username">
						<label class="labelbox" for="password">Password</label>
						<input type="password" name="password">
						<button type="submit">Entrar</button>
					</form>
				';

				// login.php returned authentication error
				if (isset($_SESSION["errormsg"])) {
					echo ErrorHTMLCondensed($_SESSION["errormsg"]);
					unset($_SESSION["errormsg"]);
				}
			}
			?>
		</div>
	</div>
	<div id=datazones>
		<div id=datazonesrow>
			<div id="zone2-container" class="container">
				<div id=zone2>
					<div id=sitemap-container>
						<h3>Mapa del sitio</h3>
						<?php
						/*
						* Show navigation map based on:
						* 1. loaded siteMap (includes/DefaultSettings.php)
						* 2. the selector path (current tree position)
						*/

						echo SiteMap2UnorderedList($siteMap, true, $selectorPath);
						?>
					</div>
				</div>
			</div>
			<div id="zone3-container" class="container">
				<div id=zone3>
					<div id=data-container>
						<?php
						// Title
						echo '<h3>' . $current_siteMap['Name'] . '</h3>';

						/*
						* Load user role (includes/DefaultSettings.php) : 
						* - Default -> 'All'
						* - Authenticated -> $_SESSION["Userdata"]["Roles"] (login.php)
						*/
						$rolesUser = ["All"];
						if (isset($_SESSION["Userdata"]))
							$rolesUser = $_SESSION["Userdata"]["Roles"];

						/*
						* Check if roles authorize the user in current tree position
						*  - Example: Is user authorized to see IdSection=1&IdOperation=2?
						*/
						if (authorizedByRoles($rolesUser, $selectorPath)) {
							if ($idSection == 0) { // Show Welcome Page
								/*
								* Show operations in a box list (set aside for possible future improvements)
								* NOTE: operations will be treated as children
								*/
								$hasChildren = true;
								echo "<div id=children-boxes-container>";
								echo Children2BoxList($siteMap, 'index.php?', "IdSection", [0]);
								echo "</div>";
							} elseif ($hasChildren) { // For sections : has operations but not selected
								/*
								* Show operations in a box list
								*/
								echo "<div id=children-boxes-container>";
								echo Children2BoxList($current_siteMap['Children'], 'index.php?IdSection=' . $idSection, "IdOperation");
								echo "</div>";
							} else { // For operations : Endpoint in sitemap (includes/DefaultSettings.php)
								/*
								* Load module defined in siteMap (includes/DefaultSettings.php)
								*/
								$moduleloaded = false;
								if (array_key_exists('ModulePath', $current_siteMap)) {
									$modulePath = $current_siteMap['ModulePath'];
									if (file_exists($modulePath)) {
										echo '<div id=module-container>';
										include_once $modulePath;
										echo '</div>';
										$moduleloaded = true;
									}
								}

								if (!$moduleloaded) {
									// The module was not loaded -> Not available
									echo '<div id=error-container>';
									echo ErrorHTML("Error en la operación", "La operación solicitada no se encuentra disponible.");
									echo '</div>';
								}
							}
						} else {
							// User is not authorized by roles
							echo '<div id=error-container>';
							echo ErrorHTML("Permiso denegado", "Solicite permisos para visualizar este apartado.");
							echo '</div>';
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- <div id="zone4" class="dockbar">
		<p>Temporal</p>
	</div> -->
</body>

</html>