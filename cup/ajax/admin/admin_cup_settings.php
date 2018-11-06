<?php

$returnArray = array(
	'status' => FALSE,
	'message' => array()
);

try {

    $_language->readModule('cups', false, true);
   
    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }
    
    if (!validate_array($_POST, true)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    $postAction = (isset($_POST['action'])) ? 
        getinput($_POST['action']) : '';
    
    if (empty($postAction)) {
        throw new \Exception($_language->module['unknown_action']);
    }
    
    if ($postAction == 'saveCupSettings') {

        $cup_id = (isset($_POST['cup_id']) && validate_int($_POST['cup_id'])) ? 
            (int)$_POST['cup_id'] : 0;

        if ($cup_id < 1) {
            throw new \Exception('unknown_cup_id');
        }

        $registration = (isset($_POST['registerFormat'])) ? 
            $_POST['registerFormat'] : 'open';

        $serverEnable = (isset($_POST['serverEnable']) && is_numeric($_POST['serverEnable'])) ? 
            (int)$_POST['serverEnable'] : 0;

        $botEnable = (isset($_POST['botEnable']) && is_numeric($_POST['botEnable'])) ? 
            (int)$_POST['botEnable'] : 0;

        $mapVoteEnable = (isset($_POST['mapVoteEnable']) && is_numeric($_POST['mapVoteEnable'])) ? 
            (int)$_POST['mapVoteEnable'] : 0;

        $mapPool_id = (isset($_POST['mapPool_id']) && is_numeric($_POST['mapPool_id'])) ? 
            (int)$_POST['mapPool_id'] : 0;

        $saved = (isset($_POST['cupSaved']) && is_numeric($_POST['cupSaved'])) ? 
            (int)$_POST['cupSaved'] : 0;

        $admin = (isset($_POST['adminVisible']) && is_numeric($_POST['adminVisible'])) ? 
            (int)$_POST['adminVisible'] : 0;

        $updateQuery = mysqli_query(
            $_database,
            "UPDATE `".PREFIX."cups`
                SET	registration = '".$registration."',
                    server = ".$serverEnable.",
                    bot = ".$botEnable.",
                    mapvote_enable = ".$mapVoteEnable.",
                    mappool = ".$mapPool_id.",
                    saved = ".$saved.",
                    admin_visible = ".$admin."
                WHERE cupID = ".$cup_id
        );

        if(!$updateQuery) {
            throw new \Exception('cannot_save_cup_settings');
        }

        $returnArray['message'][] = $_language->module['cup_settings_saved'];

    } else if ($postAction == 'saveBotSettings') {

        $cup_id = (isset($_POST['cup_id']) && is_numeric($_POST['cup_id'])) ? 
            (int)$_POST['cup_id'] : 0;

        $g_LiveConfig = (isset($_POST['g_LiveConfig'])) ? $_POST['g_LiveConfig'] : '';

        $g_MaxRounds = (isset($_POST['g_MaxRounds']) && is_numeric($_POST['g_MaxRounds'])) ? 
            (int)$_POST['g_MaxRounds'] : 30;

        $g_SteamCheck = (isset($_POST['g_SteamCheck']) && is_numeric($_POST['g_SteamCheck'])) ? 
            (int)$_POST['g_SteamCheck'] : 1;

        $g_SteamForce = (isset($_POST['g_SteamForce']) && is_numeric($_POST['g_SteamForce'])) ? 
            (int)$_POST['g_SteamForce'] : 1;

        $g_RoundScore = (isset($_POST['g_RoundScore']) && is_numeric($_POST['g_RoundScore'])) ? 
            (int)$_POST['g_RoundScore'] : 1;

        $g_EseaStats = (isset($_POST['g_EseaStats']) && is_numeric($_POST['g_EseaStats'])) ? 
            (int)$_POST['g_EseaStats'] : 0;

        $g_CaptainVote = (isset($_POST['g_CaptainVote']) && is_numeric($_POST['g_CaptainVote'])) ? 
            (int)$_POST['g_CaptainVote'] : 1;

        $g_KnifeRound = (isset($_POST['g_KnifeRound']) && is_numeric($_POST['g_KnifeRound'])) ? 
            (int)$_POST['g_KnifeRound'] : 1;

        $g_PauseAfterKnife = (isset($_POST['g_PauseAfterKnife']) && is_numeric($_POST['g_PauseAfterKnife'])) ? 
            (int)$_POST['g_PauseAfterKnife'] : 0;

        $g_OvertimeEnabled = (isset($_POST['g_OvertimeEnabled']) && is_numeric($_POST['g_OvertimeEnabled'])) ? 
            (int)$_POST['g_OvertimeEnabled'] : 1;

        $overtimeString = (isset($_POST['g_OvertimeRounds'])) ? 
            $_POST['g_OvertimeRounds'] : '6_10';

        $g_OvertimeRounds = 6;
        $g_OvertimeMoney = 10000;

        if(!empty($overtimeString)) {

            $overtimeArray = explode('_', $overtimeString);
            $arrayCounter = count($overtimeArray);

            if($arrayCounter == 2) {
                $g_OvertimeRounds = $overtimeArray[0];
                $g_OvertimeMoney = $overtimeArray[1] * 1000;
            }

        }

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT 
                        COUNT(*) AS `exist` 
                    FROM `" . PREFIX . "cups_bot_settings`
                    WHERE cupID = ".$cup_id
            )
        );

        if($checkIf['exist']) {

            $updateQuery = mysqli_query(
                $_database,
                "UPDATE `".PREFIX."cups_bot_settings`
                    SET	g_LiveConfig = '".$g_LiveConfig."',
                        g_MaxRounds = ".$g_MaxRounds.",
                        g_SteamCheck = ".$g_SteamCheck.",
                        g_SteamForce = ".$g_SteamForce.",
                        g_RoundScore = ".$g_RoundScore.",
                        g_EseaStats = ".$g_EseaStats.",
                        g_CaptainVote = ".$g_CaptainVote.",
                        g_KnifeRound = ".$g_KnifeRound.",
                        g_PauseAfterKnife = ".$g_PauseAfterKnife.",
                        g_OvertimeEnabled = ".$g_OvertimeEnabled.",
                        g_OvertimeRounds = ".$g_OvertimeRounds.",
                        g_OvertimeMoney = ".$g_OvertimeMoney."
                    WHERE cupID = ".$cup_id
            );

            if (!$updateQuery) {
                throw new \Exception('cannot_update_bot_settings');
            }

            $returnArray['message'][] = $_language->module['bot_settings_saved'];

        } else {

            $insertQuery = mysqli_query(
                $_database,
                "INSERT INTO `".PREFIX."cups_bot_settings`
                    (
                        `cupID`,
                        `g_LiveConfig`,
                        `g_MaxRounds`,
                        `g_SteamCheck`,
                        `g_SteamForce`,
                        `g_RoundScore`,
                        `g_EseaStats`,
                        `g_CaptainVote`,
                        `g_KnifeRound`,
                        `g_PauseAfterKnife`,
                        `g_OvertimeEnabled`,
                        `g_OvertimeRounds`,
                        `g_OvertimeMoney`
                    )
                    VALUES
                    (
                        ".$cup_id.",
                        '".$g_LiveConfig."',
                        ".$g_MaxRounds.",
                        ".$g_SteamCheck.",
                        ".$g_SteamForce.",
                        ".$g_RoundScore.",
                        ".$g_EseaStats.",
                        ".$g_CaptainVote.",
                        ".$g_KnifeRound.",
                        ".$g_PauseAfterKnife.",
                        ".$g_OvertimeEnabled.",
                        ".$g_OvertimeRounds.",
                        ".$g_OvertimeMoney."
                    )"
            );

            if (!$insertQuery) {
                throw new \Exception('cannot_insert_bot_settings');
            }

            $returnArray['message'][] = $_language->module['bot_settings_saved'];

        }

    } else {
        throw new \Exception($_language->module['unknown_action']);
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
