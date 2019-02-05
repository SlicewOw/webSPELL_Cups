<?php

function getTeamDetailsByMatchId($cup_array, $match_id) {

    $teamArray = array();

    if (!validate_array($cup_array, true)) {
        return $teamArray;
    }

    if (!validate_int($match_id, true)) {
        return $teamArray;
    }

    global $_language, $image_url;

    $_language->readModule('cups', true);

    $logotypeBase = $image_url . '/cup/teams/';

    $matchArray = getmatch($match_id);

    for ($x = 1; $x < 3; $x++) {

        $team_ident = 'team' . $x;
        $teamURL = 'javascript:;';

        if ($cup_array['mode'] == '1on1') {

            $user_id = $matchArray[$team_ident . '_id'];

            if ($user_id > 0) {

                $teamName = getnickname($user_id);
                $teamURL = 'index.php?site=profile&id=' . $user_id;
                $matchLogo = getuserpic($user_id, true);

            } else if ($matchArray[$team_ident . '_freilos'] == 1) {
                $teamName = $_language->module['cup_freilos'];
                $matchLogo = $logotypeBase . 'team_nologotype.png';
            } else {
                $teamName = '';
                $matchLogo = $logotypeBase . 'team_nologotype.png';
            }

        } else {

            $team_array = getteam($matchArray[$team_ident . '_id']);

            if ($matchArray[$team_ident . '_id'] > 0) {
                $teamURL = 'index.php?site=teams&amp;action=details&amp;id=' . $matchArray[$team_ident . '_id'];
                $teamName = $team_array['name'];
            } else if ($matchArray[$team_ident . '_freilos'] == 1) {
                $teamName = $_language->module['cup_freilos'];
            } else {
                $teamName = '';
            }

            $matchLogo = $team_array['logotype'];

        }

        $teamArray[$x] = array(
            'name' => $teamName,
            'name_url' => '<a href="' . $teamURL . '" title="' . $teamName . '">' . $teamName . '</a>',
            'logotype' => '<img src="' . $matchLogo . '" width="180" height="180" alt="' . $teamName . '" title="' . $teamName . '" />',
            'url' => $teamURL
        );

    }

    return $teamArray;

}

function getMatchDetailsByMatchId($cup_array, $match_id) {

    if (!validate_array($cup_array, true)) {
        return '';
    }

    if (!validate_int($match_id, true)) {
        return '';
    }

    $matchArray = getmatch($match_id);

    global $_language;

    $_language->readModule('cups', true);

    $formatArray = array(
        'bo1',
        'bo3'
    );

    $matchInfoArray = array();
    $matchInfoArray[] = $cup_array['name'];
    $matchInfoArray[] = $_language->module['cup_' . $matchArray['bracket'] . '_round_' . $cup_array['size'] . '_' . $matchArray['runde']];
    $matchInfoArray[] = $_language->module['cup_match_start'] . ': ' . getformatdatetime($matchArray['date']);
    $matchInfoArray[] = 'Format: ' . ucfirst($matchArray['format']);

    if (in_array($matchArray['format'], $formatArray) && ($matchArray['mapvote'] == 1)) {

        if (($matchArray['team1_freilos'] == 0) && ($matchArray['team2_freilos'] == 0)) {
            $matchInfoArray[] = getmap($match_id, $matchArray['format']);
        }

    }

    return implode(' / ', $matchInfoArray);

}

function getMatchStatusAsListByMatchId($cup_array, $match_id) {

    if (!validate_array($cup_array, true)) {
        return '';
    }

    if (!validate_int($match_id, true)) {
        return '';
    }

    $matchArray = getmatch($match_id);

    global $_language;

    $_language->readModule('cups', true);

    $status = '';

    if ($matchArray['match_confirm'] == 1) {

        if ($matchArray['admin_confirm'] == 1) {
            $confirmStatus = $_language->module['match_admin_confirmed'];
        } else {
            $confirmStatus = $_language->module['match_confirmed'];
        }

    } else {
        $confirmStatus = $_language->module['match_not_played'];
    }

    $status .= '<div class="list-group-item">'.$confirmStatus.'</div>';

    if ($cup_array['mappool'] > 0) {
        $matchVeto = ($matchArray['mapvote'] == 1) ?
            $_language->module['match_veto_ok'] : $_language->module['match_veto_ip'];
        $status .= '<div class="list-group-item">' . $matchVeto . '</div>';
    }

    //
    //  Screenshots
    global $image_url;

    $screenshot_url = $image_url . '/cup/match_screenshots/';
    $screenshot_local_url = __DIR__ . '/../../images/cup/match_screenshots/';

    $screenshotArray = getScreenshots($match_id);

    if (validate_array($screenshotArray, true)) {

        $statusScreenshotArray = array();
        foreach ($screenshotArray as $screenshot) {

            if (file_exists($screenshot_local_url . $screenshot['file'])) {
                $statusScreenshotArray[] = '<a href="' . $screenshot_url . $screenshot['file'] . '" target="_blank">' . $screenshot['category_name'] . '</a>';
            } else {
                $statusScreenshotArray[] = '<del>' . $screenshot['category_name'] . '</del>';
            }

        }

        $status .= '<div class="list-group-item">Screens: ' . implode(', ', $statusScreenshotArray) . '</div>';

    }

    return $status;

}