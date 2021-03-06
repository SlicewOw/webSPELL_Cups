<?php

$returnArray = getDefaultReturnArray();

try {

    $_language->readModule('teams');

    if (!validate_array($_POST, true)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $team_id = (isset($_POST[getConstNameTeamIdWithUnderscore()]) && validate_int($_POST[getConstNameTeamIdWithUnderscore()], true)) ?
        (int)$_POST[getConstNameTeamIdWithUnderscore()] : 0;

    if ($team_id < 1) {
        throw new \UnexpectedValueException($_language->module['team_not_found']);
    }

    $postAction = (isset($_POST['action'])) ?
        getinput($_POST['action']) : '';

    if ($postAction = 'changePassword') {

        $password = RandPass(20);

        //
        // Setze zufälliges Passwort
        $saveQuery = cup_query(
            "UPDATE `" . PREFIX . "cups_teams`
                SET `password` = '" . $password . "'
                WHERE `teamID` = " . $team_id,
            __FILE__
        );

        $returnArray['password'] = $password;

    } else {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    setLog('', $e->getMessage(), __FILE__, $e->getLine());
    $returnArray['error'][] = $e->getMessage();
}

echo json_encode($returnArray);
