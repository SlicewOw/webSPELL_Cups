<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {

	if(!iscupadmin($userID)) {
		throw new \Exception('access_denied');
	}
	
	$gameacc_id = (isset($_GET['gameacc_id']) && validate_int($_GET['gameacc_id'])) ? 
		(int)$_GET['gameacc_id'] : 0;

	if($gameacc_id < 1) {
		throw new \Exception('unknown_gameaccount');
	}
	
	$steam_id = (isset($_GET['steam_id'])) ? 
		getinput($_GET['steam_id']) : '';

	if(empty($steam_id)) {
		throw new \Exception('unknown_steam_id');
	}

	$get = mysqli_fetch_array(
		mysqli_query(
			$_database,
			"SELECT 
					COUNT(*) AS `anz` 
				FROM `".PREFIX."cups_gameaccounts_csgo` cg_csgo
				JOIN `" . PREFIX . "cups_gameaccounts` cg ON cg_csgo.`gameaccID = cg.`gameaccID`
				WHERE cg_csgo.`gameaccID` = " . $gameacc_id . " AND cg.`value` = '" . $steam_id . "'"
		)
	);	

	$accountDetails = getCSGOAccountInfo($steam_id);
	
	$hours_played = $accountDetails['csgo_stats']['time_played']['hours'];

	$isBanned = (empty($accountDetails['vac_status']['VACBanned'])) ? 
		0 : 1;

	$bannDate = 0;
	if($isBanned) {
		$dateNow = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		$bannDays = 86400 * $accountDetails['vac_status']['DaysSinceLastBan'];
		$bannDate = $dateNow - $bannDays;
	}

	if($get['anz'] > 0) {
		
		$query = mysqli_query(
			$_database,
			"UPDATE `" . PREFIX . "cups_gameaccounts_csgo`
                SET hours = " . $hours_played . ",
                    date = " . time() . ",
                    vac_bann = " . $isBanned . ",
                    bann_date = " . $bannDate . "
                WHERE `gameaccID` = " . $gameacc_id
		);
		
	} else {
		
		$query = mysqli_query(
			$_database,
			"INSERT INTO `".PREFIX."cups_gameaccounts_csgo`
				(
					`gameaccID`,
					`date`, 
					`hours`, 
					`vac_bann`, 
					`bann_date`
				)
				VALUES
				(
					" . $gameacc_id . ", 
					" . time() . ", 
					" . $hours_played . ", 
					" . $isBanned . ", 
					" . $bannDate . "
				)"
		);
		
	}

	if(!$query) {
		throw new \Exception('query failed');
	} 

} catch(Excepton $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
