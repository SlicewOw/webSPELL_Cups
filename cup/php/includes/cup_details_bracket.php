<?php

if (!isset($content)) {
    $content = '';
}

try {

    $navi_bracket = 'btn-info white darkshadow';

    $bracket = '';

    if (isset($cupArray['settings']['challonge']['state']) && ($cupArray['settings']['challonge']['state'] == 1)) {

        $challonge_api = getChallongeApiObject();

        $tournament_parameter = array();
        $tournament_parameter['include_participants'] = '1';
        $tournament_parameter['include_matches'] = '1';

        $tournament_data = $challonge_api->getTournaments($tournament_parameter);

        echo showInfo('You will see a bracket soon.');

    } else if (($cupArray['status'] > 1)) {


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
