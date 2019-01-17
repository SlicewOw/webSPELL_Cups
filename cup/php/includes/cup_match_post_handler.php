<?php

if (validate_array($_POST, true)) {

    $parent_url = 'index.php?site=cup&action=match&id=' . $cup_id . '&mID=' . $match_id;

    try {

        //
        // Submits
        if (!($teamAdminAccess || $cupAdminAccess)) {
            throw new \Exception($_language->module['access_denied']);
        }

        //
        // Map-Veto
        if ($cupAdminAccess && isset($_POST['submitMatchReset'])) {

            //
            // Map Array
            $mapsArray = unserialize($matchArray['maps']);

            if (isset($mapsArray['list'])) {
                $mapsArray['open'] = $mapsArray['list'];
            } else {
                $mapsArray['open'] = array();
            }
            $mapsArray['banned']['team1'] = array();
            $mapsArray['banned']['team2'] = array();
            $mapsArray['picked'] = array();

            $insertValueArray = array();
            $insertValueArray[] = '`mapvote` = 0';
            $insertValueArray[] = '`maps` = \'' . serialize($mapsArray) . '\'';
            $insertValueArray[] = '`ergebnis1` = 0';
            $insertValueArray[] = '`team1_confirmed` = 0';
            $insertValueArray[] = '`ergebnis2` = 0';
            $insertValueArray[] = '`team2_confirmed` = 0';
            $insertValueArray[] = '`admin_confirmed` = 0';

            if ($cupArray['bot']) {
                $insertValueArray[] = '`server` = \'\'';
            }

            $insertValue = implode(', ', $insertValueArray);

            $query = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "cups_matches_playoff`
                    SET " . $insertValue . "
                    WHERE `matchID` = " . $match_id
            );

            if (!$query) {
                throw new \Exception('cups_matches_playoff_query_update_failed');
            }

        } else if ($cupAdminAccess && isset($_POST['submitMapvoteReset'])) {

            //
            // Map Array
            $mapsArray = unserialize($matchArray['maps']);

            $mapsArray['open'] = $mapsArray['list'];
            $mapsArray['banned']['team1'] = array();
            $mapsArray['banned']['team2'] = array();
            $mapsArray['picked'] = array();

            //
            // Map Array speichern
            $maps = serialize($mapsArray);
            $query = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "cups_matches_playoff`
                    SET `mapvote` = 0,
                        `maps` = '" . $maps . "'
                    WHERE `matchID` = " . $match_id
            );

            if (!$query) {
                throw new \Exception('cups_matches_playoff_query_update_failed');
            }

        } else if (isset($_POST['submitMatchScore']) || isset($_POST['submitAdminWinner'])) {

            $match_id = (isset($_POST['match_id']) && validate_int($_POST['match_id'], true)) ?
                (int)$_POST['match_id'] : 0;

            if ($match_id < 1) {
                throw new \Exception($_language->module['no_match']);
            }

            $team1_score = (isset($_POST['team1_score']) && validate_int($_POST['team1_score'], true)) ?
                (int)$_POST['team1_score'] : 0;

            $team2_score = (isset($_POST['team2_score']) && validate_int($_POST['team2_score'], true)) ?
                (int)$_POST['team2_score'] : 0;

            if (isset($_POST['team1_defwin'])) {
                $team1_score = 1;
                $team2_score = 0;
            } else if (isset($_POST['team2_defwin'])) {
                $team1_score = 0;
                $team2_score = 1;
            }

            //
            // correctScore:
            // 1: kein Unentschieden
            // 2: kein 0:0
            if ((($team1_score > 0) || ($team2_score > 0)) && ($team1_score != $team2_score)) {

                $correctScore = TRUE;

                if ($team1_score > $team2_score) {
                    $winner_id = $matchArray['team1_id'];
                    $loser_id = $matchArray['team2_id'];
                } else {
                    $winner_id = $matchArray['team2_id'];
                    $loser_id = $matchArray['team1_id'];
                }

            } else {
                $correctScore = FALSE;
            }

            if ($correctScore) {

                //
                // Score Check
                for ($x = 1; $x < 3; $x++) {

                    $teamCheckValue[$x] = FALSE;

                    $teamScore = (int)$_POST['team'.$x.'_score'];

                    //
                    // Match confirmed by Team $x?
                    if ($teamScore == $matchArray['ergebnis'.$x]) {

                        //
                        // Match confirmed by Team $x?
                        if (isset($matchArray['team'.$x.'_confirm']) && $matchArray['team'.$x.'_confirm']) {

                            $teamCheckValue[$x] = TRUE;

                        }

                    }

                }

                $matchConfirm = (($_POST['team'] === 'admin') || ($teamCheckValue[1] && $teamCheckValue[2])) ?
                    TRUE : FALSE;

                //
                // Confirmed by?
                // 1: team1_confirmed
                // 2: team2_confirmed
                // 3: admin_confirmed
                $ergebnisCheck = $_POST['team'] . '_confirmed';

                $setValueArray = array();
                $setValueArray[] = '`ergebnis1` = ' . $team1_score;
                $setValueArray[] = '`ergebnis2` = ' . $team2_score;
                $setValueArray[] = '`' . $ergebnisCheck . '` = 1';

                $team1_checkValue = ($team1_score == $matchArray['ergebnis1']) ?
                    TRUE : FALSE;

                $team2_checkValue = ($team2_score == $matchArray['ergebnis2']) ?
                    TRUE : FALSE;

                if (!$team1_checkValue || !$team2_checkValue) {

                    if ($_POST['team'] == 'team1') {
                        $setValueArray[] = '`team2_confirmed` = 0';
                    } else {
                        $setValueArray[] = '`team1_confirmed` = 0';
                    }

                }

                if ($ergebnisCheck == 'admin_confirmed') {
                    $setValueArray[] = '`mapvote` = 1';
                }

                $setValues = implode(', ', $setValueArray);

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_matches_playoff`
                        SET " . $setValues . "
                        WHERE `matchID` = " . $match_id
                );

                if (!$query) {
                    throw new \Exception('cups_matches_playoff_query_update_failed (' . $setValues . ')');
                }

                if ($matchConfirm) {

                    //
                    // Upper or lower Seeding?
                    $nextTeam = ($matchArray['spiel']%2) ? 'team1' : 'team2';

                    //
                    // Spielnummer
                    $nextGame = floor(($matchArray['spiel'] / 2) + 0.5);

                    $whereClauseBaseArray = array();
                    $whereClauseBaseArray[] = '`cupID` = ' . $cup_id;
                    $whereClauseBaseArray[] = '`runde` = ' . ($matchArray['runde'] + 1);
                    $whereClauseBaseArray[] = '`spiel` = ' . $nextGame;

                    //
                    // WhereClause Winner Match
                    $winnerBracketWhereClauseArray = $whereClauseBaseArray;
                    $winnerBracketWhereClauseArray[] = '`wb` = 1';

                    $winnerBracketWhereClause = implode(' AND ', $winnerBracketWhereClauseArray);

                    $setValueArray = array();
                    $setValueArray[] = '`active` = 1';
                    $setValueArray[] = '`' . $nextTeam . '` = ' . $winner_id;

                    $setValue = implode(', ', $setValueArray);

                    //
                    // Winner Match
                    $query = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "cups_matches_playoff`
                            SET " . $setValue . "
                            WHERE " . $winnerBracketWhereClause
                    );

                    if (!$query) {
                        throw new \Exception('cups_matches_playoff_query_update_failed (' . $setValue . ' / ' . $winnerBracketWhereClause . ')');
                    }

                    //
                    // Spiel um Platz 3
                    if ($matchArray['runde'] == ($cupArray['anz_runden'] - 1)) {

                        //
                        // WhereClause Winner Match
                        $loserBracketWhereClauseArray = $whereClauseBaseArray;
                        $loserBracketWhereClauseArray[] = '`wb` = 0';

                        $loserBracketWhereClause = implode(' AND ', $loserBracketWhereClauseArray);

                        $setValueArray = array();
                        $setValueArray[] = '`active` = 1';
                        $setValueArray[] = '`' . $nextTeam . '` = ' . $loser_id;

                        $setValue = implode(', ', $setValueArray);

                        //
                        // Loser Match
                        $query = mysqli_query(
                            $_database,
                            "UPDATE `".PREFIX."cups_matches_playoff`
                                SET " . $setValue . "
                                WHERE " . $loserBracketWhereClause
                        );

                        if (!$query) {
                            throw new \Exception('cups_matches_playoff_query_update_failed (' . $setValue . ' / ' . $loserBracketWhereClause . ')');
                        }

                    }

                }

            } else if (isset($_POST['admin_defwin']) && ($_POST['admin_defwin'] > 0)) {

                //
                // 0: normales Spiel
                // 1: Team 1 Def-Win
                // 2: Team 2 Def-Win
                // 3: kein Sieger (freilos)
                $adminPanel = (int)$_POST['admin_defwin'];

                // Current Game
                $team1_score = ($adminPanel == 1) ? 16 : 0;
                $team2_score = ($adminPanel == 2) ? 16 : 0;
                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX."cups_matches_playoff`
                        SET `ergebnis1` = " . $team1_score . ",
                            `ergebnis2` = " . $team2_score . ",
                            `admin_confirmed` = 1
                        WHERE matchID = " . $match_id
                );

                if (!$query) {
                    throw new \Exception('cups_matches_playoff_query_update_failed (#' . $match_id . ', `admin_confirmed` = 1)');
                }

                //
                // Next Game
                $winner_id = ($adminPanel < 3) ? $matchArray['team'.$adminPanel.'_id'] : 0;
                $nextTeam = ($matchArray['spiel']%2) ? 'team1' : 'team2';
                $nextGame = floor(($matchArray['spiel'] / 2) + 0.5);

                $whereClauseArray = array();
                $whereClauseArray[] = '`cupID` = ' . $cup_id;
                $whereClauseArray[] = '`wb` = 1';
                $whereClauseArray[] = '`runde` = ' . ($matchArray['runde'] + 1);
                $whereClauseArray[] = '`spiel` = ' . $nextGame;

                if ($winner_id == 0) {
                    $whereClauseArray[] = '`' . $nextTeam . '_freilos` = 1';
                }

                $whereClause = implode(' AND ', $whereClauseArray);

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_matches_playoff`
                        SET `" . $nextTeam . "` = " . $winner_id . ",
                            `active` = 1
                        WHERE " . $whereClause
                );

                if (!$query) {
                    throw new \Exception('cups_matches_playoff_query_update_failed (' . $whereClause . ')');
                }

            } else {
                throw new \Exception($_language->module['unknown_action']);
            }

        } else if (isset($_POST['submitScreenUpload'])) {

            $match_id = (isset($_POST['match_id']) && validate_int($_POST['match_id'], true)) ?
                (int)$_POST['match_id'] : 0;

            if ($match_id < 1) {
                throw new \Exception($_language->module['no_match']);
            }

            $category_id = (isset($_POST['screenshot_category']) && validate_int($_POST['screenshot_category'], true)) ?
                (int)$_POST['screenshot_category'] : 0;

            if ($category_id < 1) {
                throw new \Exception($_language->module['no_category']);
            }

            $_language->readModule('formvalidation', true);

            $upload = new \webspell\HttpUpload('screenshot_status');
            if (!$upload->hasFile()) {
                throw new \Exception($_language->module['no_image']);
            }

            if ($upload->hasError() !== false) {
                throw new \Exception($_language->module['broken_image']);
            }

            $mime_types = array(
                'image/jpeg',
                'image/png',
                'image/gif'
            );

            if (!$upload->supportedMimeType($mime_types)) {
                throw new \Exception($_language->module['unsupported_image_type']);
            }

            $imageInformation = getimagesize($upload->getTempFile());

            if (!is_array($imageInformation)) {
                throw new \Exception($_language->module['broken_image']);
            }

            switch ($imageInformation[2]) {
                case 1:
                    $endung = '.gif';
                    break;
                case 3:
                    $endung = '.png';
                    break;
                default:
                    $endung = '.jpg';
                    break;
            }

            $filepath = './images/cup/match_screenshots/';
            $file = convert2filename($match_id . '_' . $category_id, true, true) . $endung;

            if (!$upload->saveAs($filepath . $file, true)) {
                throw new \Exception($_language->module['broken_image']);
            }

            @chmod($filepath . $file, $new_chmod);

            $insertQuery = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "cups_matches_playoff_screens`
                    (
                        `matchID`,
                        `file`,
                        `category_id`,
                        `date`
                    )
                    VALUES
                    (
                        " . $match_id . ",
                        '" . $file . "',
                        " . $category_id . ",
                        " . time() . "
                    )"
            );

            if (!$insertQuery) {
                throw new \Exception('cups_matches_playoff_screens_query_insert_failed (' . $file . ')');
            }

        } else {
            throw new \Exception($_language->module['unknown_action']);
        }

    } catch (Exception $e) {

        if (!preg_match('/_query_/', $e->getMessage())) {
            $_SESSION['errorArray'][] = $e->getMessage();
        } else {

            if (preg_match('/_query_update_/', $e->getMessage())) {
                $_SESSION['errorArray'][] = $_language->module['query_update_failed'];
            } else if (preg_match('/_query_insert_/', $e->getMessage())) {
                $_SESSION['errorArray'][] = $_language->module['query_insert_failed'];
            } else if (preg_match('/_query_delete_/', $e->getMessage())) {
                $_SESSION['errorArray'][] = $_language->module['query_delete_failed'];
            } else {
                $_SESSION['errorArray'][] = $_language->module['query_select_failed'];
            }

        }

    }

    header('Location: ' . $parent_url);

}
