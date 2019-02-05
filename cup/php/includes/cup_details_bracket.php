<?php

if (!isset($content)) {
    $content = '';
}

try {

    $navi_bracket = 'btn-info white darkshadow';

    if (($cupArray['status'] > 1)) {

        $bracket = '';

        $bracketFile = __DIR__ . '/cup_bracket.php';
        if (file_exists($bracketFile)) {
            include($bracketFile);
        }

        $bracket = '<div class="panel panel-default">' . $bracket . '</div>';

    } else {

        if ($cupArray['groupstage'] == 1) {
            $bracket_txt = $_language->module['no_groups'];
        } else {
            $bracket_txt = $_language->module['cup_not_started'];
        }

        $bracket = '<div class="panel panel-default"><div class="panel-body italic">' . $bracket_txt . '</div></div>';

    }

    $content .= $bracket;

} catch (Exception $e) {
    $content .= showError($e->getMessage());
}
