<?php

try {

    $_language->readModule('cups');

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['no_match']);
    }

    //
    // Cup Array
    $cupArray = getcup($cup_id);

    checkCupDetails($cupArray, $cup_id);

    $match_id = (isset($_GET['mID']) && validate_int($_GET['mID'], true)) ?
        (int)$_GET['mID'] : 0;

    if ($match_id < 1) {
        throw new \Exception($_language->module['no_match']);
    }

    $cupAdminAccess = (iscupadmin($userID)) ?
        TRUE : FALSE;

    if (!(getmatch($match_id, 'active_playoff') || $cupAdminAccess)) {
        throw new \Exception($_language->module['login']);
    }

    $debug = 0;

    $error = '';
    $error_score = '';
    $time_now = time();

    //
    // Game Array
    $gameArray = getGame($cupArray['game']);

    //
    // Match Array
    $matchArray = getmatch($match_id);

    if (!isset($matchArray['cup_id']) || ($matchArray['cup_id'] != $cup_id)) {
        throw new \Exception($_language->module['no_match']);
    }

    //
    // Access
    if ($cupArray['mode'] == '1on1') {
        $matchAdminAccess_team1 = (($matchArray['team1_id'] > 0) && ($userID == $matchArray['team1_id'])) ? TRUE : FALSE;
        $matchAdminAccess_team2 = (($matchArray['team2_id'] > 0) && ($userID == $matchArray['team2_id'])) ? TRUE : FALSE;
    } else {
        $matchAdminAccess_team1 = (($matchArray['team1_id'] > 0) && isinteam($userID, $matchArray['team1_id'], 'admin')) ? TRUE : FALSE;
        $matchAdminAccess_team2 = (($matchArray['team2_id'] > 0) && isinteam($userID, $matchArray['team2_id'], 'admin')) ? TRUE : FALSE;
    }

    $matchAdminAccess_player = FALSE;
    if (!($matchAdminAccess_team1) && (!$matchAdminAccess_team2)) {

        $teamAdminAccess = FALSE;

        if (isinteam($userID, $matchArray['team1_id'], 'player')) {
            $matchAdminAccess_player = TRUE;
        } else if (isinteam($userID, $matchArray['team2_id'], 'player')) {
            $matchAdminAccess_player = TRUE;
        }

    } else {
        $teamAdminAccess = TRUE;
    }

    //
    // Content
    if (!((($matchArray['admin'] == 0) && ($cupArray['admin'] == 0)) || $cupAdminAccess)) {
        throw new \Exception($_language->module['login']);
    }

    if (validate_array($_POST, true)) {

        include(__DIR__ . '/cup_match_post_handler.php');

    } else {

        // Admin Information
        if ($cupArray['admin'] == 1) {
            echo showInfo($_language->module['admin_only'], true);
        }

        if ((!$matchArray['active']) && $cupAdminAccess) {
            echo showInfo($_language->module['match_inactive'], true);
        }

        $error_screen = '';
        $error_screen_ok = '';

        $ergebnis1 = $matchArray['ergebnis1'];
        $ergebnis2 = $matchArray['ergebnis2'];

        //
        // Team Details
        $teamArray = getTeamDetailsByMatchId($cupArray, $match_id);

        $bracket_ext = ($matchArray['bracket'] == 1) ?
            'Winner Bracket' : 'Loser Bracket';

        //
        // Match Detail Info
        $match_info = getMatchDetailsByMatchId($cupArray, $match_id);

        //
        // Match Status
        $status = getMatchStatusAsListByMatchId($cupArray, $match_id);

        $admin = '';

        //
        // Server
        if (($cupArray['server'] == 1) && ($matchArray['match_confirm'] == 0)) {

            $server_info = '';
            if (($cupArray['bot'] == 1) && empty($matchArray['server'])) {

                //
                // Team1 Admin: Request
                if ($matchArray['mapvote']) {

                    if ($matchAdminAccess_team1) {

                        $data_array = array();
                        $data_array['$image_url'] = $image_url;
                        $data_array['$cupID'] = $cup_id;
                        $data_array['$matchID'] = $match_id;
                        $server_info = $GLOBALS["_template_cup"]->replaceTemplate("cup_server_request", $data_array);

                    } else {

                        $server_info = '<div class="list-group-item lh_twenty">' . $_language->module['server_request_info'] . '</div>';
                        $server_info = str_replace(
                            '%team1%',
                            '"'.$teamArray[1]['name'].'"',
                            $server_info
                        );

                    }

                }

            } else {

                $serverArray = unserialize($matchArray['server']);

                if (!empty($serverArray['ip']) && (!empty($serverArray['password']) || ($cupArray['bot'] == 1))) {

                    if ($matchAdminAccess_player || $matchAdminAccess_team1 || $matchAdminAccess_team2 || $cupAdminAccess) {

                        $server_url = 'steam://connect/' . $serverArray['ip'];
                        if (!empty($serverArray['password'])) {
                            $server_url .= '/' . $serverArray['password'];
                        }

                        $server_info_txt = 'connect '.$serverArray['ip'];
                        if (!empty($serverArray['password'])) {
                            $server_info_txt = ';password ' . $serverArray['password'];
                        }

                        $data_array = array();
                        $data_array['$server_url'] = $server_url;
                        $data_array['$server_info_txt'] = $server_info_txt;
                        $server_info .= $GLOBALS["_template_cup"]->replaceTemplate("cup_match_server", $data_array);

                    }

                    if (!empty($serverArray['rcon']) && ($matchAdminAccess_team1 || $cupAdminAccess)) {
                        $server_info .= '<div class="list-group-item">rcon_password ' . $serverArray['rcon'] . '</div>';
                    }

                }

                if (!empty($serverArray['gotv'])) {
                    $server_url = 'steam://connect/' . $serverArray['gotv'];
                    $gotv_connect = 'connect ' . $serverArray['gotv'];
                    if (!empty($serverArray['gotv_pw'])) {
                        $server_url .= '/' . $serverArray['gotv_pw'];
                        $gotv_connect .= ';password ' . $serverArray['gotv_pw'];
                    }
                    $server_info .= '<a href="' . $server_url . '" class="list-group-item">GOTV: ' . $gotv_connect . '</a>';
                }

                $data_array = array();
                $data_array['$panel_type'] = 'panel-default';
                $data_array['$panel_title'] = 'Server';
                $data_array['$panel_content'] = $server_info;
                $admin .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

            }

        }

        //
        // Admin Settings
        if (($teamAdminAccess == TRUE) || ($cupAdminAccess == TRUE)) {

            if (($cupArray['mappool'] > 0) && ($matchArray['mapvote'] == 0)) {

                //
                // Team Admin: Mapvote
                include($dir_cup . 'cup_match_mapvote.php');

            } else if ($cupArray['status'] < 4) {

                $scoreEditable = 1;
                if ($matchAdminAccess_team1 && ($matchArray['team1_confirm'] == 1)) {
                    $scoreEditable = 0;
                } else if ($matchAdminAccess_team2 && ($matchArray['team2_confirm'] == 1)) {
                    $scoreEditable = 0;
                }

                if (($matchArray['match_confirm'] != 1) && !$scoreEditable) {
                    $scoreInfo = showInfo($_language->module['wait_until_score_is_confirmed'], true);
                } else {
                    $scoreInfo = '';
                }

                //
                // Team Admin: Match Ergebnis
                $data_array = array();
                $data_array['$scoreInfo'] = $scoreInfo;
                $data_array['$scoreEditable'] = $scoreEditable;
                $data_array['$error_score'] = $error_score;
                $data_array['$matchAdminURL'] = 'index.php?site=cup&amp;action=match&amp;id=' . $cup_id . '&amp;mID=' . $match_id;
                $data_array['$team1_name'] = $teamArray[1]['name'];
                $data_array['$team2_name'] = $teamArray[2]['name'];
                $data_array['$ergebnis1'] = $ergebnis1;
                $data_array['$ergebnis1'] = $ergebnis1;
                $data_array['$ergebnis2'] = $ergebnis2;
                $data_array['$team'] = ($matchAdminAccess_team1) ?
                    'team1' : 'team2';
                $data_array['$matchID'] = $match_id;
                $data_array['$screenshotCategories'] = getScreenshotCategoriesAsOptions($gameArray['id'], false);
                $admin .= $GLOBALS["_template_cup"]->replaceTemplate("cup_match_confirm", $data_array);

            }

            //
            // Admin Ergebnis Panel
            if ($cupAdminAccess) {
                $data_array = array();
                $data_array['$error_score'] = $error_score;
                $data_array['$ergebnis1'] = $ergebnis1;
                $data_array['$ergebnis2'] = $ergebnis2;
                $data_array['$matchID'] = $match_id;
                $admin .= $GLOBALS["_template_cup"]->replaceTemplate("cup_match_admin", $data_array);
            }

            //
            // Screenshots und Protest-Link nur, wenn Cup aktiv
            if ($cupArray['status'] < 4) {

                //
                // Protest Link
                if ($matchArray['match_confirm'] == 0) {

                    $protest_url = 'index.php?site=support&amp;action=new_ticket&amp;matchID=' . $match_id;

                    $admin .= '<a class="btn btn-danger btn-sm white darkshadow" href="' . $protest_url . '">' . $_language->module['open_protest'] . '</a><br /><br />';

                }

            }

        }

        //
        // Cup Sponsoren
        $content = getSponsorsByCupIdAsPanelBody($cup_id);

        $data_array = array();
        $data_array['$match_title'] = $teamArray[1]['name_url'].' vs. '.$teamArray[2]['name_url'];
        $data_array['$match_info'] 	= $match_info;
        $data_array['$team1_name'] 	= $teamArray[1]['name'];
        $data_array['$team1_url'] = $teamArray[1]['url'];
        $data_array['$match_logo1'] = $teamArray[1]['logotype'];
        $data_array['$score'] = $ergebnis1.' : '.$ergebnis2;
        $data_array['$team2_name'] = $teamArray[2]['name'];
        $data_array['$team2_url'] = $teamArray[2]['url'];
        $data_array['$match_logo2'] = $teamArray[2]['logotype'];
        $data_array['$status'] = $status;
        $data_array['$error'] = $error;
        $data_array['$content'] = $content;
        $data_array['$admin'] = $admin;
        $cup_match = $GLOBALS["_template_cup"]->replaceTemplate("cup_match_home", $data_array);
        echo $cup_match;

        //
        // Update Hits
        setHits('cups_matches_playoff', 'matchID', $match_id, false);

        $comments_allowed = $matchArray['comments'];
        $parentID = $match_id;
        $type = "cm";
        $referer = 'index.php?site=cup&action=match&amp;id=' . $cup_id . '&amp;mID=' . $match_id;
        include(__DIR__ . '/../../../comments.php');

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
