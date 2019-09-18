<?php

$returnArray = array(
    'status' => FALSE,
    'text' => '',
    'message' => array()
);

try {

    $_language->readModule('cups', false, true);

    if (!iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $match_id = (isset($_GET['match_id']) && validate_int($_GET['match_id'])) ?
        (int)$_GET['match_id'] : 0;

    if ($match_id < 1) {
        throw new \UnexpectedValueException($_language->module['unknown_match']);
    }

    if ($getAction == 'mapvote') {

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    cmp.`matchID` AS `match_id`,
                    cmp.`runde` AS `runde`,
                    cmp.`maps` AS `maps`,
                    cmp.`format` AS `format`,
                    c.`mode` AS `cup_mode`,
                    c.`mappool` AS `cup_mappool`
                FROM `" . PREFIX . "cups_matches_playoff` cmp
                JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
                WHERE cmp.`matchID` = " . $match_id
        );

        if (!$selectQuery) {
            throw new \UnexpectedValueException($_language->module['query_failed']);
        }

        $get = mysqli_fetch_array($selectQuery);

        if ($match_id != $get['match_id']) {
            throw new \UnexpectedValueException($_language->module['unknown_match']);
        }

        if (empty($get['maps'])) {
            throw new \UnexpectedValueException($_language->module['unknown_match_maps']);
        }

        $mapArray = unserialize($get['maps']);

        $openMaps = '';

        if (isset($mapArray['open'])) {

            $openMapArray = $mapArray['open'];
            $anzMaps = count($openMapArray);
            if ($anzMaps > 0) {

                for ($n = 0; $n < $anzMaps; $n++) {
                    $openMaps .= '<div class="list-group-item">' . $openMapArray[$n] . '</div>';
                }

            } else {
                $openMaps .= '<div class="list-group-item italic">keine Maps offen</div>';
            }

        }

        $pickedMaps = '';

        $getMapsToBePlayed = substr($get['format'], 2, strlen($get['format']));

        if (isset($mapArray['picked'])) {

            $pickedMapArray = $mapArray['picked'];
            $anzMaps = count($pickedMapArray);
            if ($anzMaps > 0) {

                if ($getMapsToBePlayed > 1) {

                    for ($n = 1; $n < $getMapsToBePlayed; $n++) {

                        $index = 'team'.$n;
                        if (isset($pickedMapArray[$index])) {

                            $anzMaps = count($pickedMapArray[$index]);
                            for ($j = 0; $j < $anzMaps; $j++) {

                                $mapName = $pickedMapArray[$index][$j];
                                $mapName .= ' <span class="pull-right grey">(Team ' . $n . ')</span>';

                                $pickedMaps .= '<div class="list-group-item">' . $mapName . '</div>';

                            }

                        }

                    }

                }

                if (isset($pickedMapArray[0])) {

                    $mapName = $pickedMapArray[0];

                    if ($get['format'] == 'bo3') {
                        $mapName .= ' <span class="pull-right grey">(Decider)</span>';
                    }

                    $pickedMaps .= '<div class="list-group-item">' . $mapName . '</div>';

                }

            } else {
                $pickedMaps .= '<div class="list-group-item italic">keine Maps gepicked</div>';
            }

        }

        $bannedMaps = array(
            1 => '',
            2 => ''
        );

        for ($x = 1; $x < 3; $x++) {

            $teamMapArray = (isset($mapArray['banned']['team' . $x])) ?
                $mapArray['banned']['team' . $x] : array();

            $anzMaps = count($teamMapArray);
            if ($anzMaps > 0) {

                for ($n = 0; $n < $anzMaps; $n++) {
                    $bannedMaps[$x] .= '<div class="list-group-item">' . $teamMapArray[$n] . '</div>';
                }

            }

        }

        $data_array = array();
        $data_array['$openMaps'] = $openMaps;
        $data_array['$pickedMaps'] = $pickedMaps;
        $data_array['$bannedTeam1'] = $bannedMaps[1];
        $data_array['$bannedTeam2'] = $bannedMaps[2];
        $mapData = $GLOBALS["_template_cup"]->replaceTemplate("cup_match_details_maps", $data_array);
        $returnArray['text'] = $mapData;

        if ($get['cup_mappool'] > 0) {

            $data_array = array();
            $data_array['$mapList'] = getMapsAsOptions($get['cup_mappool']);
            $data_array['$match_id'] = $match_id;
            $data_array['$runde'] = $get['runde'];
            $admin_set_maps = $GLOBALS["_template_cup"]->replaceTemplate("cup_match_details_maps_bo" . $getMapsToBePlayed, $data_array);
            $returnArray['text'] .= $admin_set_maps;

        }

    } else if ($getAction == 'saveMatchServer') {

        $serverArray = array(
            'ip' => '',
            'password' => '',
            'rcon' => '',
            'gotv' => '',
            'gotv_pw' => ''
        );

        foreach ($serverArray as $value) {

            if (isset($_POST[$value])) {
                $serverArray[$value] = getinput($_POST[$value]);
            }

        }

        $serverDetails = serialize($serverArray);

        safe_query(
            "UPDATE `" . PREFIX . "cups_matches_playoff`
                SET	`server` = '" . $serverDetails . "'
                WHERE `matchID` = " . $match_id
        );

        $returnArray['message'][] = $_language->module['query_saved'];

    } else if ($getAction == 'saveMatchSettings') {

        $updateValueArray = array();

        if (isset($_POST['format']) && (strlen($_POST['format']) < 5)) {
            $updateValueArray[] = '`format` = "' . $_POST['format'] . '"';
        }

        if (isset($_POST['map_vote'])) {
            $updateValueArray[] = '`mapvote` = ' . (int)$_POST['map_vote'];
        }

        if (isset($_POST['match_active'])) {
            $updateValueArray[] = '`active` = ' . (int)$_POST['match_active'];
        }

        if (isset($_POST['match_comments'])) {
            $updateValueArray[] = '`comments` = ' . (int)$_POST['match_comments'];
        }

        if (isset($_POST['match_admin'])) {
            $updateValueArray[] = '`admin` = ' . (int)$_POST['match_admin'];
        }

        if (isset($_POST['team1_confirmed'])) {
            $updateValueArray[] = '`team1_confirmed` = ' . (int)$_POST['team1_confirmed'];
        }

        if (isset($_POST['team2_confirmed'])) {
            $updateValueArray[] = '`team2_confirmed` = ' . (int)$_POST['team2_confirmed'];
        }

        if (isset($_POST['admin_confirmed'])) {
            $updateValueArray[] = '`admin_confirmed` = ' . (int)$_POST['admin_confirmed'];
        }

        if (isset($_POST['date_start'])) {

            $matchDate = strtotime($_POST['date_start']);
            $matchDate += ((int)$_POST['hour'] * 3600);
            $matchDate += ((int)$_POST['minute'] * 60);

            if($matchDate > 10000) {
                $updateValueArray[] = '`date` = ' . $matchDate;
            }

        }

        if (!validate_array($updateValueArray, true)) {
            throw new \UnexpectedValueException($_language->module['no_data']);
        }

        $updateValues = implode(', ', $updateValueArray);

        $saveQuery = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . "cups_matches_playoff`
                SET	" . $updateValues . "
                WHERE matchID = " . $match_id
        );

        if (!$saveQuery) {
            throw new \UnexpectedValueException($_language->module['query_failed']);
        }

        $returnArray['message'][] = $_language->module['match_settings_saved'];

    } else {

        $returnArray = array(
            'status' => FALSE,
            getConstNameCupIdWithUnderscore() => 0,
            'cup_round'	=> 1,
            'server' => array(
                'server' => array(
                    'ip' => '',
                    'pw' => '',
                    'rcon' => ''
                ),
                'gotv' => array(
                    'ip' => '',
                    'pw' => ''
                )
            ),
            'player' => array(
                'team1'	=> array(
                    'details' => array(
                        'name' => 'unknown'
                    )
                ),
                'team2'	=> array(
                    'details' => array(
                        'name' => 'unknown'
                    )
                )
            ),
            'details' => array(
                'team1'	=> '',
                'team2'	=> ''
            ),
            'settings' => array(
                'html' => '',
                'format' => 'bo1',
                'date' => '',
                'mapvote' => 0,
                'active' => 0,
                'comments' => 1,
                'team1_confirmed' => 0,
                'team2_confirmed' => 0,
                'admin_confirmed' => 0,
                'admin'	=> 0
            )
        );

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    `cupID`,
                    `runde`,
                    `format`,
                    `date`,
                    `mapvote`,
                    `active`,
                    `comments`,
                    `team1`,
                    `team1_freilos`,
                    `team2`,
                    `team2_freilos`,
                    `team1_confirmed`,
                    `team2_confirmed`,
                    `admin_confirmed`,
                    `server`,
                    `admin`
                FROM `" . PREFIX . "cups_matches_playoff`
                WHERE `matchID` = " . $match_id
        );

        if (!$selectQuery) {
            throw new \UnexpectedValueException($_language->module['query_failed']);
        }

        $get = mysqli_fetch_array($selectQuery);

        if (empty($get[getConstNameCupId()]) || !validate_int($get[getConstNameCupId()])) {
            throw new \UnexpectedValueException($_language->module['unknown_cup_id']);
        }

        $cup_id = $get[getConstNameCupId()];
        $returnArray[getConstNameCupIdWithUnderscore()] = $cup_id;
        $returnArray['cup_round'] = $get['runde'];

        $getCup = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        a.mode AS mode,
                        b.tag AS game_tag
                    FROM `" . PREFIX . "cups` a
                    JOIN `" . PREFIX . "games` b ON a.game = b.tag
                    WHERE a.cupID = " . $cup_id
            )
        );

        $gameTag = $getCup['game_tag'];

        $serverArray = (!empty($get['server'])) ?
            unserialize($get['server']) : array();

        if (validate_array($serverArray)) {

            $returnArray['server'] = array(
                'server' => array(
                    'ip' => $serverArray['ip'],
                    'pw' => $serverArray['password'],
                    'rcon' => $serverArray['rcon']
                ),
                'gotv' => array(
                    'ip' => $serverArray['gotv'],
                    'pw' => $serverArray['gotv_pw']
                )
            );

        }

        for ($x = 1; $x < 3; $x++) {

            $team = 'team'.$x;
            if (!$get[$team.'_freilos']) {

                $team_id = $get[$team];

                if ($getCup['mode'] == '1on1') {

                    $user_id = $team_id;

                    $teamTableHead = ($gameTag == 'csg') ?
                        'cup_match_details_gameaccount_csgo' : 'cup_match_details_gameaccount';

                    $returnArray['details'][$team] = $GLOBALS["_template_cup"]->replaceTemplate(
                        $teamTableHead,
                        array()
                    );

                    $returnArray['details'][$team] .= '<tbody>';

                    $gameaccountWhereClauseArray = array();
                    $gameaccountWhereClauseArray[] = 'a.`userID` = ' . $user_id;
                    $gameaccountWhereClauseArray[] = 'a.`category` = \'' . $gameTag . '\'';
                    $gameaccountWhereClauseArray[] = 'a.`active` = 1';

                    $gameaccountWhereClause = implode(' AND ', $gameaccountWhereClauseArray);

                    $selectGameaccountQuery = mysqli_query(
                        $_database,
                        "SELECT
                                a.`gameaccID` AS `gameaccount_id`,
                                a.`value` AS `steam_id`,
                                a.`active` AS `active`,
                                a.`deleted` AS `deleted`,
                                b.`nickname` AS `nickname`
                            FROM `" . PREFIX . "cups_gameaccounts` a
                            JOIN `" . PREFIX . "user` b ON a.`userID` = b.`userID`
                            WHERE " . $gameaccountWhereClause . "
                            ORDER BY a.`userID` ASC"
                    );

                    if (!$selectGameaccountQuery) {
                        throw new \UnexpectedValueException($_language->module['query_failed']);
                    }

                    $getGameaccount = mysqli_fetch_array($selectGameaccountQuery);

                    $nickname = $getGameaccount['nickname'];

                    $returnArray['player'][$team] = array(
                        'team_id' => $user_id,
                        'details' => array(
                            'name' => $nickname,
                            'tag' => $nickname
                        ),
                        'list' => array(
                            array(
                                'position' => 'Player',
                                'steam_id' => $getGameaccount['steam_id'],
                                'steam64_id' => $getGameaccount['steam_id'],
                                'nickname' => $nickname
                            )
                        )
                    );

                    $teamTableRow = ($gameTag == 'csg') ?
                        'cup_match_details_gameaccount_list_csgo' : 'cup_match_details_gameaccount_list';

                    $data_array = array();
                    $data_array['$nickname'] = $nickname;
                    $data_array['$gameaccount'] = $getGameaccount['steam_id'];

                    if ($gameTag == 'csg' && validate_int($getGameaccount['gameaccount_id'], true)) {

                        $selectQuery = mysqli_query(
                            $_database,
                            "SELECT
                                    `date`,
                                    `hours`,
                                    `vac_bann`,
                                    `bann_date`
                                FROM `" . PREFIX . "cups_gameaccounts_csgo`
                                WHERE `gameaccID` = " . $getGameaccount['gameaccount_id']
                        );

                        if (!$selectQuery) {
                            throw new \UnexpectedValueException($_language->module['query_failed']);
                        }

                        $getSteamData = mysqli_fetch_array($selectQuery);

                        if ($getSteamData['vac_bann']) {
                            $isBanned = '<span class="btn btn-danger btn-xs white darkshadow">ja</span>';
                            $bannDate = getformatdate($getSteamData['bann_date']);
                        } else {
                            $isBanned = '<span class="btn btn-success btn-xs white darkshadow">nein</span>';
                            $bannDate = '<span class="grey">- / -</span>';
                        }

                        $data_array['$hours'] = $getSteamData['hours'];
                        $data_array['$is_banned'] = $isBanned;
                        $data_array['$bann_date'] = $bannDate;
                        $data_array['$date'] = getformatdate($getSteamData['date']);

                    } else {
                        $data_array['$hours'] = '- / -';
                        $data_array['$is_banned'] = '- / -';
                        $data_array['$bann_date'] = '- / -';
                        $data_array['$date'] = '- / -';
                    }

                    $returnArray['details'][$team] .= $GLOBALS["_template_cup"]->replaceTemplate(
                        $teamTableRow,
                        $data_array
                    );

                    $returnArray['details'][$team] .= '</tbody>';

                } else {

                    $subget = mysqli_fetch_array(
                        mysqli_query(
                            $_database,
                            "SELECT
                                    `name`,
                                    `tag`,
                                    `userID`
                                FROM `" . PREFIX . "cups_teams`
                                WHERE `teamID` = " . $team_id
                        )
                    );

                    $playerList = array();

                    $teamTableHead = ($gameTag == 'csg') ?
                        'cup_match_details_gameaccount_csgo' : 'cup_match_details_gameaccount';

                    $returnArray['details'][$team] = $GLOBALS["_template_cup"]->replaceTemplate(
                        $teamTableHead,
                        array()
                    );

                    $returnArray['details'][$team] .= '<tbody>';

                    $profileWhereClauseArray = array();
                    $profileWhereClauseArray[] = 'a.`active` = 1';
                    $profileWhereClauseArray[] = 'a.`teamID` = ' . $team_id;
                    $profileWhereClauseArray[] = 'b.`category` = \'' . $gameTag . '\'';
                    $profileWhereClauseArray[] = 'b.`active` = 1';

                    $profileWhereClause = implode(' AND ', $profileWhereClauseArray);

                    $getProfileQuery = mysqli_query(
                        $_database,
                        "SELECT
                                a.`position` AS `position`,
                                b.gameaccID AS `gameaccount_id`,
                                b.`value` AS `steam_id`,
                                b.`active` AS `active`,
                                b.`deleted` AS `deleted`,
                                c.`nickname` AS `nickname`
                            FROM `" . PREFIX . "cups_teams_member` a
                            JOIN `" . PREFIX . "cups_gameaccounts` b ON a.userID = b.userID
                            JOIN `" . PREFIX . "user` c ON a.userID = c.userID
                            WHERE " . $profileWhereClause . "
                            ORDER BY a.`position` DESC, a.`userID` ASC"
                    );

                    if (!$getProfileQuery) {
                        throw new \UnexpectedValueException($_language->module['query_failed']);
                    }

                    while ($getProfile = mysqli_fetch_array($getProfileQuery)) {

                        $playerList[] = array(
                            'position' => $getProfile['position'],
                            'steam_id' => $getProfile['steam_id'],
                            'steam64_id' => $getProfile['steam_id'],
                            'nickname' => $getProfile['nickname']
                        );

                        $teamTableRow = ($gameTag == 'csg') ?
                            'cup_match_details_gameaccount_list_csgo' : 'cup_match_details_gameaccount_list';

                        $data_array = array();
                        $data_array['$nickname'] = $getProfile['nickname'];
                        $data_array['$gameaccount'] = $getProfile['steam_id'];

                        if ($gameTag == 'csg') {

                            $getSteamData = mysqli_fetch_array(
                                mysqli_query(
                                    $_database,
                                    "SELECT
                                            `date`,
                                            `hours`,
                                            `vac_bann`,
                                            `bann_date`
                                        FROM `" . PREFIX . "cups_gameaccounts_csgo`
                                        WHERE `gameaccID` = " . $getProfile['gameaccount_id']
                                )
                            );

                            if ($getSteamData['vac_bann']) {
                                $isBanned = '<span class="btn btn-danger btn-xs white darkshadow">ja</span>';
                                $bannDate = getformatdate($getSteamData['bann_date']);
                            } else {
                                $isBanned = '<span class="btn btn-success btn-xs white darkshadow">nein</span>';
                                $bannDate = '<span class="grey">- / -</span>';
                            }

                            $data_array['$hours'] = $getSteamData['hours'];
                            $data_array['$is_banned'] = $isBanned;
                            $data_array['$bann_date'] = $bannDate;
                            $data_array['$date'] = getformatdate($getSteamData['date']);

                        }

                        $returnArray['details'][$team] .= $GLOBALS["_template_cup"]->replaceTemplate(
                            $teamTableRow,
                            $data_array
                        );

                    }

                    $returnArray['player'][$team] = array(
                        'team_id' => $team_id,
                        'details' => array(
                            'name' => $subget['name'],
                            'tag' => $subget['tag']
                        ),
                        'list' => $playerList
                    );

                    $returnArray['details'][$team] .= '</tbody>';


                }

            } else {

                $returnArray['player'][$team] = array(
                    'team_id' => 0,
                    'details' => array(
                        'name' => 'freilos',
                        'tag' => 'freilos'
                    ),
                    'list' => array()
                );

            }

        }

        /**
         * Match Settings
         */

        //
        // Format
        $returnArray['settings']['format'] = $get['format'];

        $settingForm = '';
        $settingForm .= ($get['format'] == 'bo1') ?
            '<option value="bo1" selected="selected">Best of 1</option>' :
            '<option value="bo1">Best of 1</option>';
        $settingForm .= ($get['format'] == 'bo3') ?
            '<option value="bo3" selected="selected">Best of 3</option>' :
            '<option value="bo3">Best of 3</option>';
        $settingForm .= ($get['format'] == 'bo5') ?
            '<option value="bo5" selected="selected">Best of 5</option>' :
            '<option value="bo5">Best of 5</option>';

        //
        // Date
        $returnArray['settings']['date'] = getformatdatetime($get['date']);
        $dateArray = getSelectDateTime('all', $get['date']);

        $options = '';
        $options .= '<option value="0">nein</option>';
        $options .= '<option value="1">ja</option>';

        //
        // Map-Vote
        $returnArray['settings']['mapvote'] = $get['mapvote'];

        $settingMapVote = str_replace(
            'value="'.$get['mapvote'].'"',
            'value="'.$get['mapvote'].'" selected="selected"',
            $options
        );

        //
        // Match Active
        $returnArray['settings']['active'] = $get['active'];

        $settingMatchActive = str_replace(
            'value="'.$get['active'].'"',
            'value="'.$get['active'].'" selected="selected"',
            $options
        );

        //
        // Comments
        $returnArray['settings']['comments'] = $get['comments'];

        $settingComments = str_replace(
            'value="'.$get['comments'].'"',
            'value="'.$get['comments'].'" selected="selected"',
            $options
        );

        //
        // Team1 confirmed?
        $returnArray['settings']['team1_confirmed'] = $get['team1_confirmed'];

        $settingTeam1Confirmed = str_replace(
            'value="'.$get['team1_confirmed'].'"',
            'value="'.$get['team1_confirmed'].'" selected="selected"',
            $options
        );

        //
        // Team2 confirmed?
        $returnArray['settings']['team2_confirmed'] = $get['team2_confirmed'];

        $settingTeam2Confirmed = str_replace(
            'value="'.$get['team2_confirmed'].'"',
            'value="'.$get['team2_confirmed'].'" selected="selected"',
            $options
        );

        //
        // Admin confirmed?
        $returnArray['settings']['admin_confirmed'] = $get['admin_confirmed'];

        $settingAdminConfirmed = str_replace(
            'value="'.$get['admin_confirmed'].'"',
            'value="'.$get['admin_confirmed'].'" selected="selected"',
            $options
        );

        //
        // Admin Match
        $returnArray['settings']['admin'] = $get['admin'];

        $settingAdminMatch = str_replace(
            'value="'.$get['admin'].'"',
            'value="'.$get['admin'].'" selected="selected"',
            $options
        );

        $data_array = array();
        $data_array['$match_id'] = $match_id;
        $data_array['$settingForm'] = $settingForm;
        $data_array['$match_date'] = date('Y-m-d', $get['date']);
        $data_array['$hours'] = $dateArray['hours'];
        $data_array['$minutes'] = $dateArray['minutes'];
        $data_array['$settingMapVote'] = $settingMapVote;
        $data_array['$settingMatchActive'] = $settingMatchActive;
        $data_array['$settingComments'] = $settingComments;
        $data_array['$settingTeam1Confirmed'] = $settingTeam1Confirmed;
        $data_array['$settingTeam2Confirmed'] = $settingTeam2Confirmed;
        $data_array['$settingAdminConfirmed'] = $settingAdminConfirmed;
        $data_array['$settingAdminMatch'] = $settingAdminMatch;
        $match_settings = $GLOBALS["_template_cup"]->replaceTemplate("cup_match_details_settings", $data_array);
        $returnArray['settings']['html'] .= $match_settings;

    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
