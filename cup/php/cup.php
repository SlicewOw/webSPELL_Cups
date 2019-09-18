<?php

try {

    $_language->readModule('cups');

    $cupDetailsFile = $dir_cup . '/includes/cup_' . $getAction . '.php';
    if (file_exists($cupDetailsFile)) {
        include($cupDetailsFile);
    } else {

        $baseWhereClauseArray = array();
        $baseWhereClauseArray[] = '`saved` = 1';

        if ($getAction != 'archive') {
            $baseWhereClauseArray[] = '`status` < 4';
        } else {
            $baseWhereClauseArray[] = '`status` = 4';
        }

        if (!iscupadmin($userID)) {
            $baseWhereClauseArray[] = '`admin_visible` = 0';
        }

        $filterByGameTag = (isset($_GET['game'])) ?
            getinput($_GET['game']) : '';

        if (!empty($filterByGameTag)) {
            $whereClauseArray[] = '`game` = \'' . $filterByGameTag . '\'';
        }

        $whereClause = (validate_array($baseWhereClauseArray, true)) ?
            'WHERE ' . implode(' AND ', $baseWhereClauseArray) : '';

        /**
         * Quickfilter
         */

        $selectGamesQuery = cup_query(
            "SELECT DISTINCT
                    `game`
                FROM `" . PREFIX . "cups`
                " . $whereClause . "
                ORDER BY `status` ASC, `start_date` ASC",
                __FILE__
        );

        $availableGames = array(
            'list' => array()
        );

        while ($get = mysqli_fetch_array($selectGamesQuery)) {

            $cup_game_tag = $get['game'];
            $gameArray = getGame($cup_game_tag);

            if (!in_array($cup_game_tag, $availableGames['list'])) {
                $availableGames['list'][] = $cup_game_tag;
                $availableGames[$cup_game_tag] = array(
                    'icon' => $gameArray['icon'],
                    'shortcut' => $gameArray['short']
                );
            }

        }

        $quickfilter = '<a href="index.php?site=cup"><span class="fa fa-globe sixteen"></span></a>';

        foreach ($availableGames['list'] as $game_tag) {
            $game_icon = '<img src="' . $availableGames[$game_tag]['icon'] . '" alt="" />';
            $quickfilter .= '<a href="index.php?site=cup&amp;game=' . $game_tag . '" title="' . $availableGames[$game_tag]['shortcut'] . '">' . $game_icon . '</a>';
        }

        /**
         * Cup table content
         */

        $whereClauseArray = $baseWhereClauseArray;

        if (!empty($filterByGameTag)) {
            $whereClauseArray[] = '`game` = \'' . $filterByGameTag . '\'';
        }

        $whereClause = (validate_array($whereClauseArray, true)) ?
            'WHERE ' . implode(' AND ', $whereClauseArray) : '';

        $ergebnis = cup_query(
            "SELECT
                    `cupID`
                FROM `" . PREFIX . "cups`
                " . $whereClause . "
                ORDER BY `status` ASC, `start_date` ASC",
                __FILE__
        );

        if (mysqli_num_rows($ergebnis) > 0) {

            $cupList = '';
            while ($ds = mysqli_fetch_array($ergebnis)) {

                $cup_id = $ds[getConstNameCupId()];

                $cupArray = getcup($cup_id);

                if ($cupArray['status'] == 1) {
                    $size = $cupArray['teams']['registered'] . ' / ' . $cupArray['size'];
                    $date = getformatdatetime($cupArray['checkin']);
                } else {
                    $size = $cupArray['teams']['checked_in'] . ' / ' . $cupArray['size'];
                    $date = getformatdatetime($cupArray['start']);
                }

                $cup_game_tag = $cupArray['game'];

                $data_array = array();
                $data_array['$cup_id'] = $cup_id;
                $data_array['$game'] = getGame($cup_game_tag, 'icon');;
                $data_array['$name'] = $cupArray['name'];
                $data_array['$mode'] = $cupArray['mode'];
                $data_array['$date'] = $date;
                $data_array['$size'] = $size;
                $data_array['$status'] = $_language->module['cup_status_' . $cupArray['status']];
                $data_array['$url'] = 'index.php?site=cup&amp;action=details&amp;id=' . $cup_id;
                $cupList .= $GLOBALS["_template_cup"]->replaceTemplate("cups_list", $data_array);

            }

        } else {
            $cupList = '<tr><td colspan="8">' . $_language->module['no_cup'] . '</td></tr>';
        }

        $data_array = array();
        $data_array['$quickfilter'] = $quickfilter;
        $data_array['$overview'] = (empty($getAction)) ?
            'btn-info white darkshadow' : 'btn-default';
        $data_array['$archive'] = ($getAction == 'archive') ?
            'btn-info white darkshadow' : 'btn-default';
        $cups_controls = $GLOBALS["_template_cup"]->replaceTemplate("cups_controls", $data_array);
        echo $cups_controls;

        $data_array = array();
        $data_array['$cupList'] = $cupList;
        $cups_home = $GLOBALS["_template_cup"]->replaceTemplate("cups_home", $data_array);
        echo $cups_home;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
