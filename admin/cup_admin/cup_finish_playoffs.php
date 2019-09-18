<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \UnexpectedValueException($_language->module['unknown_cup_id']);
    }

    $cupArray = getcup($cup_id);

    if (($cupArray['status'] > 3) || ($cupArray['status'] != 3)) {
        throw new \UnexpectedValueException($_language->module['cup_already_closed']);
    }

    if (!isChallongeCup($cup_id)) {

        if (!getmatch($cup_id, 'confirmed_final')) {
            // Finish cup if final is confirmed only
            throw new \UnexpectedValueException($_language->module['cup_wrong_status']);
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

                $get_platz = safe_query(
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

                while ($ds = mysqli_fetch_array($get_platz)) {

                    if (!(($ds['team1_freilos'] == 0) && ($ds['team2_freilos'] == 0))) {
                        continue;
                    }

                    $loser_id = ($ds['ergebnis1'] < $ds['ergebnis2']) ?
                        $ds['team1'] : $ds['team2'];

                    if (!isset($rankArray[$x])) {
                        continue;
                    }

                    setCupPlacement($cup_id, $loser_id, $rankArray[$x]);

                }

            }

        }

        $whereClauseArray = $baseWhereClauseArray;

        // Platzierungen 1 & 2
        $whereClauseArray[] = '`runde` = ' . $cupArray['anz_runden'];

        $whereClause = implode(' AND ', $whereClauseArray);

        $ds = mysqli_fetch_array(
            safe_query(
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

            if ($winner_id > 0) {
                setCupPlacement($cup_id, $winner_id, '1');
                setCupAward($cup_id, $winner_id, 1);
            }

            if ($loser_id > 0) {
                setCupPlacement($cup_id, $loser_id, '2');
                setCupAward($cup_id, $loser_id, 2);
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
            safe_query(
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

            if ($winner_id > 0) {
                setCupPlacement($cup_id, $winner_id, '3');
                setCupAward($cup_id, $winner_id, 3);
            }

            if ($loser_id > 0) {
                setCupPlacement($cup_id, $loser_id, '4');
            }

        }

    }

    safe_query(
        "UPDATE `" . PREFIX . "cups`
            SET `status` = 4
            WHERE `cupID` = " . $cup_id
    );

    header('Location: admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id);

} catch (Exception $e) {
    echo showError($e->getMessage());
}