<?php

if (!isset($content)) {
    $content = '';
}

$navi_bracket = 'btn-info white darkshadow';

if (($cupArray['status'] > 1)) {

    $bracket = '';

    $bracketFile = $dir_cup . 'cup_bracket.php';
    if (file_exists($bracketFile)) {
        include($bracketFile);
    }

    $bracket = '<div class="panel panel-default"><div class="full_content">' . $bracket . '</div></div>';

} else if ($cupArray['groupstage'] == 1) {
    $bracket = '<div class="panel panel-default"><div class="panel-body italic">'.$_language->module['no_groups'].'</div></div>';
} else {
    $bracket = '<div class="panel panel-default"><div class="panel-body italic">'.$_language->module['cup_not_started'].'</div></div>';
}

$content .= $bracket;
