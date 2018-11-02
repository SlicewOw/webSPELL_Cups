<?php

if(!isset($content)) {
    $content = '';
}

$ergebnis_act = mysqli_query(
	$_database, 
	"SELECT 
			ac.`date`, 
			ac.`comment`,
			u.`nickname` 
		FROM `" . PREFIX . "activityfeed_cup` ac
		JOIN `" . PREFIX . "user` u ON ac.userID = u.userID
		WHERE parentID = " . $cup_id . " 
		ORDER BY date DESC"
);

$act_admin = '';
if(mysqli_num_rows($ergebnis_act)) {
	
	while($dx = mysqli_fetch_array($ergebnis_act)) {
		$info_activity = $dx['nickname'];
		$info_activity .= ' - '.getformatdatetime($dx['date']);
		$act_admin .= '<div class="list-group-item">'.$dx['comment'].'<span class="pull-right">'.$info_activity.'</span></div>';
	}
	
}

$content .= '<div class="panel panel-default"><div class="panel-heading">'.$_language->module['activity_feed'].' (Admin)</div>
<div class="list-group">'.$act_admin.'</div></div>';
