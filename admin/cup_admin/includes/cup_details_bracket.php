<?php

$bracket = '';

try {

    if (!isset($content)) {
        $content = '';
    }

    if (($cupArray['status'] < 2)) {
        throw new \Exception($_language->module['cup_not_started']);
    }

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (!isset($cupArray) || !validate_array($cupArray)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (!isset($cup_id) || !validate_int($cup_id, true)) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id . '&page=bracket';

        try {

            $cupRunde = (isset($_POST['runde']) && validate_int($_POST['runde'], true)) ?
                (int)$_POST['runde'] : 0;

            if ($cupRunde < 1) {
                throw new \Exception($_language->module['unknown_round']);
            }

            $whereClauseArray = array();
            $whereClauseArray[] = '`cupID` = ' . $cup_id;
            $whereClauseArray[] = '`runde` = ' . $cupRunde;

            $whereClause = implode(' AND ', $whereClauseArray);

            if (isset($_POST['resetMaps'])) {

                $cupArray = getcup($cup_id);

                //
                // Map Pool
                $mapList = getMaps($cupArray['mappool']);

                $mapArray['open'] = $mapList;
                $mapArray['banned'] = array(
                    'team1' => array(),
                    'team2' => array()
                );
                $mapArray['picked'] = array();
                $mapArray['list'] = $mapList;
                $maps = serialize($mapArray);

                $saveQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_matches_playoff`
                        SET `mapvote` = 0,
                            `maps` = '" . $maps . "'
                        WHERE " . $whereClause
                );

                if (!$saveQuery) {
                    throw new \Exception($_language->module['query_update_failed']);
                }

                $text = 'Maps wurden zur&uuml;ckgesetzt für Runde ' . $cupRunde . ' vom Cup #' . $cup_id;
                $_SESSION['successArray'][] = $text;

            } else if (isset($_POST['submitMasterComment'])) {

                $parent_url .= '#cup_round_' . $cupRunde;

                $message = getinput($_POST['comment']);

                $query = mysqli_query(
                    $_database,
                    "SELECT
                            `matchID`
                        FROM `" . PREFIX . "cups_matches_playoff`
                        WHERE " . $whereClause
                );

                if (!$query) {
                    throw new \Exception($_language->module['query_select_failed']);
                }

                while ($get = mysqli_fetch_array($query)) {

                    $match_id = $get['matchID'];

                    $saveQuery = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "comments`
                            (
                                `parentID`,
                                `type`,
                                `userID`,
                                `date`,
                                `comment`,
                                `announcement`
                            )
                            VALUES
                            (
                                " . $match_id . ",
                                'cm',
                                " . $userID . ",
                                " . time() . ",
                                '" . $message . "',
                                1
                            )"
                    );

                    if (!$saveQuery) {
                        throw new \Exception($_language->module['query_insert_failed']);
                    }

                    $comment_id = mysqli_insert_id($_database);

                    $text = 'Match Kommentar #' . $comment_id . ' hinzugef&uuml;gt';
                    $_SESSION['successArray'][] = $text;

                }

            } else if (isset($_POST['submitMapsByAdmin'])) {

                $match_id = (isset($_POST['match_id']) && validate_int($_POST['match_id'], true)) ?
                    (int)$_POST['match_id'] : 0;

                if ($match_id < 1) {
                    throw new \Exception($_language->module['unknown_match']);
                }

                $mapArray = (isset($_POST['map']) && validate_array($_POST['map'], true)) ?
                    $_POST['map'] : array();

                $mapCount = count($mapArray);
                if ($mapCount < 1) {
                    throw new \Exception($_language->module['unknown_match']);
                }

                $setMapsArray = array(
                    'open' => array(),
                    'banned' => $mapArray,
                    'picked' => array()
                );

                for ($x = 0; $x < $mapCount; $x++) {

                    $map = $mapArray[$x];

                    if (($x + 1) == $mapCount) {
                        $setMapsArray['picked'][] = $map;
                    } else {

                        $team_id = ($x%2) ?
                            'team2' : 'team1';

                        $setMapsArray['picked'][$team_id][] = $map;

                    }

                }

                $setMaps = serialize($setMapsArray);

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_matches_playoff`
                        SET `maps` = '" . $setMaps . "',
                            `mapvote` = 1
                        WHERE `matchID` = " . $match_id
                );

                if (!$updateQuery) {
                    throw new \Exception($_language->module['query_update_failed']);
                }

                $text = 'Maps f&uuml;r Match gesetzt durch Admin';
                $_SESSION['successArray'][] = $text;

            } else {
                throw new \Exception($_language->module['unknown_action']);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $data_array = array();
        $server_settings = $GLOBALS["_template_cup"]->replaceTemplate("cup_match_details_server", $data_array);

        $data_array = array();
        $data_array['$serverSettings'] = $server_settings;
        $data_array['$image_url'] = $image_url;
        $bracket .= $GLOBALS["_template_cup"]->replaceTemplate("cup_match_details_modal", $data_array);

        for ($round_id = 1; $round_id < ($cupArray['anz_runden'] + 1); $round_id++) {

            $bracket .= '<div id="cup_round_'.$round_id.'" class="panel panel-default">';
            $bracket .= '<div class="panel-heading">'.$_language->module['round'].' '.$round_id.'</div>';
            $bracket .= '<div id="cup_round_'.$round_id.'_matches" class="list-group">';

            include(__DIR__ . '/matches_round.php');

            $bracket .= '</div>';

            $data_array = array();
            $data_array['$cup_id'] = $cup_id;
            $data_array['$i'] = $round_id;
            $bracket .= $GLOBALS["_template_cup"]->replaceTemplate("cup_admin_match_round_footer", $data_array);

            $bracket .= '</div>';

        }

    }


} catch (Exception $e) {
    $bracket = showError($e->getMessage());
}

$content .= '<div class="panel panel-default"><div class="panel-body">' . $bracket . '</div></div>';
