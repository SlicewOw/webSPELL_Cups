<?php

use webspell_ng\UserSession;
use webspell_ng\Utils\DateUtils;

use myrisk\Cup\Handler\CupPenaltyHandler;
use myrisk\Cup\Handler\TeamHandler;

try {

    $team_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($team_id < 1) {
        throw new \UnexpectedValueException($_language->module['no_team']);
    }

    $team = TeamHandler::getTeamByTeamId($team_id);

    if (validate_array($_POST, true)) {

        try {

            if (isset($_POST['submitAdminChange'])) {

                $admin_id = (int)$_POST['changeAdminSelect'];

                if ($team->getTeamAdmin()->getUser()->getUserId() == $admin_id) {
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
        $detailArray[] = $_language->module['created'] . ' ' . DateUtils::getFormattedDateTime($team->getCreationDate());

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
                $data_array['$date'] = DateUtils::getFormattedDate($team_member->getJoinDate());
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

        $penalties_of_team = CupPenaltyHandler::getPenaltiesOfTeam($team);

        foreach ($penalties_of_team as $penalty_of_team) {

            if (!$penalty_of_team->isActive()) {
                continue;
            }

            if ($penalty_of_team->getPenaltyCategory()->getPenaltyPoints() == 1) {
                $pen = '1 ' . $_language->module['penalty'];
            } else {
                $pen = $penalty_of_team->getPenaltyCategory()->getPenaltyPoints() . ' ' . $_language->module['penalties'];
            }

            $penalty = '';
            $penalty .= '<span class="bold">' . $pen . ':</span>';

            if (isset($_SESSION['language']) && ($_SESSION['language'] == 'de')) {
                $penalty .= ' ' . $penalty_of_team->getPenaltyCategory()->getNameInGerman();
            } else {
                $penalty .= ' ' . $penalty_of_team->getPenaltyCategory()->getNameInEnglish();
            }

            $penalty .= ' (' . $_language->module['penalty_until'] . ' ' . DateUtils::getFormattedDateTime($penalty_of_team->getDateUntilPenaltyIsActive()) . ')';

            $penaltyArray[] = $penalty;

        }

        $time_now = time();

        if (validate_array($memberArray, true)) {

            $memberList = implode(', ', $memberArray);

            $get_pp = mysqli_query(
                $_database,
                "SELECT
                        a.ppID
                    FROM `" . PREFIX . "cups_penalty` a
                    WHERE a.duration_time > " . $time_now . " AND a.userID IN (" . $memberList . ") AND a.deleted = 0"
            );
            if (mysqli_num_rows($get_pp)) {

                while ($get = mysqli_fetch_array($get_pp)) {

                    $penalty_of_team_member = CupPenaltyHandler::getPenaltyByPenaltyId((int) $get['ppID']);

                    if ($penalty_of_team_member->getPenaltyCategory()->getPenaltyPoints() == 1) {
                        $pen = '1 ' . $_language->module['penalty'];
                    } else {
                        $pen = $penalty_of_team_member->getPenaltyCategory()->getPenaltyPoints() . ' ' . $_language->module['penalties'];
                    }

                    $penalty = '';
                    $penalty .= '<span class="bold">' . $pen . ':</span>';

                    if (isset($_SESSION['language']) && ($_SESSION['language'] == 'de')) {
                        $penalty .= ' ' . $penalty_of_team_member->getPenaltyCategory()->getNameInGerman();
                    } else {
                        $penalty .= ' ' . $penalty_of_team_member->getPenaltyCategory()->getNameInEnglish();
                    }

                    $penalty .= ' (' . $_language->module['penalty_until'] . ' ' . DateUtils::getFormattedDateTime($penalty_of_team_member->getDateUntilPenaltyIsActive()) . ')';


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
