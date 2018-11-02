<?php

if(!isset($content)) {
    $content = '';
}

$navi_groups = 'btn-info white darkshadow';

if(($cupArray['status'] > 1) && ($cupArray['groupstage'] == 1)) {
    $groups = '';
} elseif($cupArray['groupstage'] == 1) {
    $groups = '<font class="italic">'.$_language->module['cup_not_started'].'</font>';	
} else {
    $groups = '<font class="italic">'.$_language->module['no_groups'].'</font>';	
}

$content .= $groups;