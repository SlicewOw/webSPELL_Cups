<?php


try {

    $_language->readModule('teams', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=teams';

        try {

            systeminc('classes/cup_teams');

            $team = new \myrisk\cup_team();

            if (isset($_POST['submitAddCupTeam'])) {

                try {

                    if (isset($_POST['teamname'])) {

                        $teamname = getinput($_POST['teamname']);

                        $team->setName($teamname);

                        $_SESSION['cup']['team']['name'] = $teamname;

                    }

                    if (isset($_POST['teamtag'])) {

                        $teamtag = getinput($_POST['teamtag']);

                        $team->setTag($teamtag);

                        $_SESSION['cup']['team']['tag'] = $teamtag;

                    }

                    if (isset($_POST['homepage'])) {

                        $homepage = $_POST['homepage'];

                        $team->setHomepage($homepage);

                        $_SESSION['cup']['team']['hp'] = $homepage;

                    }

                    if (isset($_POST['country'])) {

                        $country = $_POST['country'];

                        $_SESSION['cup']['team']['country'] = $country;

                    } else {
                        $country = getCupDefaultLanguage();
                    }
                    $team->setCountry($country);

                    //
                    // Team Image
                    if (isset($_FILES['logotype'])) {
                        $team->uploadLogotype($_FILES['logotype']);
                    }

                    if (isset($_POST['admin_only']) && ($_POST['admin_only'] == '1')) {
                        $team->setAdminTeamOnly(true);
                    }

                    if (isset($_POST['admin'])) {
                        $team->setAdminId($_POST['admin']);
                    }

                    //
                    // Team speichern in DB
                    $team->saveTeam();

                    setPlayerLog($userID, $team->getTeamId(), 'cup_team_created');

                    if (isset($_POST['player']) && validate_array($_POST['player'], true)) {

                        $insertPlayerArray = array();

                        $playerArray = array_unique($_POST['player']);
                        foreach ($playerArray as $player_id) {
                            if (validate_int($player_id, true)) {
                                $insertPlayerArray[] = '(' . $player_id . ', ' . $team->getTeamId() . ', 3, ' . time() . ')';
                            }
                        }

                        if (validate_array($insertPlayerArray, true)) {

                            $insertQuery = cup_query(
                                "INSERT INTO `" . PREFIX . "cups_teams_member`
                                    (
                                        `userID`,
                                        `teamID`,
                                        `position`,
                                        `join_date`
                                    )
                                    VALUES
                                    " . implode(', ', $insertPlayerArray),
                                __FILE__
                            );

                        }

                    }

                    unset($_SESSION['cup']);

                    $parent_url .= '&action=admin&id=' . $team->getTeamId();

                } catch (Exception $e) {

                    $_SESSION['cup']['team']['error'] = showError($e->getMessage());

                    if (!is_null($team->getLogotype()) && !empty($team->getLogotype())) {
                        @unlink(__DIR__ . '/../../images/cup/teams/' . $team->getLogotype());
                    }

                    $parent_url .= '&action=add';

                }

            } else if (isset($_POST['submitDeleteTeam'])) {

                $team_id = (isset($_POST[getConstNameTeamIdWithUnderscore()]) && validate_int($_POST[getConstNameTeamIdWithUnderscore()], true)) ?
                    (int)$_POST[getConstNameTeamIdWithUnderscore()] : 0;

                if ($team_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_team_id']);
                }

                $team->setId($team_id);
                $team->deleteTeam();

            } else {
                throw new \UnexpectedValueException($_language->module['unknown_action']);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $team_id = (isset($_POST['id']) && validate_int($_POST['id'], true)) ?
            (int)$_POST['id'] : 0;

        $breadcrumbArray = array();

        if (empty($getAction) || ($getAction == 'home')) {
            $breadcrumbArray[] = '<li class="active">Home</li>';
        } else if ($getAction == 'add') {
            $breadcrumbArray[] = '<li class="active">' . $_language->module['add_team'] . '</li>';
        } else if ($getAction == 'delete') {
            $breadcrumbArray[] = '<li class="active">' . $_language->module['delete_team'] . '</li>';
        }

        $data_array = array();
        $data_array['$breadcrumbs'] = implode(' ', $breadcrumbArray);
        $teams_menu = $GLOBALS["_template_cup"]->replaceTemplate("teams_admin_menu", $data_array);
        echo $teams_menu;

        if ($getAction == 'add') {

            $error = '';
            if (isset($_SESSION['cup']['team']['error']) && !empty($_SESSION['cup']['team']['error'])) {
                $error = $_SESSION['cup']['team']['error'];
                unset($_SESSION['cup']['team']['error']);
            }

            $logotype_max_size = (isset($cup_team_logotype_max_size) && $cup_team_logotype_max_size) ?
                $cup_team_logotype_max_size : 500;

            $image_response = str_replace(
                '%max_pixels%',
                $logotype_max_size,
                $_language->module['image_response']
            );

            $data_array = array();
            $data_array['$title'] = $_language->module['add_team'];
            $data_array['$logotype_is_required'] = (isset($cup_team_logotype_is_required) && $cup_team_logotype_is_required) ?
                ' *' : '';
            $data_array['$image_response'] = $image_response;
            $data_array['$error_add'] = $error;
            $data_array['$teamname'] = '';
            $data_array['$teamtag'] = '';
            $data_array['$userlist'] = getuserlist();
            $data_array['$homepage'] = '';
            $data_array['$countries'] = getcountries(getCupDefaultLanguage());
            $data_array['$pic'] = '';
            $data_array['$team_id'] = 0;
            $data_array['$postName'] = 'submitAddCupTeam';
            $team_add = $GLOBALS["_template_cup"]->replaceTemplate("teams_action_admin", $data_array);
            echo $team_add;

        } else if ($getAction == 'delete') {

            $team_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
                (int)$_GET['id'] : 0;

            if ($team_id < 1) {
                throw new \UnexpectedValueException('unknown_team');
            }

            $teamArray = getteam($team_id);

            $delete_info_text = str_replace(
                '%team_name%',
                $teamArray['name'],
                $_language->module['delete_info_text']
            );

            $data_array = array();
            $data_array['$delete_info_text'] = $delete_info_text;
            $data_array['$team_id'] = $team_id;
            $team_add = $GLOBALS["_template_cup"]->replaceTemplate("teams_admin_delete", $data_array);
            echo $team_add;


        } else {

            $showEntries = 20;

            $page = (isset($_GET['page']) && validate_int($_GET['page'], true)) ?
                (int)$_GET['page'] : 1;

            $teamCountQuery = cup_query(
                "SELECT
                        COUNT(*) AS `count`
                    FROM `" . PREFIX . "cups_teams`",
                __FILE__
            );

            $getTeam = mysqli_fetch_array($teamCountQuery);

            $pages = ceil($getTeam['count'] / $showEntries);

            $start = ($page > 0) ?
                ($page - 1) * $showEntries : 0;

            $end = $showEntries;

            $selectQuery = cup_query(
                "SELECT
                        ct.`teamID` AS `team_id`,
                        ct.`name` AS `team_name`,
                        ct.`tag` AS `team_tag`,
                        ct.`logotype` AS `team_logo`,
                        ct.`country` AS `team_country`,
                        ct.`date` AS `team_created_on`,
                        ct.`hits` AS `team_hits`,
                        ct.`deleted` AS `team_is_deleted`,
                        ct.`admin` AS `admin_team_only`,
                        ct.`userID` AS `team_admin_id`,
                        u.`username` AS `team_admin_name`
                    FROM `" . PREFIX . "cups_teams` ct
                    LEFT JOIN `" . PREFIX . "user` u ON ct.`userID` = u.`userID`
                    ORDER BY ct.`name` ASC
                    LIMIT " . $start . ", " . $end,
                __FILE__
            );

            if (mysqli_num_rows($selectQuery) > 0) {

                $teamList = '';

                while ($get = mysqli_fetch_array($selectQuery)) {

                    $team_id = $get[getConstNameTeamIdWithUnderscore()];

                    $teamRowClass = '';
                    if ($get['admin_team_only']) {
                        $teamRowClass = 'class="alert-info"';
                    } else if ($get['team_is_deleted']) {
                        $teamRowClass = 'class="alert-danger"';
                    }

                    $logotype_url = getCupTeamImage($team_id, true);
                    $logotype = '<img src="' . $logotype_url . '" alt="" style="max-height: 16px;" />';

                    $data_array = array();
                    $data_array['$teamRowClass'] = $teamRowClass;
                    $data_array['$hp_url'] = $hp_url;
                    $data_array['$team_id'] = $team_id;
                    $data_array['$logotype_url'] = $logotype_url;
                    $data_array['$logotype'] = $logotype;
                    $data_array['$country'] = getCountryImage($get['team_country'], true);
                    $data_array['$name'] = $get['team_name'];
                    $data_array['$tag'] = $get['team_tag'];
                    $data_array['$admin_id'] = $get['team_admin_id'];
                    $data_array['$admin_name'] = $get['team_admin_name'];
                    $data_array['$date'] = getformatdatetime($get['team_created_on']);
                    $data_array['$hits'] = $get['team_hits'];
                    $teamList .= $GLOBALS["_template_cup"]->replaceTemplate("teams_admin_list", $data_array);

                }

            } else {
                $teamList = '<tr><td colspan="9">' . $_language->module['no_teams'] . '</td></tr>';
            }

            $page_link = makepagelink("admincenter.php?site=cup&amp;mod=teams", $page, $pages);

            $data_array = array();
            $data_array['$teamList'] = $teamList;
            $data_array['$page_link'] = $page_link;
            $teams_home = $GLOBALS["_template_cup"]->replaceTemplate("teams_admin_home", $data_array);
            echo $teams_home;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
