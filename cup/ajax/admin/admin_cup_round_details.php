<?php

$returnArray = array(
	'status' => FALSE,
	'message' => array(),
	'html' => ''
);

try {
    
    $_language->readModule('cups', false, true);
    
    if (!iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    $cup_id = (isset($_GET['cup_id']) && validate_int($_GET['cup_id'], true)) ? 
        (int)$_GET['cup_id'] : 0;
    
    if ($cup_id < 1) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    $round_id = (isset($_GET['round_id']) && validate_int($_GET['round_id'], true)) ? 
        (int)$_GET['round_id'] : 0;
    
    if ($round_id < 1) {
        throw new \Exception($_language->module['unknown_round']);
    }

    $returnArray['data'] = array(
        'cup_id' => $cup_id,
        'round_id' => $round_id
    );

    $bracket = '';

    $adminMatchesRoundInclude = $dir_cup . 'admin/includes/matches_round.php';
    if (!file_exists($adminMatchesRoundInclude)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    $cupID = $cup_id;

    if (!isset($cupArray) || !is_array($cupArray)) {
        $cupArray = getcup($cupID);
    }

    include($adminMatchesRoundInclude);	

    if (empty($bracket)) {
        
        if (isset($errorArray)) {
            $returnArray['message'] = $errorArray;
        }

        throw new \Exception($_language->module['unknown_bracket']);
        
    }

    $returnArray['html'] .= $bracket;
    $returnArray['status'] = TRUE;
    
} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
