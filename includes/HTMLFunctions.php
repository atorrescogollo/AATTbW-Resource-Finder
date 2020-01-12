<?php

/**
 * siteMap2UnorderedList
 *
 * Gets the siteMap and turns into a list. It is used for printing the navigation tree.
 *
 * @param siteMap Array. Site Map to print. Usually it is the siteMap defined in
 *           includes/DefaultSettings.php, but it is a param for recursion purposes
 * @param collapse Boolean. Do children have to be shown?
 * @param currentSelectorPath Array. Current position in navigation tree.
 * @param recursiveLevel Integer. It should not be used: recursion purposes.
 * @param prefixHRef String. It should not be used: recursion purposes.
 *
 * @return String. HTML code
 */
function siteMap2UnorderedList($siteMap, $collapse, $currentSelectorPath, $recursiveLevel = 0, $prefixHRef = '')
{
    global $siteMap_IdNames;

    $selected = false;
    $hrefOperator = (empty($prefixHRef) ? '?' : '&');
    $idName = $siteMap_IdNames[$recursiveLevel]; // IdSection or IdOperation
    $sS = '<ul class=ullevel' . $recursiveLevel . '>';

    // For each item in current siteMap
    foreach ($siteMap as $id => $dataarray) {
        // Is selected?
        $selected = (array_key_exists($recursiveLevel, $currentSelectorPath) && $id == $currentSelectorPath[$recursiveLevel]);
        // Retrieve name
        $name = $dataarray['Name'];

        // Accumulate href from recursion (IdSection+IdOperation)
        $hrefItem = $prefixHRef. $hrefOperator . $idName . '=' . $id;
        $styleClass = ($selected)?'class=selected':'';

        // Print item with attributes
        $sS .= '<li ' . $styleClass . '>';
        $sS .= '<a href="' . $hrefItem . '">' . $name . '</a>';

        $printpolicy=($selected || !$collapse); // Print children when selected or no collapse policy ...
        if ( $printpolicy && array_key_exists('Children', $dataarray) && !empty($dataarray['Children'])) { // ... and has children
            // Recursion over children
            $sS .= siteMap2UnorderedList($dataarray['Children'], !$selected, $currentSelectorPath, $recursiveLevel + 1, $hrefItem);
        }
        $sS .= '</li>';
    }
    $sS .= '</ul>';
    return $sS;
}

/**
 * Children2BoxList
 *
 * Gets a siteMap layer and prints the names as boxes. Useful when selecting a section and not an operation
 *
 * @param children Array. SiteMap fragment
 * @param prefixHRef String. Prefix for href. Example: 'index.php?'. NOTE: This function only adds '&k=v' to href
 * @param childrenIdName String. IdName to apply to href
 * @param excludedIdArray Array. Indexes that don't have to be shown of children array
 *
 * @return String. HTML code
 */
function Children2BoxList($children, $prefixHRef, $childrenIdName, $excludedIdArray = array())
{
    $sS = '<ul>';
    foreach ($children as $id => $data) {
        // TODO: Show real images and descriptions
        if (!in_array($id, $excludedIdArray)) {
            $sS .= '<a href=' . $prefixHRef . '&' . $childrenIdName . '=' . $id . '>';
            $sS .= '<li>';
            $sS .= '<img src="images/logo.png" />';
            $sS .= '<h4>' . $data['Name'] . '</h4>';
            $sS .= '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent euismod ultrices ante, ac laoreet nulla vestibulum adipiscing. Nam quis justo in augue auctor imperdiet.</p>';
            $sS .= '</li>';
            $sS .= '</a>';
        }
    }
    $sS .= '</ul>';
    return $sS;
}

/**
 * Prints a simple notification message
 * 
 * @param description Message to showç
 * 
 * @return String. HTML code
 */
function NotificationHTMLCondensed($description)
{
    $sS  = '<div id=notification-condensed-container>';
    $sS .= '<p>' . $description . '</p>';
    $sS .= '</div>';
    return $sS;
}

/**
 * Prints an error message
 * 
 * @param title Subject of the message
 * @param description Message to show
 * 
 * @return String. HTML code
 */
function ErrorHTML($title, $description)
{
    $sS  = '<img src="images/warning.png" />';
    $sS .= '<h4>' . $title . '</h4>';
    $sS .= '<div id=error-description-container>';
    $sS .= '<p>' . $description . '</p>';
    $sS .= '</div>';
    return $sS;
}

/**
 * Prints a simple error message
 * 
 * @param description Message to show
 * 
 * @return String. HTML code
 */
function ErrorHTMLCondensed($description)
{
    $sS  = '<div id=error-condensed-container>';
    $sS .= '<p>' . $description . '</p>';
    $sS .= '</div>';
    return $sS;
}
