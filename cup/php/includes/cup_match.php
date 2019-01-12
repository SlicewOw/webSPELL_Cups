<?php

try {

    $_language->readModule('cups');

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['no_match']);
    }

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
    // Info Arrays
    $cupArray = getcup($cup_id);
    $gameArray = getGame($cupArray['game']);
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
            echo showInfo('Dieses Match ist nicht aktiv.', true);
        }

        $error_screen = '';
        $error_screen_ok = '';

        $ergebnis1 = $matchArray['ergebnis1'];
        $ergebnis2 = $matchArray['ergebnis2'];

        //
        // Team Details
        $logotypeBase = $image_url . '/cup/teams/';
        for ($x = 1; $x < 3; $x++) {

            if ($cupArray['mode'] == '1on1') {

                $user_id = $matchArray['team'.$x.'_id'];

                $teamURL = 'javascript:;';
                if ($user_id > 0) {

                    $teamName = getnickname($user_id);
                    $teamURL = 'index.php?site=profile&id='.$user_id.'#content';
                    $matchLogo = getuserpic($user_id, true);

                } else if ($matchArray['team'.$x.'_freilos'] == 1) {
                    $teamName = $_language->module['cup_freilos'];
                    $matchLogo = $logotypeBase . 'team_nologotype.png';
                } else {
                    $teamName = '';
                    $matchLogo = $logotypeBase . 'team_nologotype.png';
                }

            } else {

                $team_array = getteam($matchArray['team'.$x.'_id']);

                $teamURL = 'javascript:;';
                if ($matchArray['team'.$x.'_id'] > 0) {
                    $teamURL = 'index.php?site=teams&amp;action=details&amp;id='.$matchArray['team'.$x.'_id'];
                    $teamName = $team_array['name'];
                } else if ($matchArray['team'.$x.'_freilos'] == 1) {
                    $teamName = $_language->module['cup_freilos'];
                } else {
                    $teamName = '';
                }

                $matchLogo = $team_array['logotype'];

            }

            $teamArray[$x] = array(
                'name' => $teamName,
                'name_url' => '<a href="'.$teamURL.'" title="'.$teamName.'">'.$teamName.'</a>',
                'logotype' => '<img src="'.$matchLogo.'" width="180" height="180" alt="'.$teamName.'" title="'.$teamName.'" />',
                'url' => $teamURL
            );

        }

        $bracket_ext = ($matchArray['bracket'] == 1) ?
            'Winner Bracket' : 'Loser Bracket';

        $formatArray = array(
            'bo1',
            'bo3'
        );

        //
        // Match Detail Info
        $matchInfoArray = array();
        $matchInfoArray[] = $cupArray['name'];
        $matchInfoArray[] = $_language->module['cup_'.$matchArray['bracket'].'_round_'.$cupArray['size'].'_'.$matchArray['runde']];
        $matchInfoArray[] = $_language->module['cup_match_start'].': '.getformatdatetime($matchArray['date']);
        $matchInfoArray[] = 'Format: '.strtoupper($matchArray['format']);

        if (in_array($matchArray['format'], $formatArray) && ($matchArray['mapvote'] == 1)) {

            if (($matchArray['team1_freilos'] == 0) && ($matchArray['team2_freilos'] == 0)) {
                $matchInfoArray[] = getmap($match_id, $matchArray['format']);
            }

        }

        $match_info = implode(' / ', $matchInfoArray);

        //
        // Match Status
        $status = '';
        if ($matchArray['match_confirm'] == 1) {

            if ($matchArray['admin_confirm'] == 1) {
                $confirmStatus = $_language->module['match_admin_confirmed'];
            } else {
                $confirmStatus = $_language->module['match_confirmed'];
            }

        } else {
            $confirmStatus = $_language->module['match_not_played'];
        }
        $status .= '<div class="list-group-item">'.$confirmStatus.'</div>';

        if ($cupArray['mappool'] > 0) {
            $matchVeto = ($matchArray['mapvote'] == 1) ? 
                $_language->module['match_veto_ok'] : $_language->module['match_veto_ip'];
            $status .= '<div class="list-group-item">' . $matchVeto . '</div>';
        }

        //
        //  Screenshots
        $screenshot_url = $image_url . '/cup/match_screenshots/';
        $screenshot_local_url = __DIR__ . '/../../../images/cup/match_screenshots/';

        $screenshotArray = getScreenshots($match_id);

        if (validate_array($screenshotArray, true)) {

            $statusScreenshotArray = array();
            foreach ($screenshotArray as $screenshot) {

                if (file_exists($screenshot_local_url . $screenshot['file'])) {
                    $statusScreenshotArray[] = '<a href="' . $screenshot_url . $screenshot['file'] . '" target="_blank">' . $screenshot['category_name'] . '</a>';
                } else {
                    $statusScreenshotArray[] = '<del>' . $screenshot['category_name'] . '</del>';
                }

            }

            $status .= '<div class="list-group-item">Screens: ' . implode(', ', $statusScreenshotArray) . '</div>';

        }

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
                        $data_array['$cupID'] 	= $cup_id;
                        $data_array['$matchID'] = $match_id;
                        $server_info = $GLOBALS["_template_cup"]->replaceTemplate("cup_server_request", $data_array);

                    } else {

                        $server_info = '<div class="list-group-item lh_twenty">'.$_language->module['server_request_info'].'</div>';
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

                    if($matchAdminAccess_player || $matchAdminAccess_team1 || $matchAdminAccess_team2 || $cupAdminAccess) {
                        $server_url = 'steam://connect/'.$serverArray['ip'];
                        if (!empty($serverArray['password'])) {
                            $server_url .= '/'.$serverArray['password'];
                        }
                        $server_info .= '<div class="list-group-item">';
                        $server_info .= 'connect '.$serverArray['ip'];
                        if(!empty($serverArray['password'])) {
                            $server_info .= ';password '.$serverArray['password'];
                        }
                        $server_info .= '<a class="pull-right btn btn-default btn-xs" href="'.$server_url.'">connect</a>';
                        $server_info .= '<div class="clear"></div>';
                        $server_info .= '</div>';
                    }

                    if (!empty($serverArray['rcon']) && ($matchAdminAccess_team1 || $cupAdminAccess)) {
                        $server_info .= '<div class="list-group-item">rcon_password '.$serverArray['rcon'].'</div>';
                    }

                }

                if (!empty($serverArray['gotv'])) {
                    $server_url = 'steam://connect/'.$serverArray['gotv'];
                    $gotv_connect = 'connect '.$serverArray['gotv'];
                    if(!empty($serverArray['gotv_pw'])) {
                        $server_url .= '/'.$serverArray['gotv_pw'];
                        $gotv_connect .= ';password '.$serverArray['gotv_pw'];
                    }
                    $server_info .= '<a href="'.$server_url.'" class="list-group-item">GOTV: '.$gotv_connect.'</a>';
                }

            }

            if(!empty($server_info)) {
                $admin .= '<div class="panel panel-default"><div class="panel-heading">Server</div><div class="list-group">'.$server_info.'</div></div>';	
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

                    $admin .= '<div class="clear"></div>';
                    $admin .= '<a style="width: 100%;" class="alert alert-danger center" href="'.$protest_url.'">'.$_language->module['open_protest'].'</a>';

                }

            }

        }

        //
        // Cup Sponsoren
        $sponsors = mysqli_query(
            $_database,
            "SELECT
                    `sponsorID`
                FROM `" . PREFIX . "cups_sponsors`
                WHERE `cupID` = " . $cup_id
        );
        if (mysqli_num_rows($sponsors)) {

            $content_sponsors = '';
            while ($db = mysqli_fetch_array($sponsors)) {

                $sponsorArray = getsponsor($db['sponsorID']);

                $linkAttributeArray = array();
                $linkAttributeArray[] = 'href="' . $sponsorArray['url'] . '"';
                $linkAttributeArray[] = 'target="_blank"';
                $linkAttributeArray[] = 'title="' . $sponsorArray['name'] . '"';
                $linkAttributeArray[] = 'onclick="setHitsJS(\'sponsors\', ' . $db['sponsorID'] . ');"';
                $linkAttributeArray[] = 'class="pull-left"';

                $banner_url = getSponsorImage($db['sponsorID'], true, 'white');

                $content_sponsors .= '<a ' . implode(' ', $linkAttributeArray) . '><img src="' . $banner_url . '" alt="' . $sponsorArray['name'] . '" /></a>';

            }

            $admin .= '<div class="panel panel-default"><div class="panel-heading">Sponsoren</div><div class="panel-body">'.$content_sponsors.'<div class="clear"></div></div></div>';

        }

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
