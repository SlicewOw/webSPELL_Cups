<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    $cupArray = getcup($cup_id);

    if ($cupArray['status'] > 3) {
        throw new \Exception($_language->module['cup_already_closed']);
    }

    if ($cupArray['status'] != 3) {
        throw new \Exception($_language->module['cup_already_closed']);
    }

    if (!getmatch($cup_id, 'confirmed_final')) {
        // Finish cup if final is confirmed only
        throw new \Exception($_language->module['cup_wrong_status']);
    }

    $baseWhereClauseArray = array();
    $baseWhereClauseArray[] = '`cupID` = ' . $cup_id;
    $baseWhereClauseArray[] = '`wb` = 1';

    $rankArray = array();

    $index = 1;
    if ($cupArray['anz_runden'] == 3) {
        $rankArray[$index++] = '5-8';
    } else if ($cupArray['anz_runden'] == 4) {
        $rankArray[$index++] = '9-16';
        $rankArray[$index++] = '5-8';
    } else if ($cupArray['anz_runden'] == 5) {
        $rankArray[$index++] = '17-32';
        $rankArray[$index++] = '9-16';
        $rankArray[$index++] = '5-8';
    } else if ($cupArray['anz_runden'] == 6) {
        $rankArray[$index++] = '33-64';
        $rankArray[$index++] = '17-32';
        $rankArray[$index++] = '9-16';
        $rankArray[$index++] = '5-8';
    }

    if (count($rankArray) > 0) {

        for ($x = 1; $x < $cupArray['anz_runden']; $x++) {

            $whereClauseArray = $baseWhereClauseArray;
            $whereClauseArray[] = '`runde` = 1';

            $whereClause = implode(' AND ', $whereClauseArray);

            $get_platz = mysqli_query(
                $_database,
                "SELECT
                        `team1`,
                        `team1_freilos`,
                        `ergebnis1`,
                        `team2`,
                        `team2_freilos`,
                        `ergebnis2`
                    FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE " . $whereClause
            );

            if (!$get_platz) {
                throw new \Exception($_language->module['query_select_failed']);
            }

            while ($ds = mysqli_fetch_array($get_platz)) {

                if (($ds['team1_freilos'] == 0) && ($ds['team2_freilos'] == 0)) {

                    $loser_id = ($ds['ergebnis1'] < $ds['ergebnis2']) ?
                        $ds['team1'] : $ds['team2'];

                    if (isset($rankArray[$x])) {

                        $insertQuery = mysqli_query(
                            $_database,
                            "INSERT INTO `" . PREFIX . "cups_platzierungen`
                                (
                                    `cupID`,
                                    `teamID`,
                                    `platzierung`
                                )
                                VALUES
                                (
                                    " . $cup_id . ",
                                    " . $loser_id . ",
                                    '" . $rankArray[$x] . "'
                                )"
                        );

                        if (!$insertQuery) {
                            throw new \Exception($_language->module['query_insert_failed']);
                        }

                    }

                }

            }

        }

    }

    $whereClauseArray = $baseWhereClauseArray;

    // Platzierungen 1 & 2
    $whereClauseArray[] = '`runde` = ' . $cupArray['anz_runden'];

    $whereClause = implode(' AND ', $whereClauseArray);

    $ds = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    `team1`,
                    `team1_freilos`,
                    `ergebnis1`,
                    `team2`,
                    `team2_freilos`,
                    `ergebnis2`
                FROM `" . PREFIX . "cups_matches_playoff`
                WHERE " . $whereClause
        )
    );

    if (($ds['team1_freilos'] == 0) && ($ds['team2_freilos'] == 0)) {

        if ($ds['ergebnis1'] < $ds['ergebnis2']) {
            $winner_id = $ds['team2'];
            $loser_id = $ds['team1'];
        } else {
            $winner_id = $ds['team1'];
            $loser_id = $ds['team2'];
        }

        if(($winner_id > 0) && ($loser_id > 0)) {

            $query = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "cups_platzierungen`
                    (
                        `cupID`,
                        `teamID`,
                        `platzierung`
                    )
                    VALUES
                    (
                        " . $cup_id . ",
                        " . $winner_id . ",
                        '1'
                    ),
                    (
                        " . $cup_id . ",
                        " . $loser_id . ",
                        '2'
                    )"
            );

            if (!$query) {
                throw new \Exception($_language->module['query_insert_failed']);
            }

            // Awards
            $query = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "cups_awards`
                    (`teamID`, `cupID`, `award`, `date`)
                    VALUES
                    (" . $winner_id . ", " . $cup_id . ", 1, " . time() . "),
                    (" . $loser_id  .", " . $cup_id . ", 2, " . time() . ")"
            );

            if (!$query) {
                throw new \Exception($_language->module['query_insert_failed']);
            }

        }

    }

    /**
     * Platzierungen 3 & 4
     */

    $whereClauseArray = array();
    $whereClauseArray[] = '`cupID` = ' . $cup_id;
    $whereClauseArray[] = '`wb` = 0';
    $whereClauseArray[] = '`runde` = ' . $cupArray['anz_runden'];

    $whereClause = implode(' AND ', $whereClauseArray);

    $ds = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    `team1`,
                    `team1_freilos`,
                    `ergebnis1`,
                    `team2`,
                    `team2_freilos`,
                    `ergebnis2`
                FROM `" . PREFIX . "cups_matches_playoff`
                WHERE " . $whereClause
        )
    );

    if (($ds['team1_freilos'] == 0) && ($ds['team2_freilos'] == 0)) {

        if ($ds['ergebnis1'] < $ds['ergebnis2']) {
            $winner_id = $ds['team2'];
            $loser_id = $ds['team1'];
        } else {
            $winner_id = $ds['team1'];
            $loser_id = $ds['team2'];
        }

        if(($winner_id > 0) && ($loser_id > 0)) {

            $query = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "cups_platzierungen`
                    (
                        `cupID`,
                        `teamID`,
                        `platzierung`
                    )
                    VALUES
                    (
                        " . $cup_id. ",
                        " . $winner_id . ",
                        '3'
                    ),
                    (
                        " . $cup_id . ",
                        " . $loser_id . ",
                        '4'
                    )"
            );

            if (!$query) {
                throw new \Exception($_language->module['query_insert_failed']);
            }

            // Awards
            $query = mysqli_query(
                $_database, 
                "INSERT INTO `" . PREFIX . "cups_awards`
                    (`teamID`, `cupID`, `award`, `date`)
                    VALUES 
                    (" . $winner_id . ", " . $cup_id . ", 3, " . time() . ")"
            );

            if (!$query) {
                throw new \Exception($_language->module['query_insert_failed']);
            }

        }

    }

    $saveQuery = mysqli_query(
        $_database,
        "UPDATE `" . PREFIX . "cups`
            SET `status` = 4
            WHERE `cupID` = " . $cup_id
    );

    if (!$saveQuery) {
        throw new \Exception($_language->module['error_update_query_failed']);
    }

    header('Location: admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id);

} catch (Exception $e) {
    echo showError($e->getMessage());
}