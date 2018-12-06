<?php

try {

    if (!$loggedin) {
        throw new \Exception($_language->module['login']);
    }

    $result_checkin = FALSE;

    $error = array();

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['wrong_cup_id']);
    }

    if (!checkIfContentExists($cup_id, 'cupID', 'cups')) {
        throw new \Exception($_language->module['wrong_cup_id']);
    }

    //
    // Cup Array
    $cupArray = getcup($cup_id);

    if (!validate_array($cupArray)) {
        throw new \Exception($_language->module['no_cup']);
    }

    if (!isset($cupArray['id']) || ($cupArray['id'] != $cup_id)) {
        throw new \Exception($_language->module['no_cup']);
    }

	if (!isset($cupArray['status']) || ($cupArray['status'] > 1)) {
        throw new \Exception($_language->module['status_running']);
    }

    if ($cupArray['mode'] == '1on1') {

        $checkIf = mysqli_fetch_array(
            mysqli_query(
            $_database, 
                "SELECT 
                      COUNT(*) AS `exist`, 
                      checked_in AS `checked_in` 
                    FROM `ws_j12_cups_teilnehmer`
                    WHERE `cupID` = " . $cup_id . " AND `teamID` = " . $userID
            )
        );

        if ($checkIf['exist'] && $checkIf['checked_in']) {
            throw new \Exception($_language->module['player_already_checked_in']);
        } else if ($checkIf['exist'] == 0) {
            throw new \Exception($_language->module['player_not_registered']);
        }

        $player_pps = getUserPenalty($userID);
        if ($player_pps >= $cupArray['max_pps']) {
            // zu viele Strafpunkte
            throw new \Exception($_language->module['error_too_much_pps']);
        }

        $team_anz = $cupArray['teams']['checked_in'];
        if ($team_anz > $cupArray['max_size']) {
            // kein Platz mehr frei
            throw new \Exception($_language->module['error_cup_full']);
        }

        $updateQuery = mysqli_query(
            $_database, 
            "UPDATE `" . PREFIX . "cups_teilnehmer` 
                SET `checked_in` = 1,
                    `date_checkin` = " . time() . "
                WHERE `cupID` = " . $cup_id . " AND `teamID` = " . $userID
        );

        if (!$updateQuery) {
            throw new \Exception($_language->module['query_update_failed']);
        }

        //
        // User Log
        setPlayerLog($userID, $cup_id, 'cup_join_' . $cup_id);

        $_SESSION['successArray'][] = $_language->module['cup_register_ok_player'];

        $result_checkin = TRUE;

    } else {

        if (!isinteam($userID, 0, 0)) {
            throw new \Exception($_language->module['cup_checkin_failure']);
        }

        $checkIf = mysqli_fetch_array(
            mysqli_query(
            $_database,
                "SELECT
                      COUNT(*) AS `exist`,
                      a.`checked_in` AS `checked_in`
                    FROM `" . PREFIX . "cups_teilnehmer` a
                    JOIN `" . PREFIX . "cups_teams_member` b ON a.`teamID` = b.`teamID`
                    WHERE a.`cupID` = " . $cup_id . " AND b.`userID` = " . $userID . " AND b.`active` = 1"
            )
        );

        if ($checkIf['exist'] && $checkIf['checked_in']) {
            throw new \Exception($_language->module['team_already_checked_in']);
        } else if ($checkIf['exist'] == 0) {
            throw new \Exception($_language->module['team_not_registered']);
        }

        $get_id = mysqli_query(
            $_database, 
            "SELECT teamID FROM `" . PREFIX . "cups_teilnehmer` 
                WHERE cupID = " . $cup_id . " AND checked_in = 0"
        );
        while ($te = mysqli_fetch_array($get_id)) {

            $query = mysqli_query(
                $_database, 
                "SELECT teamID FROM ".PREFIX."cups_teams_member 
                    WHERE userID = " . $userID . " AND teamID = " . $te['teamID'] . " AND active = 1"
            );
            $anz = mysqli_num_rows($query);
            if ($anz == 1) {

                $get_team = mysqli_fetch_array($query);

                $teamID = $get_team['teamID'];

                $mode = $cupArray['max_mode'];
                if (($mode == '2') && (getteam($teamID, 'anz_member') == '2')) {
                    // Team besteht aus genau 2 Spielern
                    $mode_ok = TRUE;
                } elseif (($mode == '5') && (getteam($teamID, 'anz_member') >= '5')) {
                    // Team besteht aus 5 oder mehr Spielern
                    $mode_ok = TRUE;
                } else {
                    // Team hat nicht genuegend Spieler
                    throw new \Exception($_language->module['error_team_player_count']);
                }

                $team_pps = getteam($teamID, 'anz_pps');
                if ($team_pps >= $cupArray['max_pps']) {
                    // zu viele Strafpunkte
                    throw new \Exception($_language->module['error_too_much_pps']);
                }

                $team_anz = $cupArray['teams']['checked_in'];
                if ($team_anz > $cupArray['size']) {
                    // kein Platz mehr frei
                    throw new \Exception($_language->module['error_cup_full']);
                }

                $saveQuery = mysqli_query(
                    $_database, 
                    "UPDATE `" . PREFIX . "cups_teilnehmer` 
                        SET `checked_in` = 1,
                            `date_checkin` = " . time() . "
                        WHERE `cupID` = " . $cup_id . " AND `teamID` = " . $teamID
                );

                if (!$saveQuery) {
                    throw new \Exception($_language->module['query_update_failed']);
                }

                $teamname = getteam($teamID, 'name');

                //
                // Team Log
                setCupTeamLog($teamID, $teamname, 'cup_checkin_'.$cup_id);

                $result_checkin = TRUE;

            }

        }

    }

    if (!$result_checkin) {
        echo showError($_language->module['cup_checkin_failure']);
    } else {
        header('Location: index.php?site=cup&action=details&id=' . $cup_id);
    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
