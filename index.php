<?php
require_once("includes/HTMLFunctions.php");
require_once("includes/DefaultSettings.php");
include_once("LocalSettings.php");


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
    if (!is_numeric($idOperation) or !array_key_exists('Children', $current_siteMap) or !array_key_exists($idOperation, $current_siteMap["Children"])) {
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
			<form>
				<label class="labelbox" for="login-box">Login</label>
				<input type="text" name="login-box">
				<label class="labelbox" for="password-box">Password</label>
				<input type="password" name="password-box">
				<button type="submit">Entrar</button>
			</form>
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
                            $selectorPath=[$idSection, $idOperation];
                        } else {
                            $selectorPath=[$idSection];
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
							$moduleloaded=false;
                            if (array_key_exists('ModulePath', $current_siteMap)) {
                                $modulePath = $current_siteMap['ModulePath'];
                                if (file_exists($modulePath)) {
									include_once($modulePath);
									$moduleloaded=true;
                                }
							}
							
							if(!$moduleloaded){
								echo '<div id=error-container>';
								echo ErrorHTML("Error en la operación", "La operación solicitada no se encuentra disponible.");
								echo '</div>';
							}
                        }
                        ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="zone4" class="dockbar">
		<p>Temporal</p>
	</div>
</body>

</html>