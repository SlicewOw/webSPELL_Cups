<?php

$cupID = (int)$_GET['id'];

$finish_cup_info = '<div class="cup_container"><h1 class="black fontbig bold">'.$_language->module['cup_placements'].'</h1><div class="teams_spacer"></div>';
$get_plaetze = safe_query("SELECT teamID, platzierung FROM ".PREFIX."cups_platzierungen WHERE cupID = '".$cupID."' ORDER BY pID DESC");
while($getp = mysql_fetch_array($get_plaetze)) {

	$teamID = $getp[getConstNameTeamId()];
	$platz = $getp['platzierung'];
	$platz_team = getteam($teamID, 'name');

	eval("\$finish_cup_info .= \"".gettemplatecup("cups_details_platzierung")."\";");

}
$finish_cup_info .= '</div>';

?>