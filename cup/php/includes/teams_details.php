<?php

try {

    $team_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($team_id < 1) {
        throw new \Exception($_language->module['no_team']);
    }

    if (validate_array($_POST, true)) {

        try {

            if (isset($_POST['submitAdminChange'])) {

                $admin_id = (int)$_POST['changeAdminSelect'];

                $get = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT `name`, `userID` FROM `" . PREFIX . "cups_teams` 
                            WHERE `teamID` = " . $team_id
                    )
                );

                if($get['userID'] == $admin_id) {
                    throw new \Exception($_language->module['error_still_admin']);
                }

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_teams`
                        SET userID = " . $admin_id . "
                        WHERE teamID = " . $team_id
                );

                if (!$updateQuery) {
                    throw new \Exception($_language->module['query_update_failed']);
                }

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_teams_member`
                        SET position = 1
                        WHERE teamID = " . $team_id . " AND userID = " . $admin_id
                );

                if (!$updateQuery) {
                    throw new \Exception($_language->module['query_update_failed']);
                }

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_teams_member` 
                        SET position = 3 
                        WHERE teamID = " . $team_id . " AND userID = " . $userID
                );

                if (!$updateQuery) {
                    throw new \Exception($_language->module['query_update_failed']);
                }

                setPlayerLog($admin_id, $team_id, 'cup_team_admin');

                setCupTeamLog($team_id, $get['name'], 'leader_transfer', 0, $admin_id);

                $_SESSION['successArray'][] = $_language->module['leader_transfer_saved'];

            } else {
                throw new \Exception($_language->module['unknown_action']);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: index.php?site=teams&action=details&id=' . $team_id);

    } else {

        $info = mysqli_query(
            $_database,
            "SELECT * FROM `" . PREFIX . "cups_teams` 
             WHERE `teamID` = " . $team_id
        );

        if (mysqli_num_rows($info) != 1) {
            throw new \Exception($_language->module['no_team']);
        }

        $ds = mysqli_fetch_array($info);

        if (!(($ds['deleted'] == 0) || (($ds['deleted'] == 1) && (iscupadmin($userID))))) {
            throw new \Exception($_language->module['deleted']);
        }

        //
        // Team Hits
        setHits('cups_teams', 'teamID', $team_id, false);

        if (($ds['deleted'] == 1) && (iscupadmin($userID))) {
            echo showInfo($_language->module['deleted']);
        }

        //
        // Team-Admin Rechte
        $teamAdminAccess = ($userID == $ds['userID']) ? TRUE : FALSE;

        if ($teamAdminAccess) {
            echo '<a href="index.php?site=teams&amp;action=admin&amp;id='.$team_id.'" class="btn btn-info btn-sm white darkshadow">Team Admin</a>';
            echo '<br /><br />';
        }

        $name = $ds['name'];

        $detailArray = array();
        $detailArray[] = $_language->module['created'] . ' ' . getformatdatetime($ds['date']);

        if (getteam($team_id, 'anz_matches') == 1) {
            $detailArray[] = '1 ' . $_language->module['match_played1'];
        } else {
            $detailArray[] = getteam($team_id, 'anz_matches') . ' ' . $_language->module['match_played'];
        }

        if (getteam($team_id, 'anz_cups') == 1) {
            $detailArray[] = '1 ' . $_language->module['cups_played1'];
        } else {
            $detailArray[] = getteam($team_id, 'anz_cups') . ' ' . $_language->module['cups_played'];
        }

        $detailArray[] = 'Admin: <a href="index.php?site=profile&amp;id=' . $ds['userID'] . '">' . getnickname($ds['userID']) . '</a>';

        $data_array = array();
        $data_array['$name'] = $name;
        $data_array['$details'] = implode(' / ', $detailArray);
        $teams_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_details_head", $data_array);
        echo $teams_list;

        $logo = '<img src="' . getCupTeamImage($team_id, true) . '" alt="" style="width: 100%;" />';

        $memberArray = array();

        $memberQuery = mysqli_query(
            $_database,
            "SELECT
                    a.`userID` AS `user_id`,
                    a.`join_date` AS `date_join`,
                    b.`name` AS `position`,
                    c.`nickname` AS `nickname`,
                    c.`firstname` AS `firstname`,
                    c.`lastname` AS `lastname`
                FROM `" . PREFIX . "cups_teams_member` a
                LEFT JOIN `" . PREFIX . "cups_teams_position` b ON a.`position` = b.`positionID`
                JOIN `" . PREFIX . "user` c ON a.`userID` = c.`userID`
                WHERE `teamID` = " . $team_id . " AND `active` = 1
                ORDER BY b.`sort` ASC, a.`join_date` ASC"
        );

        if (!$memberQuery) {
            throw new \Exception($_language->module['query_select_failed']);
        }

        if (mysqli_num_rows($memberQuery) < 1) {
            $members = '<tr><td>' . showInfo($_language->module['no_member']) . '</td></tr>';
        } else {

            $members = '';

            while ($dc = mysqli_fetch_array($memberQuery)) {

                $user_id = $dc['user_id'];

                $name = '';

                if (!empty($dc['firstname'])) {
                    $name .= $dc['firstname'] . ' "';
                }

                $name .= '<a href="index.php?site=profile&amp;id=' . $user_id . '" class="blue">' . $dc['nickname'] . '</a>';

                if (!empty($dc['firstname'])) {
                    $name .= '"';
                }

                $links = '';
                if ($loggedin && ($user_id == $userID) && !isinteam($userID, $team_id, 'admin')) {

                    $teamLeaveURL = 'index.php?site=teams&amp;action=delete&amp;id=' . $team_id;
                    $links .= ' <a href="' . $teamLeaveURL . '&amp;player=left" class="btn btn-default btn-xs">' . $_language->module['team_leave'] . '</a>';

                }

                $memberArray[] = $user_id;

                $data_array = array();
                $data_array['$user_id'] = $user_id;
                $data_array['$name'] = $name;
                $data_array['$position'] = $dc['position'];
                $data_array['$date'] = getformatdate($dc['date_join']);
                $data_array['$links'] = $links;
                $members .= $GLOBALS["_template_cup"]->replaceTemplate("teams_details_member", $data_array);

            }

        }

        /**********
        Strafpunkte
        **********/

        //
        // Leere Initialisierung
        $penaltyArray = array();

        $time_now = time();
        $get_pp = mysqli_query(
            $_database,
            "SELECT
                    a.duration_time AS date_duration,
                    b.name_de AS penalty_name,
                    b.points AS penalty_points,
                    b.lifetime AS penalty_lifetime
                FROM `" . PREFIX . "cups_penalty` a
                JOIN `".PREFIX."cups_penalty_category` b ON a.reasonID = b.reasonID 
                WHERE a.duration_time > " . $time_now . " AND a.teamID = " . $team_id . " AND a.deleted = 0"
        );
        if (mysqli_num_rows($get_pp)) {

            $penalty = '';
            while ($get = mysqli_fetch_array($get_pp)) {

                if ($get['penalty_points'] == 1) {
                    $pen = '1 ' . $_language->module['penalty'];
                } else {
                    $pen = $get['penalty_points'] . ' ' . $_language->module['penalties'];
                }

                $penalty = '';
                $penalty .= '<span class="bold">' . $pen . ':</span>';
                $penalty .= ' ' . $get['penalty_name'];
                $penalty .= ' (' . $_language->module['penalty_until'] . ' ' . getformatdatetime($get['date_duration']) . ')';

                $penaltyArray[] = $penalty;

            }

        }

        if (validate_array($memberArray, true)) {

            $memberList = implode(', ', $memberArray);

            $get_pp = mysqli_query(
                $_database,
                "SELECT
                        a.duration_time AS date_duration,
                        b.name_de AS penalty_name,
                        b.points AS penalty_points,
                        b.lifetime AS penalty_lifetime,
                        c.nickname AS nickname
                    FROM `" . PREFIX . "cups_penalty` a
                    JOIN `".PREFIX."cups_penalty_category` b ON a.reasonID = b.reasonID 
                    JOIN `".PREFIX."user` c ON a.userID = c.userID 
                    WHERE a.duration_time > " . $time_now . " AND a.userID IN (" . $memberList . ") AND a.deleted = 0"
            );
            if (mysqli_num_rows($get_pp)) {

                while ($get = mysqli_fetch_array($get_pp)) {

                    if ($get['penalty_points'] == 1) {
                        $pen = '1 ' . $_language->module['penalty'];
                    } else {
                        $pen = $get['penalty_points'] . ' ' . $_language->module['penalties'];
                    }

                    $penalty = '';
                    $penalty .= '<span class="bold">' . $pen . ':</span>';
                    $penalty .= ' ' . $get['nickname'] . ' -';
                    $penalty .= ' ' . $get['penalty_name'];
                    $penalty .= ' (' . $_language->module['penalty_until'] . ' ' . getformatdatetime($get['date_duration']) . ')';

                    $penaltyArray[] = $penalty;

                }

            }

        }

        $penalty = '';
        if (validate_array($penaltyArray, true)) {

            $penalty .= '<div class="alert alert-info center">';
            $penalty .= implode('<br />', $penaltyArray);
            $penalty .= '</div>';

        }

        $team_award = mysqli_query(
            $_database,
            "SELECT
                    a.awardID,
                    a.award,
                    a.cupID AS cup_id,
                    b.name AS cup_name,
                    c.name AS award_name,
                    c.icon AS award_icon
                FROM `" . PREFIX . "cups_awards` a
                LEFT JOIN `".PREFIX."cups` b ON a.cupID = b.cupID
                LEFT JOIN `".PREFIX."cups_awards_category` c ON a.award = c.awardID
                WHERE a.teamID = " . $team_id . "
                ORDER BY a.date DESC
                LIMIT 0, 10"
        );
        if (mysqli_num_rows($team_award)) {

            $team_awards = '';
            while ($dx = mysqli_fetch_array($team_award)) {

                if ($dx['cup_id'] > 0) {
                    $info = $dx['cup_name'];
                    $info .= '<span class="pull-right">' . $dx['award'] . '</span>';
                } else {
                    $info = '<div style="background: url(' . $image_url . '/cup/' . $dx['award_icon'] . '_small.png) no-repeat left; padding: 0 0 0 20px;">' . $dx['award_name'] . '</div>';
                }

                $team_awards .= '<div class="list-group-item">' . $info . '</div>';

            }

        } else {
            $team_awards = '<div class="list-group-item">' . $_language->module['no_award'] . '</div>';
        }

        $team_cup = mysqli_query(
            $_database,
            "SELECT
                    a.`cupID`,
                    b.`name`
                FROM `" . PREFIX . "cups_teilnehmer` a
                LEFT JOIN `" . PREFIX . "cups` b ON a.cupID = b.cupID
                WHERE `teamID` = " . $team_id . " AND `checked_in` = 1
                ORDER BY b.`start_date` DESC
                LIMIT 0, 10"
        );
        if (mysqli_num_rows($team_cup) > 0) {

            $played_cups = '';
            while ($dx = mysqli_fetch_array($team_cup)) {

                $subget = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT
                                `platzierung`
                            FROM `" . PREFIX . "cups_platzierungen`
                            WHERE `teamID` = " . $team_id . " AND `cupID` = " . $dx['cupID']
                    )
                );

                $platz = (!empty($subget['platzierung'])) ? $subget['platzierung'] : '';

                $url = 'index.php?site=cup&amp;action=details&amp;id=' . $dx['cupID'];

                $played_cups .= '<a href="' . $url . '" class="list-group-item">';
                $played_cups .= $dx['name'];

                if (!empty($platz)) {
                    $played_cups .= '<span class="pull-right">' . $platz . '</span>';
                }

                $played_cups .= '</a>';

            }

        } else {
            $played_cups = '<div class="list-group-item">' . $_language->module['no_cup'] . '</div>';
        }

        $data_array = array();
        $data_array['$team_id'] = $team_id;
        $data_array['$penalty'] = $penalty;
        $data_array['$logo'] = $logo;
        $data_array['$members'] = $members;
        $data_array['$changeAdmin'] = '';
        $data_array['$team_awards'] = $team_awards;
        $data_array['$played_cups'] = $played_cups;
        $teams_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_details", $data_array);
        echo $teams_list;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
