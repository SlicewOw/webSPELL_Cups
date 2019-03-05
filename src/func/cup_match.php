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

            $maps = getmap($match_id, $matchArray['format']);
            if (!empty($maps)) {
                $matchInfoArray[] = $maps;
            }

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

                $linkAttributeArray = array();
                $linkAttributeArray[] = 'href="' . $screenshot_url . $screenshot['file'] . '"';
                $linkAttributeArray[] = 'class="cup-match-screenshot-viewer"';
                $linkAttributeArray[] = '';

                $statusScreenshotArray[] = '<a ' . implode(' ', $linkAttributeArray) . '>' . $screenshot['category_name'] . '</a>';

            } else {
                $statusScreenshotArray[] = '<del>' . $screenshot['category_name'] . '</del>';
            }

        }

        $status .= '<div class="list-group-item">Screens: ' . implode(', ', $statusScreenshotArray) . '</div>';

    }

    return $status;

}

function resetMatchMapVote($maps_array) {

    $mapsArray = array();

    if (!validate_array($maps_array, true)) {
        $maps_array['list'] = array();
    }

    if (isset($maps_array['list'])) {
        $mapsArray['open'] = $maps_array['list'];
    } else {
        $mapsArray['open'] = array();
    }

    $mapsArray['banned']['team1'] = array();
    $mapsArray['banned']['team2'] = array();
    $mapsArray['picked'] = array();

    return $mapsArray;

}


/* Matches */
function getmatch($id, $cat = '') {

    global $_database;

    if ($cat == 'active_playoff') {
        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `active`
                    FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE `matchID` = " . $id
            )
        );
        $returnValue = $get['active'];
    } else if (($cat == 'confirmed_final') || ($cat == 'not_confirmed_playoff')) {
        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `team1_confirmed`,
                        `team2_confirmed`,
                        `admin_confirmed`
                    FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE `cupID` = " . $id . "
                    ORDER BY runde DESC
                    LIMIT 0, 1"
            )
        );
        if (($get['admin_confirmed'] == 1) || (($get['team1_confirmed'] == 1) && ($get['team2_confirmed'] == 1))) {
            $returnValue = ($cat == 'confirmed_final') ?
                TRUE : FALSE;
        } else {
            $returnValue = ($cat == 'confirmed_final') ?
                FALSE : TRUE;
        }
    } else if ($cat == 'map_vote') {
        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `mapvote`
                    FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE `matchID` = " . $id
            )
        );
        $returnValue = ($get['mapvote'] == 0) ? FALSE : TRUE;
    } else if ($cat == 'format') {
        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `format`
                    FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE `matchID` = " . $id
            )
        );
        $returnValue = $get['format'];
    } else {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE `matchID` = " . $id
            )
        );

        if (($get['admin_confirmed'] == 1) || (($get['team1_confirmed'] == 1) && ($get['team2_confirmed'] == 1))) {
            $matchConfirm = 1;
        } else {
            $matchConfirm = 0;
        }

        $cupArray = getCup($get['cupID']);

        for ($x = 1; $x < 3; $x++) {

            if (isset($cupArray['mode'])) {

                if ($cupArray['mode'] == '1on1') {
                    $team[$x]['name'] = getnickname($get['team'.$x]);
                } else {
                    $team[$x]['name'] = getteam($get['team'.$x], 'name');
                }

            } else {
                $team[$x]['name'] = 'unknown';
            }

        }

        $returnValue = array(
            "cup_id"		=> $get['cupID'],
            "bracket"		=> $get['wb'],
            "runde"			=> $get['runde'],
            "spiel"			=> $get['spiel'],
            "format"		=> $get['format'],                // bo1, bo3, ...
            "date"			=> $get['date'],
            "mapvote"		=> $get['mapvote'],
            "team1_id"		=> $get['team1'],
            "team1"			=> array(
                "name"		=> $team[1]['name']
            ),
            "team1_freilos"	=> $get['team1_freilos'],
            "team1_confirm"	=> $get['team1_confirmed'],
            "ergebnis1"		=> $get['ergebnis1'],
            "team2_id"		=> $get['team2'],
            "team2"			=> array(
                "name"		=> $team[2]['name']
            ),
            "team2_freilos"	=> $get['team2_freilos'],
            "team2_confirm"	=> $get['team2_confirmed'],
            "ergebnis2"		=> $get['ergebnis2'],
            "active"		=> $get['active'],
            "comments"		=> $get['comments'],
            "maps"			=> $get['maps'],
            "admin_confirm"	=> $get['admin_confirmed'],
            "match_confirm"	=> $matchConfirm,
            "server"		=> $get['server'],
            "bot"			=> $get['bot'],
            "admin"			=> $get['admin']
        );
    }

    return $returnValue;

}
function getmatches($cup_id = 0, $selected_id = 0, $allMatches = TRUE) {


    $whereClauseArray = array();

    if ($cup_id > 0) {
        $whereClauseArray[] = '`cupID` = ' . $cup_id;
    }

    if (!$allMatches) {
        $whereClauseArray[] = '`team1_freilos` = 0';
        $whereClauseArray[] = '`team2_freilos` = 0';
    }

    $whereClause = (validate_array($whereClauseArray, true)) ?
        'WHERE ' . implode(' AND ', $whereClauseArray) : '';

    $match = cup_query(
        "SELECT
                `matchID`,
                `wb`,
                `runde`,
                `spiel`,
                `team1`,
                `team1_freilos`,
                `team2`,
                `team2_freilos`
            FROM `" . PREFIX . "cups_matches_playoff`
            " . $whereClause . "
            ORDER BY wb DESC, runde DESC, spiel ASC",
        __FILE__
    );

    global $userID;

    $cupAdminAccess = (iscupadmin($userID)) ? TRUE : FALSE;

    $activeBracket = 100;

    $matches = '<option value="0">-- / --</option>';

    $n = 0;
    while($dx = mysqli_fetch_array($match)) {

        if($activeBracket > $dx['wb']) {

            if($n > 0) {
                $matches .= '</optgroup>';
            }

            $activeBracket = $dx['wb'];

            $label = ($dx['wb']) ? 'Winner Bracket' : 'Loser Bracket';
            $matches .= '<optgroup label="'.$label.'">';

        }

        if ($dx['team1'] != 0) {
            $team1 = getteam($dx['team1'], 'name');
        } else {
            $team1 = 'freilos';
        }

        if ($dx['team2'] != 0) {
            $team2 = getteam($dx['team2'], 'name');
        } else {
            $team2 = 'freilos';
        }

        $match_info = '';
        $match_info .= 'Match #'.$dx['matchID'].' - ';

        if ($cupAdminAccess) {
            $match_info .= 'R'.$dx['runde'].' - ';
        }

        $match_info .= $team1 . ' vs. ' . $team2;

        $matches .= '<option value="' . $dx['matchID'] . '">' . $match_info . '</option>';

        $n++;

    }

    $matches .= '</optgroup>';

    return selectOptionByValue($matches, $selected_id, true);

}

function getmap($matchID, $format) {

    $selectQuery = cup_query(
        "SELECT
                `maps`
            FROM `" . PREFIX . "cups_matches_playoff`
            WHERE `matchID` = " . $matchID,
        __FILE__
    );

    $get = mysqli_fetch_array($selectQuery);

    $mapsArray = unserialize($get['maps']);

    $returnMapArray = array();

    if ($format == 'bo1') {

        if (isset($mapsArray['picked'][0])) {
            $returnMapArray[] = $mapsArray['picked'][0];
        }

    } else if ($format == 'bo3' || $format == 'bo5') {

        $returnValue = '';

        if (!empty($mapsArray['picked'])) {

            $returnValue .= 'Maps: ';

            if (isset($mapsArray['picked']['team1'][0])) {
                $returnMapArray[] = $mapsArray['picked']['team1'][0];
            }

            if (isset($mapsArray['picked']['team2'][0])) {
                $returnMapArray[] = $mapsArray['picked']['team2'][0];
            }

            if (isset($mapsArray['picked']['team1'][1])) {
                $returnMapArray[] = $mapsArray['picked']['team1'][1];
            }

            if (isset($mapsArray['picked']['team2'][1])) {
                $returnMapArray[] = $mapsArray['picked']['team2'][1];
            }

            if (isset($mapsArray['picked'][0])) {
                $returnMapArray[] = $mapsArray['picked'][0];
            }

        }

    }

    return (validate_array($returnMapArray, true)) ?
            'Maps: ' . implode(', ', $returnMapArray) : '';

}


function getScreenshots($match_id) {

    if (!validate_int($match_id, true)) {
        return array();
    }

    $selectQuery = cup_query(
        "SELECT
                cmps.`category_id`,
                cmps.`file`,
                cmps.`date`,
                cmpsc.`name`
            FROM `" . PREFIX . "cups_matches_playoff_screens` cmps
            JOIN `" . PREFIX . "cups_matches_playoff_screens_category` cmpsc ON cmpsc.`categoryID` = cmps.`category_id`
            WHERE cmps.`matchID` = " . $match_id . "
            ORDER BY cmps.`date` ASC",
        __FILE__
    );

    if (!$selectQuery) {
        return array();
    }

    if (mysqli_num_rows($selectQuery) < 1) {
        return array();
    }

    $returnArray = array();

    while ($get = mysqli_fetch_array($selectQuery)) {

        $returnArray[] = array(
            'category_id' => $get['category_id'],
            'category_name' => $get['name'],
            'file' => $get['file'],
            'date' => $get['date']
        );

    }

    return $returnArray;

}
