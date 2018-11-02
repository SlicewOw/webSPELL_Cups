<?php

$adminList = '';

if(!iscupadmin($userID)) {
	throw new \Exception($_language->module['access_denied']);
}

$checkIf = mysqli_fetch_array(
	mysqli_query(
		$_database,
		"SELECT COUNT(*) AS exist FROM `".PREFIX."cups_team`
			ORDER BY userID ASC"
	)
);

if($checkIf['exist'] > 0) {

	$query = mysqli_query(
		$_database,
		"SELECT * FROM `".PREFIX."cups_team`
			ORDER BY userID ASC"
	);

	while($get = mysqli_fetch_array($query)) {

		if(!empty($get['description'])) {
			$description = getoutput($get['description']);
		} else {
			$description = $_language->module['no_description'];
		}

		$data_array = array();
		$data_array['$name'] 		= getnickname($get['userID']);
		$data_array['$position'] 	= getuserposition($get['position']);
		$data_array['$date'] 		= getformatdatetime($get['date']);
		$data_array['$description'] = $description;
		$data_array['$user_id'] 	= $get['userID'];
		$adminList .= $GLOBALS["_template_cup"]->replaceTemplate("cup_admin_list", $data_array);

	}

} else {
	$adminList = '<tr><td colspan="4">'.$_language->module['no_admin'].'</td></tr>';
}
