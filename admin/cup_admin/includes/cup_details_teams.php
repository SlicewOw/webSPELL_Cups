<?php

if(!isset($content)) {
    $content = '';
}

$teams = '';

$teamQuery = mysqli_query(
	$_database, 
	"SELECT 
			teamID, 
			checked_in,
			date_register,
			date_checkin
		FROM `".PREFIX."cups_teilnehmer` 
		WHERE cupID = '".$cup_id."' 
		ORDER BY checked_in DESC, date_checkin ASC, date_register ASC"
);
if(mysqli_num_rows($teamQuery)) {

	$profile_url = $cup_url . '/index.php?site=profile&id=';

	$n = 1;
	while( $db = mysqli_fetch_array($teamQuery) ) {

		//
		// max_mode = 1, wenn Cup Modus 1on1
		if($cupArray['max_mode'] == 1) {

			$user_id = $db['teamID'];


			if($db['checked_in'] == 1) {
				$status = '<span class="btn btn-success btn-xs">'.$_language->module['checkin_ok'].'</span> ';
			} else {
				$status = '<span class="btn btn-default btn-xs">'.$_language->module['register_ok'].'</span>';
			}

			$team_info = '';
			$team_info .= '<a class="btn btn-info btn-xs white darkshadow" href="'.$profile_url.$user_id.'#content" target="_blank">'.$_language->module['view'].'</a> ';
			$team_info .= '<button class="btn btn-default btn-xs" type="submit" name="deleteTeam_'.$cup_id.'_'.$db['teamID'].'">'.$_language->module['delete'].'</button>';

			$data_array = array();
			$data_array['$n'] 				= $n++;
			$data_array['$team_name'] 		= getnickname($user_id);
			$data_array['$admin_info'] 		= '';
			$data_array['$status'] 			= $status;
			$data_array['$date_register'] 	= ($db['date_register'] > 0) ? getformatdatetime($db['date_register']) : '';
			$data_array['$date_checkin'] 	= ($db['date_checkin'] > 0) ? getformatdatetime($db['date_checkin']) : '';
			$data_array['$cup_counter'] 	= '';
			$data_array['$match_counter'] 	= '';
			$data_array['$team_info'] 		= $team_info;
			$teams .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_team_list", $data_array);

		} else {

			$team_id = $db['teamID'];

			$teamArray = getteam($team_id);

			$url_detail = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;teamID='.$team_id;

			$admin_info = '<a href="'.$profile_url.$teamArray['admin_id'].'#content" class="blue" target="_blank">'.getnickname($teamArray['admin_id']).'</a>';

			if($db['checked_in'] == 1) {
				$status = '<span class="btn btn-success btn-xs">'.$_language->module['checkin_ok'].'</span>';
			} else {
				$status = '<span class="btn btn-default btn-xs">'.$_language->module['register_ok'].'</span>';
			}

			$team_info = '';
			$team_info .= '<a class="btn btn-info btn-xs white darkshadow" href="'.$url_detail.'" target="_blank">'.$_language->module['view'].'</a> ';
			$team_info .= '<button class="btn btn-default btn-xs" type="submit" name="deleteTeam_'.$cup_id.'_'.$team_id.'">'.$_language->module['delete'].'</button>';

			$data_array = array();
			$data_array['$n'] 				= $n++;
			$data_array['$team_name'] 		= $teamArray['name'];
			$data_array['$admin_info'] 		= $admin_info;
			$data_array['$status'] 			= $status;
			$data_array['$date_register'] 	= ($db['date_register'] > 0) ? getformatdatetime($db['date_register']) : '';
			$data_array['$date_checkin'] 	= ($db['date_checkin'] > 0) ? getformatdatetime($db['date_checkin']) : '';
			$data_array['$cup_counter'] 	= getteam($team_id, 'anz_cups');
			$data_array['$match_counter'] 	= getteam($team_id, 'anz_matches');
			$data_array['$team_info'] 		= $team_info;
			$teams .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_team_list", $data_array);

		}

	}				
} else { 
	$teams = '<tr><td colspan="9">'.$_language->module['no_team'].'</td></tr>';	
}

$data_array = array();
$data_array['$cupID'] = $cup_id;
$data_array['$teams'] = $teams;
$content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_teams", $data_array);
