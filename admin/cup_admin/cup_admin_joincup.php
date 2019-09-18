<?php

try {

    if (!$loggedin) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    $actionArray = array(
        'playeradd',
        'teamadd'
    );

    if (!in_array($getAction, $actionArray)) {
        throw new \UnexpectedValueException($_language->module['unknown_action']);
    }

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \UnexpectedValueException($_language->module['unknown_cup_id']);
    }

    $cupArray = getcup($cup_id);

    if (!validate_array($cupArray)) {
        throw new \UnexpectedValueException($_language->module['unknown_cup_id']);
    }

    if (!isset($cupArray['id']) || ($cupArray['id'] != $cup_id)) {
        throw new \UnexpectedValueException($_language->module['no_cup']);
    }

    $cupname = $cupArray['name'];
    $checkin = getformatdatetime($cupArray[getConstNameCheckIn()]);
    $start = getformatdatetime($cupArray[getConstNameStart()]);
    $maxSize = $cupArray['size'];
    $maxMode = $cupArray['max_mode'];
    $mode = $cupArray['mode'];

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id . '&page=teams';

        try {

            if (isset($_POST['submitCupJoin'])) {

                $team_id = $_POST['team'];

                if (!cup($cup_id, $team_id, 'join')) {
                    // Team/Spieler bereits fuer den Cup angemeldet
                    throw new \UnexpectedValueException($_language->module['cup_join_error1']);
                }

                $anzMember = getteam($team_id, 'anz_member');
                if ($mode == '1on1') {
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
                        throw new \UnexpectedValueException($_language->module['cup_join_error2_2on2']);
                    } else if ($mode == '5on5') {
                        throw new \UnexpectedValueException($_language->module['cup_join_error2_5on5']);
                    } else {
                        throw new \UnexpectedValueException($_language->module['cup_join_error2']);
                    }

                }

                if (!cup($cup_id, $team_id, 'gameaccount')) {
                    // fehlende Gameaccounts
                    throw new \UnexpectedValueException($_language->module['cup_join_error3']);
                }

                $saveQuery = cup_query(
                    "INSERT INTO `" . PREFIX . "cups_teilnehmer`
                        (
                            `cupID`,
                            `teamID`,
                            `date_register`
                        )
                        VALUES
                        (
                            " . $cup_id . ",
                            " . $team_id . ",
                            " . time() . "
                        )",
                    __FILE__
                );

                $status = 0;
                if ($mode == '1on1') {

                    //
                    // User Log
                    setPlayerLog($team_id, $cup_id, 'cup_join_' . $cup_id);

                    $_SESSION['successArray'][] = $_language->module['cup_register_ok_player'];

                } else {

                    $teamname = getteam($team_id, 'name');

                    //
                    // Team Log
                    setCupTeamLog($team_id, $teamname, 'cup_join_' . $cup_id);

                    $query = cup_query(
                        "SELECT
                                `userID`
                            FROM `" . PREFIX . "cups_teams_member`
                            WHERE `teamID` = " . $team_id . " AND `active` = 1",
                        __FILE__
                    );

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

            $parent_url = 'admincenter.php?site=cup&mod=cup&action=teamadd&id=' . $cup_id;

        }

        header('Location: ' . $parent_url);

    } else {

        $status = 1;
        $error = '';

        if (isset($_SESSION['cupErrorArray']) && validate_array($_SESSION['cupErrorArray'], true)) {

            $error = showError(implode('<br />', $_SESSION['cupErrorArray']));
            unset($_SESSION['cupErrorArray']);

        }

        if ($getAction == 'teamadd' || $getAction == 'playeradd') {

            $registeredParticipantArray = array();

            $selectParticipantsQuery = cup_query(
                "SELECT
                        `teamID`
                    FROM `" . PREFIX . "cups_teilnehmer`
                    WHERE `cupID` = " . $cup_id,
                __FILE__
            );

            while ($get = mysqli_fetch_array($selectParticipantsQuery)) {
                $registeredParticipantArray[] = $get[getConstNameTeamId()];
            }

            $teams = '';

            if ($maxMode == 1) {

                //
                // Userlist (1on1)

                $whereClauseArray = array();
                $whereClauseArray[] = 'a.`category` = \'' . $cupArray['game'] . '\'';
                $whereClauseArray[] = 'a.`active` = 1';
                $whereClauseArray[] = 'a.`deleted` = 0';

                if (validate_array($registeredParticipantArray, true)) {
                    $whereClauseArray[] = 'b.`userID` NOT IN (' . implode(', ', $registeredParticipantArray) . ')';
                }

                $whereClause = implode(' AND ', $whereClauseArray);

                $query = cup_query(
                    "SELECT
                            a.`userID` AS `user_id`,
                            a.`gameaccID` AS `gameaccount_id`,
                            a.`value` AS `gameaccount_value`,
                            b.`nickname` AS `nickname`
                        FROM `" . PREFIX . "cups_gameaccounts` a
                        JOIN `" . PREFIX . "user` b ON a.`userID` = b.`userID`
                        WHERE " .$whereClause . "
                        ORDER BY b.`nickname` ASC",
                    __FILE__
                );

                while ($db = mysqli_fetch_array($query)) {

                    $accountInfo = '';
                    $accountInfo .= $db['nickname'];
                    $accountInfo .= ' (#' . $db['gameaccount_id'] . ' / ' . $db['gameaccount_value'] . ')';

                    $teams .= '<option value="' . $db['user_id'] . '">' . $accountInfo . '</option>';

                }

            } else {

                $whereClauseArray = array();
                $whereClauseArray[] = '`deleted` = 0';

                if (validate_array($registeredParticipantArray, true)) {
                    $whereClauseArray[] = '`teamID` NOT IN (' . implode(', ', $registeredParticipantArray) . ')';
                }

                $whereClause = implode(' AND ', $whereClauseArray);

                //
                // Team List
                $info = cup_query(
                    "SELECT
                            `teamID`,
                            `name`
                        FROM `" . PREFIX . "cups_teams`
                        WHERE " . $whereClause . "
                        ORDER BY `name` ASC",
                    __FILE__
                );

                while ($db = mysqli_fetch_array($info)) {

                    if (($maxMode == 2) && (getteam($db[getConstNameTeamId()], 'anz_member') == $maxMode)) {

                        $teams .= '<option value="'.$db[getConstNameTeamId()].'">'.$db['name'].'</option>';

                    } else if (($maxMode != 2) && getteam($db[getConstNameTeamId()], 'anz_member') >= $maxMode) {

                        $teams .= '<option value="'.$db[getConstNameTeamId()].'">'.$db['name'].'</option>';

                    }

                }

            }

        } else {
            $teams = '<option value="0">Error</option>';
        }

        $submitButtonStatus = '';
        if (empty($teams)) {

            $submitButtonStatus = ' disabled="disabled"';

            if ($maxMode == 1) {
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
        $data_array['$teams'] = $teams;
        $data_array['$submitButtonStatus'] = $submitButtonStatus;
        $cups_join = $GLOBALS["_template_cup"]->replaceTemplate("cup_admin_joincup", $data_array);
        echo $cups_join;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
