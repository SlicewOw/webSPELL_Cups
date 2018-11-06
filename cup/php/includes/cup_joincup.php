<?php

try {

    $_language->readModule('cups');

    if (!$loggedin) {
        throw new \Exception($_language->module['login']);
    }

    $varPage = (mb_substr(basename($_SERVER[ 'REQUEST_URI' ]), 0, 15) != "admincenter.php") ?
        'admin' : 'cup';

    $actionArray = array(
        'joincup',
        'teamadd'
    );

    if (!in_array($getAction, $actionArray)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['wrong_cup_id']);
    }

    $cupArray = getcup($cup_id);

    if (!validate_array($cupArray)) {
        throw new \Exception($_language->module['no_cup']);
    }

    if (!isset($cupArray['id']) || ($cupArray['id'] != $cup_id)) {
        throw new \Exception($_language->module['no_cup']);
    }

    $cupname 	= $cupArray['name'];
    $checkin 	= getformatdatetime($cupArray['checkin']);
    $start 		= getformatdatetime($cupArray['start']);
    $maxSize 	= $cupArray['size'];
    $maxMode 	= $cupArray['max_mode'];
    $mode 		= $cupArray['mode'];

    if (validate_array($_POST, true)) {

        if ($varPage == 'admin') {
            $parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id . '&page=teams';
        } else {
            $parent_url = 'index.php?site=cup&action=details&id=' . $cup_id;	
        }

        try {

            if (isset($_POST['submitCupJoin'])) {

                if ($maxMode == 1) {
                    $teamID = $userID;
                } else {
                    $teamID = $_POST['team'];
                }

                if (!cup($cup_id, $teamID, 'join')) {
                    // Team/Spieler bereits fuer den Cup angemeldet
                    throw new \Exception($_language->module['cup_join_error1']);
                }

                $anzMember = getteam($teamID, 'anz_member');
                if ($mode == '1on1')	{
                    // Benutzer ist eingeloggt und nimmt an Cup teil
                    $mode_ok = TRUE;
                } else if (($mode == '2on2') && ($anzMember == '2')) {
                    // Team besteht aus genau 2 Spielern
                    $mode_ok = TRUE;
                } else if (($mode == '5on5') && ($anzMember >= '5')) {
                    // Team besteht aus 5 oder mehr Spielern
                    $mode_ok = TRUE;
                } else if (($mode == '11on11') && ($anzMember >= '5')) {
                    // Team besteht aus 5 oder mehr Spielern
                    $mode_ok = TRUE;
                } else {
                    // Team hat nicht genuegend Spieler
                    $mode_ok = FALSE;
                }

                if (!$mode_ok) {

                    // Team Mitglieder Anzahl nicht korrekt
                    if ($mode == '2on2') {
                        throw new \Exception($_language->module['cup_join_error2_2on2']);
                    } else if ($mode == '5on5') {
                        throw new \Exception($_language->module['cup_join_error2_5on5']);
                    } else {
                        throw new \Exception($_language->module['cup_join_error2']);
                    }

                }

                if (!cup($cup_id, $teamID, 'gameaccount')) {
                    // fehlende Gameaccounts
                    throw new \Exception($_language->module['cup_join_error3']);
                }

                $saveQuery = mysqli_query(
                    $_database,
                    "INSERT INTO `" . PREFIX . "cups_teilnehmer` 
                        (
                            cupID, 
                            teamID,
                            date_register
                        ) 
                        VALUES 
                        (
                            " . $cup_id . ", 
                            " . $teamID . ",
                            " . time() . "
                        )"
                );

                if (!$saveQuery) {
                    throw new \Exception($_language->module['query_insert_failed']);
                }

                $status = 0;
                if ($mode == '1on1') {

                    //
                    // User Log
                    setPlayerLog($teamID, $cup_id, 'cup_join_' . $cup_id);

                    $_SESSION['successArray'][] = $_language->module['cup_register_ok_player'];

                } else {

                    $teamname = getteam($teamID, 'name');

                    //
                    // Team Log
                    setCupTeamLog($teamID, $teamname, 'cup_join_' . $cup_id);

                    $query = mysqli_query(
                        $_database,
                        "SELECT userID FROM `" . PREFIX . "cups_teams_member`
                            WHERE teamID = " . $teamID . " AND active = 1"
                    );

                    if (!$query) {
                        throw new \Exception($_language->module['query_select_failed']);
                    }

                    while ($get = mysqli_fetch_array($query)) {

                        setNotification(
                            $get['userID'], 
                            'index.php?site=cup&amp;action=details&amp;id=' . $cup_id, 
                            $cup_id, 
                            $text
                        );

                    }

                    $_SESSION['successArray'][] = $_language->module['cup_register_ok_team'];

                }

            }

        } catch (Exception $e) {

            $_SESSION['cupErrorArray'][] = $e->getMessage();

            if ($varPage == 'admin') {
                $parent_url = 'admincenter.php?site=cup&mod=cup&action=teamadd&id=' . $cup_id;
            } else {
                $parent_url = 'index.php?site=cup&action=joincup&id=' . $cup_id;
            }

        }

        header('Location: ' . $parent_url);

    } else {

        $status = 1;
        $error = '';

        if (isset($_SESSION['cupErrorArray']) && validate_array($_SESSION['cupErrorArray'], true)) {

            $error = showError(implode('<br />', $_SESSION['cupErrorArray']));
            unset($_SESSION['cupErrorArray']);

        }

        if ($getAction == 'joincup') {

            //
            // join cup
            // (user)

            $minMember = $maxMode;

            $teams = '';
            if ($maxMode == 1) {

                $teams .= '<option value="' . $userID . '">' . getnickname($userID) . '</option>';

            } else {

                $info = mysqli_query(
                    $_database,
                    "SELECT teamID, name FROM ".PREFIX."cups_teams
                        WHERE userID = " . $userID . " AND deleted = 0"
                );
                while($db = mysqli_fetch_array($info)) {

                    if(getteam($db['teamID'], 'anz_member') >= $minMember) {
                        $teams .= '<option value="'.$db['teamID'].'">'.$db['name'].'</option>';
                    }

                }

            }

        } else if ($getAction == 'teamadd') {

            //
            // add team
            // (admin)

            $teams = '';

            if ($maxMode == 1) {

                //
                // Userlist (1on1)

                $query = mysqli_query(
                    $_database,
                    "SELECT 
                            a.`userID` AS `user_id`,
                            a.`gameaccID` AS `gameaccount_id`,
                            a.`value` AS `gameaccount_value`,
                            b.`nickname` AS `nickname`
                        FROM `" . PREFIX . "cups_gameaccounts` a
                        JOIN `" . PREFIX . "user` b ON a.`userID` = b.`userID`
                        WHERE a.`category` = '" . $cupArray['game'] . "' AND a.`active` = 1 AND a.`deleted` = 0
                        ORDER BY b.`nickname` ASC"
                );

                if (!$query) {
                    throw new \Exception($_language->module['query_select_failed']);
                }

                while ($db = mysqli_fetch_array($query)) {

                    $accountInfo = '';
                    $accountInfo .= $db['nickname'];
                    $accountInfo .= ' (#'.$db['gameaccount_id'].' / '.$db['gameaccount_value'].')';

                    $teams .= '<option value="' . $db['user_id'] . '">' . $accountInfo . '</option>';

                }

            } else {

                //
                // Team List
                $info = mysqli_query(
                    $_database,
                    "SELECT
                            `teamID`,
                            `name`
                        FROM `" . PREFIX . "cups_teams`
                        WHERE `deleted` = 0
                        ORDER BY `name` ASC"
                );

                if (!$info) {
                    throw new \Exception($_language->module['query_select_failed']);
                }

                while ($db = mysqli_fetch_array($info)) {

                    if (($maxMode == 2) && (getteam($db['teamID'], 'anz_member') == $maxMode)) {

                        $teams .= '<option value="'.$db['teamID'].'">'.$db['name'].'</option>';

                    } else if (($maxMode != 2) && getteam($db['teamID'], 'anz_member') >= $maxMode) {

                        $teams .= '<option value="'.$db['teamID'].'">'.$db['name'].'</option>';

                    }

                }

            }

        } else {
            $teams = '<option value="0">Error</option>';
        }

        $submitButtonStatus = '';
        if (empty($teams)) {

            $submitButtonStatus = ' disabled="disabled"';

            if($maxMode == 1) {
                $teams = '<option value="0">- kein Spieler vorhanden -</option>';
            } else {
                $teams = '<option value="0">- kein vollständiges Team vorhanden -</option>';
            }

        }

        $data_array = array();
        $data_array['$error'] = $error;
        $data_array['$cupID'] = $cup_id;
        $data_array['$cupname'] = $cupname;
        $data_array['$checkin'] = $checkin;
        $data_array['$start'] = $start;
        $data_array['$showTeams'] = ($maxMode == 1) ? ' style="display: none;"' : '';
        $data_array['$teams'] = $teams;
        $data_array['$submitButtonStatus'] = $submitButtonStatus;
        $cups_join = $GLOBALS["_template_cup"]->replaceTemplate("cups_join", $data_array);
        echo $cups_join;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
