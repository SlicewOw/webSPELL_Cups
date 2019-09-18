<?php

$team_id = (isset($_GET['id']) && is_numeric($_GET['id'])) ?
    (int)$_GET['id'] : 0;

$user_id = (isset($_GET['uID']) && is_numeric($_GET['uID'])) ?
    (int)$_GET['uID'] : 0;

$playerAction 	= isset($_GET['player']) ? $_GET['player'] : '';

if ($loggedin && ($team_id > 0) && ($user_id < 1) && isinteam($userID, $team_id, 'admin') && empty($playerAction)) {

    $ds = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT * FROM `".PREFIX."cups_teams`
                WHERE teamID = " . $team_id . " AND userID = " . $userID
        )
    );

    $get_players = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teams_member`
                WHERE teamID = ".$ds[getConstNameTeamId()]." AND active = 1"
        )
    );

    if($get_players['anz'] > 1) {
        //
        // zu viele Spieler im Team
        echo showError('<span class="glyphicon glyphicon-info-sign"></span> '.$_language->module['delete_not_ok_players_member']);
    } elseif(getpenalty($team_id, 'team_delete') > 0) {
        //
        // zu viele Strafpunkte
        echo showError('<span class="glyphicon glyphicon-info-sign"></span> '.$_language->module['delete_not_ok_players_pps']);
    } else {

        $saveQuery = mysqli_query(
            $_database,
            "UPDATE `".PREFIX."cups_teams`
                SET deleted = 1
                WHERE teamID = " . $team_id
        );

        if($saveQuery) {

            $query = mysqli_query(
                $_database,
                "UPDATE `".PREFIX."cups_teams_member`
                    SET 	left_date = " . time() . ",
                            active = 0
                    WHERE 	teamID = " . $team_id
            );

            $teamname = $ds['name'];

            // Team Log
            setCupTeamLog($team_id, $teamname, 'team_deleted');

            // Player log
            setPlayerLog($userID, $team_id, 'cup_team_deleted');

            $_SESSION['successArray'][] = $_language->module['delete_ok'];

        }

        header('Location: index.php?site=teams&action=show');

    }

} elseif($loggedin && isset($_GET['id']) && isinteam($userID, $team_id, 'admin') && ($user_id > 0) && empty($playerAction)) {

    $canLeftTeam = TRUE;

    //
    // Active Cups?
    $get = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teilnehmer`
                WHERE teamID = " . $team_id
        )
    );
    if($get['anz'] > 0) {

        //
        // Anzahl Member
        $member = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teams_member`
                    WHERE teamID = " . $team_id . " AND active = 1"
            )
        );

        $query = mysqli_query(
            $_database,
            "SELECT b.status FROM `".PREFIX."cups_teilnehmer` a
                JOIN `".PREFIX."cups` b ON a.cupID = b.cupID
                WHERE teamID = " . $team_id . "
                ORDER BY checked_in ASC"
        );
        while($ds = mysqli_fetch_array($query)) {

            if($ds['status'] < 4) {
                $canLeftTeam = FALSE;
                $message = $_language->module['delete_not_ok_1'];
                break;
            }

        }

    }

    //
    // Check Team-Penalty
    if($canLeftTeam) {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_penalty`
                    WHERE teamID = " . $team_id . " AND duration_time > " . time()
            )
        );

        if($get['anz'] > 0) {
            $canLeftTeam = FALSE;
            $message = $_language->module['delete_not_ok_2'];
        }

    }

    if ($canLeftTeam) {

        $query = mysqli_query(
            $_database,
            "UPDATE `".PREFIX."cups_teams_member`
                SET 	left_date = " . time() . ",
                        kickID = " . $userID . ",
                        active = 0
                WHERE 	teamID = " . $team_id . " AND userID = " . $user_id
        );
        if($query) {

            $teamname = getteam($team_id, 'name');

            //
            // Team Log
            setCupTeamLog($team_id, $teamname, 'player_kicked', $user_id);

            //
            // Player Log
            setPlayerLog($user_id, $team_id, 'cup_team_kicked');

            $text = '<div class="alert alert-success" role="alert">'.$_language->module['leave_team_ok'].'</div>';

        } else {
            $text = showError($_language->module['delete_not_ok']);
        }

    } else {
        $text = showError($message);
    }

    redirect('index.php?site=teams&action=admin&teamID='.$team_id, $text, 3);

} else if ($loggedin && ($team_id > 0) && isinteam($userID, $team_id, 'player') && ($user_id < 1) && !empty($playerAction)) {

    if (($playerAction == 'left') && isinteam($userID, $team_id, 'admin')) {

        echo '<div class="alert alert-danger"><span class="glyphicon glyphicon-info-sign"></span> '.$_language->module['delete_not_ok_admin'].'</div>';

    } elseif($playerAction == 'left') {

        $text = '';
        $message = '';
        $canLeftTeam = TRUE;

        //
        // Active Cups?
        $get = mysqli_fetch_array(
            mysqli_query(
            $_database,
            "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teilnehmer`
                WHERE teamID = '".$team_id."'"
            )
        );
        if($get['anz'] > 0) {

            //
            // Anzahl Member
            $member = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teams_member`
                        WHERE teamID = '".$team_id."' AND active = '1'"
                )
            );

            $query = mysqli_query(
                $_database,
                "SELECT cupID FROM `".PREFIX."cups_teilnehmer`
                    WHERE teamID = '".$team_id."'
                    ORDER BY checked_in ASC"
            );
            while($ds = mysqli_fetch_array($query)) {

                $cupArray = getcup($ds[getConstNameCupId()]);
                if(($cupArray['status'] < 4) && (($member['anz'] - 1) < $cupArray['max_mode'])) {
                    $canLeftTeam = FALSE;
                    $message = $_language->module['delete_not_ok_1'];
                    break;
                    // Team Mitglieder zählen
                }

            }

        }

        //
        // Check Team-Penalty
        if($canLeftTeam) {
            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_penalty`
                        WHERE teamID = '".$team_id."' AND duration_time > '".time()."'"
                )
            );
            if($get['anz'] > 0) {
                $canLeftTeam = FALSE;
                $message = $_language->module['delete_not_ok_2'];
            }
        }

        if($canLeftTeam) {

            $query = mysqli_query(
                $_database,
                "UPDATE `".PREFIX."cups_teams_member`
                    SET 	left_date = '".time()."',
                            active = '0'
                    WHERE 	teamID = '".$team_id."' AND userID = '".$userID."'"
            );

            $teamname = getteam($team_id, 'name');

            //
            // Team Log
            setCupTeamLog($team_id, $teamname, 'player_left');

            //
            // Player Log
            setPlayerLog($userID, $team_id, 'cup_team_left');

            $text = '<div class="alert alert-success" role="alert">'.$_language->module['leave_team_ok'].'</div>';

        } else {
            $text = showError($message);
        }

        redirect('index.php?site=teams&action=details&amp;id='.$team_id, $text, 3);

    } else {
        echo showError('<span class="glyphicon glyphicon-info-sign"></span> '.$_language->module['not_loggedin']);
    }

} else {
    echo $_language->module['not_loggedin'];
}
