<?php

try {

    $_language->readModule('support', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=support';

        try {

            if(isset($_POST['submitAddAdminTicket'])) {

                $text = (isset($_POST['text'])) ?
                    getinput($_POST['text']) : '';

                if(isset($_POST['match_protest']) && is_numeric($_POST['match_protest']) && ($_POST['match_protest'] == 1)) {

                    $admin_name = getfirstname($userID).' "<b>'.getnickname($userID).'</b>" '.getlastname($userID);

                    $requestTimer = (isset($_POST['requestTimer']) && is_numeric($_POST['requestTimer'])) ? (int)$_POST['requestTimer'] : 15;

                    $time_now = time();
                    $time_up_to = $time_now + (60 * $requestTimer);

                    $requests = '';
                    for($x=1;$x<3;$x++) {

                        $team = 'team'.$x;

                        $teamDetails[$x]['name'] = '';
                        if(isset($_POST[$team.'_id'])) {
                            $teamDetails[$x]['name'] = getteam((int)$_POST[$team.'_id'], 'name');
                        }

                        if(isset($_POST['checkbox'][$team])) {

                            $teamArray = $_POST['checkbox'][$team];

                            $requests .= '<b>'.$teamDetails[$x]['name'].'</b><br />- ';

                            $requests .= implode('<br />- ', $teamArray);

                            if($x == 1) {
                                $requests .= '<br /><br />';
                            }

                        }

                    }

                    $name = str_replace(
                        array('%team1_name%', '%team2_name%'),
                        array($teamDetails[1]['name'], $teamDetails[2]['name']),
                        $_language->module['title_match_protest']
                    );

                    $text = str_replace(
                        array('%time_now%', '%time_up_to%', '%demo_requests%', '%admin_name%'),
                        array(getformatdatetime($time_now), getformatdatetime($time_up_to), $requests, $admin_name),
                        $_language->module['text_match_protest']
                    );

                } else {

                    $name = (isset($_POST['name'])) ? getinput($_POST['name']) : '';

                }

                if(empty($name)) {
                    throw new \UnexpectedValueException($_language->module['ticket_error_1']);
                }

                if(empty($text)) {
                    throw new \UnexpectedValueException($_language->module['ticket_error_2']);
                }

                $categoryID	= (isset($_POST['categoryID']) && validate_int($_POST['categoryID'])) ?
                    (int)$_POST['categoryID'] : 0;

                if($categoryID > 0) {
                    $_SESSION['support']['category_id'] = $categoryID;
                }

                $cupID = (isset($_POST['selectCupID']) && validate_int($_POST['selectCupID'])) ?
                    (int)$_POST['selectCupID'] : 0;

                if($cupID > 0) {
                    $_SESSION['support'][getConstNameCupIdWithUnderscore()] = $cupID;
                }

                $matchID = (isset($_POST['selectMatchID']) && validate_int($_POST['selectMatchID'])) ?
                    (int)$_POST['selectMatchID'] : 0;

                if($matchID > 0) {
                    $_SESSION['support']['match_id'] = $matchID;
                }

                $teamAdminTeam1 = (isset($_POST['player']) && validate_int($_POST['player'])) ?
                    (int)$_POST['player'] : 0;

                if($teamAdminTeam1 > 0) {
                    $_SESSION['support']['player_id'] = $teamAdminTeam1;
                }

                if(isset($_POST['team1_id']) && validate_int($_POST['team1_id'])) {
                    $team = (int)$_POST['team1_id'];
                } else if(isset($_POST['teams']) && validate_int($_POST['teams'])) {
                    $team = (int)$_POST['teams'];
                } else {
                    $team = 0;
                }

                if($team > 0) {
                    $_SESSION['support'][getConstNameTeamIdWithUnderscore()] = $team;
                }

                $opponentID	= (isset($_POST['team2_id']) && validate_int($_POST['team2_id'])) ?
                    (int)$_POST['team2_id'] : 0;

                if($opponentID > 0) {
                    $_SESSION['support']['opponent_id'] = $opponentID;
                }

                if(($teamAdminTeam1 == 0) && ($team > 0)) {
                    $teamArray = getteam($team);
                    $teamAdminTeam1 = $teamArray['admin_id'];
                }

                if($teamAdminTeam1 < 1) {
                    throw new \UnexpectedValueException($_language->module['ticket_error_3']);
                }

                $teamAdminTeam2 = 0;
                if($opponentID > 0) {
                    $teamArray = getteam($opponentID);
                    $teamAdminTeam2 = $teamArray['admin_id'];
                }

                $saveQuery = mysqli_query(
                    $_database,
                    "INSERT INTO ".PREFIX."cups_supporttickets
                        (
                            `start_date`,
                            `userID`,
                            `opponent_adminID`,
                            `adminID`,
                            `name`,
                            `categoryID`,
                            `teamID`,
                            `cupID`,
                            `opponentID`,
                            `matchID`,
                            `text`,
                            `status`
                        )
                        VALUES
                        (
                            '".time()."',
                            '".$teamAdminTeam1."',
                            '".$teamAdminTeam2."',
                            '".$userID."',
                            '".$name."',
                            '".$categoryID."',
                            '".$team."',
                            '".$cupID."',
                            '".$opponentID."',
                            '".$matchID."',
                            '".$text."',
                            2
                        )"
                );

                if(!$saveQuery) {

                    $parent_url = 'admincenter.php?site=cup&mod=support&action=admin_add';
                    throw new \UnexpectedValueException($_language->module['query_insert_failed']);

                }

                $ticketID = mysqli_insert_id($_database);

                $username = getfirstname($userID).' "'.getnickname($userID).'" '.getlastname($userID);

                $message = str_replace(
                    array('%admin_name%', '%ticket_id%', '%hp_url%'),
                    array($username, $ticketID, $hp_url),
                    $_language->module['ticket_email_message_new']
                );

                $sendmail = \webspell\Email::sendEmail(
                    'noreply@myrisk-gaming.de',
                    'myRisk Gaming e.V.',
                    getemail($teamAdminTeam1),
                    $_language->module['ticket_email_title_new'],
                    $message
                );

                if($teamAdminTeam2 > 0) {
                    $sendmail = \webspell\Email::sendEmail(
                        'noreply@myrisk-gaming.de',
                        'myRisk Gaming e.V.',
                        getemail($teamAdminTeam2),
                        $_language->module['ticket_email_title_new'],
                        $message
                    );
                }

                if($_FILES['screenshot']['error'] == 0) {
                    if(isimage($_FILES['screenshot']['type'])) {
                        $path = __DIR__ . '/../../images/cup/ticket_screenshots/';
                        if($_FILES['screenshot']['type'] === 'image/jpeg') 		{ $file_type = '.jpg'; }
                        elseif($_FILES['screenshot']['type'] === 'image/gif') 	{ $file_type = '.gif'; }
                        else 													{ $file_type = '.png'; }
                        $filename = $ticketID.'_'.time().$file_type;
                        if(move_uploaded_file($_FILES['screenshot']['tmp_name'],$path.$filename)) {
                            mysqli_query(
                                $_database,
                                "UPDATE ".PREFIX."cups_supporttickets
                                    SET screenshot = '".$filename."'
                                    WHERE ticketID = ".$ticketID
                            );
                        }
                    }
                }

                $parent_url .= '&action=details&id=' . $ticketID;

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $name = '';

        $catID = isset($_GET['catID']) ? (int)$_GET['catID'] : '0';
        $cupID 		= isset($_GET[getConstNameCupId()]) ? (int)$_GET[getConstNameCupId()] : '0';
        $matchID 	= isset($_GET['mID']) ? (int)$_GET['mID'] : '0';
        $teamID 	= isset($_GET[getConstNameTeamId()]) ? (int)$_GET[getConstNameTeamId()] : '0';
        $user_id 	= isset($_GET['userID']) ? (int)$_GET['userID'] : '0';

        $select_cup_visible = 'ticket_form_invisble';
        if($catID && (($catID != 7) && ($catID != 4))) {
            $select_cup_visible = 'ticket_form_visble';
        }

        $select_match_visible = 'ticket_form_invisble';
        if($cupID) {
            $select_match_visible = 'ticket_form_visble';
        }

        $select_team_visible = 'ticket_form_invisble';
        if(($matchID || ($catID == 2)) && ($catID != 1)) {
            $select_team_visible = 'ticket_form_visble';
        }

        $select_user_visible = 'ticket_form_invisble';
        if(($teamID || ($catID == 7)) && ($catID != 1)) {
            $select_user_visible = 'ticket_form_visble';
        }

        $matches = '';
        $teams = '';
        $users = '';

        $categories = getticketcategories();

        $cups = '<option value="0" class="italic">'.$_language->module['ticket_no_cup'].'</option>';
        $cups .= getcups();

        if (!empty($cupID)) {

            $cups = selectOptionByValue($cups, $cupID, true);

            $matches = '<option value="0">- / -</option>';
            $match = safe_query(
                "SELECT
                        `matchID`,
                        `team1`,
                        `team1_freilos`,
                        `team2`,
                        `team2_freilos`
                    FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE `cupID` = " . $cupID . "
                    ORDER BY `matchID` DESC"
            );
            while($dx = mysqli_fetch_array($match)) {
                $team1 = '';
                if ($dx['team1'] > 0) {
                    $team1 = getteam($dx['team1'], 'name');
                }

                $team2 = '';
                if ($dx['team2'] > 0) {
                    $team2 = getteam($dx['team2'], 'name');
                }

                if (!empty($team1) && !empty($team2)) {
                    $matches .= '<option value="'.$dx['matchID'].'">Match #'.$dx['matchID'].' - '.$team1.' vs. '.$team2.'</option>';
                }

            }

            if (!empty($matchID)) {

                $matches = str_replace(
                    'value="'.$matchID.'"',
                    'value="'.$matchID.'" selected="selected"',
                    $matches
                );

                $team_array = getteam($matchID, 'team_ids');
                $teams = '<option value="0" class="italic">'.$_language->module['ticket_no_team'].'</option>';
                $teams .= '<option value="'.$team_array[0].'" class="italic">'.getteam($team_array[0], 'name').'</option>';
                $teams .= '<option value="'.$team_array[1].'" class="italic">'.getteam($team_array[1], 'name').'</option>';

                if(!empty($teamID)) {
                    $teams = str_replace('value="'.$teamID.'"', 'value="'.$teamID.'" selected="selected"', $teams);
                }

                if(!empty($teamID)) {
                    $teams = str_replace('value="'.$teamID.'"', 'value="'.$teamID.'" selected="selected"', $teams);

                    $info = safe_query("SELECT userID FROM ".PREFIX."cups_teams_member WHERE teamID = '".$teamID."' ORDER BY userID");
                    if(mysqli_num_rows($info)) {
                        while($ds = mysqli_fetch_array($info)) {
                            $users .= '<option value="'.$ds['userID'].'">'.getnickname($ds['userID']).'</option>';
                        }
                    }

                    if(!empty($user_id)) {
                        $users = str_replace('value="'.$user_id.'"', 'value="'.$user_id.'" selected="selected"', $users);
                    }

                }

            }

        }

        if(empty($teams)) {
            $teams = getteams();
        }

        if(empty($users)) {
            $users = getuserlist();
            if(!empty($user_id)) {
                $users = str_replace(
                    'value="'.$user_id.'"',
                    'value="'.$user_id.'" selected="selected"',
                    $users
                );
            }
        }

        $text = '';

        if(isset($_SESSION['support']) && validate_array($_SESSION['support'])) {

            if(isset($_SESSION['support']['category_id'])) {

                $categories = str_replace(
                    'value="'.$_SESSION['support']['category_id'].'"',
                    'value="'.$_SESSION['support']['category_id'].'" selected="selected"',
                    $categories
                );

            }

            if(isset($_SESSION['support'][getConstNameCupIdWithUnderscore()])) {

                $cups = str_replace(
                    'value="'.$_SESSION['support'][getConstNameCupIdWithUnderscore()].'"',
                    'value="'.$_SESSION['support'][getConstNameCupIdWithUnderscore()].'" selected="selected"',
                    $cups
                );

            }

            if(isset($_SESSION['support']['match_id'])) {

                $matches = str_replace(
                    'value="'.$_SESSION['support']['match_id'].'"',
                    'value="'.$_SESSION['support']['match_id'].'" selected="selected"',
                    $matches
                );

            }

            if(isset($_SESSION['support'][getConstNameTeamIdWithUnderscore()])) {

                $teams = str_replace(
                    'value="'.$_SESSION['support'][getConstNameTeamIdWithUnderscore()].'"',
                    'value="'.$_SESSION['support'][getConstNameTeamIdWithUnderscore()].'" selected="selected"',
                    $teams
                );

            }

            if(isset($_SESSION['support']['player_id'])) {

                $users = str_replace(
                    'value="'.$_SESSION['support']['player_id'].'"',
                    'value="'.$_SESSION['support']['player_id'].'" selected="selected"',
                    $users
                );

            }

            unset($_SESSION['support']);

        }

        $urlArray = array();
        for($x=0;$x<4;$x++) {
            $urlArray[] = ($getSite == 'support') ?
                'index.php?site=support&amp;action=admin' :
                'admincenter.php?site=cup&amp;mod=support&amp;action=admin';
        }

        $data_array = array();
        $data_array['$catID'] = $catID;
        $data_array['$cupID'] = $cupID;
        $data_array['$matchID'] = $matchID;
        $data_array['$teamID'] = $teamID;
        $data_array['$basis_url'] = 'admincenter.php?site=cup&amp;mod=support&amp;action=admin_add';
        $data_array['$name'] = $name;
        $data_array['$categories'] = $categories;
        $data_array['$select_cup_visible'] = $select_cup_visible;
        $data_array['$cups'] = $cups;
        $data_array['$select_match_visible'] = $select_match_visible;
        $data_array['$matches'] = $matches;
        $data_array['$select_team_visible'] = $select_team_visible;
        $data_array['$teams'] = $teams;
        $data_array['$select_user_visible'] = $select_user_visible;
        $data_array['$users'] = $users;
        $data_array['$text'] = $text;
        $ticket_add_admin = $GLOBALS["_template_cup"]->replaceTemplate("ticket_add_admin", $data_array);
        echo $ticket_add_admin;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
