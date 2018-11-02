<?php

if(!isset($content)) {
    $content = '';
}

if(($cupArray['status'] > 1) && ($cupArray['groupstage'] == 1)) {
	$groups = '';
} elseif($cupArray['groupstage'] == 1) {
	$groups = '<font class="italic">'.$_language->module['cup_not_started'].'</font>';	
} else {
	$groups = '<font class="italic">'.$_language->module['no_groups'].'</font>';	
}

$content .= '<div class="cup_container"><div class="cup_container">'.$groups.'</div><div class="clear"></div></div>';
