<?php

try {

    if (!$loggedin) {
        throw new \Exception('access_denied');
    }

    $anzMatches = 0;
    $matches = array();

    $whereClause_base = '((team1_confirmed = 0 AND team2_confirmed = 0)';
    $whereClause_base .= ' OR (team1_confirmed = 0 AND team2_confirmed = 1)';
    $whereClause_base .= ' OR (team1_confirmed = 1 AND team2_confirmed = 0))';
    $whereClause_base .= ' AND admin_confirmed = 0';

    $query = mysqli_query(
        $_database,
        "SELECT
                `teamID`
            FROM `" . PREFIX . "cups_teams_member`
            WHERE userID = " . $userID . " AND active = 1"
    );

    if (!$query) {
        throw new \Exception('query_select_failed');
    }

    while ($get = mysqli_fetch_array($query)) {

        $team_id = $get['teamID'];

        $whereClause = $whereClause_base.' AND (team1 = ' . $team_id . ' OR team2 = ' . $team_id . ')';

        $subquery = mysqli_query(
            $_database,
            "SELECT
                    a.matchID,
                    a.cupID,
                    a.team1,
                    a.team2,
                    b.mode
                FROM `".PREFIX."cups_matches_playoff` a
                JOIN `".PREFIX."cups` b ON a.cupID = b.cupID
                WHERE " . $whereClause
        );

        if (!$subquery) {
            throw new \Exception('query_select_failed');
        }

        while ($subget = mysqli_fetch_array($subquery)) {

            $match_id = $subget['matchID'];
            $matches['list'][] = $match_id;	

            if($subget['mode'] != '1on1') {

                $opponent = ($subget['team1'] == $team_id) ? 
                    $subget['team2'] : $subget['team1'];

            } else {

                $opponent = ($subget['team1'] == $userID) ? 
                    $subget['team2'] : $subget['team1'];

            }

            $matches[$match_id] = array(
                'cupID' 	=> $subget['cupID'],
                'opponent'	=> $opponent
            );

            $anzMatches++;

        }

    }

    if ($anzMatches > 0) {

        $_language->readModule('cups');

        $base_url = 'index.php?site=cup&amp;action=match&amp;id=';

        $text = ($anzMatches > 1) ? 
            $anzMatches.' '.$_language->module['open_matches'] : '1 '.$_language->module['open_match'];

        $matchList = '';
        for($x=0;$x<$anzMatches;$x++) {
            $match_id = $matches['list'][$x];
            $url = $base_url . $matches[$match_id]['cupID'].'&amp;mID='.$match_id;
            $matchList .= '<li><a href="'.$url.'">Match vs. '.getteam($matches[$match_id]['opponent'], 'name').'</a></li>';	
        }

        $data_array = array();
        $data_array['$text'] = $text;
        $data_array['$matchList'] = $matchList;
        $activeMatches = $GLOBALS["_template_cup"]->replaceTemplate("navigation_active_matches", $data_array);

    }

} catch (Exception $e) {}