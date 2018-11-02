<?php

if(isset($cupArray) && is_array($cupArray)) {
	
	$navi_teams = 'btn-info white darkshadow';

	$teams = '';

	for($x=0;$x<2;$x++) {

		//
		// 1: Teams (Checked-In)
		// 0: Teams (Registered)
		$isChecked = ($x == 0) ? 1 : 0;
		if($cupArray['mode'] == '1on1') {
			$teamsTitle = ($x == 0) ? 'player_checked_in' : 'player_registered';
		} else {
			$teamsTitle = ($x == 0) ? 'teams_checked_in' : 'teams_registered';
		}

		$team = mysqli_query(
			$_database, 
			"SELECT teamID FROM ".PREFIX."cups_teilnehmer 
				WHERE cupID = " . $cup_id . " AND checked_in = '".$isChecked."'"
		);
		if(mysqli_num_rows($team) > 0) {

			if($cupArray['mode'] == '1on1') {

				$teams .= '<div class="panel panel-default"><div class="panel-heading">'.$_language->module[$teamsTitle].'</div>';
				$teams .= '<div class="list-group">';	

				while( $db = mysqli_fetch_array($team) ) {

					$user_id = $db['teamID'];

					$url = 'index.php?site=profile&id='.$user_id.'#content';

					$data_array = array();
					$data_array['$url'] 		= $url;
					$data_array['$name'] 		= getnickname($user_id);
					$data_array['$team_info'] 	= getnickname($user_id);
					$teams .= $GLOBALS["_template_cup"]->replaceTemplate("teams_list", $data_array);

				}

				$teams .= '</div></div>';	

			} else {

				$teams .= '<div class="panel panel-default"><div class="panel-heading">'.$_language->module[$teamsTitle].'</div>';
				$teams .= '<div class="list-group">';	
				while( $db = mysqli_fetch_array($team) ) {

                    $team_id = $db['teamID'];
                    
					$teamArray = getteam($team_id, '');
					$url 	= 'index.php?site=teams&amp;action=details&amp;id=' . $team_id;
					$name	= $teamArray['name'];
					$logo	= $teamArray['logotype'];

					$team_info = '<img src="' . $teamArray['logotype'] . '" class="img-rounded" alt="" width="16" height="16" />';
					$team_info .= '<span style="margin: 0 0 0 10px">' . $name . '</span>';

					$data_array = array();
					$data_array['$url'] = $url;
					$data_array['$name'] = $name;
					$data_array['$team_info'] = $team_info;
					$teams .= $GLOBALS["_template_cup"]->replaceTemplate("teams_list", $data_array);

				}
				$teams .= '</div></div>';	

			}

		}

	}

	if(getcup($cup_id, 'anz_teams') == 0) { 
		$teams = '<div class="panel panel-default"><div class="panel-body">'.$_language->module['no_team'].'</div></div>';	
	}

	if(!isset($content)) {
		$content = '';
	}

	$content .= $teams;

}
