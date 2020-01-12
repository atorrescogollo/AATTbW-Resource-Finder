<?php
// Load dependencies
include_once("includes/LoginFunctions.php");
include_once("includes/DefaultSettings.php");
include_once("LocalSettings.php");

session_start();

// Prepare referer to redirect after login/logout
$referer='index.php';
$refererparams = '';
if (isset($_SESSION['SelectorPath'])) {
    $count = 0;
    foreach ($_SESSION['SelectorPath'] as $idValue) {
        if (!isset($siteMap_IdNames[$count])) break; // Endpoint for IDs

        $idName = $siteMap_IdNames[$count];
        $refererparams .= (strlen($refererparams) == 0) ? '?' : '&';
        $refererparams .= $idName.'='.$idValue;
        $count++;
    }
}
$referer.=$refererparams;

if (!empty($_POST)) {
    if (isset($_POST['action']) && $_POST['action'] == "login" && isset($_POST['username']) && isset($_POST['password'])) {
        // Example (POST request) : login.php?action=login&username=XXX&password=YYY -> Authenticate
        $userdata = authenticate($_POST['username'], $_POST['password']);
        if ($userdata) {
            // Authentication succeded
            $_SESSION["username"] = $_POST['username'];
            $_SESSION["Authenticated"] = true;
            $_SESSION["Userdata"] = $userdata;
        } else {
            // Authentication failed -> Redirect to index.php (errormsg will be printed)
            $_SESSION["errormsg"] = "Usuario y/o contraseña inválidos";
        }
    } else if (isset($_POST['action']) && $_POST['action'] == "logout") {
        // Example (POST request) : login.php?action=logout
        session_destroy();
    }
}

// Redirect to referer
header("Location: ".$referer);
die();
