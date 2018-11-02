<?php

try {

    $_language->readModule('cups', false, true);
    
	if(!$loggedin || !iscupadmin($userID)) {
		throw new \Exception($_language->module['access_denied']);
	}

	$maxMaps = 10;

	function getMapList() {

		global $maxMaps;

		$mapList = '';

		for($x=1;$x<($maxMaps + 1);$x++) {

			$isRequired = ($x == 1) ? ' required="required"' : '';
			$value = (isset($mapArray[$x-1])) ? $mapArray[$x-1] : '';

			$mapList .= '<input type="text" name="map'.$x.'" placeholder="Map '.$x.'" value="'.$value.'" class="form-control"'.$isRequired.' />';	

			if($x < $maxMaps) {
				$mapList .= '<br />';
			}

		}

		return $mapList;

	}

	if (validate_array($_POST, true)) {

		$parent_url = 'admincenter.php?site=cup&mod=mappool';

		try {

			if(isset($_POST['submitDeleteMapPool'])) {

				$mappool_id = (isset($_POST['mappool_id']) && validate_int($_POST['mappool_id'])) ?
					(int)$_POST['mappool_id'] : 0;

				if($mappool_id < 1) {	
					throw new \Exception($_language->module['error_id']);
				}

				$name = (isset($_POST['name'])) ? 
					getinput($_POST['name']) : '';
				
				$query = mysqli_query(
					$_database, 
					"UPDATE `".PREFIX."cups_mappool`  
						SET deleted = 1
						WHERE mappoolID = " . $mappool_id
				);

				if(!$query) {	
					throw new \Exception($_language->module['query_delete_failed']);
				}

				$text = 'Map Pool "'.$name.'" wurde gel&ouml;scht';

			} else if(isset($_POST['submitAddMapPool']) || isset($_POST['submitEditMapPool'])) {

				$name = getinput($_POST['name']);

				$game_tag = $_POST['game'];
				$gameArray = getGame($game_tag);

				$mapArray = array();
				for($x=1;$x<($maxMaps + 1);$x++) {
					if(isset($_POST['map'.$x]) && !empty($_POST['map'.$x])) {
						$mapArray[] = $_POST['map'.$x];
					}
				}
				$maps = serialize($mapArray);	

				if(isset($_POST['submitAddMapPool'])) {

					$query = mysqli_query(
						$_database, 
						"INSERT INTO `" . PREFIX . "cups_mappool` 
							(
								name, 
								game, 
								gameID, 
								maps
							) 
							VALUES 
							(
								'".$name."', 
								'".$game_tag."', 
								'".$gameArray['id']."', 
								'".$maps."'
							)"
					);

					if(!$query) {
						throw new \Exception($_language->module['query_insert_failed']);
					}

					$mappool_id = mysqli_insert_id($_database);
                    
                    $text = 'Map Pool "'.$name.'" wurde hinzugef&uuml;gt';

				} else {

					$mappool_id = (isset($_POST['mappool_id']) && validate_int($_POST['mappool_id'])) ?
						(int)$_POST['mappool_id'] : 0;

					if($mappool_id < 1) {
						throw new \Exception($_language->module['error_id']);	
					}
					
					$query = mysqli_query(
						$_database, 
						"UPDATE `".PREFIX."cups_mappool` 
							SET name = '" . $name . "', 
								game = '" . $game_tag . "', 
								gameID = " . $gameArray['id'] . ", 
								maps = '" . $maps . "' 
							WHERE mappoolID = " . $mappool_id
					);

					if(!$query) {
						throw new \Exception($_language->module['query_update_failed']);
					}

					$text = 'Map Pool "'.$name.'" wurde editiert';
				}

			}

            if (isset($text) && !empty($text)) {
                $_SESSION['successArray'][] = $text;
            }
            
		} catch(Exception $e) {
			$_SESSION['errorArray'][] = $e->getMessage();	
		}

		header('Location: ' . $parent_url);

	} else {

		if($getAction == 'add') {

			$data_array = array();
			$data_array['$title'] 		= $_language->module['add'];
			$data_array['$name'] 		= '';
			$data_array['$games'] 		= getGamesAsOptionList();
			$data_array['$mapList'] 	= getMapList();
			$data_array['$mappool_id'] 	= 0;
			$data_array['$postName'] 	= 'submitAddMapPool';
			$pool_add = $GLOBALS["_template_cup"]->replaceTemplate("cup_mappool_action", $data_array);
			echo $pool_add;	

		} else if($getAction == 'edit') {

			$mappool_id = (isset($_GET['id']) && validate_int($_GET['id'])) ?
				(int)$_GET['id'] : 0;

			if($mappool_id < 1) {
				throw new \Exception($_language->module['error_id']);
			}

			$ds = mysqli_fetch_array(
				mysqli_query(
					$_database, 
					"SELECT name, game, gameID, maps FROM `".PREFIX."cups_mappool` 
						WHERE mappoolID = " . $mappool_id
				)
			);

			if(!empty($ds['maps'])) {

				$maps = unserialize($ds['maps']);
				$anzMaps = count($maps);
				for($x=0;$x<$maxMaps;$x++) {
					if($x<$anzMaps) {
						$map[] = $maps[$x];	
					} else {
						$map[] = '';
					}
				}

			} 

			$mapList = '';

			for($x=1;$x<($maxMaps + 1);$x++) {

				$isRequired = ($x == 1) ? ' required="required"' : '';

				$mapList .= '<input type="text" name="map'.$x.'" placeholder="Map '.$x.'" value="'.$map[$x - 1].'" class="form-control"'.$isRequired.' />';	

				if($x < $maxMaps) {
					$mapList .= '<br />';
				}

			}   

			$data_array = array();
			$data_array['$title'] 		= $_language->module['edit'];
			$data_array['$name'] 		= $ds['name'];
			$data_array['$games'] 		= getGamesAsOptionList($ds['game']);
			$data_array['$mapList'] 	= $mapList;
			$data_array['$mappool_id'] 	= $mappool_id;
			$data_array['$postName'] 	= 'submitEditMapPool';
			$pool_edit = $GLOBALS["_template_cup"]->replaceTemplate("cup_mappool_action", $data_array);
			echo $pool_edit;
 
		} else if($getAction == 'delete') {

			$mappool_id = (isset($_GET['id']) && validate_int($_GET['id'])) ?
				(int)$_GET['id'] : 0;

			if($mappool_id < 1) {
				throw new \Exception($_language->module['error_id']);
			}

			$ds = mysqli_fetch_array(
				mysqli_query(
					$_database, 
					"SELECT 
							m.*, 
							g.name AS `game_name` 
						FROM `".PREFIX."cups_mappool` m
						LEFT JOIN `".PREFIX."games` g ON m.gameID = g.gameID
						WHERE mappoolID = " . $mappool_id
				)
			);

			$maps = unserialize($ds['maps']);
			$anzMaps = count($maps);        

			$mapList = '';
			for($x=0;$x<$anzMaps;$x++) {
				
                $mapList .= '<input type="text" name="map'.($x + 1).'" value="'.$maps[$x].'" class="form-control" readonly="readonly" />';	
				
				if($x != $maxMaps) {
                    $mapList .= '<br />';	
                }
                
			}   

			$data_array = array();
			$data_array['$name'] 		= $ds['name'];
			$data_array['$game'] 		= $ds['game_name'];
			$data_array['$mapList'] 	= $mapList;
			$data_array['$mappool_id'] 	= $mappool_id;
			$pool_delete = $GLOBALS["_template_cup"]->replaceTemplate("cup_mappool_delete", $data_array);
			echo $pool_delete;

		} else {

			$ergebnis = mysqli_query(
				$_database, 
				"SELECT 
						m.*,
						g.name AS `game_name`
					FROM `".PREFIX."cups_mappool` m
					LEFT JOIN `".PREFIX."games` g ON m.gameID = g.gameID
					WHERE m.deleted = 0
					ORDER BY m.gameID ASC, m.name ASC"
			);
			if(mysqli_num_rows($ergebnis) > 0) {

				$poolList = '';

				while($ds = mysqli_fetch_array($ergebnis)) {

					$data_array = array();
					$data_array['$mappool_id'] = $ds['mappoolID'];
					$data_array['$name'] = getoutput($ds['name']);
					$data_array['$game'] = $ds['game_name'];
					$data_array['$anz'] = count(unserialize($ds['maps']));
					$poolList .= $GLOBALS["_template_cup"]->replaceTemplate("cup_mappool_list", $data_array);

				}

				$data_array = array();
				$data_array['$poolList'] = $poolList;
				$pool_home = $GLOBALS["_template_cup"]->replaceTemplate("cup_mappool_home", $data_array);
				echo $pool_home;

			} else {

				$data_array = array();
				$data_array['$title'] 		= $_language->module['add'];
				$data_array['$name'] 		= '';
				$data_array['$games'] 		= getGamesAsOptionList();
				$data_array['$mapList'] 	= getMapList();
				$data_array['$mappool_id'] 	= 0;
				$data_array['$postName'] 	= 'submitAddMapPool';
				$pool_add = $GLOBALS["_template_cup"]->replaceTemplate("cup_mappool_action", $data_array);
				echo $pool_add;	

			}   

		}

	}
	
} catch(Exception $e) {
	echo showError($e->getMessage());
}
