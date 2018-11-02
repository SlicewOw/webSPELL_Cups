<?php

$teamArray = array(
	'action' => 'setplayers',
	'list' => array(),
	'details' => array()
);

try {
	
	if (!isset($cup_id)) {
		$cup_id = (isset($cupID)) ? $cupID : 0;
	}

	if ($cup_id < 1) {
		throw new \Exception('unknown_cup_id');
	}

	if (!isset($gameArray) || !is_array($gameArray)) {
		$cupArray 	= getcup($cup_id);
		$gameArray 	= getGame($cupArray['game']);	
	}

	$query = mysqli_query(
		$_database, 
		"SELECT 
				`teamID` 
			FROM `" . PREFIX . "cups_teilnehmer` 
			WHERE `cupID` = " . $cup_id . " AND `checked_in` = 1"
	);
	
	if (!$query) {
		throw new \Exception('cups_teilnehmer_query_select_failed');
	}
	
	if (mysqli_num_rows($query) < 1) {
		throw new \Exception('no_teams_found');
	}
	
	while ($get = mysqli_fetch_array($query)) {

		$team_id = $get['teamID'];

		//
		// Team Data
		$teamData = getteam($team_id);

		//
		// Set Team Data
		$teamArray['list'][] = $team_id;
		$teamArray['details'][$team_id] = array(
			'name' 		=> addslashes($teamData['name']),
			'player' 	=> array()
		);

		$subquery = mysqli_query(
			$_database, 
			"SELECT * FROM `" . PREFIX . "cups_teams_member` 
				WHERE `teamID` = " . $team_id . " AND `active` = 1"
		);
	
		if (!$subquery) {
			throw new \Exception('cups_teams_member_query_select_failed');
		}

		while ($subget = mysqli_fetch_array($subquery)) {

			$user_id = $subget['userID'];

			//
			// Nickname
			$playerArray['name'] = addslashes(getnickname($user_id));	

			//
			// Gameaccount
			$gameaccount = gameaccount($user_id, 'get', $gameArray['tag']);
			if (!empty($gameaccount)) {

				if (in_array($gameArray['tag'], $checkSteamGameaccount)) {
					$gameaccount = preg_replace('/\s+/', '', $gameaccount);
					$gameaccount = str_replace(' ', '', $gameaccount);
					$gameaccount = trim($gameaccount);
					$gameaccount = 'STEAM_' . $gameaccount;
				}
				
				$playerArray['gameaccount']	= $gameaccount;		

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

	$value = json_encode($teamArray);

	//
	// Daten in JSON Datei schreiben
	$filepath = $dir_global . 'pages/cup/tmp/';
	$datei = fopen($filepath.'cup_setplayer_'.$cup_id.'.json', 'w');
	if (fwrite($datei, $value, strlen($value))) {
		$status = TRUE;	
	}
	fclose($datei);

	// TODO: URL aendern
	$file = $bot_url . '/cup/index.php?q=cup_setplayer_'.$cup_id;
	if ($contents = file_get_contents($file)) {

		$dataArray = json_decode($contents, TRUE);
		if ($dataArray['status'] == 'true') {

		}

	}

} catch (Exception $e) {
	echo showError($e->getMessage());
}
