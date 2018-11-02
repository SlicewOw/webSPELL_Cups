<?php

if(isset($_GET['id']) && isset($_GET['mID']) && getmatch((int)$_GET['mID'], 'active_playoff')) {
	
	$cupID 		= (int)$_GET['id'];
	$cupArray 	= getCup($cupID);
	
	$matchID 	= (int)$_GET['mID'];
			
	$mapArray = unserialize($matchArray['maps']);
	$anzMapsOpen = count($mapArray['open']);
	
	$debug = FALSE;
	if($debug && isdevadmin($userID)) {
		echo '<pre>';
		print_r($mapArray);
		echo '</pre>';
	}
	
	$cupFormat = $matchArray['format'];
	if(substr($cupFormat, 0, 2) == 'bo') {
		$finalMapsLeft = substr($cupFormat, 2, strlen($cupFormat));	
	} else {
		$finalMapsLeft = 1;	
	}

	if($teamAdminAccess === TRUE) {
			
		$team = ($anzMapsOpen % 2) ? 'team1' : 'team2';	
		if($cupArray['mode'] == '1on1') {
			
			if(($matchArray[$team.'_id'] > 0) && ($userID == $matchArray[$team.'_id'])) {
				$activeMapVote = TRUE;
			} else {
				$activeMapVote = FALSE;
			}
			
		} else {
			
			if(($matchArray[$team.'_id'] > 0) && isinteam($userID, $matchArray[$team.'_id'], 'admin')) {
				$activeMapVote = TRUE;
			} else {
				$activeMapVote = FALSE;
			}
			
		}
		
		if(($matchArray['team1_id'] > 0) && ($matchArray['team2_id'] > 0)) {
				
			$mapList = '';
			for($x=0;$x<$anzMapsOpen;$x++) {
				
				$submitName = str_replace(
					array(' ',  'ä',  'ö',  'ü',  'ß'), 
					array('_', 'ae', 'oe', 'ue', 'ss'), 
					$mapArray['open'][$x]
				);
				
				if($activeMapVote) {
					$vote = 'voteMap(\''.$mapArray['open'][$x].'\', \''.$team.'\');';
					$mapList .= ' <button class="mapButton btn btn-default btn-sm" onclick="'.$vote.'">'.$mapArray['open'][$x].'</button>';	
				} else {
					$mapList .= ' <span class="btn btn-default btn-sm">'.$mapArray['open'][$x].'</span>';	
				}
				
			}
			
			$info = '';
			if($finalMapsLeft == 1) {
				
				$info = (!$activeMapVote) ? 
						$_language->module['cup_veto_team'] : 
						$_language->module['map_vote_info'];
					
				if($cupArray['mode'] == '1on1') {	
					$teamname = ($team == 'team1') ? 
						getnickname($matchArray['team1_id']) : 
						getnickname($matchArray['team2_id']);
				} else {
					$teamname = ($team == 'team1') ? 
						getteam($matchArray['team1_id'], 'name') : 
						getteam($matchArray['team2_id'], 'name');
				}
				
				$info = str_replace('%team%', '<span class="bold">'.$teamname.'</span>', $info);
				
			} elseif($finalMapsLeft == 3) {
				
				if($anzMapsOpen <= $finalMapsLeft) {
					$info = ($activeMapVote) ? 
						$_language->module['cup_choose_team1'] : 
						$_language->module['cup_choose_team2'];
				} else {
					$info = (!$activeMapVote) ? 
						$_language->module['cup_veto_team'] : 
						$_language->module['map_vote_info'];
				}	
					
				if($cupArray['mode'] == '1on1') {	
					$teamname = ($team == 'team1') ? 
						getnickname($matchArray['team1_id']) : 
						getnickname($matchArray['team2_id']);
				} else {
					$teamname = ($team == 'team1') ? 
						getteam($matchArray['team1_id'], 'name') : 
						getteam($matchArray['team2_id'], 'name');
				}
				//$teamname = ($team == 'team1') ? getteam($matchArray['team2_id'], 'name') : getteam($matchArray['team1_id'], 'name');
				
				$info = str_replace('%team%', '<span class="bold">'.$teamname.'</span>', $info);
				
			}
			
			$data_array = array();
			$data_array['$cup_id'] 			= $cupID;
			$data_array['$match_id'] 		= $matchID;
			$data_array['$matchAdminURL'] 	= 'index.php?site=cup&amp;action=match&amp;id='.$cupID.'&amp;mID='.$matchID;
			$data_array['$map_veto'] 		= $mapList;
			$data_array['$footerInfo'] 		= (!$activeMapVote) ? ' alert-info' : '';
			$data_array['$map_vote_info'] 	= $info;
			$admin .= $GLOBALS["_template_cup"]->replaceTemplate("cup_match_mapveto", $data_array);
			
			if(!$activeMapVote) {
				$admin .= '<script type="text/javascript">voteMapUpdate(\'\', \''.$team.'\');</script>';
			}

		}
				
	} elseif(iscupadmin($userID)) {
		
		if(!isset($mapArray['open']) || empty($mapArray['list'])) {
			
			//
			// Map Pool
			$mapList = getMaps($cupArray['mappool']);

			$mapArray['open'] = $mapList;
			$mapArray['banned'] = array(
				'team1' => array(),
				'team2' => array()
			);
			$mapArray['picked'] = array();
			$mapArray['list'] = $mapList;
			$maps = serialize($mapArray);
			
			$saveQuery = mysqli_query(
				$_database,
				"UPDATE `".PREFIX."cups_matches_playoff`
					SET maps = '".$maps."'
					WHERE matchID = '".$matchID."'"
			);
			
		}
				
		$data_array = array();
		$data_array['$maps_open'] 			= implode(', ', $mapArray['open']);
		$data_array['$maps_banned_team1'] 	= implode(', ', $mapArray['banned']['team1']);
		$data_array['$maps_banned_team2'] 	= implode(', ', $mapArray['banned']['team2']);
		$admin .= $GLOBALS["_template_cup"]->replaceTemplate("cup_match_mapveto_admin", $data_array);
			
	}
	
}
?>