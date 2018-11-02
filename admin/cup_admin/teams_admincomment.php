<?php

if(iscupadmin($userID) && isset($teamID)) {
	
	if(isset($_POST['saveAdminComment'])) {
		
		$comment = getinput($_POST['admin_comment']);
		
		$query = mysqli_query($_database, "INSERT INTO `".PREFIX."cups_teams_comments`
											(teamID, date, userID, comment)
											VALUES
											('".$teamID."', '".time()."', '".$userID."', '".$comment."')");
		
		header('Location: index.php?'.$_SERVER['QUERY_STRING']);
		
	}
	
	$commentList = '';
	
	$get = mysqli_fetch_array(
		mysqli_query($_database, "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teams_comments`
									WHERE teamID = '".$teamID."'")
	);
	if($get['anz'] > 0) {
		
		$query = mysqli_query($_database, "SELECT * FROM `".PREFIX."cups_teams_comments`
											WHERE teamID = '".$teamID."'");
		while($get = mysqli_fetch_array($query)) {
			
			$commentList .= '<div class="list-group-item">';
			$commentList .= getoutput($get['comment']);
			$commentList .= '<br /><hr />'.getformatdatetime($get['date']).' - '.getnickname($get['userID']);
			$commentList .= '</div>';
			
		}
		
	} else {
		
		$commentList = '<div class="list-group-item">- / -</div>';
	
	}
	
	
	$data_array = array();
	$data_array['$commentList'] = $commentList;
	$data_array['$team_id'] 	= $teamID;
	$cup_teams_admin = $GLOBALS["_template_cup"]->replaceTemplate("cup_teams_admin_comment", $data_array);
	echo $cup_teams_admin;

}
