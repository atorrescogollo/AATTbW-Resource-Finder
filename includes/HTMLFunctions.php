<?php
function siteMap2UnorderedList($siteMap, $currentSelectorPath, $recursiveLevel = 0, $prefixHRef = '')
{
    global $siteMap_IdNames;

    $selected = false;
    $hrefOperator = (empty($prefixHRef) ? '?' : '&');
    $idName = $siteMap_IdNames[$recursiveLevel];
    $sS = '<ul class=ullevel' . $recursiveLevel . '>';
    foreach ($siteMap as $id => $dataarray) {
        $selected = false;
        $sS .= '<li>';
        $name = $dataarray['Name'];

        $hrefItem = $prefixHRef;
        if (!empty($idName)) {
            $hrefItem .= $hrefOperator . $idName . '=' . $id;
            $styleClass = '';
            if (array_key_exists($recursiveLevel, $currentSelectorPath) and $id == $currentSelectorPath[$recursiveLevel]) {
                $selected = true;
                $styleClass = 'class=selected';
            }
            $sS .= '<a ' . $styleClass . ' href="' . $hrefItem . '">' . $name . '</a>';
        } else {
            $sS .= $name;
        }
        if (array_key_exists('Children', $dataarray) and !empty($dataarray['Children'])) {
            $sS .= siteMap2UnorderedList($dataarray['Children'], ($selected) ? $currentSelectorPath : array(), $recursiveLevel + 1, $hrefItem);
        }
        $sS .= '</li>';
    }
    $sS .= '</ul>';
    return $sS;
}

function Children2BoxList($children, $prefixHRef, $childrenIdName, $excludedIdArray = array())
{
    $hrefOperator = (empty($prefixHRef) ? '?' : '&');

    $sS = '<ul>';
    foreach ($children as $id => $data) {
        // TODO: Show real images and descriptions
        if (!in_array($id, $excludedIdArray)) {
            $sS .= '<a href=' . $prefixHRef . $hrefOperator . $childrenIdName . '=' . $id . '>';
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


function ErrorHTML($title, $description)
{
    $sS = '  <img src="images/warning.png" />';
    $sS .= '    <h4>' . $title . '</h4>';
    $sS .= '    <div id=error-description-container>';
    $sS .= '      <p>' . $description . '</p>';
    $sS .= '    </div>';
    return $sS;
}


function ErrorHTMLCondensed($description)
{
    $sS = "";
    $sS .= '    <div id=error-condensed-container>';
    $sS .= '      <p>' . $description . '</p>';
    $sS .= '    </div>';
    return $sS;
}
