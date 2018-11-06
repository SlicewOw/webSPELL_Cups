<?php

$returnArray = array(
	'status'	=> FALSE,
	'message'	=> array(),
	'html'		=> ''
);

try {
	
	$_language->readModule('cups');

	if($getAction == 'table') {

		include($dir_cup . '/admin/includes/admin_team.php');

		if(isset($adminList) && !empty($adminList)) {

			$returnArray['status'] = TRUE;
			$returnArray['html'] = $adminList;

		}

	} else {

		$admin_id = (isset($_POST['admin_id']) && validate_int($_POST['admin_id'])) ? 
			(int)$_POST['admin_id'] : 0;
		
		if($admin_id < 1) {
			throw new \Exception('admin not found');
		}
		
		$user_id = (isset($_POST['user_id']) && validate_int($_POST['user_id'])) ? 
			(int)$_POST['user_id'] : 0;

		if($user_id < 1) {
			throw new \Exception('user not found');
		}

		if($getAction == 'addAdmin' || $getAction == 'editAdmin') {

			$position = (isset($_POST['position'])) ? $_POST['position'] : '';
			if(empty($position)) {
				throw new \Exception('no position selected');
			}

			$description = (isset($_POST['description'])) ? 
				getinput($_POST['description']) : '';

			if($getAction == 'addAdmin') {

				$query = mysqli_query(
					$_database,
					"INSERT INTO `".PREFIX."cups_team`
						(
							userID,
							date,
							position,
							description
						)
						VALUES
						(
							" . $user_id . ",
							" . time() . ",
							'" . $position . "',
							'".$description."'
						)"
				);

				if(!$query) {
					throw new \Exception('insert query failed');
				}

			} else {

				$query = mysqli_query(
					$_database,
					"UPDATE `".PREFIX."cups_team`
						SET	date = " . time() . ",
							position = '" . $position . "',
							description = '" . $description . "'
						WHERE userID = " . $user_id
				);

				if(!$query) {
					throw new \Exception('update query failed');
				}

				$returnArray['message'][] = $description;

			}

			$returnArray['status'] = TRUE;

		} else if($getAction == 'deleteAdmin') {

			$query = mysqli_query(
				$_database,
				"DELETE FROM `" . PREFIX . "cups_team`
					WHERE userID = " . $user_id
			); 

			if(!$query) {
				throw new \Exception('error while deleting');
			}

			$returnArray['status'] = TRUE;

		} else {
			throw new \Exception('unknown_action');
		}

	}

} catch(Exception $e) {
	$returnArray['message'][] = $e->getMessage();
}


echo json_encode($returnArray);