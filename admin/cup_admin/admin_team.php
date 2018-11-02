<?php


try {

	$_language->readModule('cups', false, true);

	if(!$loggedin || !iscupadmin($userID)) {
		throw new \Exception($_language->module['no_admin_team']);
	}

	include(__DIR__ . '/includes/admin_team.php');

	$data_array = array();
	$data_array['$image_url'] = $image_url;
	$data_array['$userList'] = getuserlist();
	$data_array['$positions'] = getPlayerPosition('', 'list');
	$data_array['$user_id'] = $userID;
	$data_array['$adminList'] = $adminList;
	$data_array['$confirmText'] = $_language->module['confirm_text'];
	$admin_home = $GLOBALS["_template_cup"]->replaceTemplate("cup_admin_team", $data_array);
	echo $admin_home;

} catch(Exception $e) {
	echo showError($e->getMessage());
}
