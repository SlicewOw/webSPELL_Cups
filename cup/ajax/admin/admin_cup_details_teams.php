<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {

    $_language->readModule('cups', false, true);

    $postAction = (isset($_POST['action'])) ?
        getinput($_POST['action']) : '';

    if (empty($postAction)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    $cup_id = (isset($_POST['cup_id']) && validate_int($_POST['cup_id'], true)) ?
        (int)$_POST['cup_id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    $cupArray = getcup($cup_id);

    if (!isset($cupArray['status']) || ($cupArray['status'] > 1)) {
        throw new \Exception($_language->module['cup_started']);
    }

    if ($postAction == 'deleteTeamFromCup') {

        $team_id = (isset($_POST['team_id']) && validate_int($_POST['team_id'], true)) ?
            (int)$_POST['team_id'] : 0;

        if ($team_id < 1) {
            throw new \Exception($_language->module['unknown_team_id']);
        }

        $whereClauseArray = array();
        $whereClauseArray[] = '`cupID` = ' . $cup_id;
        $whereClauseArray[] = '`teamID` = ' . $team_id;

        $whereClause = implode(' AND ', $whereClauseArray);

        $deleteQuery = mysqli_query(
            $_database,
            "DELETE FROM `" . PREFIX . "cups_teilnehmer`
                WHERE " . $whereClause
        );

        if (!$deleteQuery) {
            throw new \Exception($_language->module['query_delete_failed'] . ' (' . $whereClause . ')');
        }

        if ($cupArray['mode'] == '1on1') {
            setPlayerLog($team_id, $cup_id, 'cup_admin_kick_' . $cup_id);
        } else {
            $teamname = getteam($team_id, 'name');
            setCupTeamLog($team_id, $teamname, 'cup_admin_kick_' . $cup_id, $userID);
        }

        $returnArray['message'][] = $_language->module['team_deleted_from_cup'];

    } else {
        throw new \Exception($_language->module['unknown_action']);
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
