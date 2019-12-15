<?php
include_once("includes/LoginFunctions.php");
include_once("includes/DefaultSettings.php");
include_once("LocalSettings.php");


session_start();
if (!empty($_POST)) {
    if (isset($_POST['action']) && $_POST['action'] == "login" && isset($_POST['username']) && isset($_POST['password'])) {

        $userdata = authenticate($_POST['username'], $_POST['password']);
        if ($userdata) {
            $_SESSION["username"] = $_POST['username'];
            $_SESSION["Authenticated"] = true;
            $_SESSION["Userdata"] = $userdata;
        } else {
            $_SESSION["errormsg"] = "Usuario y/o contraseña inválidos";
            header("Location: index.php");
            die();
        }
    } else if (isset($_POST['action']) && $_POST['action'] == "logout") {
        session_destroy();
    }
}

if (!isset($_SESSION["Authenticated"])) {
    session_destroy();
}

header("Location: index.php");
die();
