<?php

$_language->readModule('cups', false, true);

try {

    if(!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    $maxEntries = 10;

    $base_cup_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=';
    $base_team_url = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;teamID=';
    $base_userlog_url = 'admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id=';

    $cupArray = array(
        'list'		=> array(),
        'details'	=> array()
    );

    $checkIf = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT COUNT(*) AS `exist` FROM `".PREFIX."cups`"
        )
    );

    $cupChartHits = '';

    if($checkIf['exist'] > 0) {

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
    for($x=0;$x<$maxEntries;$x++) {

        $cup_id = isset($arrayKeys[$x]) ?
            $arrayKeys[$x] : 0;

        if($cup_id > 0) {

            $cuphit_list .= '<a href="' . $base_cup_url . $cup_id . '" class="list-group-item">';
            $cuphit_list .= $cupArray['details'][$cup_id];
            $cuphit_list .= '<span class="pull-right grey">'.$cupArray['list'][$cup_id].' Hits</span>';
            $cuphit_list .= '</a>';

        }

    }

    $cupteams_list = '';
    $info = mysqli_query(
        $_database,
        "SELECT 
                `cupID`, 
                COUNT(cupID) AS `anz`
            FROM `".PREFIX."cups_teilnehmer` 
            WHERE checked_in = 1 
            GROUP BY cupID 
            ORDER BY COUNT(cupID) DESC 
            LIMIT 0, " . $maxEntries
    );
    while($ds = mysqli_fetch_array($info)) {

        $cupArray = getcup($ds['cupID']);

        $anzTeams = $ds['anz'];
        $maxTeams = $cupArray['size'];

        $relative = (int)(round($anzTeams / $maxTeams * 100));

        if($relative < 75) {
            $relativeText = '<span class="red">'.$relative.'%</span>';
        } else if($relative > 95) {
            $relativeText = '<span class="green darkshadow">'.$relative.'%</span>';
        } else {
            $relativeText = $relative.'%';
        }

        $cupteams_list .= '<a href="' . $base_cup_url . $ds['cupID'] . '" class="list-group-item">';
        $cupteams_list .= $cupArray['name'];
        $cupteams_list .= '<span class="pull-right grey">'.$anzTeams.' / '.$maxTeams.' ('.$relativeText.')</span>';
        $cupteams_list .= '</a>';

    }

    $cupteam_list = '';
    $info = mysqli_query(
        $_database,
        "SELECT 
                `teamID`, 
                COUNT(teamID) AS `anz`
            FROM ".PREFIX."cups_teilnehmer 
            WHERE checked_in = '1' 
            GROUP BY teamID 
            ORDER BY COUNT(teamID) DESC 
            LIMIT 0, " . $maxEntries
    );
    while($ds = mysqli_fetch_array($info)) {

        $cupteam_list .= '<a href="' . $base_team_url . $ds['teamID'] . '" class="list-group-item">';
        $cupteam_list .= getteam($ds['teamID'], 'name');
        $cupteam_list .= '<span class="pull-right grey">'.$ds['anz'].'</span>';
        $cupteam_list .= '</a>';

    }

    $matchhit_list = '';
    $info = mysqli_query(
        $_database,
        "SELECT 
              `matchID`, 
              `team1`, 
              `team2`, 
              `hits` 
            FROM ".PREFIX."cups_matches_playoff 
            WHERE admin = 0 
            ORDER BY hits DESC 
            LIMIT 0, " . $maxEntries
    );
    while($ds = mysqli_fetch_array($info)) {
        $name = getteam($ds['team1'], 'tag').' vs. '.getteam($ds['team2'], 'tag').' (#'.$ds['matchID'].')';
        $matchhit_list .= '<div class="list-group-item">'.$name.'<span class="pull-right grey">'.$ds['hits'].' Hits</span></div>';
    }

    $matchAnzArray = array();

    $info = mysqli_query(
        $_database,
        "SELECT 
              `team1`, 
              COUNT(*) AS `anz` 
            FROM ".PREFIX."cups_matches_playoff 
            WHERE admin = '0' AND team1 > 0
            GROUP BY team1
            ORDER BY COUNT(*) DESC"
    );
    while($ds = mysqli_fetch_array($info)) {
        $matchAnzArray[$ds['team1']] = $ds['anz'];
    }

    $info = mysqli_query(
        $_database,
        "SELECT 
              `team2`, 
              COUNT(*) AS `anz` 
              FROM ".PREFIX."cups_matches_playoff 
            WHERE admin = '0' AND team2 > 0
            GROUP BY team2
            ORDER BY COUNT(*) DESC"
    );
    while($ds = mysqli_fetch_array($info)) {
        if(isset($matchAnzArray[$ds['team2']])) {
            $matchAnzArray[$ds['team2']] += $ds['anz'];
        } else {
            $matchAnzArray[$ds['team2']] = $ds['anz'];
        }
    }

    arsort($matchAnzArray);

    $matchanz_list = '';
    $arrayKeys = array_keys($matchAnzArray);
    for($x=0;$x<(2 * $maxEntries);$x++) {

        $team_id = (isset($arrayKeys[$x])) ?
            $arrayKeys[$x] : 0;

        if($team_id > 0) {

            $matchanz_list .= '<a href="' . $base_team_url . $team_id . '" class="list-group-item">';
            $matchanz_list .= getteam($team_id, 'name');
            $matchanz_list .= '<span class="pull-right grey">'.$matchAnzArray[$team_id].'</span>';
            $matchanz_list .= '</a>';

        }

    }

    $info = mysqli_num_rows(
        mysqli_query(
            $_database,
            "SELECT gameaccID FROM ".PREFIX."cups_gameaccounts 
            WHERE active = '1' AND deleted = '0'"
        )
    );

    $gameacc_act_list = '<div class="list-group-item">Anzahl Gesamt<span class="pull-right grey">'.$info.'</span></div>';

    $info = mysqli_query(
        $_database,
        "SELECT
                `category`,
                COUNT(`category`) AS `anz`
            FROM `" . PREFIX . "cups_gameaccounts`
            WHERE `active` = 1 AND `deleted` = 0
            GROUP BY `category`
            ORDER BY COUNT(`category`) DESC"
    );

    $gameaccChartRowArray = array();
    while($ds = mysqli_fetch_array($info)) {

        $gameArray = getGame($ds['category']);

        if (validate_array($gameArray, true)) {

            $game_short = (!empty($gameArray['short'])) ?
                $gameArray['short'] : $gameArray['tag'];

            $gameaccChartRowArray[] = '[\'' . $game_short . '\', ' . $ds['anz'] . ']';

            $gameacc_act_list .= '<a href="admincenter.php?site=cup&amp;mod=gameaccounts&amp;cat='.$ds['category'].'" class="list-group-item">';
            $gameacc_act_list .= $gameArray['name'];
            $gameacc_act_list .= '<span class="pull-right grey">'.$ds['anz'].'</span>';
            $gameacc_act_list .= '</a>';

        }

    }

    $gameaccChartRows = implode(', ', $gameaccChartRowArray);

    for($x=0;$x<2;$x++) {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS anz FROM ".PREFIX."cups_gameaccounts_csgo 
                    WHERE validated = ".$x
            )
        );

        $getCSGOVal[$x] = $get['anz'];

    }

    $gameaccCSGOValidateRows = '';
    $gameaccCSGOValidateRows .= '[\'nicht validiert\', '.$getCSGOVal[0].']';
    $gameaccCSGOValidateRows .= ', [\'validiert\', '.$getCSGOVal[1].']';

    $info = mysqli_num_rows(
        mysqli_query(
            $_database,
            "SELECT gameaccID FROM ".PREFIX."cups_gameaccounts 
            WHERE active = '0' AND deleted = '1'"
        )
    );

    $gameacc_del_list = '<div class="list-group-item">Anzahl Gesamt<span class="pull-right grey">'.$info.'</span></div>';

    $info = mysqli_query(
        $_database,
        "SELECT 
            category, 
            COUNT(category) AS anz 
        FROM ".PREFIX."cups_gameaccounts 
        WHERE active = '0' AND deleted = '1' 
        GROUP BY category 
        ORDER BY COUNT(category) DESC 
        LIMIT 0, ".($maxEntries - 1)
    );
    while($ds = mysqli_fetch_array($info)) {

        $gameacc_del_list .= '<a href="admincenter.php?site=cup&amp;mod=gameaccounts&amp;cat='.$ds['category'].'" class="list-group-item">';
        $gameacc_del_list .= getgamename($ds['category']);
        $gameacc_del_list .= '<span class="pull-right grey">'.$ds['anz'].'</span>';
        $gameacc_del_list .= '</a>';

    }

    $gameacc_csgo_list = '';
    $getExtrema = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT 
                AVG(hours) AS mittel,
                MIN(hours) AS minimum,
                MAX(hours) AS maximum 
            FROM `".PREFIX."cups_gameaccounts_csgo` 
            WHERE hours > 0"
        )
    );
    $gameacc_csgo_list .= '<div class="list-group-item">Mittelwert &Oslash;<span class="pull-right grey">'.(int)$getExtrema['mittel'].' h</span></div>';
    $gameacc_csgo_list .= '<div class="list-group-item">Minimum <span class="pull-right grey">'.(int)$getExtrema['minimum'].' h</span></div>';
    $gameacc_csgo_list .= '<div class="list-group-item">Maximum <span class="pull-right grey">'.(int)$getExtrema['maximum'].' h</span></div>';

    $getExtrema = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT 
                COUNT(*) AS anz
            FROM `".PREFIX."cups_gameaccounts_csgo` 
            WHERE vac_bann != '0'"
        )
    );
    $gameacc_csgo_list .= '<div class="list-group-item">Accounts mit VAC-Bann <span class="pull-right grey">'.$getExtrema['anz'].'</span></div>';

    $getExtrema = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT 
                COUNT(*) AS anz
            FROM `".PREFIX."cups_gameaccounts_csgo` 
            WHERE hours = 0"
        )
    );
    $gameacc_csgo_list .= '<div class="list-group-item">0 Stunden <span class="pull-right grey">'.(int)$getExtrema['anz'].'</span></div>';

    $getExtrema = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT 
                COUNT(*) AS anz
            FROM `".PREFIX."cups_gameaccounts` 
            WHERE smurf = 1"
        )
    );
    $gameacc_csgo_list .= '<div class="list-group-item">Smurf Accounts <span class="pull-right grey">'.(int)$getExtrema['anz'].'</span></div>';

    /*
    * CSGO ACC LIST
    */

    $gameacc_acc_list = array(
        0 => '',
        1 => ''
    );

    for($x=0;$x<2;$x++) {

        $sortType = ($x<1) ? 'ASC' : 'DESC';

        $query = mysqli_query(
            $_database,
            "SELECT 
                a.gameaccID AS gameacc_id,
                a.hours AS hours_played,
                b.userID AS user_id,
                b.value AS value,
                b.active AS isActive,
                c.nickname AS nickname
            FROM `".PREFIX."cups_gameaccounts_csgo` a
            JOIN `".PREFIX."cups_gameaccounts` b ON a.gameaccID = b.gameaccID
            JOIN `".PREFIX."user` c ON b.userID = c.userID
            WHERE a.hours > 0 AND b.category = 'csg'
            ORDER BY a.hours ".$sortType."
            LIMIT 0, 5"
        );
        while($getExtrema = mysqli_fetch_array($query)) {

            $infoText = $getExtrema['nickname'].' <span class="pull-right grey">'.$getExtrema['hours_played'].'h - '.$getExtrema['value'];
            if(!$getExtrema['isActive']) {
                $infoText .= ' - gel&ouml;scht';
            }
            $infoText .= '</span>';

            $gameacc_acc_list[$x] .= '<a href="' . $base_userlog_url . $getExtrema['user_id'] . '" class="list-group-item">'.$infoText.'</a>';

        }

    }


    $teams_list = '';
    $info = mysqli_query(
        $_database,
        "SELECT name, hits FROM ".PREFIX."cups_teams 
        ORDER BY hits DESC 
        LIMIT 0, ".$maxEntries
    );
    while($ds = mysqli_fetch_array($info)) {
        $teams_list .= '<div class="list-group-item">'.$ds['name'].'<span class="pull-right grey">'.$ds['hits'].' Hits</span></div>';
    }

    $team_member_list = '';
    $info = mysqli_query(
        $_database,
        "SELECT 
          teamID, 
          COUNT(teamID) AS anz
        FROM ".PREFIX."cups_teams_member 
        WHERE active = '1' 
        GROUP BY teamID 
        ORDER BY COUNT(teamID) DESC 
        LIMIT 0, ".$maxEntries
    );
    while($ds = mysqli_fetch_array($info)) {
        $team_member_list .= '<div class="list-group-item">'.getteam($ds['teamID'], 'name').'<span class="pull-right grey">'.$ds['anz'].' Mitglieder</span></div>';
    }

    $totalTickets = mysqli_num_rows(
        mysqli_query(
            $_database,
            "SELECT ticketID FROM ".PREFIX."cups_supporttickets"
        )
    );
    $ticket_adm_list = '<div class="list-group-item">Anzahl Gesamt<span class="pull-right grey">'.$totalTickets.' Tickets</span></div>';
    $info = mysqli_query(
        $_database,
        "SELECT 
          adminID, 
          COUNT(adminID) AS anz
        FROM ".PREFIX."cups_supporttickets 
        WHERE adminID != '0' 
        GROUP BY adminID 
        ORDER BY COUNT(adminID) DESC 
        LIMIT 0, ".($maxEntries - 1)
    );
    while($ds = mysqli_fetch_array($info)) {

        $ticket_adm_list .= '<a href="admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id='.$ds['adminID'].'" class="list-group-item">';
        $ticket_adm_list .= getnickname($ds['adminID']);
        $ticket_adm_list .= '<span class="pull-right grey">'.$ds['anz'].' Tickets</span>';
        $ticket_adm_list .= '</a>';

    }

    $ticket_usr_list = '';
    $info = mysqli_query(
        $_database,
        "SELECT 
          userID, 
          COUNT(userID) AS anz
        FROM ".PREFIX."cups_supporttickets 
        WHERE userID != '0' 
        GROUP BY userID 
        ORDER BY COUNT(userID) DESC 
        LIMIT 0, ".$maxEntries
    );
    while($ds = mysqli_fetch_array($info)) {

        $ticket_usr_list .= '<a href="admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id='.$ds['userID'].'" class="list-group-item">';
        $ticket_usr_list .= getnickname($ds['userID']);
        $ticket_usr_list .= '<span class="pull-right grey">'.$ds['anz'].' Tickets</span>';
        $ticket_usr_list .= '</a>';
    }

    $ticket_cat_list = '';
    $info = mysqli_query(
        $_database,
        "SELECT 
          categoryID, 
          COUNT(categoryID) AS anz
        FROM ".PREFIX."cups_supporttickets 
        GROUP BY categoryID 
        ORDER BY COUNT(categoryID) DESC 
        LIMIT 0, ".$maxEntries
    );
    while($ds = mysqli_fetch_array($info)) {

        $ticketCategory = getticket($ds['categoryID'], 'category');
        if(!empty($ticketCategory)) {

            $ticket_cat_list .= '<div class="list-group-item">';
            $ticket_cat_list .= $ticketCategory;
            $ticket_cat_list .= '<span class="pull-right grey">'.$ds['anz'].' ('.(int)(($ds['anz'] / $totalTickets) * 100).'%)</span>';
            $ticket_cat_list .= '</div>';

        }

    }

    $adminlist = '';

    $data_array = array();
    $data_array['$cuphit_list'] 	    	= $cuphit_list;
    $data_array['$cupChartHits'] 	    	= $cupChartHits;
    $data_array['$cupteams_list'] 	    	= $cupteams_list;
    $data_array['$cupteam_list'] 	    	= $cupteam_list;
    $data_array['$matchhit_list'] 	    	= $matchhit_list;
    $data_array['$matchanz_list'] 	    	= $matchanz_list;
    $data_array['$gameacc_act_list']    	= $gameacc_act_list;
    $data_array['$gameaccChartRows']    	= $gameaccChartRows;
    $data_array['$gameacc_del_list'] 	    = $gameacc_del_list;
    $data_array['$gameacc_csgo_list']   	= $gameacc_csgo_list;
    $data_array['$gameacc_acc_min'] 	    = $gameacc_acc_list[0];
    $data_array['$gameacc_acc_max'] 	    = $gameacc_acc_list[1];
    $data_array['$gameaccCSGOValidateRows'] = $gameaccCSGOValidateRows;
    $data_array['$teams_list'] 		    	= $teams_list;
    $data_array['$team_member_list']    	= $team_member_list;
    $data_array['$ticket_adm_list'] 	    = $ticket_adm_list;
    $data_array['$ticket_usr_list'] 	    = $ticket_usr_list;
    $data_array['$ticket_cat_list'] 	    = $ticket_cat_list;
    $data_array['$adminlist'] 		    	= $adminlist;
    $stats_home = $GLOBALS["_template_cup"]->replaceTemplate("page_stats_home", $data_array);
    echo $stats_home;

} catch(Exception $e) {
    echo showError($e->getMessage());
}
