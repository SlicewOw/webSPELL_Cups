<?php

header('Content-Type: application/json');

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {

    if (empty($getAction)) {
        throw new \UnexpectedValueException('unknown_action');
    }

    if ($getAction == 'cup_update') {

        $returnArray = array(
            'data' => array(
                'max_mode' => 0
            ),
            'team_details' => array(
                'isRegistered' => FALSE,
                getConstNameTeamIdWithUnderscore() => 0,
                'html' => ''
            ),
            'policy_confirm' => array(
                'html' => ''
            ),
            'html' => ''
        );

        $cup_id = (isset($_GET[getConstNameCupIdWithUnderscore()]) && validate_int($_GET[getConstNameCupIdWithUnderscore()], true)) ?
            (int)$_GET[getConstNameCupIdWithUnderscore()] : 0;

        if ($cup_id < 1) {
            throw new \UnexpectedValueException('unknown_cup');
        }

        //
        // Get Cup Data Array
        $cupArray = getcup($cup_id);

        //
        // Safe Text of Team with Details
        $returnArray['html'] = str_replace(
            array(
                '%anz_player%'
            ),
            array(
                $cupArray['max_mode']
            ),
            $_language->module['update_cupInfo']
        );

        //
        // Check if User is registered
        $whereClauseArray = array();
        $whereClauseArray[] = 'ct.`cupID` = ' . $cup_id;
        $whereClauseArray[] = 'ctm.`userID` = ' . $userID;
        $whereClauseArray[] = 'ctm.`active` = 1';

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = cup_query(
            "SELECT
                  COUNT(*) AS `checkValue`,
                  ct.`teamID` AS `team_id`
                FROM `" . PREFIX . "cups_teilnehmer` ct
                JOIN `" . PREFIX . "cups_teams_member` ctm ON ct.`teamID` = ctm.`teamID`
                WHERE " . $whereClause,
            __FILE__
        );

        $isRegistered = mysqli_fetch_array($selectQuery);

        if ($isRegistered['checkValue'] < 1) {
            throw new \UnexpectedValueException('error_checkValue');
        }

        $team_id = $isRegistered[getConstNameTeamIdWithUnderscore()];

        $returnArray['team_details']['isRegistered'] = TRUE;
        $returnArray['team_details'][getConstNameTeamIdWithUnderscore()] = $team_id;

        //
        // Team Data Array
        $teamArray = getteam($team_id);

        $returnArray['team_details']['html'] = str_replace(
            array(
                '%teamName%',
                '%teamLink%',
                '%team_id%'
            ),
            array(
                $teamArray['name'],
                'index.php?site=teams&amp;action=details&amp;id=' . $team_id,
                $team_id
            ),
            $_language->module['update_teamRegistered']
        );

    } else if ($getAction == 'team_update') {

        $returnArray = array(
            'data' => array(
                'anz_member' => 0,
                'anz_gameaccounts' => 0
            ),
            'styles' => array(
                'createTeam' => array(
                    'status' => FALSE,
                    'color' => ' status-error'
                ),
                'policyConfirm'	=> array(
                    'status' => FALSE,
                    'color' => ' status-disable'
                )
            ),
            'html' => ''
        );

        $team_id = (isset($_GET[getConstNameTeamIdWithUnderscore()]) && validate_int($_GET[getConstNameTeamIdWithUnderscore()], true)) ?
            (int)$_GET[getConstNameTeamIdWithUnderscore()] : 0;

        if ($team_id < 1) {
            throw new \UnexpectedValueException('unknown_team');
        }

        $cup_id = (isset($_GET[getConstNameCupIdWithUnderscore()]) && validate_int($_GET[getConstNameCupIdWithUnderscore()], true)) ?
            (int)$_GET[getConstNameCupIdWithUnderscore()] : 0;

        if ($cup_id < 1) {
            throw new \UnexpectedValueException('unknown_team');
        }

        //
        // Team Data Array
        $teamArray = getteam($team_id);

        //
        // Cup Data Array
        $cupArray = getcup($cup_id);

        //
        // Get all Players of the Team
        $userArray = $teamArray['member'];

        //
        // Get all Gameaccounts (active) of the Team (Game => selected Cup)
        $anzGameaccounts = 0;
        if (validate_array($userArray, true)) {

            $whereClauseArray = array();
            $whereClauseArray[] = '`userID` IN (' . implode(', ', $userArray) . ')';
            $whereClauseArray[] = '`category` = \'' . $cupArray['game'] . '\'';
            $whereClauseArray[] = '`active` = 1';
            $whereClauseArray[] = '`deleted` = 0';

            $whereClause = implode(' AND ', $whereClauseArray);

            $selectQuery = cup_query(
                "SELECT
                        COUNT(*) AS `anz`
                    FROM `" . PREFIX . "cups_gameaccounts`
                    WHERE " . $whereClause,
                __FILE__
            );

            $getGameaccount = mysqli_fetch_array($selectQuery);

            $returnArray['data']['anz_gameaccounts'] = $getGameaccount['anz'];

        }

        //
        // Safe Data in returnArray
        $returnArray['data']['anz_member'] = $teamArray['anz_member'];


        //
        // Safe Text of Team with Details
        $returnArray['html'] = str_replace(
            array(
                '%teamName%',
                '%teamLink%',
                '%anz_player%',
                '%anz_gameaccounts%'
            ),
            array(
                $teamArray['name'],
                'index.php?site=teams&amp;action=details&amp;id=' . $team_id,
                $teamArray['anz_member'],
                $anzGameaccounts
            ),
            $_language->module['update_teamInfo']
        );

        //
        // Cup Register Check
        // Bedingung: Anzahl Spieler = Anzahl Gameaccounts
        if ($teamArray['anz_member'] == $anzGameaccounts) {

            if ($cupArray['max_mode'] == 5) {

                //
                // 5 oder mehr Spieler im Team?
                if ($teamArray['anz_member'] >= 5) {

                    $returnArray['styles']['createTeam']['status'] = TRUE;
                    $returnArray['styles']['createTeam']['color'] = ' status-ok';

                    $returnArray['styles']['policyConfirm']['color'] = ' status-error';

                } else {

                    $returnArray['styles']['createTeam']['color'] = ' status-error';
                    $returnArray['styles']['policyConfirm']['color'] = ' status-disable';

                }

            } else {

                //
                // 5 oder mehr Spieler im Team?
                if($teamArray['anz_member'] == $cupArray['max_mode']) {

                    $returnArray['styles']['createTeam']['status'] = TRUE;
                    $returnArray['styles']['createTeam']['color'] = ' status-ok';

                    $returnArray['styles']['policyConfirm']['color'] = ' status-error';

                } else {

                    $returnArray['styles']['createTeam']['color'] = ' status-error';
                    $returnArray['styles']['policyConfirm']['color'] = ' status-disable';

                }

            }

        }

    } else {
        throw new \UnexpectedValueException('unknown_action');
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    setLog('', $e->getMessage(), __FILE__, $e->getLine());
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
