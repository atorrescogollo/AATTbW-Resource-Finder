<?php
require_once("includes/DefaultSettings.php");
include_once("LocalSettings.php");

function authenticate($username, $password)
{
    global $aUsuarios;
    // TODO: MySQL Statement instead
    if (isset($aUsuarios[$username][USU_PASSW]) and $aUsuarios[$username][USU_PASSW] == $password) {
        if (!isset($aUsuarios[$username][USU_ROLES])) {
            $aUsuarios[$username][USU_ROLES] = ["Authenticated"];
        }
        return [
            "Nombre" => $aUsuarios[$username][USU_NOMBRE],
            "Roles" => $aUsuarios[$username][USU_ROLES]
        ];
    }
    return null;
}

function authorizedByRoles($roles, $siteMapPath = array(0))
{
    foreach ($roles as $role) {
        if (authorizedByRole($role, $siteMapPath)) {
            return true;
        }
    }
    return false;
}

function authorizedByRole($role, $siteMapPath = array(0))
{
    // TODO: Improve roles with $roles['*']['*'] possible combinations
    global $roles;

    $current = $roles[$role];
    if (is_bool($current)) { // Reached boolean in root (All allowed)
        return $current;
    }

    foreach ($siteMapPath as $id) {
        if (array_key_exists($id, $current)) {
            $current = $current[$id];
            if (is_bool($current)) { // Reached boolean before endpoint
                return $current;
            }
        } else { // Not defined... denied for policy
            return false;
        }
    }

    if (is_array($current)) { // Endpoint is an array
        return true;
    } else
        return $current;
}
