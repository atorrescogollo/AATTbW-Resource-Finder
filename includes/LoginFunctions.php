<?php
// Load requirements
require_once("includes/DefaultSettings.php");
include_once("LocalSettings.php");

/**
 * Authenticated a user
 * 
 * @param username
 * @param password
 * 
 * @return Array. User Data: [ 'Nombre' => ... , 'Roles' => ... ] 
 */
function authenticate($username, $password)
{
    global $aUsuarios;

    // Validate format
    if (!filter_var($username, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-z0-9_-]{' . PASSWD_MIN_LENGTH . ',' . PASSWD_MAX_LENGTH . '}$/i', $password)) {
        return null;
    }

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

/**
 * Check if a set of roles authorizes some siteMap Position
 * 
 * @param roles Array. Roles to test
 * @param siteMapPath Array. Navigation Tree Path
 * 
 * @return Boolean. Authorized?
 */
function authorizedByRoles($roles, $siteMapPath = array(0))
{
    foreach ($roles as $role) {
        if (authorizedByRole($role, $siteMapPath)) {
            return true;
        }
    }
    return false;
}

/**
 * Check if a single role authorizes some siteMap Position
 * 
 * @param roles String. Role to test
 * @param siteMapPath Array. Navigation Tree Path
 * 
 * @return Boolean. Authorized?
 */
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
