<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'])) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    if (!checkIfContentExists($cup_id, 'cupID', 'cups')) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    $maxPrices = 6;

	if (validate_array($_POST, true)) {

		$parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id;

		try {

			if (isset($_POST['edit'])) {

				$cupname = (isset($_POST['cupname']) && !empty($_POST['cupname'])) ?
					getinput($_POST['cupname']) : '';

				if(empty($cupname)) {
					throw new \Exception($_language->module['cup_noname']);
				}

				$priority = (isset($_POST['priority'])) ?
					getinput($_POST['priority']) : 'normal';

				$registration = (isset($_POST['registration'])) ?
					getinput($_POST['registration']) : 'open';

				$elimination = (isset($_POST['elimination'])) ?
					getinput($_POST['elimination']) : 'single';

				$date_checkin = strtotime($_POST['date_checkin']);
				$date_checkin += ((int)$_POST['hour_ci'] * 3600);
				$date_checkin += ((int)$_POST['minute_ci'] * 60);

				$date_start = strtotime($_POST['date_start']);
				$date_start += ((int)$_POST['hour'] * 3600);
				$date_start += ((int)$_POST['minute'] * 60);

				$game_tag = (isset($_POST['game'])) ? 
					getinput($_POST['game']) : 'csg';
				$gameArray = getGame($game_tag);

				$mode = (isset($_POST['mode'])) ? 
					getinput($_POST['mode']) : '5on5';

				$ruleID = (isset($_POST['ruleID']) && validate_int($_POST['ruleID'])) ?
					(int)$_POST['ruleID'] : 0;

				$size = (isset($_POST['size']) && validate_int($_POST['size'])) ?
					(int)$_POST['size'] : 32;

				$pps = (isset($_POST['max_pps']) && validate_int($_POST['max_pps'])) ?
					(int)$_POST['max_pps'] : 12;

				$admin_visible 	= (isset($_POST['admin_visible']) && validate_int($_POST['admin_visible'])) ?
					(int)$_POST['admin_visible'] : 0;

				$query = mysqli_query(
					$_database,
					"UPDATE `" . PREFIX . "cups`
						SET	priority = '".$priority."',
							name = '".$cupname."',
							registration = '".$registration."',
							checkin_date = " . $date_checkin . ",
							start_date = " . $date_start . ",
							game = '".$gameArray['tag']."',
							gameID = " . $gameArray['id'] . ",
							elimination = '".$elimination."',
							mode = '".$mode."',
							ruleID = " . $ruleID . ",
							max_size = '".$size."',
							max_penalty = ".$pps.",
							admin_visible = '".$admin_visible."'
						WHERE cupID = " . $cup_id
				);

				if (!$query) {
					throw new \Exception($_language->module['query_update_failed']);
				}

				$_SESSION['successArray'][] = $_language->module['query_saved'];

				//
				// Speichere Preise
				for ($x = 1; $x < ($maxPrices + 1); $x++) {

					if (isset($_POST['preis'][$x])) {

						$preis = $_POST['preis'][$x];

						if (!empty($preis)) {

							$anz = mysqli_num_rows(
								mysqli_query(
									$_database,
									"SELECT preisID FROM ".PREFIX."cups_preise
										WHERE cupID = " . $cup_id . " AND platzierung = " . $x
								)
							);
							if($anz) {
								mysqli_query(
									$_database,
									"UPDATE `".PREFIX."cups_preise`
										SET preis = '" . $preis . "'
										WHERE cupID = " . $cup_id . " AND platzierung = " . $x
								);
							} else {

								mysqli_query(
									$_database,
									"INSERT INTO `".PREFIX."cups_preise`
										(
											`cupID`,
											`preis`,
											`platzierung`
										)
										VALUES
										(
											" . $cup_id . ",
											'" . $preis . "',
											'" . $x . "'
										)"
								);

							}

						}

					}

				}

			}

		} catch (Exception $e) {

			$_SESSION['errorArray'][] = $e->getMessage();

			$parent_url = 'admincenter.php?site=cup&mod=cup&action=edit&id=' . $cup_id;

		}

		header('Location: ' . $parent_url);

	} else {

		$status = 1;
		$error = '';

		//
		// Cup Array
		$cupArray = getcup($cup_id);

		$cupOptions = getCupOption();

		if ($cupArray['admin'] == 1) {
			$admin_only = '<option value="1" selected="selected">'.$_language->module['yes'].'</option><option value="0">'.$_language->module['no'].'</option>';
		} else {
			$admin_only = '<option value="1">'.$_language->module['yes'].'</option><option value="0" selected="selected">'.$_language->module['no'].'</option>';
		}

		$priority = str_replace(
			'value="'.$cupArray['priority'].'"', 
			'value="'.$cupArray['priority'].'" selected="selected"', 
			$cupOptions['priority']
		);

		$registration = str_replace(
			'value="'.$cupArray['registration'].'"', 
			'value="'.$cupArray['registration'].'" selected="selected"', 
			$cupOptions['registration']
		);

		$elimination = str_replace(
			'value="'.$cupArray['elimination'].'"', 
			'value="'.$cupArray['elimination'].'" selected="selected"', 
			$cupOptions['elimination']
		);

		$days = '';
		$months = '';
		$hours = '';
		for ($i = 1; $i < 32; $i++) {
			$value = ($i < 10) ? '0'.$i : $i;
			$days .= '<option value="'.$value.'">'.$value.'</option>';
			if ($i<13) {
				$months .= '<option value="'.$value.'">'.$value.'</option>';
			}
			if ($i<25) {
				$hours .= '<option value="'.$value.'">'.$value.'</option>';
			}
		}

		$years = '';
		for ( $i=2014; $i<2018; $i++ ) {
			$years .= '<option value="'.$i.'">'.$i.'</option>';
		}

		$minutes = '';
		for ($i=0;$i<4;$i++) {
			$value = (($i * 15) > 0) ? ($i * 15) : '00';
			$minutes .= '<option value="'.$value.'">'.$value.'</option>';
		}

		$date_checkin = date('Y-m-d', $cupArray['checkin']);

		$hours_ci = str_replace(
			'value="'.date('H', $cupArray['checkin']).'"', 
			'value="'.date('H', $cupArray['checkin']).'" selected="selected"', 
			$hours
		);
		$minutes_ci = str_replace(
			'value="'.date('i', $cupArray['checkin']).'"', 
			'value="'.date('i', $cupArray['checkin']).'" selected="selected"', 
			$minutes
		);

		$date_start = date('Y-m-d', $cupArray['start']);

		$hours_sd = str_replace(
			'value="'.date('H', $cupArray['start']).'"', 
			'value="'.date('H', $cupArray['start']).'" selected="selected"', 
			$hours
		);
		$minutes_sd = str_replace(
			'value="'.date('i', $cupArray['start']).'"', 
			'value="'.date('i', $cupArray['start']).'" selected="selected"', 
			$minutes
		);

		$game_id = getGame($cupArray['game'], 'id');

		$games = getGamesAsOptionList($game_id, FALSE);

		$mode = str_replace(
			'value="'.$cupArray['mode'].'"', 
			'value="'.$cupArray['mode'].'" selected="selected"', 
			$cupOptions['mode']
		);

		$mappool = getMappool($cupArray['mappool'], 'list');

		$rules = getrules($cupArray['rule_id'], 'list', true);

		$size = str_replace(
			'value="'.$cupArray['size'].'"', 
			'value="'.$cupArray['size'].'" selected="selected"', 
			$cupOptions['size']
		);

		$pps = str_replace(
			'value="'.$cupArray['max_pps'].'"', 
			'value="'.$cupArray['max_pps'].'" selected="selected"', 
			$cupOptions['penalty']
		);

		$preisArray = array();
		$preisQuery = mysqli_query(
			$_database, 
			"SELECT * FROM `" . PREFIX . "cups_preise` 
				WHERE `cupID` = " . $cup_id
		);
		while ($dx = mysqli_fetch_array($preisQuery)) {
			if (!empty($dx['preis'])) {
				$preisArray[$dx['platzierung']] = $dx['preis'];
			}
		}

		if ($status) {

			$data_array = array();
			$data_array['$title'] 			= $_language->module['cup_add'].' - '.$cupArray['name'];
			$data_array['$cupID'] 			= $cup_id;
			$data_array['$error'] 			= $error;
			$data_array['$cupname'] 		= $cupArray['name'];
			$data_array['$admin_only'] 		= $admin_only;
			$data_array['$priority'] 		= $priority;
			$data_array['$registration'] 	= $registration;
			$data_array['$elimination'] 	= $elimination;
        	$data_array['$date_checkin'] 	= $date_checkin;
			$data_array['$hours_ci'] 		= $hours_ci;
			$data_array['$minutes_ci'] 		= $minutes_ci;
        	$data_array['$date_start'] 		= $date_start;
			$data_array['$hours_sd'] 		= $hours_sd;
			$data_array['$minutes_sd'] 		= $minutes_sd;
			$data_array['$games'] 			= $games;
			$data_array['$mode'] 			= $mode;
			$data_array['$rules'] 			= $rules;
			$data_array['$size'] 			= $size;
			$data_array['$pps'] 			= $pps;

			for ($x = 1; $x < ($maxPrices + 1); $x++) {
				$data_array['$preis' . $x] = (isset($preisArray[$x])) ? $preisArray[$x] : '';
			}

			$data_array['$postName'] 			= 'edit';
			$cups_edit = $GLOBALS["_template_cup"]->replaceTemplate("cups_action", $data_array);
			echo $cups_edit;

		}

	}
	
} catch (Exception $e) {
    echo showError($e->getMessage());
}
