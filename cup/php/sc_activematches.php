<?php

try {

    if (!$loggedin) {
        throw new \UnexpectedValueException('access_denied');
    }

    $matchArray = array();

    $whereClause_base = '((`team1_confirmed` = 0 AND `team2_confirmed` = 0)';
    $whereClause_base .= ' OR (`team1_confirmed` = 0 AND `team2_confirmed` = 1)';
    $whereClause_base .= ' OR (`team1_confirmed` = 1 AND `team2_confirmed` = 0))';
    $whereClause_base .= ' AND `admin_confirmed` = 0';

    $selectQuery = cup_query(
        "SELECT
                `teamID`
            FROM `" . PREFIX . "cups_teams_member`
            WHERE `userID` = " . $userID . " AND `active` = 1",
        __FILE__
    );

    while ($get = mysqli_fetch_array($selectQuery)) {

        $team_id = $get[getConstNameTeamId()];

        $whereClause = $whereClause_base . ' AND (`team1` = ' . $team_id . ' OR `team2` = ' . $team_id . ')';
        $whereClause .= ' AND `mode` != \'1on1\'';

        $matchQuery = cup_query(
            "SELECT
                    cmp.`matchID` AS `match_id`,
                    cmp.`cupID` AS `cup_id`,
                    cmp.`team1` AS `team1_id`,
                    cmp.`team2` AS `team2_id`,
                    c.`mode` AS `cup_mode`
                FROM `" . PREFIX . "cups_matches_playoff` cmp
                JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
                WHERE " . $whereClause,
            __FILE__
        );

        while ($getMatch = mysqli_fetch_array($matchQuery)) {

            $opponent = ($getMatch['team1_id'] == $userID) ?
                $getMatch['team2_id'] : $getMatch['team1_id'];

            $opponent_name = getnickname($opponent);

            $matchArray[] = array(
                'match_id' =>  $getMatch['match_id'],
                getConstNameCupIdWithUnderscore() => $getMatch[getConstNameCupIdWithUnderscore()],
                'opponent' => $opponent_name
            );

        }

    }

    $whereClause = $whereClause_base . ' AND (`team1` = ' . $userID . ' OR `team2` = ' . $userID . ')';
    $whereClause .= ' AND `mode` = \'1on1\'';

    $matchQuery = cup_query(
        "SELECT
                cmp.`matchID` AS `match_id`,
                cmp.`cupID` AS `cup_id`,
                cmp.`team1` AS `team1_id`,
                cmp.`team2` AS `team2_id`,
                c.`mode` AS `cup_mode`
            FROM `" . PREFIX . "cups_matches_playoff` cmp
            JOIN `" . PREFIX . "cups` c ON cmp.`cupID` = c.`cupID`
            WHERE " . $whereClause,
        __FILE__
    );

    while ($getMatch = mysqli_fetch_array($matchQuery)) {

        $opponent = ($getMatch['team1_id'] == $team_id) ?
            $getMatch['team2_id'] : $getMatch['team1_id'];

        $opponent_name = getteam($opponent, 'name');

        $matchArray[] = array(
            'match_id' =>  $getMatch['match_id'],
            getConstNameCupIdWithUnderscore() => $getMatch[getConstNameCupIdWithUnderscore()],
            'opponent' => $opponent_name
        );

    }

    $anzMatches = count($matchArray);
    if ($anzMatches > 0) {

        $base_url = 'index.php?site=cup&amp;action=match&amp;id=';

        $matchList = '';
        for ($x = 0; $x < $anzMatches; $x++) {

            if (!isset($matchArray[$x]['match_id'])) {
                throw new \UnexpectedValueException('unknown_array_entry (`match_id`)');
            }

            if (!isset($matchArray[$x][getConstNameCupIdWithUnderscore()])) {
                throw new \UnexpectedValueException('unknown_array_entry (`cup_id`)');
            }

            if (!isset($matchArray[$x]['opponent'])) {
                throw new \UnexpectedValueException('unknown_array_entry (`opoonent`)');
            }

            $url = $base_url . $matchArray[$x][getConstNameCupIdWithUnderscore()] . '&amp;mID=' . $matchArray[$x]['match_id'];

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