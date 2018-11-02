<?php

/**
 * General
 **/

function getParentIdByValue($value_name, $isIntegerParentId = TRUE) {

    global $_language;

    $_language->readModule('cups', true);

    if (!isset($_GET[$value_name])) {
        return -1;
    }

    if ($isIntegerParentId) {

        if (!validate_int($_GET[$value_name], true)) {
            throw new \Exception($_language->module['unknown_unique_id_type']);
        }

    }

    return (int)$_GET[$value_name];

}

function checkIfContentExists($primary_id, $primary_name, $table) {

    try {

        if (!isset($primary_id)) {
            return FALSE;
        }

        if (!validate_int($primary_id, true)) {

            if ($primary_id == 0) {
                return FALSE;
            }

            throw new \Exception('unknown_parameter_primary_id');

        }

        if (!isset($primary_name) || empty($primary_name)) {
            throw new \Exception('unknown_parameter_primary_name');
        }

        if (!isset($table) || empty($table)) {
            throw new \Exception('unknown_parameter_table');
        }

        global $_database;

        $whereClauseArray = array();
        $whereClauseArray[] = '`' . $primary_name . '` = ' . $primary_id;

        $whereClause = implode(' AND ', $whereClauseArray);

        $query = mysqli_query(
            $_database,
            "SELECT
                    COUNT(*) AS `exist`
                FROM `" . PREFIX . $table . "`
                WHERE " . $whereClause
        );

        if (!$query) {
            throw new \Exception('query_failed (table=' . $table . ', ' . $whereClause . ')');
        }

        $checkIf = mysqli_fetch_array($query);

        if (!$checkIf['exist']) {
            return FALSE;
        } else {
            return TRUE;
        }

    } catch (Exception $e) {
        return FALSE;
    }

}

function updateUserVisitorStatistic($user_id) {

    if (!validate_int($user_id, true)) {
        return;
    }

    global $userID, $_database;

    if (($userID == $user_id) || ($userID < 1)) {
        return;
    }

    $updateQuery = mysqli_query(
        $_database,
        "UPDATE `" . PREFIX . "user`
            SET visits = visits + 1
            WHERE userID = " . $user_id
    );

    $whereClauseArray = array();
    $whereClauseArray[] = '`userID` = ' . $user_id;
    $whereClauseArray[] = '`visitor` = ' . $userID;

    $whereClause = implode(' AND ', $whereClauseArray);

    $selectQuery = mysqli_query(
        $_database,
        "SELECT 
                `visitID` 
            FROM `" . PREFIX . "user_visitors`
            WHERE " . $whereClause
    );

    if (!$selectQuery) {
        return;
    } else {

        $rowExists = mysqli_num_rows($selectQuery);

        $date = time();

        if ($rowExists == 1) {

            $updateQuery = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "user_visitors` 
                    SET date = " . $date . "
                    WHERE " . $whereClause
            );

        } else {

            $insertQuery = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "user_visitors` 
                    (
                        userID, 
                        visitor, 
                        date
                    )
                    VALUES 
                    (
                        " . $user_id . ", 
                        " . $userID . ", 
                        " . $date . "
                    )"
            );

        }

    }

}

function getAge($user_id) {

    if (!validate_int($user_id, true)) {
        return 0;
    }

    global $_database;

    $get = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    `birthday`,
                    DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(birthday)), '%Y') 'age'
                FROM `" . PREFIX . "user`
                WHERE userID = " . (int)$user_id
        )
    );

    $age = (int) $get['age'];

    $birthday = strtotime($get['birthday']);
    if (date('Y', $birthday) == 2000) {

        if ((date('d', $birthday) == date('d')) && (date('m', $birthday) == date('m'))) {
            $age++;
        }

    }

    return (int) $age;

}

function getGame($game_id, $cat = '') {

    global $_database, $image_url, $dir_global;

    if (validate_int($game_id)) {
        $whereClause = '`gameID` = ' . $game_id;
    } else {
        $whereClause = '`tag` = \'' . getinput($game_id) . '\'';
    }

    $get = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    COUNT(gameID) AS `exist`,
                    a.*
                FROM `" . PREFIX . "games` a
                WHERE " . $whereClause
        )
    );

    if ($get['exist'] != 1) {
        return (empty($cat)) ? array() : '';
    }

    if (file_exists($dir_global . 'images/games/' . $get['tag'] . '.gif')) {
        $icon = $image_url . '/games/' . $get['tag'] . '.gif';
    } else {
        $icon = '';
    }

    $returnArray = array(
        "id" => $get['gameID'],
        "name" => $get['name'],
        "tag" => $get['tag'],
        "short" => $get['short'],
        "icon" => $icon,
        "pic" => $get['pic']
    );

    if (empty($cat) || ($cat == 'all')) {
        return $returnArray;
    } else {
        return $returnArray[$cat];
    }

}

function getuserlist($selected = '') {
    global $_database;

    $users = '<option value="0">-- / --</option>';
    $user_id = mysqli_query(
        $_database, 
        "SELECT userID, nickname FROM ".PREFIX."user 
            WHERE banned IS NULL 
            ORDER BY nickname ASC"
    );

    while ($ds = mysqli_fetch_array($user_id)) {
        $users .= '<option value="'.$ds['userID'].'">'.$ds['nickname'].' (#'.$ds['userID'].')</option>';
    }

    if(!empty($selected)) {
        $users = str_replace(
            'value="'.$selected.'"', 
            'value="'.$selected.'" selected="selected"', 
            $users
        );
    }

    return $users;
}

function getPlayerPosition($position, $cat = '', $game_id = NULL, $addPublicOption = FALSE, $idAsAttribute = FALSE) {

    /*
     * $game_id : NULL, wenn Game nicht beruecksichtigt werden soll
     *				  	>=0, wenn Game einbezogen werden soll
     */

    global $_database;

    if (empty($cat) || ($cat == 'name') || ($cat == 'tag')) {

        $whereClauseArray = array();

        $whereClauseArray[] = (validate_int($position, true)) ? 
            'positionID = ' . (int)$position : 'tag = \'' . $position . '\'';

        if (validate_int($game_id, true)) {
            $whereClauseArray[] = 'game_id = ' . $game_id;
        } else if (!validate_int($position, true)) {
            $whereClauseArray[] = 'game_id IS NULL';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database, 
            "SELECT 
                    `name`, 
                    `tag`, 
                    `game_id` 
                FROM `" . PREFIX . "user_position_static` 
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            return '';
        }

        $ds = mysqli_fetch_array($selectQuery);

        if ($cat == 'tag') {
            return $ds['tag'];
        }

        $returnValue = $ds['name'];

        if (validate_int($ds['game_id'], true)) {
            $returnValue .= ' ' . getGame($ds['game_id'], 'short');
        }

        return $returnValue;

    } else if ($cat == 'list') {

        $returnValue = '';

        if ($addPublicOption) {
            $returnValue .= '<option value="0">Public</option>';
        }

        $whereClauseArray = array();
        if (is_null($game_id)) {
            $whereClauseArray[] = 'game_id IS NULL';
        }

        $whereClause = (validate_array($whereClauseArray, true)) ?
            'WHERE ' . implode(' AND ', $whereClauseArray) : '';

        $query = mysqli_query(
            $_database, 
            "SELECT 
                    `positionID`,
                    `tag`, 
                    `name`, 
                    `game_id` 
                FROM `" . PREFIX . "user_position_static` 
                " . $whereClause . "
                ORDER BY `sort` ASC"
        );

        if (!$query) {
            return '<option value="0">Query failed.</option>';
        }

        while ($ds = mysqli_fetch_array($query)) {

            $option = $ds['name'];
            if(!is_null($ds['game_id']) && ($ds['game_id'] > 0)) {
                $option .= ' (' . getGame($ds['game_id'], 'short') . ')';
            }

            $optionValue = ($idAsAttribute) ?
                $ds['positionID'] : $ds['tag'];

            $returnValue .= '<option value="' . $optionValue . '">' . $option . '</option>';

        }

        $returnValue = selectOptionByValue($returnValue, $position);

        return $returnValue;

    } else if ($cat == 'id') {

        if (empty($position) || (strlen($position) != 2)) {
            return 0;
        }

        if (validate_int($game_id, true)) {
            $whereClause = ' AND game_id = ' . $game_id;
        } else {
            $whereClause = ' AND game_id IS NULL';
        }

        $ds = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT positionID FROM `".PREFIX."user_position_static` 
                    WHERE tag = '" . $position . "'" . $whereClause
            )
        );

        $returnValue = $ds['positionID'];
        return $returnValue;

    } else if ($cat == 'array') {

        $basicArray = array(
            'id' => 0,
            'name' => '',
            'tag' => '',
            'game_id' => 0,
            'sort' => 0
        );

        $whereClauseArray = array();

        if (validate_int($position, true)) {
            $whereClauseArray[] = '`positionID` = ' . $position;
        } else if (strlen($position) == 2) {
            $whereClauseArray[] = '`tag` = \'' . $position . '\'';
        } else {
            throw new \Exception('unknown_parameter_position');
        }

        if (validate_int($game_id, true)) {
            $whereClauseArray[] = '`game_id` = ' . $game_id;
        } else {
            $whereClauseArray[] = '`game_id` IS NULL';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database, 
            "SELECT * FROM `" . PREFIX . "user_position_static` 
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            return $basicArray;
        }

        $ds = mysqli_fetch_array($selectQuery);

        $basicArray['id'] = $ds['positionID'];
        $basicArray['name'] = $ds['name'];
        $basicArray['tag'] = $ds['tag'];
        $basicArray['game_id'] = $ds['game_id'];
        $basicArray['sort'] = $ds['sort'];

        return $basicArray;

    } else {
        return FALSE;
    }

}

/* Adminzugang */

function iscupadmin($user_id) {

    if (!validate_int($user_id)) {
        return FALSE;
    }

    global $_database;

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                `userID`
            FROM `" . PREFIX . "user_groups`
            WHERE (`cup` = 1 OR `super` = 1) AND `userID` = " . $user_id
    );

    if (!$selectQuery) {
        return FALSE;
    }

    $ergebnis = mysqli_num_rows($selectQuery);

    return ($ergebnis == 1) ? TRUE : FALSE;

}

function isinteam($userID, $teamID, $admin) {

    global $_database;

    if ($admin == 'admin') {

        //
        // return : Anzahl Teams als Admin

        $whereClauseArray = array();

        $whereClauseArray[] = 'userID = ' . $userID;
        $whereClauseArray[] = 'deleted = 0';

        if(validate_int($teamID)) {
            $whereClauseArray[] = 'teamID = ' . $teamID;
        }

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `anz`
                    FROM `" . PREFIX . "cups_teams`
                    WHERE " . implode(' AND ' , $whereClauseArray)
            )
        );

        $returnValue = $get['anz'];

    } else if ($admin == 'player') {

        //
        // return : 0 / 1
        // 0 : Spieler ist in keinem Team
        // 1 : Spieler ist in mindestens einem Team

        $whereClauseArray = array();

        $whereClauseArray[] = 'userID = ' . $userID;
        $whereClauseArray[] = 'active = 1';

        if(validate_int($teamID)) {
            $whereClauseArray[] = 'teamID = ' . $teamID;
        }

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `anz`
                    FROM `" . PREFIX . "cups_teams_member`
                    WHERE " . implode(' AND ' , $whereClauseArray)
            )
        );

        $returnValue = ($get['anz'] > 0) ?
            TRUE : FALSE;

    } else {

        //
        // return : Anzahl Teams des Benutzers

        $whereClauseArray = array();

        $whereClauseArray[] = 'userID = ' . $userID;
        $whereClauseArray[] = 'active = 1';

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `anz`
                    FROM `" . PREFIX . "cups_teams_member`
                    WHERE " . implode(' AND ', $whereClauseArray)
            )
        );

        $returnValue = $get['anz'];

    }

    return $returnValue;

}
function cup_checkin($cup_id, $user_id, $cat = '') {

    global $_database;

    if (!validate_int($cup_id)) {
        return FALSE;
    }

    if (!validate_int($user_id)) {
        return FALSE;
    }

    if ($cat == 'is_registered') {
        $checkinValue = 0;
    } else if ($cat == 'is_checked_in') {
        $checkinValue = 1;
    } else {
        $checkinValue = -1;
    }

    if ($checkinValue < 0) {
        return FALSE;
    }

    $i = 0;

    $cupMode = getcup($cup_id, 'mode');

    if ($cupMode == '1on1') {

        $whereClauseArray = array();
        $whereClauseArray[] = '`cupID` = ' . $cup_id;
        $whereClauseArray[] = '`teamID` = ' . $user_id;
        $whereClauseArray[] = '`checked_in` = ' . $checkinValue;

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    COUNT(*) AS `exist`
                FROM `" . PREFIX . "cups_teilnehmer`
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            return FALSE;
        }

        $checkIf = mysqli_fetch_array($selectQuery);

        return ($checkIf['exist'] == 1) ?
            TRUE : FALSE;

    } else {

        $get_id = mysqli_query(
            $_database,
            "SELECT
                    `teamID`
                FROM `" . PREFIX . "cups_teilnehmer`
                WHERE `cupID` = " . $cup_id . " AND `checked_in` = " . $checkinValue
        );

        while ($te = mysqli_fetch_array($get_id)) {

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            COUNT(*) AS `anz`
                        FROM `".PREFIX."cups_teams_member`
                        WHERE `userID` = " . $user_id . " AND `teamID` = " . $te['teamID'] . " AND `active` = 1"
                )
            );

            if ($get['anz'] > 0) {
                $i++;
            }

        }

        return ($i > 0) ? TRUE : FALSE;

    }

}

/* Cup Informationen */
function getcup($id, $cat = '') {

    $returnValueAsArray = true;

    try {

        if (!validate_int($id, true)) {
            throw new \Exception('error_unknown_parameter_cup_id');
        }

        if (!checkIfContentExists($id, 'cupID', 'cups')) {
            throw new \Exception('unknown_cup (' . $id . ', ' . $cat . ')');
        }

        global $_database, $userID;

        $whereClauseArray = array();
        $whereClauseArray[] = '`cupID` = ' . $id;

        if (($cat == 'anz_teams') || ($cat == 'anz_teams_checkedin')) {

            $returnValueAsArray = false;

            //
            // return : Anzahl Teams im Cup

            if ($cat == 'anz_teams_checkedin') {
                $whereClauseArray[] = '`checked_in` = 1';
            }

            $whereClause = implode(' AND ', $whereClauseArray);

            $selectQuery = mysqli_query(
                $_database,
                "SELECT
                        `teamID`
                    FROM `" . PREFIX . "cups_teilnehmer`
                    WHERE " . $whereClause
            );

            if (!$selectQuery) {
                throw new \Exception('cups_teilnehmer_query_select_failed');
            }

            $anz = mysqli_num_rows($selectQuery);
            return $anz;

        } else if ($cat == 'rand_teams') {

            //
            // return : Array mit zufällig sortierten Teams

            $whereClause = implode(' AND ', $whereClauseArray);

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            `max_size`
                        FROM `" . PREFIX . "cups`
                        WHERE " . $whereClause
                )
            );

            $size = $get['max_size'];

            $whereClauseArray[] = '`checked_in` = 1';
            $whereClause = implode(' AND ', $whereClauseArray);

            $info = mysqli_query(
                $_database,
                "SELECT
                        `teamID`
                    FROM `" . PREFIX . "cups_teilnehmer`
                    WHERE " . $whereClause
            );

            if (!$info) {
                throw new \Exception('cups_teilnehmer_query_select_failed');
            }

            $getTeamCountCheckedIn = mysqli_num_rows($info);

            if($getTeamCountCheckedIn < 1) {
                return array();
            }

            $arr[] = array();

            $arr = array_fill(0, $size, 0);

            $i=0;
            while ($ds = mysqli_fetch_array($info)) {
                $arr[$i++] = $ds['teamID'];
            }

            shuffle($arr);
            return $arr;

        } else if ($cat != 'all' && !empty($cat)) {

            //
            // return : Spezifische Abfrage der Kategorie

            $whereClause = implode(' AND ', $whereClauseArray);

            $selectQuery = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "cups`
                    WHERE " . $whereClause
            );

            if (!$selectQuery) {
                throw new \Exception('cups_query_select_failed');
            }

            $get = mysqli_fetch_array($selectQuery);

            $returnValue = (isset($get[$cat])) ?
                $get[$cat] : '';

        } else {

            $timeNow = time();

            $whereClause = implode(' AND ', $whereClauseArray);

            $query = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "cups`
                    WHERE " . $whereClause
            );

            if (!$query) {
                throw new \Exception('cups_query_select_failed');
            }

            $get = mysqli_fetch_array($query);

            if (!validate_int($get['cupID'], true)) {
                throw new \Exception('unknown_cup_id (all, ' . $whereClause . ')');
            }

            $cup_id = $get['cupID'];

            $mode = explode('on', $get['mode']);
            $maxSize = $mode[0];

            $anzRunden = log($get['max_size'], 2);

            if ($get['status'] == 1) {

                //
                // Phase: Anmeldung & Check-In

                $cupPhase = '';
                if ($timeNow <= $get['checkin_date']) {

                    //
                    // Phase: Anmeldung
                    if (isinteam($userID, 0, 'admin')) {
                        $cupPhase = 'admin_register';
                    } else if (($get['mode'] == '1on1') || isinteam($userID, 0, '')) {
                        $cupPhase = 'register';
                    }

                } else if ($timeNow <= $get['start_date']) {

                    //
                    // Phase: Check-In
                    if (isinteam($userID, 0, 'admin')) {
                        $cupPhase = 'admin_checkin';
                    } else if (($get['mode'] == '1on1') || isinteam($userID, 0, '')) {
                        $cupPhase = 'checkin';
                    }

                } else {
                    $cupPhase = 'running';
                }

                if (empty($cupPhase)) {
                    $cupPhase = 'no_team';
                }

            } else {

                //
                // Phase: Cup laeuft bereits
                $cupPhase = ($get['status'] < 4) ?
                    'running' : 'finished';

            }

            $hits_total = 0;
            $hits_total += $get['hits'];
            $hits_total += $get['hits_teams'];
            $hits_total += $get['hits_groups'];
            $hits_total += $get['hits_bracket'];
            $hits_total += $get['hits_rules'];

            $settingsArray = array(
                'format' => array()
            );

            $returnValue = array(
                "id"			=> $cup_id,
                "name"			=> $get['name'],
                "registration"	=> $get['registration'],	// Registration? Open/Invite/Closed
                "priority"		=> $get['priority'],		// Priorität? normal/main
                "elimination"	=> $get['elimination'],		// Elimination? Single/Double/Swiss/...
                "phase"			=> $cupPhase,
                "checkin"		=> $get['checkin_date'],
                "start"			=> $get['start_date'],
                "game"			=> $get['game'],            // Game-Tag
                "server"		=> $get['server'],          // Server enabeld? 1/0
                "bot"			=> $get['bot'],             // Bot enabled? 1/0
                "map_vote"		=> $get['mapvote_enable'],  // Map-Vote? 1/0
                "mappool"		=> $get['mappool'],         // Map-Pool ID
                "mode"			=> $get['mode'],            // 1on1, 2on2, 5on5
                "max_mode"		=> $maxSize,                // 1=1on1, 2=2on2, 5=5on5
                "rule_id"		=> $get['ruleID'],          // Rule ID
                "size"			=> $get['max_size'],        // 2, 4, 8, 16, 32, 64
                'teams' => array(
                    'registered' => getcup($cup_id, 'anz_teams'),
                    'checked_in' => getcup($cup_id, 'anz_teams_checkedin')
                ),
                "anz_runden"	=> $anzRunden,              // 1 (2er Bracket), 2 (4er Bracket), 3, 4, 5, 6
                "max_pps"		=> $get['max_penalty'],
                "groupstage"	=> $get['groupstage'],      // Groupstage enabled? 1/0
                "status"		=> $get['status'],          // Cup State: 1=open, 2=groupstage, 3=playoffs, 4=finished
                "hits"			=> $hits_total,
                "hits_detail"	=> array(
                    "home"		=> $get['hits'],
                    "teams"		=> $get['hits_teams'],
                    "groups"	=> $get['hits_groups'],
                    "bracket"	=> $get['hits_bracket'],
                    "rules"		=> $get['hits_rules']
                ),
                "settings"		=> $settingsArray,
                "description"	=> $get['description'],
                "saved"			=> $get['saved'],           // Cup oeffentlich? 1/0
                "admin"			=> $get['admin_visible']    // Admin only? 1/0
            );

        }

    } catch (Exception $e) {

        if ($returnValueAsArray) {
            return array();
        } else {
            return 0;
        }

    }

    return $returnValue;

}
function getcups($cat = '', $selected = '') {
    global $_database, $userID;

    $activeGame = '';

    if (empty($cat)) {

        $where_clause = (!iscupadmin($userID)) ? 
            ' WHERE a.admin_visible = \'0\'' : '';

        $ergebnis = mysqli_query(
            $_database, 
            "SELECT 
                    a.cupID AS cup_id, 
                    a.name AS cup_name,
                    a.admin_visible AS cup_isVisible,
                    b.name AS game_name
                FROM `".PREFIX."cups` a
                JOIN `".PREFIX."games` b ON a.gameID = b.gameID
                ".$where_clause." 
                ORDER BY a.gameID ASC, a.admin_visible ASC, a.name ASC"
        );

        $returnValue = '';
        while($ds = mysqli_fetch_array($ergebnis)) {

            if(empty($activeGame) || ($activeGame != $ds['game_name'])) {
                if(!empty($activeGame)) {
                    $returnValue .= '</optgroup>';
                }
                $activeGame = $ds['game_name'];
                $returnValue .= '<optgroup label="'.$activeGame.'">';
            }

            $cupName = $ds['cup_name'];
            if($ds['cup_isVisible']) {
                $cupName .= ' (Admin Cup)';
            }
            $returnValue .= '<option value="'.$ds['cup_id'].'">'.$cupName.'</option>';

        }

        $returnValue .= '</optgroup>';

    } else if($cat == 'active_cups') {

        $where_clause = (!iscupadmin($userID)) ? ' AND admin_visible = \'0\'' : '';

        $ergebnis = mysqli_query(
            $_database,
            "SELECT 
                    a.cupID AS cup_id, 
                    a.name AS cup_name,
                    a.admin_visible AS cup_isVisible,
                    b.name AS game_name
                FROM `".PREFIX."cups` a
                JOIN `".PREFIX."games` b ON a.gameID = b.gameID
                WHERE start_date >= '".time()."'".$where_clause." 
                ORDER BY a.gameID ASC, a.admin_visible ASC, a.name ASC"
        );

        $returnValue = '<option value="0">-- / --</option>';
        while($ds = mysqli_fetch_array($ergebnis)) {

            if(empty($activeGame) || ($activeGame != $ds['game_name'])) {
                if(!empty($activeGame)) {
                    $returnValue .= '</optgroup>';
                }
                $activeGame = $ds['game_name'];
                $returnValue .= '<optgroup label="'.$activeGame.'">';
            }

            $cupName = $ds['cup_name'];
            if($ds['cup_isVisible']) {
                $cupName .= ' (Admin Cup)';
            }
            $returnValue .= '<option value="'.$ds['cup_id'].'">'.$cupName.'</option>';

        }

        $returnValue .= '</optgroup>';

    }
    return $returnValue;
}

/* Cup Kontrolle */
function cup($cupID, $teamID, $cat) {
    global $_database;

    if($cat == 'join') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teilnehmer` 
                    WHERE cupID = ".$cupID." AND teamID = ".$teamID
            )
        );

        $returnValue = TRUE;
        if ($get['anz'] > 0) {
            // Team ist bereits angemeldet
            $returnValue = FALSE;
        } else {

            $ergebnis = mysqli_query(
                $_database, 
                "SELECT userID FROM `".PREFIX."cups_teams_member` 
                    WHERE teamID = ".$teamID." AND active = 1"
            );
            while($ds = mysqli_fetch_array($ergebnis)) {

                $team = mysqli_query(
                    $_database, 
                    "SELECT teamID FROM `".PREFIX."cups_teams_member` 
                        WHERE userID = ".$ds['userID']." AND active = 1"
                );
                while($dx = mysqli_fetch_array($team)) {

                    $get = mysqli_fetch_array(
                        mysqli_query(
                            $_database, 
                            "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teilnehmer` 
                                WHERE cupID = ".$cupID." AND teamID = ".$dx['teamID']
                        )
                    );
                    if($get['anz'] > 0) {
                        // Mitglied des Teams ist bereits beim Cup angemeldet
                        $returnValue = FALSE;
                        break;
                    }

                }
            }

        }

    } else if ($cat == 'gameaccount') {

        $returnValue = TRUE;

        $cup = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT game, mode FROM `".PREFIX."cups` 
                    WHERE cupID = ".$cupID
            )
        );

        if($cup['mode'] == '1on1') {

            $user_id = $teamID;

            $where_clause = 'userID = \''.$user_id.'\' AND category = \''.$cup['game'].'\' AND active = \'1\' AND deleted = \'0\'';

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database, "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts` 
                        WHERE ".$where_clause
                )
            );

            if($get['anz'] == 0) {
                // Mitglied hat keinen Gameaccount fuer dieses Spiel
                $returnValue = FALSE;
            }

        } else {

            $ergebnis = mysqli_query(
                $_database, 
                "SELECT userID FROM `".PREFIX."cups_teams_member` 
                    WHERE teamID = ".$teamID." AND kickID = 0 AND active = 1"
            );
            while($ds = mysqli_fetch_array($ergebnis)) {

                $where_clause = 'userID = '.$ds['userID'].' AND category = \''.$cup['game'].'\' AND active = 1 AND deleted = 0';

                $get = mysqli_fetch_array(
                    mysqli_query(
                        $_database, "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts` 
                            WHERE ".$where_clause
                    )
                );

                if($get['anz'] == 0) {
                    // Mitglied hat keinen Gameaccount fuer dieses Spiel
                    $returnValue = FALSE;
                    break;
                }
            }

        }

    }
    return $returnValue;
}

/* Team Informationen */
function getteam($id, $cat = '') {
	
    global $_database, $userID, $dir_global;
	
    if($cat == 'admin') {
		$info = mysqli_query(
		    $_database,
            "SELECT userID FROM `".PREFIX."cups_teams` 
				WHERE teamID = " . $id . " AND deleted = 0"
        );
		if(mysqli_num_rows($info)) {
			$ds = mysqli_fetch_array($info);
			$returnValue = $ds['userID'];
		} else {
			$returnValue = FALSE;
		}
	} else if ($cat == 'teamID') {
        
		$info = mysqli_query(
            $_database, 
            "SELECT 
                    `teamID` 
                FROM `" . PREFIX . "cups_teams` 
                WHERE userID = " . $id . " AND deleted = '0'"
        );
        
		if(mysqli_num_rows($info)) {
			$IDs = array();
			while($ds = mysqli_fetch_array($info)) {
				$IDs[] = $ds['teamID'];
			}
			$returnValue = $IDs;
		} else {
			$returnValue = FALSE;
		}
        
	} else if ($cat == 'anz_matches') {
		$anz1 = mysqli_num_rows(
			mysqli_query($_database, "SELECT * FROM `".PREFIX."cups_gruppen` 
										WHERE (team1 = '".$id."' OR team2 = '".$id."')")
		);
		$anz2 = mysqli_num_rows(
			mysqli_query($_database, "SELECT * FROM `".PREFIX."cups_matches_playoff` 
										WHERE (team1 = '".$id."' OR team2 = '".$id."')")
		);
		$returnValue = ($anz1 + $anz2);
	} else if ($cat == 'anz_cups') {
		$anz = mysqli_num_rows(
			mysqli_query($_database, "SELECT ID FROM `".PREFIX."cups_teilnehmer` 
										WHERE teamID = '".$id."' AND checked_in = '1'")
		);
		$returnValue = $anz;
	} else if ($cat == 'anz_member') {
		$anz = mysqli_num_rows(
			mysqli_query($_database, "SELECT userID FROM `".PREFIX."cups_teams_member` 
										WHERE teamID = '".$id."' AND active = '1'")
		);
		$returnValue = $anz;
	} else if ($cat == 'anz_pps') {
		$anz = mysqli_num_rows(
			mysqli_query($_database, "SELECT ppID FROM `".PREFIX."cups_penalty` 
										WHERE teamID = '".$id."'"));
		$returnValue = $anz;
	} else if ($cat == 'name_exist' || $cat == 'tag_exist') {
        
        $whereClauseArray = array();
        $whereClauseArray[] = '`deleted` = 0';
        
        if ($cat == 'name_exist') {
            $whereClauseArray[] = '`name` = \'' . $id . '\'';
        } else {
            $whereClauseArray[] = '`tag` = \'' . $id . '\'';
        }
        
        $whereClause = implode(' AND ', $whereClauseArray);
        
        $selectQuery = mysqli_query(
            $_database, 
            "SELECT 
                    `teamID` 
                FROM `" . PREFIX . "cups_teams` 
                WHERE " . $whereClause
        );
        
        if (!$selectQuery) {
            return FALSE;
        }
        
		$anz = mysqli_num_rows($selectQuery);
        
		$returnValue = ($anz > 0) ? FALSE : TRUE;
        
	} else if ($cat == 'team_ids') {
		$anz = mysqli_fetch_array(
			mysqli_query($_database, "SELECT team1, team2 FROM `".PREFIX."cups_matches_playoff` 
										WHERE matchID = '".$id."'")
		);
		$returnValue = array($anz['team1'], $anz['team2']);
	} else {
		
		$team_id	= 0;
		$date		= '';
		$name		= '';
		$tag		= '';
		$admin_id	= '';
		$anzMember	= 0;
		$hp			= '';
		$logotype	= '';
		$hits		= '';
		$deleted	= '';
		$isVisible 	= 0;
        $userArray  = array();
        $anzMatches = 0;

		if ($id > 0) {

			$info = mysqli_query(
			    $_database,
                "SELECT * FROM `".PREFIX."cups_teams` 
					WHERE teamID = " . $id
            );
			if (mysqli_num_rows($info) == 1) {

				$ds = mysqli_fetch_array($info);
				if (($ds['deleted'] == 0) || (iscupadmin($userID))) {
					$isVisible = 1;
				}

				$team_id	= $ds['teamID'];
				$date		= $ds['date'];
				$name		= $ds['name'];
				$tag		= $ds['tag'];
				$admin_id	= $ds['userID'];
				$anzMember	= getteam($team_id, 'anz_member');
				$hp			= $ds['hp'];
				$logotype	= getCupTeamImage($team_id, true);
				$hits		= $ds['hits'];
				$deleted	= $ds['deleted'];

		        $anzMatches = getteam($team_id, 'anz_matches');

                $query = mysqli_query(
                    $_database,
                    "SELECT 
                            `userID` 
                        FROM `" . PREFIX . "cups_teams_member`
                        WHERE `teamID` = " . $team_id . " AND `active` = 1
                        ORDER BY `position` DESC"
                );
                
                while ($get = mysqli_fetch_array($query)) {
                    $userArray[] = $get['userID'];
                }

			}

		}
		
		$returnValue = array(
			"team_id" => $team_id,
			"date" => $date,
			"name" => $name,
			"tag" => $tag,
			"admin_id" => $admin_id,
			"anz_member" => $anzMember,
			"hp" => $hp,
			"logotype" => $logotype,
			"hits" => $hits,
			"deleted" => $deleted,
			"visible" => $isVisible,
            "member" => $userArray,
            "statistic" => array(
                "anz_matches" => $anzMatches
            )
		);
		
		if (!empty($cat) && isset($returnValue[$cat])) {
			$returnValue = $returnValue[$cat];
		} 
		
	}
    
	return $returnValue;
    
}
function getteams($selected = '') {
    
	global $_database;
		
	$info = mysqli_query(
		$_database,
		"SELECT teamID, name FROM `".PREFIX."cups_teams` 
			ORDER BY name ASC"
	);
	
	$teams = '<option value="0">-- / --</option>';
	if(mysqli_num_rows($info)) {
		while($ds = mysqli_fetch_array($info)) {
			$teams .= '<option value="'.$ds['teamID'].'">'.$ds['name'].'</option>';	
		}
	}
		
    return selectOptionByValue($teams, $selected, true);
	
}
function getcupteams($user_id = '', $default = '') {
	global $_database;
	$where_clause = (!empty($user_id)) ? ' WHERE userID = \''.$user_id.'\'' : '';
	$teams = (empty($default)) ? '<option value="0">- / -</option>' : '';
	$team_id = mysqli_query($_database, "SELECT teamID, name FROM `".PREFIX."cups_teams`".$where_clause." ORDER BY name");
	while($ds = mysqli_fetch_array($team_id)) {
		$teams .= '<option value="'.$ds['teamID'].'">'.$ds['name'].' (ID: '.$ds['teamID'].')</option>';
	}
	return $teams;
}
function setCupTeamLog($team_id, $team_name, $text, $kicked_id = 0, $parent_id = 0) {
	global $_database, $userID;
	
	$saveQuery = mysqli_query(
		$_database,
		"INSERT INTO `".PREFIX."cups_teams_log`
			(
				`teamID`,
				`teamName`,
				`date`,
				`user_id`,
				`parent_id`,
				`kicked_id`,
				`action`
			)
			VALUES
			(
				".$team_id.",
				'".$team_name."',
				".time().",
				".$userID.",
				".$parent_id.",
				".$kicked_id.",
				'".$text."'
			)"
	);
	
}

/* Matches */
function getmatch($id, $cat = '') {
	global $_database;
	if($cat == 'active_playoff') {
		$get = mysqli_fetch_array(
			mysqli_query($_database, "SELECT active FROM `".PREFIX."cups_matches_playoff` WHERE matchID = '".$id."'")
		);
		$returnValue = $get['active'];
	} elseif($cat == 'confirmed_final') {
		$get = mysqli_fetch_array(
			mysqli_query($_database, "SELECT team1_confirmed, team2_confirmed, admin_confirmed FROM `".PREFIX."cups_matches_playoff` WHERE cupID = '".$id."' ORDER BY runde DESC LIMIT 0,1")
		);
		if(($get['admin_confirmed'] == 1) || (($get['team1_confirmed'] == 1) && ($get['team2_confirmed'] == 1))) {
			$returnValue = TRUE;
		} else {
			$returnValue = FALSE;
		}
	} elseif($cat == 'not_confirmed_playoff') {
		$get = mysqli_fetch_array(
			mysqli_query($_database, "SELECT team1_confirmed, team2_confirmed, admin_confirmed FROM `".PREFIX."cups_matches_playoff` WHERE matchID = '".$id."'")
		);
		if(($get['admin_confirmed'] == 1) || (($get['team1_confirmed'] == 1) && ($get['team2_confirmed'] == 1))) {
			$returnValue = FALSE;
		} else {
			$returnValue = TRUE;
		}
	} elseif($cat == 'map_vote') {
		$get = mysqli_fetch_array(
			mysqli_query($_database, "SELECT mapvote FROM `".PREFIX."cups_matches_playoff` WHERE matchID = '".$id."'")
		);
		$returnValue = ($get['mapvote'] == 0) ? FALSE : TRUE;
	} elseif($cat == 'format') {
		$get = mysqli_fetch_array(
			mysqli_query($_database, "SELECT format FROM `".PREFIX."cups_matches_playoff` WHERE matchID = '".$id."'")
		);
		$returnValue = $get['format'];
	} else {
		
		$get = mysqli_fetch_array(
			mysqli_query(
				$_database, 
				"SELECT * FROM `".PREFIX."cups_matches_playoff` 
					WHERE matchID = '".$id."'"
			)
		);
		
		if(($get['admin_confirmed'] == 1) || (($get['team1_confirmed'] == 1) && ($get['team2_confirmed'] == 1))) {
			$matchConfirm = 1;
		} else {
			$matchConfirm = 0;
		}
		
		$cupArray = getCup($get['cupID']);
            
        for($x=1;$x<3;$x++) {

            if(isset($cupArray['mode'])) {
                
                if($cupArray['mode'] == '1on1') {
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
			"format"		=> $get['format'],
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
    
	global $_database, $userID;
	
	$whereClause = ($cup_id > 0) ? 'WHERE cupID = \''.$cup_id.'\'' : '';
	
	if(!$allMatches) {
		if(empty($whereClause)) {
			$whereClause = 'WHERE';
		} else {
			$whereClause .= ' AND';
		}
		$whereClause .= ' team1_freilos = 0 AND team2_freilos = 0';
	}

	$match = mysqli_query(
		$_database,
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
			ORDER BY wb DESC, runde DESC, spiel ASC"
	);
	
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

		if($dx['team1'] != 0) {
		    $team1 = getteam($dx['team1'], 'name');
		} else {
            $team1 = 'freilos';
        }

		if($dx['team2'] != 0) {
		    $team2 = getteam($dx['team2'], 'name');
		} else {
            $team2 = 'freilos';
        }

        $match_info = '';
        $match_info .= 'Match #'.$dx['matchID'].' - ';

        if($cupAdminAccess) {
            $match_info .= 'R'.$dx['runde'].' - ';
        }

        $match_info .= $team1.' vs. '.$team2;

        $matches .= '<option value="'.$dx['matchID'].'">'.$match_info.'</option>';

        $n++;

	}

    $matches .= '</optgroup>';
		
    return selectOptionByValue($matches, $selected_id, true);
	
}
function getvote($mappoolID, $cat) {
	global $_database;
	if($cat == 'anz_votes') {
		$anz = 0;
		$get = mysqli_fetch_array(
			mysqli_query($_database, "SELECT * FROM `".PREFIX."cups_mappool` WHERE mappoolID = '".$mappoolID."'")
		);
		if(!empty($get['map1'])) {
			$anz += 1;
		}
		if(!empty($get['map2'])) {
			$anz += 1;
		}
		if(!empty($get['map3'])) {
			$anz += 1;
		}
		if(!empty($get['map4'])) {
			$anz += 1;
		}
		if(!empty($get['map5'])) {
			$anz += 1;
		}
		if(!empty($get['map6'])) {
			$anz += 1;
		}
		if(!empty($get['map7'])) {
			$anz += 1;
		}
		if(!empty($get['map8'])) {
			$anz += 1;
		}
		if(!empty($get['map9'])) {
			$anz += 1;
		}
		if(!empty($get['map10'])) {
			$anz += 1;
		}
		$returnValue = $anz;
	} elseif($cat == 'anz_maps') {
		$get = mysqli_fetch_array(
			mysqli_query($_database, "SELECT * FROM `".PREFIX."cups_mappool` WHERE mappoolID = '".$mappoolID."'")
		);
		$anz = 0;
		if(!empty($get['map1']))	$anz += 1;
		if(!empty($get['map2']))	$anz += 1;
		if(!empty($get['map3']))	$anz += 1;
		if(!empty($get['map4']))	$anz += 1;
		if(!empty($get['map5']))	$anz += 1;
		if(!empty($get['map6']))	$anz += 1;
		if(!empty($get['map7']))	$anz += 1;
		if(!empty($get['map8']))	$anz += 1;
		if(!empty($get['map9']))	$anz += 1;
		if(!empty($get['map10']))	$anz += 1;
		
		$returnValue = $anz;
	}
	return $returnValue;
}
function getmap($matchID, $format) {
	global $_database;
	$get = mysqli_fetch_array(
		mysqli_query($_database, "SELECT maps FROM `".PREFIX."cups_matches_playoff` WHERE matchID = '".$matchID."'")
	);
	$mapsArray = unserialize($get['maps']);
	if($format == 'bo1') {
		$returnValue = (isset($mapsArray['picked'][0])) ? 'Map: '.$mapsArray['picked'][0] : '';
	} elseif($format == 'bo3') {
		$returnValue = '';
		if(!empty($mapsArray['picked'])) {
			$returnValue .= 'Maps: ';
			if(isset($mapsArray['picked']['team1'])) {
				$returnValue .= $mapsArray['picked']['team1'][0];
			}
			if(isset($mapsArray['picked']['team2'])) {
				$returnValue .= ', '.$mapsArray['picked']['team2'][0];
			}
			if(isset($mapsArray['picked'][0])) {
				$returnValue .= ', '.$mapsArray['picked'][0];
			}
		}
	} else {
		$returnValue = $mapsArray['picked'];
	}
	return $returnValue;
}

function getScreenshotCategoriesAsOptions($game_id, $showEmptyOption = FALSE, $selected_id = 0) {
    
    $returnValue = '';
    
    if (!validate_int($game_id, true)) {
        return '<option value="0">-- / --</option>';
    }
    
    if ($showEmptyOption) {
        $returnValue .= '<option value="0">-- / --</option>';
    }
    
    global $_database;
    
    $selectQuery = mysqli_query(
        $_database, 
        "SELECT 
                `categoryID`, 
                `name` 
            FROM `" . PREFIX . "cups_matches_playoff_screens_category` 
            WHERE game_id = " . $game_id . "
            ORDER BY `name` ASC"
    );
    
    if (!$selectQuery) {
        return '<option value="0">-- / --</option>';
    }
    
    if (mysqli_num_rows($selectQuery) < 1) {
        $returnValue .= '<option value="0">-- / --</option>';
    }
    
    while ($get = mysqli_fetch_array($selectQuery)) {
        $returnValue .= '<option value="' . $get['categoryID'] . '">' . $get['name'] . '</option>';
    }
    
    return selectOptionByValue($returnValue, $selected_id, true);
    
}

function getScreenshots($match_id) {
        
    if (!validate_int($match_id, true)) {
        return array();
    }
    
    global $_database;
    
    $selectQuery = mysqli_query(
        $_database, 
        "SELECT 
                cmps.`category_id`, 
                cmps.`file`,
                cmps.`date`,
                cmpsc.`name`
            FROM `" . PREFIX . "cups_matches_playoff_screens` cmps
            JOIN `" . PREFIX . "cups_matches_playoff_screens_category` cmpsc ON cmpsc.`categoryID` = cmps.`category_id`
            WHERE cmps.`matchID` = " . $match_id . "
            ORDER BY cmps.`date` ASC"
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

/* Gameaccount Informationen */
function gameaccount($userID, $value, $game) {
    global $_database;

    if($value == 'exist') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts` 
                    WHERE userID = '".$userID."' AND category = '".$game."' AND deleted = 0"
            )
        );

        $returnValue = ($get['anz'] > 0) ? TRUE : FALSE; 

    } elseif($value == 'still_exist') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts` 
                    WHERE category = '".$game."' AND value = '".$userID."' AND deleted = 0"
            )
        );

        $returnValue = ($get['anz'] > 0) ? TRUE : FALSE; 

    } elseif($value == 'deleted_seen') {

        $updateQuery = mysqli_query(
            $_database, 
            "UPDATE `".PREFIX."cups_gameaccounts` 
                SET deleted_seen = '1' 
                WHERE userID = '".$userID."' AND deleted = '1'"
        );

        $returnValue = ($updateQuery) ? TRUE : FALSE; 

    } elseif($value == 'not_active') {

        if(iscupadmin($userID)) {

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database, 
                    "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts` 
                        WHERE active = 0 AND deleted = 1"
                )
            ); 

            $returnValue = $ds['anz'];

        } else {
            $returnValue = FALSE;
        }

    } elseif($value == 'count') {

        $ds = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts` 
                    WHERE userID = ".$userID." AND active = 1 AND deleted = 0"
            )
        ); 

        $returnValue = $ds['anz'];

    } elseif($value == 'get') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT value FROM `".PREFIX."cups_gameaccounts` 
                    WHERE 
                            userID = '".$userID."' 
                        AND 
                            category = '".$game."' 
                        AND 
                            active = '1' 
                        AND 
                            deleted = '0'"
            )
        ); 

        return $get['value'];

    } elseif($value == 'steam64') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT `value` FROM `".PREFIX."cups_gameaccounts` 
                    WHERE 
                            userID = " . $userID . " 
                        AND 
                            category = 'csg' 
                        AND 
                            active = 1 
                        AND 
                            deleted = 0"
            )
        ); 

        return $get['value'];

    } elseif($value == 'validated') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT
                        b.validated AS isValidated
                    FROM `".PREFIX."cups_gameaccounts` a
                    JOIN `".PREFIX."cups_gameaccounts_csgo` b ON a.gameaccID = b.gameaccID
                    WHERE a.userID = '".$userID."' AND a.category = '".$game."' AND a.deleted = '0'")
        );

        return $get['isValidated'];

    } else {

        if($game == 'csg' || $game == 'css' || $game == 'cs') {
            $value = strtolower($value);
            $value = str_replace('steam_', '', $value);
        }

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts` 
                    WHERE value = '".$value."' AND active = '1' AND deleted = '0'"
            )
        );

        $returnValue = ($get['anz'] > 0) ? TRUE : FALSE; 

    }
    return $returnValue;
}
function getCSGOAccountInfo($steam64_id, $multipleAccounts = FALSE) {

    $returnArray = array(
        'status'            => FALSE,
        'error'             => array(),
        'steam_profile'		=> array(),
        'vac_status'		=> array(),
        'csgo_stats'		=> array()
    );

    try {

        global $userID;

        $steamCommunityIdArray = array();

        if ($multipleAccounts) {
            $steamCommunityIdArray = explode(',', $steam64_id);
        } else {
            $steamCommunityIdArray[] = $steam64_id;
        }

        if(!validate_array($steamCommunityIdArray, true)) {
            throw new \Exception('unknown_steam_id_array');
        }

        $anzSteamAccount = count($steamCommunityIdArray);

        //
        // Steam API Key
        $steam_api_key = '';

        if (empty($steam_api_key)) {
            throw new \Exception('unknown_steam_api_key');
        }

        //
        // API URL
        $base_url = 'http://api.steampowered.com/';
        $profile_url = $base_url . 'ISteamUser/GetPlayerSummaries/v0002/?key=' . $steam_api_key . '&steamids=';
        $vac_status_url = $base_url . 'ISteamUser/GetPlayerBans/v1/?key=' . $steam_api_key . '&steamids=';
        $csgo_stats_url = $base_url . 'ISteamUserStats/GetUserStatsForGame/v0002/?appid=730&key=' . $steam_api_key . '&steamid=';

        for ($n = 0; $n < $anzSteamAccount; $n++) {

            $steam64_id = $steamCommunityIdArray[$n];

            if (strlen($steam64_id) != 17) {
                throw new \Exception('wrong_steam64_id_length, ' . $steam64_id);
            }

        }

        $steam64_ids = implode(',', $steamCommunityIdArray);

        //
        // Steam Profile
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $profile_url . $steam64_ids);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($resultArray = @json_decode($result, true)) {

            if ($multipleAccounts) {
                $returnArray['steam_profile'] = (isset($resultArray['response']['players'][0])) ?
                    $resultArray['response']['players'] : array();
            } else {
                $returnArray['steam_profile'] = (isset($resultArray['response']['players'][0])) ?
                    $resultArray['response']['players'][0] : '';
            }

            //
            // VAC Status
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $vac_status_url . $steam64_ids);
            $result = curl_exec($ch);
            curl_close($ch);

            $resultArray = json_decode($result, true);

            if ($multipleAccounts) {
                $returnArray['vac_status'] = (isset($resultArray['players'][0])) ?
                    $resultArray['players'] : array();
            } else {
                $returnArray['vac_status'] = (isset($resultArray['players'][0])) ?
                    $resultArray['players'][0] : '';
            }

            for ($n = 0; $n < $anzSteamAccount; $n++) {

                //
                // CS:GO Stats Status
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $csgo_stats_url . $steamCommunityIdArray[$n]);
                $result = curl_exec($ch);
                curl_close($ch);

                $resultArray = json_decode($result, true);

                $timePlayed = (isset($resultArray['playerstats']['stats'][2])) ?
                    $resultArray['playerstats']['stats'][2] : array('value' => 0);

                $returnArray['csgo_stats'] = array(
                    'time_played'	=> array(
                        'sec'	=> $timePlayed['value'],
                        'hours'	=> (int)($timePlayed['value'] / 60 / 60)
                    )
                );

            }

        } else {
            $returnArray['error'][] = 'bad_request';
        }

        $returnArray['status'] = TRUE;

    } catch (Exception $e) {
        $returnArray['error'][] = $e->getMessage();
    }

    return $returnArray;

}
function updateCSGOGameaccount($user_id) {

    global $_database;

    if (!validate_int($user_id)) {
        return FALSE;
    }

    try {

        $whereClauseArray = array();
        $whereClauseArray[] = 'a.`userID` = ' . $user_id;
        $whereClauseArray[] = 'a.`category` = \'csg\'';
        $whereClauseArray[] = 'a.`active` = 1';

        $whereClause = implode(' AND ', $whereClauseArray);

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT COUNT(*) AS `exist` FROM `".PREFIX."cups_gameaccounts` a
                    WHERE " . $whereClause
            )
        );

        if ($checkIf['exist'] != 1) {
            return;
        }

        $selectQuery = mysqli_query(
            $_database, 
            "SELECT 
                    a.`gameaccID` AS `gameaccount_id`,
                    a.`value` AS `steam64_id`
                FROM `" . PREFIX . "cups_gameaccounts` a
                JOIN `" . PREFIX . "cups_gameaccounts_csgo` b ON a.gameaccID = b.gameaccID
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            throw new \Exception('cups_gameaccounts_query_failed_select (' . $whereClause . ')');
        }

        $getAccount = mysqli_fetch_array($selectQuery);

        $gameaccount_id = $getAccount['gameaccount_id'];

        if (!validate_int($gameaccount_id, true)) {
            throw new \Exception('unknown_gameaccount_id');
        }

        $steam64_id = $getAccount['steam64_id'];

        $accountDetails = getCSGOAccountInfo($steam64_id);
        if (!isset($accountDetails['status']) || !$accountDetails['status']) {
            throw new \Exception('getCSGOAccountInfo_failed');
        }

        $hours_played = $accountDetails['csgo_stats']['time_played']['hours'];

        $isBanned = (empty($accountDetails['vac_status']['VACBanned'])) ? 0 : 1;

        $bannDate = 0;
        if ($isBanned) {
            $dateNow = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
            $bannDays = 86400 * $accountDetails['vac_status']['DaysSinceLastBan'];
            $bannDate = $dateNow - $bannDays;
        }

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT 
                        COUNT(*) AS `anz` 
                    FROM `" . PREFIX . "cups_gameaccounts_csgo`
                    WHERE `gameaccID` = " . $gameaccount_id
            )
        );

        if ($get['anz'] != 1) {

            $query = mysqli_query(
                $_database, 
                "INSERT INTO `" . PREFIX . "cups_gameaccounts_csgo`
                    (
                        `gameaccID`
                    )
                    VALUES
                    (
                        " . $gameaccount_id . "
                    )"
            );

            if (!query) {
                throw new \Exception('query_failed_insert (empty_csgo_account)');
            }

        }

        $insertValuesArray = array();
        $insertValuesArray[] = '`date` = ' . time();
        $insertValuesArray[] = '`vac_bann` = ' . $isBanned;
        $insertValuesArray[] = '`bann_date` = ' . $bannDate;

        if (validate_int($hours_played)) {
            $insertValuesArray[] = '`hours` = ' . $hours_played;
        }

        $insertValues = implode(', ', $insertValuesArray);

        $query = mysqli_query(
            $_database, 
            "UPDATE `" . PREFIX . "cups_gameaccounts_csgo`
                SET " . $insertValues . "
                WHERE `gameaccID` = " . $gameaccount_id
        );

        if (!$query) {
            throw new \Exception('cups_gameaccounts_csgo_query_failed_update (' . $insertValues . ')');
        }

    } catch(Exception $e) {
        return FALSE;
    }

    return TRUE;

}

/* IMAGE */
function imagemaxsize($file, $maxsize_x, $maxsize_y) {

    $is_size = getimagesize($file);
    $is_size_x = $is_size[0];
    $is_size_y = $is_size[1];

    if (($maxsize_x == $maxsize_y) && ($is_size_x == $is_size_y) && ($is_size_x <= $maxsize_x) && ($is_size_y <= $maxsize_y)) {
        // Bild ist quadratisch
        $returnValue = TRUE;
    } else if (($is_size_x <= $maxsize_x) && ($is_size_y <= $maxsize_y)) {
        // Bild ist kleiner als maximale Seitenlängen
        $returnValue = TRUE;
    } else {
        $returnValue = FALSE;
    }
    return $returnValue;

}
function isimage($file) {
    $imageArray = array(
        'image/jpeg',
        'image/gif',
        'image/png'
    );
    $returnValue = (in_array($file, $imageArray)) ? TRUE : FALSE;
    return $returnValue;
}
function getImage($file) {
    if ($file['type'] === 'image/jpeg') {
        $returnArray['extension'] = '.jpg';
    } else if ($file['type'] === 'image/gif') {
        $returnArray['extension'] = '.gif';
    } else {
        $returnArray['extension'] = '.png';
    }
    return $returnArray;
}

/* Regeln */
function getrules($rule_id = 0, $cat = '', $throwOnFailure = FALSE) {

    global $_database, $_language;

    $_language->readModule('cups', true, false);

    if($cat == 'list') {

        $returnValue = '';

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `exist`
                    FROM `".PREFIX."cups_rules`"
            )
        );

        if($checkIf['exist'] < 1) {

            if($throwOnFailure) {
                throw new \Exception($_language->module['no_rules']);
            } else {
                return '';
            }

        }

        $query = mysqli_query(
            $_database,
            "SELECT
                    a.ruleID AS rule_id,
                    a.name AS name,
                    b.tag AS tag,
                    b.name AS game
                FROM `".PREFIX."cups_rules` a
                JOIN `".PREFIX."games` b ON a.gameID = b.gameID
                ORDER BY a.gameID ASC, a.name ASC"
        );

        while($get = mysqli_fetch_array($query)) {

            if(empty($category) || ($category != $get['tag'])) {

                $category = $get['tag'];

                if (!empty($category)) {
                    $returnValue .= '</optgroup>';
                }

                $returnValue .= '<optgroup label="'.$get['game'].'">';

            }

            $returnValue .= '<option value="'.$get['rule_id'].'">'.$get['name'].'</option>';

        }

        $returnValue .= '</optgroup>';

        $returnValue = selectOptionByValue($returnValue, $rule_id);

    } else {

        if(!validate_int($rule_id)) {
            return array();
        }

        $ds = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT ruleID, name, text, date FROM `".PREFIX."cups_rules` 
                    WHERE ruleID = " . $rule_id
            )
        );

        $returnValue = array(
            "rule_id" => $rule_id,
            "name" => $ds['name'],
            "text" => $ds['text'],
            "date" => $ds['date']
        );

        if(!empty($cat)) {

            $returnValue = (isset($returnValue[$cat])) ?
                $returnValue[$cat] : '';

        }

    }

    return $returnValue;

}
function getrulecategories() {
    global $_database;
    $reasons = '';
    $reason_id = mysqli_query(
        $_database,
        "SELECT reasonID, name_de, name_uk, points FROM `".PREFIX."cups_penalty_category` 
            ORDER BY points DESC"
    );
    while ($ds = mysqli_fetch_array($reason_id)) {
        $name = $ds['name_de'].' ('.$ds['points'].')';
        $reasons .= '<option value="'.$ds['reasonID'].'">' . $name . '</option>';
    }
    return $reasons;
}

/* Penalty */
function getUserPenalty($user_id) {

    if(!validate_int($user_id)) {
        return 100;
    }

    $time_now = time();

    global $_database;

    $get_pp = mysqli_query(
        $_database, 
        "SELECT 
                reasonID 
            FROM ".PREFIX."cups_penalty 
            WHERE duration_time > " . $time_now . " AND userID = " . $user_id . " AND deleted = 0"
    );
    if(mysqli_num_rows($get_pp) < 1) {
        return 0;
    }

    $points = 0;
    while($get = mysqli_fetch_array($get_pp)) {
        $points += getpenalty($get['reasonID'], 'points');
    }

    return $points;

}
function getPenaltyCategories($selected = 0) {

    global $_database, $_language;

    $_language->readModule('teams', true);

    $ds = mysqli_query(
        $_database,
        "SELECT 
                `reasonID`, 
                `name_de`, 
                `lifetime`, 
                `points` 
            FROM `" . PREFIX . "cups_penalty_category` 
            ORDER BY `lifetime` ASC, `points` ASC"
    );

    $returnValue = '';
    while ($get = mysqli_fetch_array($ds)) {

        $name = $get['name_de'];

        if ($get['lifetime'] == 1) {
            $name .= ' (' . $_language->module['lifetime'] . ')';
        } else {
            $name .= ' (' . $get['points'] . ' ' . $_language->module['penalties'] . ')';
        }

        $returnValue .= '<option value="' . $get['reasonID'] . '">' . $name . '</option>"';

    }

    $returnValue = selectOptionByValue($returnValue, $selected);

    return $returnValue;

}
function getPenaltyCategory($id, $cat = '') {

    global $_database;

    $ds = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT * FROM `" . PREFIX . "cups_penalty_category` 
                WHERE `reasonID` = " . (int)$id
        )
    );

    $returnValue = array(
        "reason_id"	=> $ds['reasonID'],
        "name_de" => $ds['name_de'],
        "name_de" => $ds['name_uk'],
        "points" => $ds['points'],
        "lifetime" => $ds['lifetime']
    );

    if (!empty($cat)) {
        $returnValue = (isset($returnValue[$cat])) ?
            $returnValue[$cat] : '';
    }

    return $returnValue;

}
function getpenalty($id, $cat = '') {

    global $_database;

    if ($cat == 'team_delete') {

        $memberArray = getteam($id, 'member');

        $whereClause = 'duration_time >= ' . time() . ' AND (teamID = ' . (int)$id;

        if (validate_array($memberArray, true)) {
            $whereClause .= ' OR userID IN (' . implode(',', $memberArray) . ')';
        }

        $whereClause .= ')';

        $ds = mysqli_query(
            $_database,
            "SELECT 
                    `reasonID` 
                FROM `" . PREFIX . "cups_penalty` 
                WHERE " . $whereClause . " AND deleted = 0"
        );

        $reasonArray = array();
        while($info = mysqli_fetch_array($ds)) {
            $reasonArray[] = $info['reasonID'];
        }

        if(count($reasonArray) > 0) {

            $dx = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT SUM(points) AS `counter` FROM `".PREFIX."cups_penalty_category` 
                        WHERE reasonID IN (" . implode(',', $reasonArray) . ")"
                )
            );

            $returnValue = $dx['counter'];

        } else {
            $returnValue = 0;
        }

    } else {

        $selectQuery = mysqli_query(
            $_database,
            "SELECT 
                    cp.*,
                    cpc.`points`,
                    cpc.`lifetime`,
                    cpc.`name_de` AS `category_de`,
                    cpc.`name_uk` AS `category_uk`
                FROM `" . PREFIX . "cups_penalty` cp
                LEFT JOIN `" . PREFIX . "cups_penalty_category` cpc ON cp.`reasonID` = cpc.`reasonID`
                WHERE cp.`ppID` = " . (int)$id
        );

        if (!$selectQuery) {
            return (!empty($cat)) ?
                '' : array();
        }

        $ds = mysqli_fetch_array($selectQuery);

        $returnValue = array(
            "penalty_id" => $ds['ppID'],
            "admin_id" => $ds['adminID'],
            "date" => $ds['date'],
            "expires" => $ds['duration_time'],
            "team_id" => $ds['teamID'],
            "user_id" => $ds['userID'],
            "reason_id" => $ds['reasonID'],
            "comment" => $ds['comment'],
            "deleted" => $ds['deleted'],
            "category_name" => array(
                "de" => $ds['category_de'],
                "uk" => $ds['category_uk']
            ),
            "points" => $ds['points'],
            "lifetime" => $ds['lifetime']
        );

        if (!empty($cat)) {
            $returnValue = (isset($returnValue[$cat])) ?
                $returnValue[$cat] : '';
        }

    }

    return $returnValue;

}

/* Map Pool */
function getMaps($pool_id) {

    if (!validate_int($pool_id)) {
        return array();
    }

    global $_database;

    $get = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    `maps`
                FROM `" . PREFIX . "cups_mappool`
                WHERE `mappoolID` = " . $pool_id
        )
    );

    return unserialize($get['maps']);

}
function getMapsAsOptions($pool_id, $selected_id = '', $addEmptyOption = FALSE) {

    if (!validate_int($pool_id)) {
        return '<option value="0">- / -</option>';
    }

    $mapArray = getMaps($pool_id);

    $returnValue = '';

    if ($addEmptyOption) {
        $returnValue .= '<option value="0">- / -</option>';
    }

    $anzMaps = count($mapArray);

    if ($anzMaps < 1) {
        return '<option value="0">- / -</option>';
    }

    for ($x = 0; $x < $anzMaps; $x++) {
        $map = $mapArray[$x];
        $returnValue .= '<option value="' . $map . '">' . $map . '</option>';
    }

    $returnValue = selectOptionByValue($returnValue, $selected_id);

    return $returnValue;

}
function getMappool($pool_id = 0, $cat = 'list') {
    global $_database, $_language;
    $_language->readModule('cups');

    $returnValue = '';
    $category = '';

    $query = mysqli_query(
        $_database,
        "SELECT 
            a.mappoolID AS pool_id, 
            a.name AS name, 
            a.maps AS maps,
            b.tag AS tag,
            b.name AS game
        FROM `".PREFIX."cups_mappool` a
        JOIN `".PREFIX."games` b ON a.gameID = b.gameID
        ORDER BY game ASC, name ASC"
    );
    if ($cat == 'list') {

        $returnValue .= '<option value="0">'.$_language->module['no_mappool2'].'</option>';

        while($get = mysqli_fetch_array($query)) {

            if(empty($category) || ($category != $get['tag'])) {
                $category = $get['tag'];
                if(!empty($category)) {
                    $returnValue .= '</optgroup>';
                }
                $returnValue .= '<optgroup label="'.$get['game'].'">';
            }

            $mapArray = unserialize($get['maps']);
            $anzMaps = count($mapArray);
            $returnValue .= '<option value="'.$get['pool_id'].'">'.$get['name'].' ('.$anzMaps.' Maps)</option>';

        }

        $returnValue .= '</optgroup>';

        $returnValue = selectOptionByValue($returnValue, $pool_id);

    }

    return $returnValue;

}

/**
 * Support
 */
function ticket($userID, $ticketID, $cat) {
    global $_database;

    $returnValue = FALSE;

    if (($cat == 'user') || ($cat == 'admin')) {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `userID`,
                        `adminID`
                    FROM `" . PREFIX . "cups_supporttickets`
                    WHERE `ticketID` = " . $ticketID
            )
        );
        $returnValue = ($userID == $get['userID']) ? TRUE : FALSE;
        if (!$returnValue) {
            $returnValue = ($userID == $get['adminID']) ? TRUE : FALSE;
        }

    }

    return $returnValue;

}
function getticket($ticketID, $cat) {
    global $_database;
    $returnValue = '';
    if ($cat == 'name') {
        $get = mysqli_fetch_array(
            mysqli_query($_database, "SELECT name FROM `".PREFIX."cups_supporttickets`  WHERE ticketID = '".$ticketID."'")
        );
        $returnValue = $get['name'];
    } else if ($cat == 'category') {
        $get = mysqli_fetch_array(
            mysqli_query($_database, "SELECT name_de FROM `".PREFIX."cups_supporttickets_category` WHERE categoryID = '".$ticketID."'")
        );
        $returnValue = $get['name_de'];
    } else if ($cat == 'admin') {
        $get = mysqli_fetch_array(
            mysqli_query($_database, "SELECT adminID FROM `".PREFIX."cups_supporttickets`  WHERE ticketID = '".$ticketID."'")
        );
        $returnValue = $get['adminID'];
    } else if ($cat == 'new_answer') {
        $return_value = 0;
        /*
        $anz = mysqli_num_rows(
            mysqli_query($_database, "SELECT ticketID FROM `".PREFIX."cups_supporttickets_content`  WHERE ticketID = '".$ticketID."' AND new = '1'")
        );
        if($anz) {
            $return_value++;
        }
        */
        $returnValue = $return_value;
    } else if ($cat == 'new_answer_admin') {
        $return_value = 0;
        /*
        $anz = mysqli_num_rows(
            mysqli_query($_database, "SELECT ticketID FROM `".PREFIX."cups_supporttickets_content`  WHERE ticketID = '".$ticketID."' AND new_admin = '1'")
        );
        if($anz) {
            $return_value++;
        }
        */
        $returnValue = $return_value;
    } else if ($cat == 'anz_admin_tickets') {
        $returnValue = mysqli_num_rows(
            mysqli_query($_database, "SELECT adminID FROM `".PREFIX."cups_supporttickets`  WHERE adminID = '".$ticketID."'")
        );
    } else if ($cat == 'status') {
        $returnValue = mysqli_num_rows(
            mysqli_query($_database, "SELECT ticketID FROM `".PREFIX."cups_supporttickets`  WHERE status = '".$ticketID."'")
        );
    }
    return $returnValue;
}
function getticket_anz($status, $user_id, $cat) {
    
	global $_database;

	$returnValue = 0;
    
    $whereClauseArray = array();
    
	if ($cat == 'anz_new_answer') {

	    $whereClause = '(userID = '.$user_id;

        $teamArray = getteam($user_id, 'teamID');
        if (validate_array($teamArray, true)) {

            $teamString = implode(', ', $teamArray);

            $whereClause .= ' OR teamID IN (' . $teamString . ')';
            $whereClause .= ' OR opponentID IN (' . $teamString . ')';

        }

        $whereClause .= ')';

        $whereClauseArray[] = $whereClause;
        
    } else if ($cat == 'anz_new_answer_admin') {
        
        if (validate_int($user_id, true)) {
            $whereClauseArray[] = '`adminID` = ' . $user_id;
        }
        
    } else {
        return -1;
    }
        
    if (validate_int($status, true)) {
        $whereClauseArray[] = '`status` = ' . $status;
    } else {
        $whereClauseArray[] = '`status` < 3';
    }

    if (!validate_array($whereClauseArray, true)) {
        return -1;
    }
    
    $whereClause = implode(' AND ', $whereClauseArray);
    
    $query = mysqli_query(
        $_database,
        "SELECT 
                `ticketID`, 
                `categoryID`, 
                `status` 
            FROM `" . PREFIX . "cups_supporttickets` 
            WHERE " . $whereClause
    );
    
    if (!$query) {
        return -1;
    }

    $baseWhereClauseArray = array();
    $baseWhereClauseArray[] = '(`ticket_seen_date` IS NULL OR `ticket_seen_date` < ' . time() . ')';
    
    while ($ds = mysqli_fetch_array($query)) {

        $ticket_id = $ds['ticketID'];
        $admin = ($cat == 'anz_new_answer_admin') ?
            1 : 0;
        
        $whereClauseArray = array();
        $whereClauseArray[] = '`ticket_id` = ' . $ticket_id;
        $whereClauseArray[] = '`primary_id` = ' . $user_id;
        $whereClauseArray[] = '`admin` = ' . $admin;
        
        $whereClause = implode(' AND ', $whereClauseArray);
        
        $selectQuery = mysqli_query(
            $_database,
            "SELECT 
                    `ticket_seen_date`
                FROM `" . PREFIX . "cups_supporttickets_status` 
                WHERE " . $whereClause
        );
        
        if (!$selectQuery) {
            return -1;
        }

        $status_counter = mysqli_num_rows($selectQuery);
        
        $statusGet = mysqli_fetch_array($selectQuery);

        /**
         * Es muss noch der letzte Ticket Content geholt werden (Datum) und verglichen werden anstelle des time()
         */
        
        if ($status_counter == 0) {
            insertTicketStatus($ticket_id, $user_id, $admin, -1);
            $returnValue++;
        } else {
            
            $whereClauseArray = array();
            $whereClauseArray[] = '`ticketID` = ' . $ticket_id;

            $whereClause = implode(' AND ', $whereClauseArray);

            $selectQuery = mysqli_query(
                $_database,
                "SELECT 
                        `date`
                    FROM `" . PREFIX . "cups_supporttickets_content` 
                    WHERE " . $whereClause . "
                    ORDER BY date DESC
                    LIMIT 0, 1"
            );

            if (!$selectQuery) {
                return -1;
            }

            $contentGet = mysqli_fetch_array($selectQuery);

            if (is_null($statusGet['ticket_seen_date']) || ($statusGet['ticket_seen_date'] < $contentGet['date'])) {
                $returnValue++;
            }
            
        }

    }

	return $returnValue;
    
}

function getticketcategories($selected = '', $addEmptyOption = TRUE) {

    global $_database;

    $categories = '';

    if ($addEmptyOption) {
       $categories .= '<option value="0" class="italic">-- / --</option>';
    }

    $info = mysqli_query(
        $_database,
        "SELECT categoryID, name_de FROM `".PREFIX."cups_supporttickets_category` 
        ORDER BY name_de ASC"
    );

    if (!$info) {
        return $categories;
    }

    if (mysqli_num_rows($info) < 1) {
        return $categories;
    }

    while($ds = mysqli_fetch_array($info)) {
        $categories .= '<option value="' . $ds['categoryID'] . '">' . $ds['name_de'] . '</option>';	
    }

    if (validate_int($selected, true)) {

        $categories = str_replace(
            'value="' . $selected . '"', 
            'value="' . $selected . '" selected="selected"', 
            $categories
        );

    }

    return $categories;

}
function getTicketAccess($ticket_id) {
    global $_database, $userID;

    $returnValue = FALSE;

    $teamArray = getteam($userID, 'teamID');

    $whereClause = '`userID` = '.$userID;

    if (validate_array($teamArray, true)) {

        $teamString = implode(', ', $teamArray);

        $whereClause .= ' OR `teamID` IN ('.$teamString.')';
        $whereClause .= ' OR `opponentID` IN ('.$teamString.')';

    }

    $query = mysqli_query(
        $_database,
        "SELECT 
            COUNT(*) AS `access` 
        FROM `ws_j12_cups_supporttickets`
        WHERE ticketID = ".$ticket_id." AND (".$whereClause.")"
    );

    $get = mysqli_fetch_array($query);
    $returnValue = $get['access'];

    return $returnValue;

}
function getTicketSeenDate($ticket_id, $primary_id, $admin = 0) {

    global $_database, $_language;

    $whereClauseArray = array();
    $whereClauseArray[] = '`ticket_id` = ' . $ticket_id;
    $whereClauseArray[] = '`primary_id` = ' . $primary_id;
    $whereClauseArray[] = '`admin` = ' . $admin;

    $whereClause = implode(' AND ', $whereClauseArray);

    $selectQuery = mysqli_query(
        $_database, 
        "SELECT
                COUNT(*) AS `exists`,
                `ticket_seen_date`
            FROM `" . PREFIX . "cups_supporttickets_status` 
            WHERE " . $whereClause
    );

    if (!$selectQuery) {
        throw new \Exception($_language->module['query_select_failed']);
    }

    $checkIf = mysqli_fetch_array($selectQuery);

    if ($checkIf['exists'] != 1) {
        return -1;
    } else {
        return $checkIf['ticket_seen_date'];
    }

}
function insertTicketStatus($ticket_id, $primary_id, $admin, $date = 1) {

    global $_database, $_language;

    if ($date == 1) {
        $ticket_seen_attribute = ', `ticket_seen_date`';
        $ticket_seen_date = ', ' . (time() + 20);
    } else if (validate_int($date, true) && ($date > 1)) {
        $ticket_seen_attribute = ', `ticket_seen_date`';
        $ticket_seen_date = ', ' . $date;
    } else {
        $ticket_seen_attribute = '';
        $ticket_seen_date = '';
    }

    $insertQuery = mysqli_query(
        $_database, 
        "INSERT INTO `" . PREFIX . "cups_supporttickets_status` 
            (
                `ticket_id`,
                `primary_id`,
                `admin`
                " . $ticket_seen_attribute . "
            ) 
            VALUES 
            (
                " . $ticket_id . ", 
                " . $primary_id . ", 
                " . $admin . "
                " . $ticket_seen_date . "
            )"
    );

    if (!$insertQuery) {
        throw new \Exception($_language->module['query_insert_failed']);
    }

}
function setTicketSeenDate($ticket_id, $primary_id, $admin = 0) {

    global $_database, $_language;

    $whereClauseArray = array();
    $whereClauseArray[] = '`ticket_id` = ' . $ticket_id;
    $whereClauseArray[] = '`primary_id` = ' . $primary_id;
    $whereClauseArray[] = '`admin` = ' . $admin;

    $whereClause = implode(' AND ', $whereClauseArray);

    $selectQuery = mysqli_query(
        $_database, 
        "SELECT
                COUNT(*) AS `exists`
            FROM `" . PREFIX . "cups_supporttickets_status` 
            WHERE " . $whereClause
    );

    if (!$selectQuery) {
        throw new \Exception($_language->module['query_select_failed']);
    }

    $checkIf = mysqli_fetch_array($selectQuery);

    if ($checkIf['exists'] != 1) {
        insertTicketStatus($ticket_id, $primary_id, $admin);
    }

    $updateQuery = mysqli_query(
        $_database, 
        "UPDATE `" . PREFIX . "cups_supporttickets_status` 
            SET `ticket_seen_date` = " . time() . "
            WHERE " . $whereClause
    );

    if (!$updateQuery) {
        throw new \Exception($_language->module['query_update_failed']);
    }

}

/* Awards */
function getawardcat($award_id, $cat = '') {
    global $_database;
    $get = mysqli_fetch_array(
        mysqli_query($_database, "SELECT * FROM `".PREFIX."cups_awards_category` WHERE awardID = '".$award_id."'")
    );
    $returnArray = array(
        'id'			=> $get['awardID'],
        'name'			=> $get['name'],
        'icon'			=> $get['icon'],
        'platzierung'	=> $get['platzierung'],
        'anz_matches'	=> $get['anz_matches'],
        'anz_cups'		=> $get['anz_cups'],
        'description'	=> $get['description']
    );
    if(!empty($cat) && isset($returnArray[$cat])) {
        return $returnArray[$cat];
    } else {
        return $returnArray;
    }
}

/* Cup Options */
function getCupOption($cat = '') {
    if($cat == 'size') {
        $returnValue = '';
        $returnValue .= '<option value="2">2</option>';
        $returnValue .= '<option value="4">4</option>';
        $returnValue .= '<option value="8">8</option>';
        $returnValue .= '<option value="16">16</option>';
        $returnValue .= '<option value="20" disabled="disabled">20</option>';
        $returnValue .= '<option value="30" disabled="disabled">30</option>';
        $returnValue .= '<option value="32">32</option>';
        $returnValue .= '<option value="64">64</option>	';
    } elseif($cat == 'penalty') {
        $returnValue = '';
        $returnValue .= '<option value="0">0</option>';
        $returnValue .= '<option value="6">6</option>';
        $returnValue .= '<option value="12">12</option>';
        $returnValue .= '<option value="18">18</option>';
        $returnValue .= '<option value="24">24</option>';
    } elseif($cat == 'mode') {
        $returnValue = '';
        $returnValue .= '<option value="1on1">1on1</option>';
        $returnValue .= '<option value="2on2">2on2</option>';
        $returnValue .= '<option value="3on3">3on3</option>';
        $returnValue .= '<option value="4on4">4on4</option>';
        $returnValue .= '<option value="5on5">5on5</option>';
        $returnValue .= '<option value="11on11">11on11</option>';
    } elseif($cat == 'csg_rounds') {
        $returnValue = '';
        $returnValue .= '<option value="18">18 (MR9)</option>';
        $returnValue .= '<option value="30" selected="selected">30 (MR15)</option>';
    } elseif($cat == 'csg_overtime') {
        $returnValue = '';
        $returnValue .= '<option value="10_16">10 (MR5) - 16.000$</option>';
        $returnValue .= '<option value="6_16">6 (MR3) - 16.000$</option>';
        $returnValue .= '<option value="6_10">6 (MR3) - 10.000$</option>';
    } else if($cat == 'registration') {
        $returnValue = '';
        $returnValue .= '<option value="open">Open</option>';
        $returnValue .= '<option value="invite">Invite</option>';
        $returnValue .= '<option value="closed">Closed</option>';
    } else if($cat == 'rounds') {
        $returnValue = '';
        $returnValue .= '<option value="bo1">Best-of-One (Bo1)</option>';
        $returnValue .= '<option value="bo3">Best-of-Three (Bo3)</option>';
        $returnValue .= '<option value="bo5">Best-of-Five (Bo5)</option>';
        $returnValue .= '<option value="bo7">Best-of-Seven (Bo7)</option>';
    } else if($cat == 'priority') {
        $returnValue = '';
        $returnValue .= '<option value="normal">normal</option>';
        $returnValue .= '<option value="main">Main</option>';
    } else if($cat == 'elimination') {
        $returnValue = '';
        $returnValue .= '<option value="single">Single-Elimination</option>';
        $returnValue .= '<option value="double" disabled="disabled">Double-elimination</option>';
        $returnValue .= '<option value="swiss" disabled="disabled">Swiss-Format</option>';
    } else {

        global $_language;
        $_language->readModule('cups');

        $returnValue = array(
            'size' 			=> getCupOption('size'),
            'penalty' 		=> getCupOption('penalty'),
            'mode' 			=> getCupOption('mode'),
            'csg_rounds' 	=> getCupOption('csg_rounds'),
            'csg_overtime' 	=> getCupOption('csg_overtime'),
            'registration' 	=> getCupOption('registration'),
            'priority' 		=> getCupOption('priority'),
            'elimination' 	=> getCupOption('elimination'),
            'rounds' 		=> getCupOption('rounds'),
            'true_false' 	=> '<option value="1">'.$_language->module['yes'].'</option><option value="0">'.$_language->module['no'].'</option>'
        );

    }
    return $returnValue;
}

/* STATISTICS */
function getStatistic($cat, $minValue, $maxValue, $anz = 5, $arrayAsReturn = TRUE) {

    global $_database;

    $returnArray = array();

    if ($cat == 'anz_cups') {

        $query = mysqli_query(
            $_database,
            "SELECT
                  COUNT(*) AS count_anz,
                  teamID AS team_id
                FROM `" . PREFIX . "cups_teilnehmer`
                WHERE checked_in = 1
                GROUP BY teamID
                HAVING (COUNT(*) < " . $maxValue . " AND COUNT(*) > " . $minValue . ")
                ORDER BY COUNT(*) DESC"
        );

        while($get = mysqli_fetch_array($query)) {

            $name = '';
            if($get['team_id'] > 0) {
                $name = getteam($get['team_id'], 'name');
            }

            $returnArray[] = array(
                'id'    => $get['team_id'],
                'name'  => $name,
                'count' => $get['count_anz']
            );

        }

    } else if($cat == 'anz_matches') {
        /**
         * WiP
         */
    } else if($cat == 'platzierung') {
        /**
         * WiP
         */
    }

    return $returnArray;

}

/* CONVERT */
function convert2gameaccount($game_tag, $value) {

    $replaceArray = array(
        'csg',
        'css',
        'cs'
    );

    $value = trim($value);

    if (in_array($game_tag, $replaceArray)) {
        $value = str_replace('steam_', '', strtolower($value));
    } else if (substr(strtolower($value), 0, 6) == 'steam_') {
        $value = str_replace('steam_', '', strtolower($value));
    }

    return $value;

}