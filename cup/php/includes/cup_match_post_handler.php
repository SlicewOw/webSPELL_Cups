<?php

if (validate_array($_POST, true)) {

    $parent_url = 'index.php?site=cup&action=match&id=' . $cup_id . '&mID=' . $match_id;

    try {

        if (!isset($teamAdminAccess) && !isset($cupAdminAccess)) {
            throw new \Exception($_language->module['access_denied']);
        }

        if (!($teamAdminAccess || $cupAdminAccess)) {
            throw new \Exception($_language->module['access_denied']);
        }

        if (isset($_POST['submitMatchReset'])) {

            if (!$cupAdminAccess) {
                throw new \Exception($_language->module['access_denied']);
            }

            //
            // Map Array
            $mapsArray = resetMatchMapVote(unserialize($matchArray['maps']));

            $insertValueArray = array();
            $insertValueArray[] = '`mapvote` = 0';
            $insertValueArray[] = '`maps` = \'' . serialize($mapsArray) . '\'';
            $insertValueArray[] = '`ergebnis1` = 0';
            $insertValueArray[] = '`ergebnis2` = 0';
            $insertValueArray[] = '`team1_confirmed` = 0';
            $insertValueArray[] = '`team2_confirmed` = 0';
            $insertValueArray[] = '`admin_confirmed` = 0';

            $insertValue = implode(', ', $insertValueArray);

            $query = cup_query(
                "UPDATE `" . PREFIX . "cups_matches_playoff`
                    SET " . $insertValue . "
                    WHERE `matchID` = " . $match_id,
                __FILE__
            );

            addMatchLog($match_id, 'match_reset');

            $_SESSION['successArray'][] = $_language->module['admin_match_reset'];

        } else if (isset($_POST['submitMapvoteReset'])) {

            if (!$cupAdminAccess) {
                throw new \Exception($_language->module['access_denied']);
            }

            //
            // Map Array
            $mapsArray = resetMatchMapVote(unserialize($matchArray['maps']));

            //
            // Map Array speichern
            $query = cup_query(
                "UPDATE `" . PREFIX . "cups_matches_playoff`
                    SET `mapvote` = 0,
                        `maps` = '" . serialize($mapsArray) . "'
                    WHERE `matchID` = " . $match_id,
                __FILE__
            );

            addMatchLog($match_id, 'map_vote_reset');

            $_SESSION['successArray'][] = $_language->module['admin_map_reset'];

        } else if (isset($_POST['submitMatchScore']) || isset($_POST['submitAdminWinner'])) {

            $match_id = (isset($_POST['match_id']) && validate_int($_POST['match_id'], true)) ?
                (int)$_POST['match_id'] : 0;

            if ($match_id < 1) {
                throw new \Exception($_language->module['no_match']);
            }

            if (!isset($_POST['team'])) {
                $_POST['team'] = '';
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

            $setValueArray = array();

            if (isset($_POST['submitAdminWinner'])) {

                if (isset($_POST['admin_defwin']) && ($_POST['admin_defwin'] > 0)) {

                    //
                    // 0: normales Spiel
                    // 1: Team 1 Def-Win
                    // 2: Team 2 Def-Win
                    // 3: kein Sieger (freilos)
                    $adminPanel = (int)$_POST['admin_defwin'];

                    $match_format = $matchArray['format'];
                    $mapsToBePlayed = substr($match_format, 2, strlen($match_format));

                    $freeWinScore = ceil($mapsToBePlayed / 2);

                    // Current Game
                    $team1_score = ($adminPanel == 1) ?
                        $freeWinScore : 0;

                    $team2_score = ($adminPanel == 2) ?
                        $freeWinScore : 0;

                    $query = cup_query(
                        "UPDATE `" . PREFIX."cups_matches_playoff`
                            SET `ergebnis1` = " . $team1_score . ",
                                `ergebnis2` = " . $team2_score . ",
                                `admin_confirmed` = 1
                            WHERE `matchID` = " . $match_id,
                        __FILE__
                    );

                    addMatchLog($match_id, 'match_admin_confirmation');

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

                    $query = cup_query(
                        "UPDATE `" . PREFIX . "cups_matches_playoff`
                            SET `" . $nextTeam . "` = " . $winner_id . ",
                                `active` = 1
                            WHERE " . $whereClause,
                        __FILE__
                    );

                    $selectMatch = cup_query(
                        "SELECT
                                `matchID`
                            FROM `" . PREFIX . "cups_matches_playoff`
                            WHERE " . $whereClause,
                        __FILE__
                    );

                    $get_match = mysqli_fetch_array($selectMatch);

                    $match_id_next_match = $get_match['matchID'];

                    addMatchLog($match_id_next_match, 'match_active_' . $nextTeam);

                }

                $setValueArray[] = '`mapvote` = 1';

                $correctScore = TRUE;

            } else {

                //
                // correctScore:
                // 1: kein Unentschieden
                // 2: kein 0:0
                if ((($team1_score > 0) || ($team2_score > 0)) && ($team1_score != $team2_score)) {
                    $correctScore = TRUE;
                } else {
                    $correctScore = FALSE;
                }

            }

            if ($correctScore) {

                $setValueArray[] = '`ergebnis1` = ' . $team1_score;
                $setValueArray[] = '`ergebnis2` = ' . $team2_score;

                if ($_POST['team'] == 'team1') {
                    $setValueArray[] = '`team1_confirmed` = 1';
                } else {
                    $setValueArray[] = '`team2_confirmed` = 1';
                }

                $setValues = implode(', ', $setValueArray);

                $updateQuery = cup_query(
                    "UPDATE `" . PREFIX . "cups_matches_playoff`
                        SET " . $setValues . "
                        WHERE `matchID` = " . $match_id,
                    __FILE__
                );

                $matchArray = getmatch($match_id);

                $matchConfirm = ($matchArray['admin_confirm'] || ($matchArray['team1_confirm'] && $matchArray['team2_confirm'])) ?
                    TRUE : FALSE;

                if ($matchConfirm) {

                    if ($team1_score > $team2_score) {
                        $winner_id = $matchArray['team1_id'];
                        $loser_id = $matchArray['team2_id'];
                    } else {
                        $winner_id = $matchArray['team2_id'];
                        $loser_id = $matchArray['team1_id'];
                    }

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
                    $query = cup_query(
                        "UPDATE `" . PREFIX . "cups_matches_playoff`
                            SET " . $setValue . "
                            WHERE " . $winnerBracketWhereClause,
                        __FILE__
                    );

                    $selectMatch = cup_query(
                        "SELECT
                                `matchID`
                            FROM `" . PREFIX . "cups_matches_playoff`
                            WHERE " . $winnerBracketWhereClause,
                        __FILE__
                    );

                    $get_match = mysqli_fetch_array($selectMatch);

                    $match_id_winner_match = $get_match['matchID'];

                    addMatchLog($match_id_winner_match, 'match_activation_' . $nextTeam);

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
                        $query = cup_query(
                            "UPDATE `".PREFIX."cups_matches_playoff`
                                SET " . $setValue . "
                                WHERE " . $loserBracketWhereClause,
                            __FILE__
                        );

                        $selectMatch = cup_query(
                            "SELECT
                                    `matchID`
                                FROM `" . PREFIX . "cups_matches_playoff`
                                WHERE " . $loserBracketWhereClause,
                            __FILE__
                        );

                        $get_match = mysqli_fetch_array($selectMatch);

                        $match_id_loser_match = $get_match['matchID'];

                        addMatchLog($match_id_loser_match, 'match_activation_' . $nextTeam);

                    }

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

            $insertQuery = cup_query(
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
                    )",
                __FILE__
            );

        } else {
            throw new \Exception($_language->module['unknown_action']);
        }

    } catch (Exception $e) {
        $_SESSION['errorArray'][] = $e->getMessage();
    }

    header('Location: ' . $parent_url);

}
