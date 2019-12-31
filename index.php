<?php
require_once "includes/HTMLFunctions.php";
require_once "includes/MySQLFunctions.php";
require_once "includes/TableFunctions.php";
require_once "includes/MapFunctions.php";
require_once "includes/LoginFunctions.php";
require_once "includes/DefaultSettings.php";
include_once "LocalSettings.php";

session_start();

$idSection = 0;
if (array_key_exists('IdSection', $_GET)) {
	$idSection = $_GET['IdSection'];
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
				echo '
					<form id=logoutform method=\'POST\' action=\'login.php\'>
						<input type="hidden" name="action" value="logout">
						<label class="labelbox">Hola, ' . $_SESSION["Userdata"]["Nombre"] . '</label>
						<button type="submit">Salir</button>
					</form>
				';
			} else {
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
						if ($hasOperation) {
							$selectorPath = [$idSection, $idOperation];
						} else {
							$selectorPath = [$idSection];
						}
						echo SiteMap2UnorderedList($siteMap, $selectorPath);
						?>
					</div>
				</div>
			</div>
			<div id="zone3-container" class="container">
				<div id=zone3>
					<div id=data-container>
						<h3> <?php echo $current_siteMap['Name']; ?> </h3>

						<?php
						$siteMapPath[] = $idSection;
						if ($hasOperation)
							$siteMapPath[] = $idOperation;

						$rolesUser = ["All"];
						if (isset($_SESSION["Userdata"])) {
							$rolesUser = $_SESSION["Userdata"]["Roles"];
						}
						if (authorizedByRoles($rolesUser, $siteMapPath)) {
							if ($idSection == 0) { // For Welcome Page
								// TODO: Improve welcome page
								$hasChildren = true;
								echo "<div id=children-boxes-container>";
								echo Children2BoxList($siteMap, 'index.php?', "IdSection", [0]);
								echo "</div>";
							} elseif ($hasChildren) { // For sections
								echo "<div id=children-boxes-container>";
								echo Children2BoxList($current_siteMap['Children'], 'index.php?IdSection=' . $idSection, "IdOperation");
								echo "</div>";
							} else { // For operations
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
									echo '<div id=error-container>';
									echo ErrorHTML("Error en la operación", "La operación solicitada no se encuentra disponible.");
									echo '</div>';
								}
							}
						} else {
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