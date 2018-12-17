<?php


try {

    $_language->readModule('teams', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=teams';

        try {

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
        }

        $data_array = array();
        $data_array['$breadcrumbs'] = implode(' ', $breadcrumbArray);
        $teams_menu = $GLOBALS["_template_cup"]->replaceTemplate("teams_admin_menu", $data_array);
        echo $teams_menu;

        $showEntries = 20;

        $page = (isset($_GET['page']) && validate_int($_GET['page'], true)) ?
            (int)$_GET['page'] : 1;

        $getTeam = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `count`
                    FROM `" . PREFIX . "cups_teams`"
            )
        );

        $pages = ceil($getTeam['count'] / $showEntries);

        $start = ($page > 0) ?
            ($page - 1) * $showEntries : 0;

        $end = $showEntries;

        $selectQuery = mysqli_query(
            $_database,
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
                JOIN `" . PREFIX . "user` u ON ct.`userID` = u.`userID`
                ORDER BY ct.`name` ASC
                LIMIT " . $start . ", " . $end
        );

        if (!$selectQuery) {
            throw new \Exception($_language->module['query_select_failed']);
        }

        if (mysqli_num_rows($selectQuery) > 0) {

            $teamList = '';

            while ($get = mysqli_fetch_array($selectQuery)) {

                $team_id = $get['team_id'];

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
            $teamList = '<tr><td colspan="8">' . $_language->module['no_teams'] . '</td></tr>';
        }

        $page_link = makepagelink("admincenter.php?site=cup&amp;mod=teams", $page, $pages);

        $data_array = array();
        $data_array['$teamList'] = $teamList;
        $data_array['$page_link'] = $page_link;
        $teams_home = $GLOBALS["_template_cup"]->replaceTemplate("teams_admin_home", $data_array);
        echo $teams_home;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
