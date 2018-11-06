<?php

$returnArray = array(
	'status' => FALSE,
	'error' => array(),
    'veto' => array(
        'status' => 'error',
        'maps' => '',
        'info' => ''
    )
);

try {
    
    $_language->readModule('cups', true, false);

    if (!$loggedin) {
        throw new \Exception($_language->module['access_denied']);
    }
    
    if (validate_array($_POST, true)) {
        
        $postAction = (isset($_POST['action'])) ?
            getinput($_POST['action']) : '';
        
        if (empty($postAction)) {
            throw new \Exception($_language->module['unknown_action']);
        }
        
        if ($postAction == 'voteMap') {
           
            $banned_map = (isset($_POST['map'])) ? 
                getinput($_POST['map']) : '';

            if (strlen($banned_map) == 0) {
                throw new \Exception('error_bann-map');
            }

            $cup_id = (isset($_POST['cup_id']) && validate_int($_POST['cup_id'], true)) ? 
                (int)$_POST['cup_id'] : 0;

            if ($cup_id < 1) {
                throw new \Exception('error_cup-id');
            }

            $match_id = (isset($_POST['match_id']) && validate_int($_POST['match_id'], true)) ? 
                (int)$_POST['match_id'] : 0;

            if ($match_id < 1) {
                throw new \Exception('error_match-id');
            }

            $team_id = (isset($_POST['team_id']) && preg_match('/team/', $_POST['team_id'])) ? 
                getinput($_POST['team_id']) : '';

            if (strlen($team_id) == 0) {
                throw new \Exception('error_team-id');
            }

            $selectQuery = mysqli_query(
                $_database, 
                "SELECT 
                        `format`, 
                        `mapvote`,
                        `team1`, 
                        `team2`, 
                        `maps` 
                    FROM `" . PREFIX . "cups_matches_playoff` 
                    WHERE matchID = " . $match_id . " AND cupID = " . $cup_id
            );

            if (!$selectQuery) {
                throw new \Exception($_language->module['query_select_failed']);
            }

            $get = mysqli_fetch_array($selectQuery);

            $cupArray = getCup($cup_id);

            if ($get['mapvote']) {
                throw new \Exception('error_mapvote');
            }

            //
            // Map-Array
            $mapsArray = unserialize($get['maps']);

            //
            // finalMapsLeft
            // 1 = bo1
            // 3 = bo3
            // 5 = bo5
            $cupFormat = strtolower($get['format']);
            if (preg_match('/bo/', $cupFormat)) {
                $finalMapsLeft = substr($cupFormat, 2, 1);	
            } else {
                $finalMapsLeft = 1;	
            }

            if ($finalMapsLeft < 1) {
                throw new \Exception('error_parsing_best-of');
            }

            $mapvote_status = '';
            $mapList = '';

            if ($finalMapsLeft == 1) {

                /**
                 * Bo1
                 **/

                //
                // Loesche Map aus Liste
                $arrayKey = array_keys($mapsArray['open'], $banned_map);
                if (isset($arrayKey[0]) && isset($mapsArray['open'][$arrayKey[0]])) {
                    unset($mapsArray['open'][$arrayKey[0]]);
                }
                $mapsArray['open'] = array_values($mapsArray['open']);

                //
                // Speichere gebannte Map
                if (!in_array($banned_map, $mapsArray['banned'][$team_id])) {
                    $mapsArray['banned'][$team_id][] = $banned_map;
                }

                $anzMapsOpen = count($mapsArray['open']);

                if ($anzMapsOpen == $finalMapsLeft) {

                    //
                    // Map Vote fertig
                    $mapsArray['picked'][] = $mapsArray['open'][0];
                    $mapsArray['open'] = array();
                    $mapvote_status = 'mapvote = \'1\', ';
                    $status = 'finished';

                } else {

                    //
                    // Liste der offenen Maps
                    $anzMapsOpen = count($mapsArray['open']);
                    for ($x = 0; $x < $anzMapsOpen; $x++) {
                        $mapList .= ' <span class="btn btn-default btn-sm">'.$mapsArray['open'][$x].'</span>';	
                    }

                    $returnArray['veto']['status'] = 'running';

                    $team_nr = ($team_id == 'team1') ? 2 : 1;
                    $info = '<span class="glyphicon glyphicon-info-sign"></span> '.$_language->module['cup_veto_team'];

                    if ($cupArray['mode'] == '1on1') {
                        $teamname = getnickname($get['team'.$team_nr]);
                    } else {
                        $teamname = getteam($get['team'.$team_nr], 'name');
                    }

                    $info = str_replace(
                        '%team%', 
                        '<span class="bold">'.$teamname.'</span>', 
                        $info
                    );

                }

            } else if ($finalMapsLeft == 3) {

                /**
                 * Bo3
                 **/

                $anzMapsOpen = count($mapsArray['open']);
                if ($anzMapsOpen <= $finalMapsLeft) {

                    /***********
                    * Map-Pick *
                    ***********/

                    //
                    // Loesche Map aus Liste
                    $arrayKey = array_keys($mapsArray['open'], $banned_map);
                    unset($mapsArray['open'][$arrayKey[0]]);
                    $mapsArray['open'] = array_values($mapsArray['open']);

                    //
                    // Speichere gebannte Map
                    if (!in_array($banned_map, $mapsArray['banned'][$team_id])) {
                        $mapsArray['picked'][$team_id][] = $banned_map;
                    }

                    $anzMapsOpen = count($mapsArray['open']);

                    //
                    // Liste der offenen Maps
                    $anzMapsOpen = count($mapsArray['open']);
                    for ($x = 0; $x < $anzMapsOpen; $x++) {
                        $mapList .= ' <span class="btn btn-default btn-sm">' . $mapsArray['open'][$x] . '</span>';	
                    }

                    $returnArray['veto']['status'] = 'running';

                    if ($anzMapsOpen == 1) {

                        //
                        // Map Vote fertig
                        $last_map = $mapsArray['open'][0];
                        $mapsArray['open'] = array();
                        $mapsArray['picked'][] = $last_map;

                        $mapvote_status = 'mapvote = \'1\', ';

                        $returnArray['veto']['status'] = 'finished';	

                    }

                    $team_nr = ($team_id == 'team1') ? 2 : 1;
                    $info = '<span class="glyphicon glyphicon-info-sign"></span> ' . $_language->module['cup_choose_team1'];

                    if ($cupArray['mode'] == '1on1') {
                        $teamname = getnickname($get['team'.$team_nr]);
                    } else {
                        $teamname = getteam($get['team'.$team_nr], 'name');
                    }

                    $info = str_replace(
                        '%team%', 
                        '<span class="bold">'.$teamname.'</span>', 
                        $info
                    );

                } else {

                    /***********
                    * Map-Bann *
                    ***********/

                    //
                    // Loesche Map aus Liste
                    $arrayKey = array_keys($mapsArray['open'], $banned_map);
                    unset($mapsArray['open'][$arrayKey[0]]);
                    $mapsArray['open'] = array_values($mapsArray['open']);

                    //
                    // Speichere gebannte Map
                    if (!in_array($banned_map, $mapsArray['banned'][$team_id])) {
                        $mapsArray['banned'][$team_id][] = $banned_map;
                    }

                    $anzMapsOpen = count($mapsArray['open']);

                    //
                    // Liste der offenen Maps
                    $anzMapsOpen = count($mapsArray['open']);
                    for ($x = 0; $x < $anzMapsOpen; $x++) {
                        $mapList .= ' <span class="btn btn-default btn-sm">' . $mapsArray['open'][$x] . '</span>';	
                    }

                    $returnArray['veto']['status'] = 'running';

                    $team_nr = ($team_id == 'team1') ? 2 : 1;
                    $info = '<span class="glyphicon glyphicon-info-sign"></span> ' . $_language->module['cup_veto_team'];

                    if ($cupArray['mode'] == '1on1') {
                        $teamname = getnickname($get['team'.$team_nr]);
                    } else {
                        $teamname = getteam($get['team'.$team_nr], 'name');
                    }

                    $info = str_replace(
                        '%team%', 
                        '<span class="bold">'.$teamname.'</span>', 
                        $info
                    );

                }

            } else if ($finalMapsLeft == 5) {

                /**
                 * Bo5
                 **/

                //
                // WIP
                //
                throw new \Exception($_language->module['unknown_action']);

            }

            //
            // Map Array speichern
            $maps = serialize($mapsArray);

            $query = mysqli_query(
                $_database, 
                "UPDATE `" . PREFIX . "cups_matches_playoff` 
                    SET " . $mapvote_status . "maps = '" . $maps . "' 
                    WHERE matchID = " . $match_id . " AND cupID = " . $cup_id
            );

            if (!$query) {
                throw new \Exception($_language->module['query_update_failed']);
            }

        } else {
            throw new \Exception($_language->module['unknown_action']);
        }
        
    } else {
        
        if ($getAction == 'updateMaps') {

            $cup_id = (isset($_GET['cup_id']) && validate_int($_GET['cup_id'], true)) ? 
                (int)$_GET['cup_id'] : 0;

            if ($cup_id < 1) {
                throw new \Exception('error_cup-id');
            }

            $match_id = (isset($_GET['match_id']) && validate_int($_GET['match_id'], true)) ? 
                (int)$_GET['match_id'] : 0;

            if ($match_id < 1) {
                throw new \Exception('error_match-id');
            }

            //
            // Cup Array
            $cupArray = getCup($cup_id);

            //
            // Match Array
            $matchArray = getmatch($match_id);

            //
            // finalMapsLeft
            // 1 = bo1
            // 3 = bo3
            // 5 = bo5
            $cupFormat = strtolower($matchArray['format']);
            if (substr($cupFormat, 0, 2) == 'bo') {
                $finalMapsLeft = substr($cupFormat, 2, strlen($cupFormat));	
            } else {
                $finalMapsLeft = 1;	
            }

            $mapsArray = unserialize($matchArray['maps']);
            $anzMapsOpen = count($mapsArray['open']);

            $team = ($anzMapsOpen % 2) ? 
                'team1' : 'team2';
            
            if ($cupArray['mode'] == '1on1') {
                
                if ($userID == $matchArray[$team . '_id']) {
                    $activeMapVote = TRUE;
                } else {
                    $activeMapVote = FALSE;
                }
                
            } else {
                
                if (($matchArray[$team . '_id'] > 0) && isinteam($userID, $matchArray[$team . '_id'], 'admin')) {
                    $activeMapVote = TRUE;
                } else {
                    $activeMapVote = FALSE;
                }

            }

            //
            // Anzeige offener Maps
            $mapList = '';
            for ($x = 0; $x < $anzMapsOpen; $x++) {

                if ($activeMapVote) {

                    $vote = 'voteMap(\''.$mapsArray['open'][$x].'\', \''.$team.'\');';
                    $mapList .= ' <button class="btn btn-default btn-sm" onclick="'.$vote.'">' . $mapsArray['open'][$x] . '</button>';	

                } else {
                    $mapList .= ' <span class="btn btn-default btn-sm">' . $mapsArray['open'][$x] . '</span>';	
                }

            }

            //
            // Info Text
            $info = '<span class="glyphicon glyphicon-info-sign"></span> ';
            if ($finalMapsLeft == 1) {

                $info .= (!$activeMapVote) ? 
                    $_language->module['cup_veto_team'] : 
                    $_language->module['map_vote_info'];

            } else if ($finalMapsLeft == 3) {

                if ($anzMapsOpen <= $finalMapsLeft) {

                    $info .= ($activeMapVote) ? 
                        $_language->module['cup_choose_team1'] : 
                        $_language->module['cup_choose_team2'];

                } else {

                    $info .= (!$activeMapVote) ? 
                        $_language->module['cup_veto_team'] : 
                        $_language->module['map_vote_info'];

                }			

            } else if ($finalMapsLeft == 5) {
                
                /**
                 * WiP
                 */
                throw new \Exception($_language->module['unknown_action']);
                
            }

            //
            // Teamname des aktiven Votes
            $team_nr = ($team == 'team1') ? 1 : 2;

            if ($cupArray['mode'] == '1on1') {
                $teamname = getnickname($matchArray['team' . $team_nr . '_id']);
            } else {
                $teamname = getteam($matchArray['team' . $team_nr . '_id'], 'name');
            }

            $info = str_replace(
                '%team%',
                '<span class="bold">' . $teamname . '</span>',
                $info
            );

            //
            // Vote-Status	
            if ($anzMapsOpen == 0) {
                $status = 'finished';	
            } else if (!$activeMapVote) {
                $status = 'waiting';	
            } else if ($activeMapVote) {
                $status = 'running';	
            } else {
                $status = 'unknown';	
            }

            $returnArray['veto']['status'] = $status;

        } else {
            throw new \Exception($_language->module['unknown_action']);
        }

    }

    if (isset($mapList)) {
        $returnArray['veto']['maps'] = $mapList;
    }
    
    if (isset($info)) {
        $returnArray['veto']['info'] = $info;
    }

    $returnArray['status'] = TRUE;
    
} catch (Exception $e) {
    $returnArray['error'][] = $e->getMessage();
}

echo json_encode($returnArray);
    