<?php

if (!isset($content)) {
    $content = '';
}

try {

    $navi_home = 'btn-info white darkshadow';

    //
    // Cup placements
    if ($cupArray['status'] == 4) {
        include(__DIR__ . '/cup_details_platzierungen.php');
    }

    //
    // Cup details
    $status = $_language->module['cup_status_' . $cupArray['status']];

    //
    // Games
    $cup_game = getgamename($cupArray['game']);

    $game_icon = getGameIcon($cupArray['game'], true);
    if (!empty($game_icon)) {
        $cup_game = '<img src="' . $game_icon . '" alt="" /> ' . $cup_game;
    }

    $info = '<span class="list-group-item">Status: ' . $status . '</span>';
    $info .= '<span class="list-group-item">' . $cup_game . '</span>';
    $info .= '<span class="list-group-item">' . $_language->module['mode'] . ': ' . $cupArray['mode'] . '</span>';
    $info .= '<span class="list-group-item">Check-In: ' . getformatdatetime($cupArray['checkin']) . '</span>';
    $info .= '<span class="list-group-item">Start: ' . getformatdatetime($cupArray['start']) . '</span>';
    if (preg_match('/register/', $cupArray['phase'])) {
        $info .= '<span class="list-group-item">' . $_language->module['teams_registered'] . ': ' . getcup($cup_id, 'anz_teams') . ' / ' . $cupArray['size'] . '</span>';
    } else if (preg_match('/checkin/', $cupArray['phase']) || $cupArray['phase'] == 'finished') {
        $info .= '<span class="list-group-item">' . $_language->module['teams_checked_in'] . ': ' . getcup($cup_id, 'anz_teams_checkedin') . ' / ' . $cupArray['size'] . '</span>';
    }
    $info .= '<span class="list-group-item">' . $_language->module['max_penalty'] . ': ' . $cupArray['max_pps'] . '</span>';

    if ($cupArray['mappool'] > 0) {

        $selectQuery = cup_query(
            "SELECT
                    `maps`
                FROM `" . PREFIX . "cups_mappool`
                WHERE `mappoolID` = " . $cupArray['mappool'],
            __FILE__
        );

        $get_maps = mysqli_fetch_array($selectQuery);

        if (!empty($get_maps['maps'])) {

            $maps = unserialize($get_maps['maps']);
            $anzMaps = count($maps);

            $info .= '<span class="list-group-item">Maps: ' . implode(', ', $maps) . '</span>';

        }

    }

    //
    // Cup Admins
    $selectAdminsQuery = cup_query(
        "SELECT
                `userID`
            FROM `" . PREFIX . "cups_admin`
            WHERE `cupID` = " . $cup_id,
        __FILE__
    );
    if ($selectAdminsQuery && (mysqli_num_rows($selectAdminsQuery) > 0)) {

        $adminArray = array();

        while ($db = mysqli_fetch_array($selectAdminsQuery)) {
            $url = 'index.php?site=profile&amp;id=' . $db['userID'];
            $adminArray[] = '<a href="' . $url . '" target="_blank" class="blue">' . getnickname($db['userID']) . '</a>';
        }

        $info .= '<span class="list-group-item">Admins: ' . implode(', ', $adminArray) . '</span>';

    }

    $data_array = array();
    $data_array['$panel_type'] = 'panel-default';
    $data_array['$panel_title'] = 'Informationen';
    $data_array['$panel_content'] = $info;
    $content .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

    if (!empty($cupArray['description'])) {

        $data_array = array();
        $data_array['$panel_type'] = 'panel-default';
        $data_array['$panel_title'] = $_language->module['description'];
        $data_array['$panel_content'] = getoutput($cupArray['description']);
        $content .= $GLOBALS["_template_cup"]->replaceTemplate("panel_body", $data_array);

    }

    //
    // Cup Format
    $cupFormatList = '';
    for ($x = 1; $x < ($cupArray['anz_runden'] + 1); $x++) {

        if (isset($cupArray['settings']['format'][$x])) {

            if ($cupArray['settings']['format'][$x] == 'bo3') {
                $formatName = 'Best of 3';
            } else if ($cupArray['settings']['format'][$x] == 'bo5') {
                $formatName = 'Best of 5';
            } else if ($cupArray['settings']['format'][$x] == 'bo7') {
                $formatName = 'Best of 7';
            } else {
                $formatName = 'Best of 1';
            }

        } else {
            $formatName = 'Best of 1';
        }

        $cupFormatList .= '<div class="list-group-item">'.$_language->module['cup_1_round_'.$cupArray['size'].'_'.$x].': '.$formatName.'</div>';

    }

    if (!empty($cupFormatList)) {

        $data_array = array();
        $data_array['$panel_type'] = 'panel-default';
        $data_array['$panel_title'] = 'Cup Format';
        $data_array['$panel_content'] = $cupFormatList;
        $content .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

    }

} catch (Exception $e) {
    $content .= showError($e->getMessage());
}
