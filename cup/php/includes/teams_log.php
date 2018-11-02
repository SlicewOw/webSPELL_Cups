<?php

$teamID = (isset($_GET['id']) && is_numeric($_GET['id'])) ? 
	(int)$_GET['id'] : 0;

if($teamID > 0) {
		
	$info = safe_query(
	    "SELECT * FROM `".PREFIX."cups_teams` 
	        WHERE teamID = ".$teamID
    );
	if(mysqli_num_rows($info)) {
				
		$ds = mysqli_fetch_array($info);
		
		$name = $ds['name'];
		$details = $_language->module['created'].' '.getformatdatetime($ds['date']);
		if(getteam($teamID, 'anz_matches') == 1) {
			$details .= ' / 1 '.$_language->module['match_played1'];
		} else {
			$details .= ' / '.getteam($teamID, 'anz_matches').' '.$_language->module['match_played'];
		}
		if(getteam($teamID, 'anz_cups') == 1) {
			$details .= ' / 1 '.$_language->module['cups_played1'];
		} else {
			$details .= ' / '.getteam($teamID, 'anz_cups').' '.$_language->module['cups_played'];
		}
		$details .= ' / Admin: <a href="index.php?site=profile&amp;id='.$ds['userID'].'">'.getnickname($ds['userID']).'</a>';
		
		$data_array = array();
		$data_array['$name'] 		= $name;
		$data_array['$details'] 	= $details;
		$teams_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_details_head", $data_array);
		echo $teams_list;	

        $logQuery = mysqli_query(
            $_database,
            "SELECT * FROM `".PREFIX."cups_teams_log`
                WHERE `teamID` = ".$teamID."
                ORDER BY date ASC"
        );

        $logs = '';
        while($get = mysqli_fetch_array($logQuery)) {

            $text = '';

            $logAction = $get['action'];

            $text .= '['.getformatdatetime($get['date']).'] '.$get['action'];

            if($logAction == 'player_kicked' && ($get['kicked_id'] > 0)) {
                $text .= ' - '.getnickname($get['kicked_id']);
            }

            $text .= '<span class="pull-right">';
            $text .= getnickname($get['user_id']);
            $text .= '</span>';

            $logs .= '<div class="list-group-item">'.$text.'</div>';

        }

		$data_array = array();
		$data_array['$logs'] = $logs;
		$teams_log = $GLOBALS["_template_cup"]->replaceTemplate("teams_log", $data_array);
		echo $teams_log;	
	
	} else {
		echo $_language->module['no_team'];
	}

} else {
	echo $_language->module['login'];
}