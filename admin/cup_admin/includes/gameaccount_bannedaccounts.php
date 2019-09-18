<?php

try {

    $_language->readModule('gameaccounts', false, true);

	if(!iscupadmin($userID)) {
		throw new \UnexpectedValueException($_language->module['access_denied']);
	}

	$infoQuery = mysqli_query(
		$_database,
		"SELECT
                cgb.*,
                g.`name` AS `game_name`,
                g.`short` AS `game_short`
            FROM `".PREFIX."cups_gameaccounts_banned` cgb
            JOIN `" . PREFIX . "games` g ON cgb.game_id = g.gameID
			ORDER BY `game_id` ASC, `id` DESC"
	);
	if(mysqli_num_rows($infoQuery) > 0) {

		$content = '';

        $activeGameId = -1;

		while($get = mysqli_fetch_array($infoQuery)) {

            if($get['game_id'] > $activeGameId) {

                $content .= '<tr><td colspan="4" class="bold">' . $get['game_name'] . '</td></tr>';

                $activeGameId = $get['game_id'];

            }

            if($get['game'] == 'csg') {
                $value = '<a href="https://steamcommunity.com/profiles/' . $get['value'] . '" class="blue" target="_blank">' . $get['value'] . '</a>';
            } else {
                $value = getoutput($get['value']);
            }

            $data_array = array();
            $data_array['$banned_id'] = $get['id'];
            $data_array['$value'] = $value;
            $data_array['$reason'] = getoutput($get['description']);
            $content .= $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_banned_list", $data_array);

		}

	} else {
		$content = '<tr><td colspan="4">'.$_language->module['no_gameaccount'].'</td></tr>';
	}

	$data_array = array();
	$data_array['$content'] = $content;
	$gameaccount_banned = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_banned", $data_array);
	echo $gameaccount_banned;

} catch(Exception $e) {
	echo showError($e->getMessage());
}
