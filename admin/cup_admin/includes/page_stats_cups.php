<?php

$cupArray = array(
    'list' => array(),
    'details' => array()
);

$selectQuery = cup_query(
    "SELECT
            COUNT(*) AS `exist`
        FROM `" . PREFIX . "cups`",
    __FILE__
);

$checkIf = mysqli_fetch_array($selectQuery);

$cupChartHits = '';

if ($checkIf['exist'] > 0) {

    $info = mysqli_query(
        $_database,
        "SELECT * FROM ".PREFIX."cups 
            ORDER BY cupID ASC"
    );

    while($ds = mysqli_fetch_array($info)) {

        $cup_id = $ds['cupID'];

        $hits = 0;
        $hits += $ds['hits'];
        $hits += $ds['hits_teams'];
        $hits += $ds['hits_groups'];
        $hits += $ds['hits_bracket'];
        $hits += $ds['hits_rules'];

        $cupArray['list'][$ds['cupID']] =  $hits;
        $cupArray['details'][$ds['cupID']] = $ds['name'];

        if(!$ds['admin_visible']) {
            $cupChartHits .= (!empty($cupChartHits)) ? 
                ', [\''.$ds['name'].'\', '.$hits.', '.$ds['hits'].', '.$ds['hits_teams'].', '.$ds['hits_groups'].', '.$ds['hits_bracket'].', '.$ds['hits_rules'].']' :
                '[\''.$ds['name'].'\', '.$hits.', '.$ds['hits'].', '.$ds['hits_teams'].', '.$ds['hits_groups'].', '.$ds['hits_bracket'].', '.$ds['hits_rules'].']';
        }

    }

    arsort($cupArray['list']);

}

$cuphit_list = '';
$arrayKeys = array_keys($cupArray['list']);
for ($x = 0; $x < $maxEntries; $x++) {

    $cup_id = isset($arrayKeys[$x]) ?
        $arrayKeys[$x] : 0;

    if ($cup_id > 0) {

        $cuphit_list .= '<a href="' . $base_cup_url . $cup_id . '" class="list-group-item">';
        $cuphit_list .= $cupArray['details'][$cup_id];
        $cuphit_list .= '<span class="pull-right grey">'.$cupArray['list'][$cup_id].' Hits</span>';
        $cuphit_list .= '</a>';

    }

}

$cupteams_list = '';

$info = cup_query(
    "SELECT
            `cupID`,
            COUNT(cupID) AS `anz`
        FROM `" . PREFIX . "cups_teilnehmer`
        WHERE `checked_in` = 1
        GROUP BY `cupID`
        ORDER BY COUNT(`cupID`) DESC
        LIMIT 0, " . $maxEntries,
    __FILE__
);

if (mysqli_num_rows($info) > 0) {

    while ($ds = mysqli_fetch_array($info)) {

        $cupArray = getcup($ds['cupID']);

        $anzTeams = $ds['anz'];
        $maxTeams = $cupArray['size'];

        $relative = (int)(round($anzTeams / $maxTeams * 100));

        if ($relative < 75) {
            $relativeText = '<span class="red">' . $relative . '%</span>';
        } else if ($relative > 95) {
            $relativeText = '<span class="green darkshadow">' . $relative . '%</span>';
        } else {
            $relativeText = $relative.'%';
        }

        $cupteams_list .= '<a href="' . $base_cup_url . $ds['cupID'] . '" class="list-group-item">';
        $cupteams_list .= $cupArray['name'];
        $cupteams_list .= '<span class="pull-right grey">' . $anzTeams . ' / ' . $maxTeams . ' (' . $relativeText . ')</span>';
        $cupteams_list .= '</a>';

    }

}

$cupteam_list = '';

$info = cup_query(
    "SELECT
            ct.`teamID` AS `participant_id`,
            COUNT(ct.teamID) AS `anz`,
            c.`mode` AS `cup_mode`
        FROM `" . PREFIX . "cups_teilnehmer` ct
        JOIN `" . PREFIX . "cups` c ON ct.`cupID` = c.`cupID`
        WHERE ct.`checked_in` = 1
        GROUP BY ct.`teamID`
        ORDER BY COUNT(ct.`teamID`) DESC
        LIMIT 0, " . $maxEntries,
    __FILE__
);

if (mysqli_num_rows($info) > 0) {

    while ($ds = mysqli_fetch_array($info)) {

        $participant_id = $ds['participant_id'];

        if ($ds['cup_mode'] == '1on1') {
            $participant_url = $base_profile_url . $participant_id;
            $participant_name = getnickname($participant_id);
        } else {
            $participant_url = $base_team_url . $participant_id;
            $participant_name = getteam($participant_id, 'name');
        }

        $cupteam_list .= '<a href="' . $participant_url . '" class="list-group-item">';
        $cupteam_list .= $participant_name;
        $cupteam_list .= '<span class="pull-right grey">' . $ds['anz'] . '</span>';
        $cupteam_list .= '</a>';

    }

}
