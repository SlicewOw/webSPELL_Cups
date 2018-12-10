<?php

try {

    //$_language->readModule('cups');

    if (!$loggedin) {
        throw new \Exception('access_denied');
    }

    $matchArray = array();

    $whereClause_base = '((`team1_confirmed` = 0 AND `team2_confirmed` = 0)';
    $whereClause_base .= ' OR (`team1_confirmed` = 0 AND `team2_confirmed` = 1)';
    $whereClause_base .= ' OR (`team1_confirmed` = 1 AND `team2_confirmed` = 0))';
    $whereClause_base .= ' AND `admin_confirmed` = 0';

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                `teamID`
            FROM `" . PREFIX . "cups_teams_member`
            WHERE `userID` = " . $userID . " AND `active` = 1"
    );

    if (!$selectQuery) {
        throw new \Exception('query_select_failed');
    }

    while ($get = mysqli_fetch_array($selectQuery)) {

        $team_id = $get['teamID'];

        $whereClause = $whereClause_base . ' AND (`team1` = ' . $team_id . ' OR `team2` = ' . $team_id . ')';

        $matchQuery = mysqli_query(
            $_database,
            "SELECT
                    cmp.`matchID` AS `match_id`,
                    cmp.`cupID` AS `cup_id`,
                    cmp.`team1` AS `team1_id`,
                    cmp.`team2` AS `team2_id`,
                    c.`mode` AS `cup_mode`
                FROM `" . PREFIX . "cups_matches_playoff` cmp
                JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
                WHERE " . $whereClause
        );

        if (!$matchQuery) {
            throw new \Exception('query_select_failed');
        }

        while ($getMatch = mysqli_fetch_array($matchQuery)) {

            if ($getMatch['cup_mode'] != '1on1') {

                $opponent = ($getMatch['team1_id'] == $team_id) ?
                    $getMatch['team2_id'] : $getMatch['team1_id'];

                $opponent_name = getteam($opponent, 'name');

            } else {

                $opponent = ($getMatch['team1_id'] == $userID) ?
                    $getMatch['team2_id'] : $getMatch['team1_id'];

                $opponent_name = getnickname($opponent);

            }

            $matchArray[] = array(
                'match_id' =>  $getMatch['match_id'],
                'cup_id' => $getMatch['cup_id'],
                'opponent' => $opponent_name
            );

        }

    }

    $anzMatches = count($matchArray);
    if ($anzMatches > 0) {

        $base_url = 'index.php?site=cup&amp;action=match&amp;id=';

        $matchList = '';
        for ($x = 0; $x < $anzMatches; $x++) {

            if (!isset($matchArray[$x]['match_id'])) {
                throw new \Exception('unknown_array_entry (`match_id`)');
            }

            if (!isset($matchArray[$x]['cup_id'])) {
                throw new \Exception('unknown_array_entry (`cup_id`)');
            }

            if (!isset($matchArray[$x]['opponent'])) {
                throw new \Exception('unknown_array_entry (`opoonent`)');
            }

            $url = $base_url . $matchArray[$x]['cup_id'] . '&amp;mID=' . $matchArray[$x]['match_id'];

            $matchList .= '<li><a href="' . $url . '">Match vs. ' . $matchArray[$x]['opponent'] . '</a></li>';

        }

        $text = ($anzMatches > 1) ?
            $anzMatches . ' ' . $_language->module['open_matches'] :
            '1 ' . $_language->module['open_match'];

        $data_array = array();
        $data_array['$text'] = $text;
        $data_array['$matchList'] = $matchList;
        $activeMatches = $GLOBALS["_template_cup"]->replaceTemplate("navigation_active_matches", $data_array);

    }

} catch (Exception $e) {}