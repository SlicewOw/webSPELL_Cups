<?php

/**
 * Teams statistics in general
 */

$teams_detailed_stats_list = '';

$selectQuery = cup_query(
    "SELECT
          COUNT(*) AS `teams`
        FROM `" . PREFIX . "cups_teams`
        WHERE `deleted` = 0 AND `admin` = 0",
    __FILE__
);

$getCountOf = mysqli_fetch_array($selectQuery);

$teams_detailed_stats_list .= '<div class="list-group-item">' . $_language->module['total_count'] . '<span class="pull-right grey">' . $getCountOf['teams'] . '</span></div>';

$selectQuery = cup_query(
    "SELECT
          COUNT(*) AS `deleted_teams`
        FROM `" . PREFIX . "cups_teams`
        WHERE `deleted` = 1 AND `admin` = 0",
    __FILE__
);

$getCountOf = mysqli_fetch_array($selectQuery);

$teams_detailed_stats_list .= '<div class="list-group-item">Teams ' . $_language->module['deleted'] . '<span class="pull-right grey">' . $getCountOf['deleted_teams'] . '</span></div>';


/**
 * Teams hits
 */

$teams_list = '';

$info = cup_query(
    "SELECT
            `teamID`,
            `name`,
            `hits`
        FROM `" . PREFIX . "cups_teams`
        ORDER BY `hits` DESC
        LIMIT 0, " . $maxEntries,
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {

    $detailed_info_txt = ($ds['hits'] == 1) ?
        $_language->module['hit'] : $_language->module['hits'];

    $detailed_info = '<span class="pull-right grey">' . $ds['hits'] . ' ' . $detailed_info_txt . '</span>';

    $teams_list .= '<a href="' . $base_team_url . $ds[getConstNameTeamId()] . '" class="list-group-item">' . $ds['name'] . $detailed_info . '</a>';

}

/**
 * Teams members
 */

$team_member_list = '';

$info = cup_query(
    "SELECT
            `teamID`,
            COUNT(`teamID`) AS `anz`
        FROM `" . PREFIX . "cups_teams_member`
        WHERE `active` = 1
        GROUP BY `teamID`
        ORDER BY COUNT(`teamID`) DESC
        LIMIT 0, ".$maxEntries,
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {

    $detailed_info_txt = ($ds['anz'] == 1) ?
        $_language->module['member'] : $_language->module['members'];

    $detailed_info = '<span class="pull-right grey">' . $ds['anz'] . ' ' . $detailed_info_txt . '</span>';

    $team_member_list .= '<a href="' . $base_team_url . $ds[getConstNameTeamId()] . '" class="list-group-item">' . getteam($ds[getConstNameTeamId()], 'name') . $detailed_info . '</a>';

}
