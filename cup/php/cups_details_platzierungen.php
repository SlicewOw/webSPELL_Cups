<?php

$finish_cup_info = '<div class="cup_container"><h1 class="black fontbig bold">'.$_language->module['cup_placements'].'</h1><div class="teams_spacer"></div>';

$get_plaetze = mysqli_query(
    $_database, 
    "SELECT 
            `teamID`, 
            `platzierung` 
        FROM `" . PREFIX . "cups_platzierungen` 
        WHERE cupID = " . $cup_id . " 
        ORDER BY pID DESC"
);

while ($getp = mysqli_fetch_array($get_plaetze)) {
	
	$teamID = $getp['teamID'];
	$platz = $getp['platzierung'];
	$platz_team = getteam($teamID, 'name');
	
	$data_array = array();
	$data_array['$teamID'] 		= $teamID;
	$data_array['$platz'] 		= $platz;
	$data_array['$platz_team'] 	= $platz_team;
	$finish_cup_info .= $GLOBALS["_template_cup"]->replaceTemplate("cups_details_platzierung", $data_array);

}

$finish_cup_info .= '</div>';
