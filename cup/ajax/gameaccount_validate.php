<?php

$returnArray = array(
	'status'	=> 0,
	'error'		=> array(),
	'data'		=> array(
		'gameaccount' 		=> '',
		'gameaccID'			=> 0,
		'steamcommunity_id' => '',
		'admin'				=> array()
	)
);

try {

	$_language->readModule('gameaccounts');
	
	if(!isset($loggedin) || !$loggedin) {
		throw new \Exception($_language->module['access_denied']);
	}

	$getGame = (isset($_GET['game']) && (strlen($_GET['game']) == 3)) ?
		getinput($_GET['game']) : '';

	//
	// Init
	$steamcommunity_url = '';
	$steamcommunity_nick = '';
	$steamcommunity_id = '';

	//
	// CSGO Account Validation
	$get = mysqli_fetch_array(
		mysqli_query(
			$_database, 
			"SELECT 
					COUNT(*) AS `exist`,
					`gameaccID` AS `gameaccount_id`, 
					`value` 
				FROM `".PREFIX."cups_gameaccounts`
				WHERE `userID` = " . $userID . " AND category = 'csg' AND deleted = 0"
		)
	);

	if($get['exist'] != 1) {
		throw new \Exception($_language->module['unknown_gameaccount']);
	}
	
	$gameaccount_id = $get['gameaccount_id'];

	$steam64_id = $get['value'];

	//
	// Get Steam API Data
	$SteamDataArray = getCSGOAccountInfo($steam64_id);
	
	if(!validate_array($SteamDataArray)) {
		throw new \Exception($_language->module['error_failed_steamrequest']);
	}

	if(!isset($SteamDataArray['status']) || ($SteamDataArray['status'] != 1)) {
		throw new \Exception($_language->module['error_failed_steamrequest']);
	}

	//
	// Steam API Data
	if(!is_array($SteamDataArray)) {
		throw new \Exception($_language->module['error_failed_steamrequest']);
	}
	
	if(!isset($SteamDataArray['steam_profile'])) {
		throw new \Exception($_language->module['error_failed_steamrequest']);
	}

	//
	// URL
	$steamcommunity_url = '';
	if(isset($SteamDataArray['steam_profile']['profileurl'])) {
		$steamcommunity_url = $SteamDataArray['steam_profile']['profileurl'];
	} elseif($steam64_id > 0) {
		$steamcommunity_url = 'https://steamcommunity.com/profiles/' . $steam64_id;
	} else {

		if(!in_array('error_profileurl', $returnArray['error'])) {
			throw new \Exception($_language->module['error_profileurl']);
		}

	}

	//
	// Nickname
	$steamcommunity_nick = '';
	if(isset($SteamDataArray['steam_profile']['personaname'])) {
		$steamcommunity_nick = $SteamDataArray['steam_profile']['personaname'];
	} else {

		if(!in_array('error_personaname', $returnArray['error'])) {
			throw new \Exception($_language->module['error_personaname']);
		}

	}

	//
	// Steam64 ID
	$steamcommunity_id = '';
	if(isset($SteamDataArray['steam_profile']['steamid'])) {
		$steamcommunity_id = $SteamDataArray['steam_profile']['steamid'];
	} else {

		if(!in_array('error_steamid', $returnArray['error'])) {
			throw new \Exception($_language->module['error_steamid']);
		}

	}

	$returnArray['data'] = array(
		'gameaccount_id' => $gameaccount_id,
		'steam_id' => $steam64_id,
		'name' => $steamcommunity_nick,
		'steamcommunity_id' => $steamcommunity_id,
		'steamcommunity_url' => $steamcommunity_url
	);

	if(!empty($steamcommunity_nick) && !empty($steamcommunity_url)) {
		$returnArray['status'] = TRUE;
	}

} catch(Exception $e) {
	$returnArray['error'][] = $e->getMessage();
}

echo json_encode($returnArray);
