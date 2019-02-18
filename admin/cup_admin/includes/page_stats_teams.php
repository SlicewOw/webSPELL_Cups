<?php

$teams_list = '';

$info = cup_query(
    "SELECT
            `name`,
            `hits`
        FROM `" . PREFIX . "cups_teams`
        ORDER BY `hits` DESC
        LIMIT 0, " . $maxEntries,
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {
    $teams_list .= '<div class="list-group-item">' . $ds['name'] . '<span class="pull-right grey">' . $ds['hits'] . ' Hits</span></div>';
}

$team_member_list = '';

$info = cup_query(
    "SELECT
            `teamID`,
            COUNT(`teamID`) AS `anz`
        FROM `".PREFIX."cups_teams_member`
        WHERE `active` = 1
        GROUP BY `teamID`
        ORDER BY COUNT(`teamID`) DESC
        LIMIT 0, ".$maxEntries,
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {
    $team_member_list .= '<div class="list-group-item">' . getteam($ds['teamID'], 'name') . '<span class="pull-right grey">' . $ds['anz'] . ' Mitglieder</span></div>';
}
