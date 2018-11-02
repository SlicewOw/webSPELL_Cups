<?php

if(!isset($content)) {
    $content = '';
}

$navi_rules = 'btn-info white darkshadow';

$rules = getrules($cupArray['rule_id'], 'text');
if(empty($rules)) {
    $rules = '<font class="italic">'.$_language->module['no_rules'].'</font>';	
}

$content .= '<div class="panel panel-default"><div class="panel-body">' . getoutput($rules) . '</div></div>';