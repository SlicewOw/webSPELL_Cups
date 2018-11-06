<?php

$returnArray = array(
    'status'    => FALSE,
    'message'   => array()
);

try {

	if (!isanyadmin($userID)) {
		throw new \Exception('access_denied');
	}

	$actionArray = array(
		'changeSmurf',
		'deleteGameaccount'
	);

	if (!in_array($getAction, $actionArray)) {
		throw new \Exception('unknown_action');
	}

	if ($getAction == 'changeSmurf') {

		$gameaccount_id = (isset($_POST['gameacc_id']) && validate_int($_POST['gameacc_id'], true)) ? 
			(int)$_POST['gameacc_id'] : 0;
		
		if ($gameaccount_id < 1) {
			throw new \Exception('unknown_gameaccount_id');
		}

		$checkIf = mysqli_fetch_array(
			mysqli_query(
				$_database,
				"SELECT 
						COUNT(*) AS `exist`,
						`smurf`
					FROM `" . PREFIX . "cups_gameaccounts`
					WHERE `gameaccID` = " . $gameaccount_id
			)
		);

		if ($checkIf['exist'] != 1) {
			throw new \Exception('unknown_gameaccount');
		}

		$newSmurfValue = ($checkIf['smurf']) ? 0 : 1;

		$query = mysqli_query(
			$_database,
			"UPDATE `" . PREFIX . "cups_gameaccounts`
				SET `smurf` = " . $newSmurfValue . "
				WHERE `gameaccID` = " . $gameaccount_id
		);

		if (!$query) {
			throw new \Exception('query_update_failed');
		}

		$returnArray['status'] = TRUE;
		$returnArray['message'][] = 'gameaccount changed successfully';

	} else if ($getAction == 'deleteGameaccount') {

		
		$gameaccount_id = (isset($_POST['gameacc_id']) && validate_int($_POST['gameacc_id'], true)) ? 
			(int)$_POST['gameacc_id'] : 0;
		
		if($gameaccount_id < 1) {
			throw new \Exception('unknown_gameaccount_id');
		}

		$query = mysqli_query(
			$_database,
			"DELETE FROM `".PREFIX."cups_gameaccounts`
				WHERE `gameaccID` = " . $gameaccount_id
		);

		if (!$query) {
			throw new \Exception('query_delete_failed');
		}

		$returnArray['status'] = TRUE;
		$returnArray['message'][] = 'gameaccount deleted';

	}

} catch (Exception $e) {
	$returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
