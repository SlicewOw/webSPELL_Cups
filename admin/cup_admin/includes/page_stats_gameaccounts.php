<?php

$selectQuery = cup_query(
    "SELECT
            `gameaccID`
        FROM `" . PREFIX . "cups_gameaccounts`
        WHERE `active` = 1 AND `deleted` = 0",
    __FILE__
);

$getGameaccountCount = mysqli_num_rows($selectQuery);

$gameacc_act_list = '<div class="list-group-item">' . $_language->module['gameaccounts_total_active'] . '<span class="pull-right grey">' . $getGameaccountCount . '</span></div>';

$info = cup_query(
    "SELECT
            `category`,
            COUNT(`category`) AS `anz`
        FROM `" . PREFIX . "cups_gameaccounts`
        WHERE `active` = 1 AND `deleted` = 0
        GROUP BY `category`
        ORDER BY COUNT(`category`) DESC",
    __FILE__
);

$gameaccChartRowArray = array();
while($ds = mysqli_fetch_array($info)) {

    $gameArray = getGame($ds['category']);

    if (validate_array($gameArray, true)) {

        $game_short = (!empty($gameArray['short'])) ?
            $gameArray['short'] : $gameArray['tag'];

        $gameaccChartRowArray[] = '[\'' . $game_short . '\', ' . $ds['anz'] . ']';

        $gameacc_act_list .= '<a href="admincenter.php?site=cup&amp;mod=gameaccounts&amp;cat=' . $ds['category'] . '" class="list-group-item">';
        $gameacc_act_list .= $gameArray['name'];
        $gameacc_act_list .= '<span class="pull-right grey">'.$ds['anz'].'</span>';
        $gameacc_act_list .= '</a>';

    }

}

$gameaccChartRows = implode(', ', $gameaccChartRowArray);

for ($x = 0; $x < 2; $x++) {

    $selectQuery = cup_query(
        "SELECT
                COUNT(*) AS `anz`
            FROM `" . PREFIX . "cups_gameaccounts_csgo`
            WHERE `validated` = " . $x,
        __FILE__
    );

    $get = mysqli_fetch_array($selectQuery);

    $getCSGOVal[$x] = $get['anz'];

}

$gameaccCSGOValidateRows = '';
$gameaccCSGOValidateRows .= '[\'' . $_language->module['not_validated'] . '\', '.$getCSGOVal[0].']';
$gameaccCSGOValidateRows .= ', [\'' . $_language->module['validated'] . '\', '.$getCSGOVal[1].']';

$selectQuery = cup_query(
    "SELECT
            `gameaccID`
        FROM `" . PREFIX . "cups_gameaccounts`
        WHERE `active` = 0 AND `deleted` = 1",
    __FILE__
);

$getDeletedGameaccountCount = mysqli_num_rows($selectQuery);

$gameacc_del_list = '<div class="list-group-item">' . $_language->module['gameaccounts_total_deleted'] . '<span class="pull-right grey">' . $getDeletedGameaccountCount . '</span></div>';

$info = cup_query(
    "SELECT
            `category`,
            COUNT(`category`) AS `anz`
        FROM `" . PREFIX."cups_gameaccounts`
        WHERE `active` = 0 AND `deleted` = 1
        GROUP BY `category`
        ORDER BY COUNT(`category`) DESC
        LIMIT 0, " . ($maxEntries - 1),
    __FILE__
);

while($ds = mysqli_fetch_array($info)) {

    $gameacc_del_list .= '<a href="admincenter.php?site=cup&amp;mod=gameaccounts&amp;cat=' . $ds['category'] . '" class="list-group-item">';
    $gameacc_del_list .= getgamename($ds['category']);
    $gameacc_del_list .= '<span class="pull-right grey">' . $ds['anz'] . '</span>';
    $gameacc_del_list .= '</a>';

}

$gameacc_csgo_list = '';

$selectQuery = cup_query(
    "SELECT
            AVG(`hours`) AS `mean`,
            MIN(`hours`) AS `minimum`,
            MAX(`hours`) AS `maximum`
        FROM `" . PREFIX . "cups_gameaccounts_csgo`
        WHERE `hours` > 0",
    __FILE__
);

$getExtrema = mysqli_fetch_array($selectQuery);

$statsArray = array(
    'mean',
    'minimum',
    'maximum'
);

foreach($statsArray as $stats) {
    $gameacc_csgo_list .= '<div class="list-group-item">' . $_language->module[$stats] . '<span class="pull-right grey">' . (int)$getExtrema[$stats] . ' h</span></div>';
}

$selectQuery = cup_query(
    "SELECT
            COUNT(*) AS `anz`
        FROM `" . PREFIX . "cups_gameaccounts_csgo`
        WHERE `vac_bann` != 0",
    __FILE__
);

$getExtrema = mysqli_fetch_array($selectQuery);

$gameacc_csgo_list .= '<div class="list-group-item">' . $_language->module['gameaccounts_total_vac'] . '<span class="pull-right grey">' . $getExtrema['anz'] . '</span></div>';

$selectQuery = cup_query(
    "SELECT
            COUNT(*) AS `anz`
        FROM `" . PREFIX . "cups_gameaccounts_csgo`
        WHERE `hours` = 0",
    __FILE__
);

$getExtrema = mysqli_fetch_array($selectQuery);

$gameacc_csgo_list .= '<div class="list-group-item">0 ' . $_language->module['hours'] . ' <span class="pull-right grey">'.(int)$getExtrema['anz'].'</span></div>';

$selectQuery = cup_query(
    "SELECT
            COUNT(*) AS `anz`
        FROM `" . PREFIX . "cups_gameaccounts`
        WHERE `smurf` = 1 AND `category` = 'csg'",
    __FILE__
);

$getExtrema = mysqli_fetch_array($selectQuery);

$gameacc_csgo_list .= '<div class="list-group-item">' . $_language->module['smurf'] . '<span class="pull-right grey">' . (int)$getExtrema['anz'] . '</span></div>';

/*
* CSGO ACC LIST
*/

$gameacc_acc_list = array(
    0 => '',
    1 => ''
);

for ($x = 0; $x < 2; $x++) {

    $sortType = ($x<1) ? 'ASC' : 'DESC';

    $query = cup_query(
        "SELECT
                a.gameaccID AS gameacc_id,
                a.hours AS hours_played,
                b.userID AS user_id,
                b.value AS value,
                b.active AS isActive,
                c.nickname AS nickname
            FROM `" . PREFIX . "cups_gameaccounts_csgo` a
            JOIN `" . PREFIX . "cups_gameaccounts` b ON a.gameaccID = b.gameaccID
            JOIN `" . PREFIX . "user` c ON b.userID = c.userID
            WHERE a.hours > 0 AND b.category = 'csg'
            ORDER BY a.hours " . $sortType . "
            LIMIT 0, 5",
        __FILE__
    );

    while ($getExtrema = mysqli_fetch_array($query)) {

        $infoText = $getExtrema['nickname'].' <span class="pull-right grey">'.$getExtrema['hours_played'].'h - '.$getExtrema['value'];
        if (!$getExtrema['isActive']) {
            $infoText .= ' - ' . $_language->module['deleted'];
        }
        $infoText .= '</span>';

        $gameacc_acc_list[$x] .= '<a href="' . $base_userlog_url . $getExtrema['user_id'] . '" class="list-group-item">'.$infoText.'</a>';

    }

}
