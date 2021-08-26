<?php

use webspell_ng\UserSession;

use myrisk\Cup\Handler\TeamHandler;


try {

    $team_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($team_id < 1) {
        throw new \UnexpectedValueException($_language->module['no_team']);
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

                if ($get['userID'] == $admin_id) {
                    throw new \UnexpectedValueException($_language->module['error_still_admin']);
                }

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_teams`
                        SET userID = " . $admin_id . "
                        WHERE teamID = " . $team_id
                );

                if (!$updateQuery) {
                    throw new \UnexpectedValueException($_language->module['query_update_failed']);
                }

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_teams_member`
                        SET position = 1
                        WHERE teamID = " . $team_id . " AND userID = " . $admin_id
                );

                if (!$updateQuery) {
                    throw new \UnexpectedValueException($_language->module['query_update_failed']);
                }

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_teams_member`
                        SET position = 3
                        WHERE teamID = " . $team_id . " AND userID = " . $userID
                );

                if (!$updateQuery) {
                    throw new \UnexpectedValueException($_language->module['query_update_failed']);
                }

                setPlayerLog($admin_id, $team_id, 'cup_team_admin');

                setCupTeamLog($team_id, $get['name'], 'leader_transfer', 0, $admin_id);

                $_SESSION['successArray'][] = $_language->module['leader_transfer_saved'];

            } else {
                throw new \UnexpectedValueException($_language->module['unknown_action']);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: index.php?site=teams&action=details&id=' . $team_id);

    } else {

        $team = TeamHandler::getTeamByTeamId($team_id);

        if (!$team->isDeleted() || ($team->isDeleted() && UserSession::isCupAdmin())) {
            throw new \UnexpectedValueException($_language->module['deleted']);
        }

        if ($team->isDeleted() && UserSession::isCupAdmin()) {
            echo showInfo($_language->module['deleted']);
        }

        //
        // Team Hits
        setHits('cups_teams', getConstNameTeamId(), $team_id, false);

        //
        // Team-Admin Rechte
        $teamAdminAccess = ($userID == $team->getTeamAdmin()->getUser()->getUserId()) ? TRUE : FALSE;

        if ($teamAdminAccess) {
            echo '<a href="index.php?site=teams&amp;action=admin&amp;id=' . $team_id . '" class="btn btn-info btn-sm white darkshadow">Team Admin</a>';
            echo '<br /><br />';
        }

        $detailArray = array();
        $detailArray[] = $_language->module['created'] . ' ' . getformatdatetime($team->getCreationDate()->getTimestamp());

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

        $detailArray[] = 'Admin: <a href="index.php?site=profile&amp;id=' . $team->getTeamAdmin()->getUser()->getUserId() . '">' . $team->getTeamAdmin()->getUser()->getUsername() . '</a>';

        $data_array = array();
        $data_array['$name'] = $team->getName();
        $data_array['$details'] = implode(' / ', $detailArray);
        $teams_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_details_head", $data_array);
        echo $teams_list;

        $logo = '<img src="' . getCupTeamImage($team_id, true) . '" alt="" style="width: 100%;" />';

        $memberArray = array();

        $team_members = $team->getMembers();

        if (empty($team_members)) {
            $members = '<tr><td>' . showInfo($_language->module['no_member']) . '</td></tr>';
        } else {

            $members = '';

            foreach ($team_members as $team_member) {

                $user_id = $team_member->getUser()->getUserId();

                $name = '';

                if (!empty($team_member->getUser()->getFirstname())) {
                    $name .= $team_member->getUser()->getFirstname() . ' "';
                }

                $name .= '<a href="index.php?site=profile&amp;id=' . $user_id . '" class="blue">' . $team_member->getUser()->getUsername() . '</a>';

                if (!empty($team_member->getUser()->getFirstname)) {
                    $name .= '"';
                }

                $links = '';
                if ($loggedin && ($user_id == $userID) && !$teamAdminAccess) {

                    $teamLeaveURL = 'index.php?site=teams&amp;action=delete&amp;id=' . $team_id;
                    $links .= ' <a href="' . $teamLeaveURL . '&amp;player=left" class="btn btn-default btn-xs">' . $_language->module['team_leave'] . '</a>';

                }

                $memberArray[] = $user_id;

                $data_array = array();
                $data_array['$user_id'] = $user_id;
                $data_array['$name'] = $name;
                $data_array['$position'] = $team_member->getPosition()->getPosition();
                $data_array['$date'] = getformatdate($team_member->getJoinDate()->getTimestamp());
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
                    b.name_de AS penalty_name_de,
                    b.name_uk AS penalty_name_uk,
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

                if (isset($_SESSION['language']) && ($_SESSION['language'] == 'de')) {
                    $penalty .= ' ' . $get['penalty_name_de'];
                } else {
                    $penalty .= ' ' . $get['penalty_name_uk'];
                }

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
                        b.name_de AS penalty_name_de,
                        b.name_uk AS penalty_name_uk,
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

                    if (isset($_SESSION['language']) && ($_SESSION['language'] == 'de')) {
                        $penalty .= ' ' . $get['penalty_name_de'];
                    } else {
                        $penalty .= ' ' . $get['penalty_name_uk'];
                    }

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

        include(__DIR__ . '/teams_details_awards.php');

        if (!isset($team_awards)) {
            $team_awards = '';
        }

        include(__DIR__ . '/teams_details_participations.php');

        if (!isset($played_cups)) {
            $played_cups = '';
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
