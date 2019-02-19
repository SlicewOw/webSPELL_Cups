<?php

/**
 * Cup match statistics in general
 */

$match_detailed_stats_list = '';

$selectQuery = cup_query(
    "SELECT
          COUNT(*) AS `matches`
        FROM `" . PREFIX . "cups_matches_playoff`",
    __FILE__
);

$getCountOf = mysqli_fetch_array($selectQuery);

$match_detailed_stats_list .= '<div class="list-group-item">' . $_language->module['total_count'] . '<span class="pull-right grey">' . $getCountOf['matches'] . '</span></div>';

/**
 * Cup match hits
 */

$matchhit_list = '';

$info = cup_query(
    "SELECT
          cmp.`matchID`,
          cmp.`team1`,
          cmp.`team2`,
          cmp.`hits`,
          c.`mode` AS `cup_mode`
        FROM `" . PREFIX . "cups_matches_playoff` cmp
        JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
        WHERE cmp.`admin` = 0
        ORDER BY cmp.`hits` DESC
        LIMIT 0, " . $maxEntries,
    __FILE__
);

if (mysqli_num_rows($info) > 0) {

    while ($ds = mysqli_fetch_array($info)) {

        if ($ds['cup_mode'] == '1on1') {
            $opponents = getnickname($ds['team1']) . ' vs. ' . getnickname($ds['team2']);
        } else {
            $opponents = getteam($ds['team1'], 'tag').' vs. '.getteam($ds['team2'], 'tag');
        }

        $detailed_info_txt = ($ds['hits'] == 1) ?
            $_language->module['hit'] : $_language->module['hits'];

        $detailed_info = '<span class="pull-right grey">' . $ds['hits'] . ' ' . $detailed_info_txt . '</span>';

        $name = $opponents .' (#' . $ds['matchID'] . ')';
        $matchhit_list .= '<div class="list-group-item">' . $name . $detailed_info . '</div>';

    }

}

/**
 * Matches of teams
 */

$matchAnzArray = array();

$info = cup_query(
    "SELECT
            cmp.`team1`,
            COUNT(*) AS `anz`
        FROM `" . PREFIX . "cups_matches_playoff` cmp
        JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
        WHERE cmp.`admin` = 0 AND cmp.`team1` > 0 AND c.`mode` != '1on1'
        GROUP BY cmp.`team1`
        ORDER BY COUNT(*) DESC",
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {
    $matchAnzArray[$ds['team1']] = $ds['anz'];
}

$info = cup_query(
    "SELECT
            cmp.`team2`,
            COUNT(*) AS `anz`
        FROM `" . PREFIX . "cups_matches_playoff` cmp
        JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
        WHERE cmp.`admin` = 0 AND cmp.`team2` > 0 AND c.`mode` != '1on1'
        GROUP BY cmp.`team2`
        ORDER BY COUNT(*) DESC",
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {
    if (isset($matchAnzArray[$ds['team2']])) {
        $matchAnzArray[$ds['team2']] += $ds['anz'];
    } else {
        $matchAnzArray[$ds['team2']] = $ds['anz'];
    }
}

arsort($matchAnzArray);

$matchanz_list_team = '';
$arrayKeys = array_keys($matchAnzArray);
for ($x = 0; $x < (2 * $maxEntries); $x++) {

    $team_id = (isset($arrayKeys[$x])) ?
        $arrayKeys[$x] : 0;

    if ($team_id > 0) {

        $matchanz_list_team .= '<a href="' . $base_team_url . $team_id . '" class="list-group-item">';
        $matchanz_list_team .= getteam($team_id, 'name');
        $matchanz_list_team .= '<span class="pull-right grey">' . $matchAnzArray[$team_id] . '</span>';
        $matchanz_list_team .= '</a>';

    }

}

/**
 * Matches of players
 */

$matchAnzArray = array();

$info = cup_query(
    "SELECT
            cmp.`team1`,
            COUNT(*) AS `anz`
        FROM `" . PREFIX . "cups_matches_playoff` cmp
        JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
        WHERE cmp.`admin` = 0 AND cmp.`team1` > 0 AND c.`mode` = '1on1'
        GROUP BY cmp.`team1`
        ORDER BY COUNT(*) DESC",
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {
    $matchAnzArray[$ds['team1']] = $ds['anz'];
}

$info = cup_query(
    "SELECT
            cmp.`team2`,
            COUNT(*) AS `anz`
        FROM `" . PREFIX . "cups_matches_playoff` cmp
        JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
        WHERE cmp.`admin` = 0 AND cmp.`team2` > 0 AND c.`mode` = '1on1'
        GROUP BY cmp.`team2`
        ORDER BY COUNT(*) DESC",
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {
    if (isset($matchAnzArray[$ds['team2']])) {
        $matchAnzArray[$ds['team2']] += $ds['anz'];
    } else {
        $matchAnzArray[$ds['team2']] = $ds['anz'];
    }
}

arsort($matchAnzArray);

$matchanz_list_player = '';
$arrayKeys = array_keys($matchAnzArray);
for ($x = 0; $x < (2 * $maxEntries); $x++) {

    $user_id = (isset($arrayKeys[$x])) ?
        $arrayKeys[$x] : 0;

    if ($user_id > 0) {

        $matchanz_list_player .= '<a href="' . $base_profile_url . $user_id . '" class="list-group-item">';
        $matchanz_list_player .= getnickname($user_id);
        $matchanz_list_player .= '<span class="pull-right grey">' . $matchAnzArray[$user_id] . '</span>';
        $matchanz_list_player .= '</a>';

    }

}