<?php

$returnArray = array(
    'status' => FALSE,
    'error' => array(),
    'errorStr' => ''
);

try {

    $_language->readModule('teams');

    if(!validate_array($_POST, true)) {
        throw new \Exception($_language->module['access_denied']);
    }

    $team_id = (isset($_POST['team_id']) && is_numeric($_POST['team_id'])) ?
        (int)$_POST['team_id'] : 0;

    if($team_id < 1) {
        throw new \Exception($_language->module['team_not_found']);
    }

    $postAction = (isset($_POST['action'])) ?
        trim($_POST['action']) : '';

    if($postAction = 'changePassword') {

        $password = RandPass(20);

        //
        // Setze zufälliges Passwort
        $saveQuery = mysqli_query(
            $_database,
            "UPDATE `".PREFIX."cups_teams` 
                SET `password` = '" . $password . "' 
                WHERE `teamID` = " . $team_id
        );

        if(!$saveQuery) {
            throw new \Exception($_language->module['query_update_failed']);
        }

        $returnArray['password'] = $password;

    } else {
        throw new \Exception($_language->module['access_denied']);
    }

    $returnArray['status'] = TRUE;
    
} catch(Exception $e) {
    $returnArray['error'][] = $e->getMessage();
}

if(count($returnArray['error']) > 0) {
    $returnArray['errorStr'] = implode('<br />', $returnArray['error']);
}

echo json_encode($returnArray);
