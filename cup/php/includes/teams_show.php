<?php

try {

	if (!$loggedin) {
		throw new \UnexpectedValueException($_language->module['no_team']);
	}

	$team_button = '<a href="index.php?site=teams&amp;action=add" class="btn btn-info btn-sm white darkshadow">'.$_language->module['add'].'</a><br /><br />';

	$query = mysqli_query(
		$_database,
		"SELECT * FROM `" . PREFIX . "cups_teams_member`
			WHERE `userID` = " . $userID . " AND `active` = 1"
	);

	if (mysqli_num_rows($query) > 0) {

		$teams = '';
		while ($ds = mysqli_fetch_array($query)) {

			$team_id = $ds['teamID'];

			$db = mysqli_fetch_array(
				mysqli_query(
					$_database,
					"SELECT * FROM `" . PREFIX . "cups_teams`
						WHERE `teamID` = " . $team_id
				)
			);

			if ($db['deleted'] == 0) {

				if(isinteam($userID, $team_id, 'admin')) {
					$url = 'index.php?site=teams&amp;action=admin&amp;id='.$team_id;
				} else {
					$url = 'index.php?site=teams&amp;action=details&amp;id='.$team_id;
				}

				$team_info = '<img src="' . getCupTeamImage($team_id, true) . '" alt="" width="16" height="16" />';
				$team_info .= '<span style="margin: 0 0 0 10px">'.$db['name'].'</span>';

				$data_array = array();
				$data_array['$url'] = $url;
				$data_array['$team_info'] = $team_info;
				$data_array['$name'] = $db['name'];
				$teams .= $GLOBALS["_template_cup"]->replaceTemplate("teams_list", $data_array);

			}

		}

	} else {
		$teams = '<span class="list-group-item">' . $_language->module['no_team'] . '</span>';
	}

	$data_array = array();
	$data_array['$team_button'] = $team_button;
	$data_array['$teams'] 		= $teams;
	$teams_home_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_home_list", $data_array);
	echo $teams_home_list;

} catch (Exception $e) {
	echo showError($e->getMessage());
}
