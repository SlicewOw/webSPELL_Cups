<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {

    $_language->readModule('gameaccounts', false, true);

    if (!iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    $postAction = (isset($_POST['action'])) ?
        getinput($_POST['action']) : '';

    if (empty($postAction)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    //
    // Gameaccount Klasse
    systeminc('classes/gameaccounts');

    if ($postAction == 'deleteBannedAccount') {
        
        $banned_id = (isset($_POST['banned_id']) && validate_int($_POST['banned_id'], true)) ?
            (int)$_POST['banned_id'] : 0;
        
        if ($banned_id < 1) {
            throw new \Exception($_language->module['unknown_gameaccount']);
        }
        
        $deleteQuery = mysqli_query(
            $_database,
            "DELETE FROM `" . PREFIX . "cups_gameaccounts_banned`
                WHERE `id` = " . $banned_id
        );

        if (!$deleteQuery) {
            throw new \Exception($_language->module['query_delete_failed']);
        }

        $returnArray['message'][] = $_language->module['query_deleted'];

    } else if ($postAction == 'addSmurf') {

		$smurfValue = (isset($_POST['smurfValue'])) ? 
			getinput($_POST['smurfValue']) : '';
		
		if (empty($smurfValue)) {
			throw new \Exception('unknown_value');
		}

		$smurfGame = (isset($_POST['smurfGame']) && (strlen($_POST['smurfGame']) == 3)) ? 
			$_POST['smurfGame'] : '';

		if (empty($smurfGame)) {
			throw new \Exception('unknown_game');
		} 

		$user_id = (isset($_POST['user_id']) && validate_int($_POST['user_id'])) ? 
			(int)$_POST['user_id'] : 0;
		
		if ($user_id < 1) {
			throw new \Exception('unknown_user');
		}
        
        $gameaccount = new \myrisk\gameaccount();
        $gameaccount->setGame($smurfGame);
        $gameaccount->setValue($smurfValue);

		$query = mysqli_query(
			$_database,
			"INSERT INTO `".PREFIX."cups_gameaccounts`
				(
					`userID`,
					`date`,
					`category`,
					`value`,
					`smurf`,
					`active`,
					`deleted`,
					`deleted_date`,
					`deleted_seen`
				)
				VALUES
				(
					" . $user_id . ",
					" . time() . ",
					'" . $smurfGame . "',
					'" . $gameaccount->getValue() . "',
					1,
					0,
					1,
					" . time() . ",
					1
				)"
		);

		if (!$query) {
			throw new \Exception('query_insert_failed');
		}

		$gameaccount_id = mysqli_insert_id($_database);

		if ($smurfGame == 'csg') {

			$subquery = mysqli_query(
				$_database,
				"INSERT INTO `".PREFIX."cups_gameaccounts_csgo`
					(
						`gameaccID`,
						`validated`,
						`date`,
						`hours`
					)
					VALUES
					(
						" . $gameaccount_id . ",
						1,
						" . time() . ",
						-1
					)"
			);

			if (!$subquery) {
				throw new \Exception('query_insert_failed');
			}

		}

	} else {
        throw new \Exception($_language->module['unknown_action']);
    }
    
    $returnArray['status'] = TRUE;
    
} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
