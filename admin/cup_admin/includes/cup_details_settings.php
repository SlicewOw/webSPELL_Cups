<?php

if (!isset($content)) {
    $content = '';
}

try {
	
	if (!iscupadmin($userID)) {
		throw new \Exception($_language->module['access_denied']);
	}
	
	if (!isset($cup_id)) {
		$cup_id = (isset($_GET['id']) && validate_int($_GET['id'])) ? 
			(int)$_GET['id'] : 0;
	}
	
	if ($cup_id < 1) {
		throw new \Exception($_language->module['unknown_cup_id']);
	}

    if (!checkIfContentExists($cup_id, 'cupID', 'cups')) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }
		
	if (!isset($cupArray)) {
		$cupArray = getcup($cup_id);
	}

	$cupOptions = getCupOption();

	$cupSettingsArray = array();

	$registerFormat = str_replace(
		'value="'.$cupArray['registration'].'"',
		'value="'.$cupArray['registration'].'" selected="selected"',
		$cupOptions['registration']
	);

	$cupSaved = str_replace(
		'value="'.$cupArray['saved'].'"',
		'value="'.$cupArray['saved'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$adminOnly = str_replace(
		'value="'.$cupArray['admin'].'"',
		'value="'.$cupArray['admin'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$mapVote = str_replace(
		'value="'.$cupArray['map_vote'].'"',
		'value="'.$cupArray['map_vote'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$mapPool = getMappool(
		$cupArray['mappool'], 
		'list'
	);

	$matchRoundFormat = '';

	for ($x = 0; $x < $cupArray['anz_runden']; $x++) {

		$matchRoundFormat .= '<div class="col-sm-3">';
		$matchRoundFormat .= '<div class="form-group">';
		$matchRoundFormat .= '<label>Runde '.($x + 1).'</label>';
		$matchRoundFormat .= '<select name="round[]" class="form-control">';

		$matchRoundFormat .= str_replace(
			'value="bo1"',
			'value="bo1" selected="selected"',
			$cupOptions['rounds']
		);

		$matchRoundFormat .= '</select>';
		$matchRoundFormat .= '</div>';
		$matchRoundFormat .= '</div>';

	}

	$enableServer = str_replace(
		'value="'.$cupArray['server'].'"',
		'value="'.$cupArray['server'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$csgoBot = str_replace(
		'value="'.$cupArray['bot'].'"',
		'value="'.$cupArray['bot'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$serverConfig = str_replace(
		'value="'.$cupSettingsArray['g_LiveConfig'].'"',
		'value="'.$cupSettingsArray['g_LiveConfig'].'" selected="selected"',
		$cupOptions['bot_configs']
	);

	$maxRounds = str_replace(
		'value="'.$cupSettingsArray['g_MaxRounds'].'"',
		'value="'.$cupSettingsArray['g_MaxRounds'].'" selected="selected"',
		$cupOptions['csg_rounds']
	);

	$g_SteamCheck = str_replace(
		'value="'.$cupSettingsArray['g_SteamCheck'].'"',
		'value="'.$cupSettingsArray['g_SteamCheck'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$g_SteamForce = str_replace(
		'value="'.$cupSettingsArray['g_SteamForce'].'"',
		'value="'.$cupSettingsArray['g_SteamForce'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$g_RoundScore = str_replace(
		'value="'.$cupSettingsArray['g_RoundScore'].'"',
		'value="'.$cupSettingsArray['g_RoundScore'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$g_EseaStats = str_replace(
		'value="'.$cupSettingsArray['g_EseaStats'].'"',
		'value="'.$cupSettingsArray['g_EseaStats'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$g_CaptainVote = str_replace(
		'value="'.$cupSettingsArray['g_CaptainVote'].'"',
		'value="'.$cupSettingsArray['g_CaptainVote'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$g_KnifeRound = str_replace(
		'value="'.$cupSettingsArray['g_KnifeRound'].'"',
		'value="'.$cupSettingsArray['g_KnifeRound'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$g_PauseAfterKnife = str_replace(
		'value="'.$cupSettingsArray['g_PauseAfterKnife'].'"',
		'value="'.$cupSettingsArray['g_PauseAfterKnife'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$g_OvertimeEnabled = str_replace(
		'value="'.$cupSettingsArray['g_OvertimeEnabled'].'"',
		'value="'.$cupSettingsArray['g_OvertimeEnabled'].'" selected="selected"',
		$cupOptions['true_false']
	);

	$otRoundsValue = $cupSettingsArray['g_OvertimeRounds'];
	$otRoundsValue .= '_'.($cupSettingsArray['g_OvertimeMoney'] / 1000);

	$otRounds = str_replace(
		'value="'.$otRoundsValue.'"',
		'value="'.$otRoundsValue.'" selected="selected"',
		$cupOptions['csg_overtime']
	);

	$data_array = array();
	$data_array['$registerFormat'] 		= $registerFormat;
	$data_array['$cupSaved'] 			= $cupSaved;
	$data_array['$adminOnly'] 			= $adminOnly;
	$data_array['$mapVote'] 			= $mapVote;
	$data_array['$mapPool'] 			= $mapPool;
	$data_array['$matchRoundFormat'] 	= $matchRoundFormat;
	$data_array['$enableServer'] 		= $enableServer;
	$data_array['$csgoBot'] 			= $csgoBot;
	$data_array['$serverConfig'] 		= $serverConfig;
	$data_array['$maxRounds'] 			= $maxRounds;
	$data_array['$g_SteamCheck'] 		= $g_SteamCheck;
	$data_array['$g_SteamForce'] 		= $g_SteamForce;
	$data_array['$g_RoundScore'] 		= $g_RoundScore;
	$data_array['$g_EseaStats'] 		= $g_EseaStats;
	$data_array['$g_CaptainVote'] 		= $g_CaptainVote;
	$data_array['$g_KnifeRound'] 		= $g_KnifeRound;
	$data_array['$g_PauseAfterKnife'] 	= $g_PauseAfterKnife;
	$data_array['$g_OvertimeEnabled'] 	= $g_OvertimeEnabled;
	$data_array['$otRounds'] 			= $otRounds;
	$data_array['$cup_id'] 				= $cup_id;
	$data_array['$cup_game'] 			= $cupArray['game'];
	$content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_settings", $data_array);

} catch(Exception $e) {
	$content .= showError($e->getMessage());
}
