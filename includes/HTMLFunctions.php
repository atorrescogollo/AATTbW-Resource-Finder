<?php
function siteMap2UnorderedList($siteMap, $recursiveLevel = 0, $prefixHRef=''){
    global $siteMap_IdNames;
    $hrefOperator=(empty($prefixHRef)?'?':'&');
    $idName=$siteMap_IdNames[$recursiveLevel];
    $sS='<ul class=ullevel'.$recursiveLevel.'>';
    foreach($siteMap as $id => $dataarray){
        $sS.='<li>';
        $name = $dataarray['Name'];

        $hrefItem = $prefixHRef;
        if(!empty($idName)){
            $hrefItem .= $hrefOperator.$idName.'='.$id;
            $sS.= '<a href="'.$hrefItem.'">'. $name .'</a>';
        }
        else{
            $sS.= $name;
        }

        if (array_key_exists('Children', $dataarray) and !empty($dataarray['Children'])){
            $sS.= siteMap2UnorderedList($dataarray['Children'], $recursiveLevel + 1, $hrefItem);
        }
        $sS.='</li>';
    }
    $sS.='</ul>';
    return $sS;
}
?>