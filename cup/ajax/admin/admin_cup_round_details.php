<?php

$returnArray = getDefaultReturnArray();

try {

    $_language->readModule('cups', false, true);

    if (!iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $cup_id = (isset($_GET[getConstNameCupIdWithUnderscore()]) && validate_int($_GET[getConstNameCupIdWithUnderscore()], true)) ?
        (int)$_GET[getConstNameCupIdWithUnderscore()] : 0;

    if ($cup_id < 1) {
        throw new \UnexpectedValueException($_language->module['unknown_cup_id']);
    }

    $round_id = (isset($_GET['round_id']) && validate_int($_GET['round_id'], true)) ?
        (int)$_GET['round_id'] : 0;

    if ($round_id < 1) {
        throw new \UnexpectedValueException($_language->module['unknown_round']);
    }

    $returnArray['data'] = array(
        getConstNameCupIdWithUnderscore() => $cup_id,
        'round_id' => $round_id
    );

    $bracket = '';

    $adminMatchesRoundInclude = __DIR__ . '/../../../admin/cup_admin/includes/matches_round.php';
    if (!file_exists($adminMatchesRoundInclude)) {
        throw new \UnexpectedValueException($_language->module['unknown_action']);
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

        throw new \UnexpectedValueException($_language->module['unknown_bracket']);

    }

    $returnArray['html'] .= $bracket;
    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
