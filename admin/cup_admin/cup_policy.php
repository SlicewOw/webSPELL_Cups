<?php

$_language->readModule('cups', false, true);

try {

	if(!$loggedin || !iscupadmin($userID)) {
		throw new \UnexpectedValueException($_language->module['login']);
	}

	if (validate_array($_POST, true)) {

		$parent_url = 'admincenter.php?site=cup&mod=policy';

		if(isset($_POST['submitPolicy'])) {

			$content = getinput($_POST['policy']);

			$query = mysqli_query(
				$_database,
				"UPDATE `".PREFIX."cups_policy`
					SET content = '" . $content . "',
						date = " . time() . "
					WHERE id = 1"
			);

			if(!$query) {
				throw new \UnexpectedValueException($_language->module['query_update_failed']);
			}

			$text = 'Cup Nutzungsbedingungen wurden aktualisiert';
            $_SESSION['successArray'][] = $text;

		}

		header('Location: ' . $parent_url);

	} else {

		$checkIf = mysqli_fetch_array(
			mysqli_query(
				$_database,
				"SELECT COUNT(*) AS `exist` FROM `".PREFIX."cups_policy`"
			)
		);

		if(!$checkIf['exist']) {

			$query = mysqli_query(
				$_database,
				"INSERT INTO `".PREFIX."cups_policy`
					(
						`id`,
						`date`
					)
					VALUES
					(
						1,
						" . time() . "
					)"
			);

		}

		$ds = mysqli_fetch_array(
			mysqli_query(
				$_database,
				"SELECT content, date FROM `".PREFIX."cups_policy`"
			)
		);

		$data_array = array();
		$data_array['$date'] = getformatdatetime($ds['date']);
		$data_array['$content']	= getoutput($ds['content']);
		$policy_home = $GLOBALS["_template_cup"]->replaceTemplate("policy_home", $data_array);
		echo $policy_home;

	}

} catch (Exception $e) {
	echo showError($e->getMessage());
}
