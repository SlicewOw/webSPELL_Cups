<?php

try {

    $_language->readModule('cups', false, true);
    
	$teamArray = array(
		'action' => 'setplayers',
		'list' => array(),
		'details' => array()
	);	
	
	$checkSteamGameaccount = array(
		'csg',
		'css',
		'cs'
	);

	if (!isset($cup_id)) {
		$cup_id = (isset($cupID)) ? $cupID : 0;
	}

	if ($cup_id < 1) {
		throw new \Exception($_language->module['unknown_cup_id']);
	}

	if (!isset($isAjax)) {
		$isAjax = FALSE;
	}

	//
	// Cup Array
	$cupArray = getcup($cup_id);

	if (!isset($gameArray) || !is_array($gameArray)) {
		$gameArray = getGame($cupArray['game']);	
	}

	$query = mysqli_query(
		$_database, 
		"SELECT `teamID` FROM `" . PREFIX . "cups_teilnehmer` 
			WHERE `cupID` = " . $cup_id . " AND `checked_in` = 1"
	);
	
	if (!$query) {
		throw new \Exception($_language->module['query_select_failed']);
	}
	
    if (mysqli_num_rows($query) < 1) {
		throw new \Exception($_language->module['no_participants']);
    }
    
	while ($get = mysqli_fetch_array($query)) {

		$team_id = $get['teamID'];

		//
		// Team Data
		$teamData = getteam($team_id);

		$varTeamName = str_replace(
			array('\'', '"',  'ä',  'Ä',  'ö',  'Ö',  'ü',  'Ü'), 
			array('',    '', 'ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue'), 
			addslashes($teamData['name'])
		);	

		//
		// Set Team Data
		$teamArray['list'][] = $team_id;
		$teamArray['details'][$team_id] = array(
			'name' => $varTeamName,
			'player' => array()
		);

		$subquery = mysqli_query(
			$_database, 
			"SELECT 
					ctm.*,
					u.`nickname`
				FROM `".PREFIX."cups_teams_member` ctm
				JOIN `" . PREFIX . "user` u ON ctm.`userID` = u.`userID`
				WHERE ctm.`teamID` = " . $team_id . " AND ctm.`active` = 1"
		);
	
        if (!$subquery) {
            throw new \Exception($_language->module['query_select_failed']);
        }
        
        if (mysqli_num_rows($subquery) < 1) {
        
            while ($subget = mysqli_fetch_array($subquery)) {

                //
                // User ID
                $user_id = $subget['userID'];

                //
                // Nickname
                $playerArray['name'] = getinput($subget['nickname']);	

                //
                // Gameaccount
                $gameaccount = gameaccount($user_id, 'get', $gameArray['tag']);
                if(!empty($gameaccount)) {

                    if(in_array($gameArray['tag'], $checkSteamGameaccount)) {
                        $gameaccount = preg_replace('/\s+/', '', $gameaccount);
                        $gameaccount = str_replace(' ', '', $gameaccount);
                        $gameaccount = trim($gameaccount);
                        $gameaccount = 'STEAM_'.$gameaccount;
                    }

                    $playerArray['gameaccount']	= $gameaccount;		

                    if($gameArray['tag'] == 'csg') {
                        $steam64_id = gameaccount($user_id, 'steam64', 'csg');
                        $playerArray['steam64_id'] = $steam64_id;		
                    }

                    //
                    // Level:
                    // 1: normal
                    // 2: Coach
                    // 3: Captain
                    // 4: Leader
                    // 5: Admin
                    $playerArray['level'] = (isinteam($user_id, $team_id, 'admin')) ? 3 : 1;

                    $teamArray['details'][$team_id]['player'][] = $playerArray;
                    unset($playerArray);

                }

            }

        }

	}

	$value = json_encode($teamArray);

	//
	// Daten in JSON Datei schreiben
	$filepath = $dir_global . 'pages/cup/tmp/';
	$datei = fopen($filepath . 'cup_setplayer_' . $cup_id . '.json', 'w');
	if (fwrite($datei, $value, strlen($value))) {
		$status = TRUE;	
	}
	fclose($datei);

	$file = 'http://myrisk.info:9100/index.php?q=cup_setplayer_' . $cup_id;
    
    $contents = @file_get_contents($file)
	if (!$contents) {

		if (!$isAjax) {
			echo '<div class="alert alert-danger">Player nicht &uuml;bertragen!</div>';
		}
        
        throw new \Exception($_language->module['error_file_get_contents']);
        
    }

    $dataArray = json_decode($contents, TRUE);
    if (!isset($dataArray['status'])) {
        throw new \Exception('unknown_attribute_status');
    }

    if ($dataArray['status'] != 'true') {
        throw new \Exception('error_status_not_true');
    }

    if (!is_array($dataArray['inserted'])) {
        throw new \Exception('error_converting_data_inserted');
    }

    if (!$isAjax) {

        if ($dataArray['inserted']['done'] > 0) {
            echo '<div class="alert alert-success">'.$dataArray['inserted']['done'].'</div>';
        }

        if ($dataArray['inserted']['exist'] > 0) {
            echo '<div class="alert alert-info">'.$dataArray['inserted']['exist'].'</div>';
        }

        if ($dataArray['inserted']['error'] > 0) {
            echo '<div class="alert alert-danger">'.$dataArray['inserted']['error'].'</div>';
        }

    }

} catch (Exception $e) {
	echo showError($e->getMessage());
}
