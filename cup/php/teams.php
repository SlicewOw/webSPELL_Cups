<?php

/**
 * Cup System written by SlicewOw - myRisk
 * Copyright (c) by SlicewOw
 **/

try {

    $_language->readModule('teams');

    if (file_exists(__DIR__ . '/includes/teams_' . $getAction . '.php')) {
        include(__DIR__ . '/includes/teams_' . $getAction . '.php');
    } else if (file_exists(__DIR__ . '/admin/teams_' . $getAction . '.php')) {
        include(__DIR__ . '/admin/teams_' . $getAction . '.php');
    } else {

        $team_button = '';
        if ($loggedin) {
            $team_button .= '<a href="index.php?site=teams&amp;action=add" class="btn btn-info btn-sm white darkshadow">' . $_language->module['add'] . '</a>';
            if (isinteam($userID, 0, 0)) {
                $team_button .= ' <a href="index.php?site=teams&amp;action=show" class="btn btn-info btn-sm white darkshadow">Team Control Center</a>';
            }
            $team_button .= '<br /><br />';
        }

        $whereClauseArray = array();
        $whereClauseArray[] = '`deleted` = 0';
        $whereClauseArray[] = '`admin` = 0';

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database,
            "SELECT * FROM `" . PREFIX . "cups_teams`
                WHERE " . $whereClause . "
                ORDER BY `name` ASC"
        );

        if (!$selectQuery) {
            throw new \UnexpectedValueException($_language->module['query_select_failed']);
        }

        $anz = mysqli_num_rows($selectQuery);

        if ($anz > 0) {

            $teams = '';

            while ($db = mysqli_fetch_array($selectQuery)) {

                $team_id = $db[getConstNameTeamId()];

                $url = 'index.php?site=teams&amp;action=details&amp;id=' . $team_id;

                $team_info = '<img src="' . getCupTeamImage($team_id, true) . '" alt="" width="16" height="16" />';

                $team_info .= '<span style="margin: 0 0 0 10px">' . $db['name'] . '</span>';

                $data_array = array();
                $data_array['$url'] = $url;
                $data_array['$name'] = $db['name'];
                $data_array['$team_info'] = $team_info;
                $teams .= $GLOBALS["_template_cup"]->replaceTemplate("teams_list", $data_array);

            }

        } else {
            $teams = '<div class="list-group-item">'.$_language->module['no_team'].'</div>';
        }

        $data_array = array();
        $data_array['$team_button'] = $team_button;
        $data_array['$teams'] = $teams;
        $teams_home_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_home_list", $data_array);
        echo $teams_home_list;

    }

} catch (Exception $e) {
    echo showError($e->getMessage(), true);
}
