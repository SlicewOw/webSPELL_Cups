<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array(),
    'html' => ''
);

try {

    $_language->readModule('cups', false, true);

    if (!iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $ticket_type = (isset($_GET['ticket_type']) && validate_int($_GET['ticket_type'], true)) ?
        (int)$_GET['ticket_type'] : '';

    $category = (isset($_GET['category'])) ?
        getinput($_GET['category']) : '';

    $value = (isset($_GET['value'])) ?
        getinput($_GET['value']) : '';

    if (!isset($cupID)) {
        $cupID = 0;
    }

    if (!isset($matchID)) {
        $matchID = 0;
    }

    if (!isset($user_id)) {
        $user_id = 0;
    }

    if ($category == 'category') {

        if ($ticket_type < 1) {
            throw new \UnexpectedValueException($_language->module['unknown_ticket_type']);
        }

        $categories = getticketcategories($value);

        $cups = '<option value="0" class="italic">' . $_language->module['ticket_no_cup'] . '</option>';
        $cups .= getcups('', $cupID);

        $matches = getmatches($cupID, $matchID, TRUE);

        $teams = getteams();
        $users = getuserlist($user_id);

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `template`
                    FROM `" . PREFIX . "cups_supporttickets_category`
                    WHERE `categoryID` = " . $ticket_type
            )
        );

        if (empty($get['template'])) {
            //
            // Default Template
            $data_array = array();
            $data_array['$categories'] = $categories;
            $data_array['$cups'] = $cups;
            $data_array['$matches'] = $matches;
            $data_array['$teams'] = $teams;
            $data_array['$users'] = $users;
            $data_array['$name'] = '';
            $data_array['$text'] = '';
            $returnArray['html'] = $GLOBALS["_template_cup"]->replaceTemplate(
                "ticket_add_admin_default",
                $data_array
            );

        } else {

            $data_array = array();
            $data_array['$categories'] = $categories;
            $data_array['$cups'] = $cups;
            $data_array['$matches'] = $matches;
            $data_array['$teams'] = $teams;
            $data_array['$users'] = $users;
            $data_array['$name'] = '';
            $data_array['$text'] = '';
            $returnArray['html'] = $GLOBALS["_template_cup"]->replaceTemplate(
                "ticket_add_admin_" . $get['template'],
                $data_array
            );

        }

    } else if ($category == 'matches') {

        $matches = getmatches($value, 0, FALSE);
        $returnArray['html'] .= $matches;

    } else if ($category == 'match_teams') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `team1`,
                        `team2`
                    FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE `matchID` = " . $value
            )
        );

        for ($x = 1; $x < 3; $x++) {

            $team = 'team'.$x;

            $team_id = $get[$team];

            $returnArray['data']['team'][$x] = array(
                'name' => getteam($team_id, 'name'),
                getConstNameTeamIdWithUnderscore() => $team_id
            );

            if (!isset($returnArray['data']['player'][$x])) {
                $returnArray['data']['player'][$x] = '';
            }

            $playerQuery = mysqli_query(
                $_database,
                "SELECT
                        a.`userID` AS `user_id`,
                        b.`nickname` AS `nickname`
                    FROM `" . PREFIX . "cups_teams_member` a
                    JOIN `" . PREFIX . "user` b ON a.`userID` = b.`userID`
                    WHERE `teamID` = " . $team_id . " AND `active` = 1"
            );

            if (!$playerQuery) {
                throw new \UnexpectedValueException($_language->module['query_select_failed']);
            }

            while ($subget = mysqli_fetch_array($playerQuery)) {

                $inputValue = '#' . $subget['user_id'] . ' - ' . $subget['nickname'];

                $returnArray['data']['player'][$x] .= '<div class="list-group-item">';
                $returnArray['data']['player'][$x] .= '<input type="checkbox" name="checkbox[' . $team . '][]" value="' . $inputValue . '" /> ' . $subget['nickname'];
                $returnArray['data']['player'][$x] .= '</div>';
            }

        }

    } else if ($category == 'match') {

        $returnArray['html'] .= '<option value="0" class="italic">' . $_language->module['ticket_no_team'] . '</option>';

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    `team1`,
                    `team2`
                FROM `" . PREFIX . "cups_matches_playoff`
                WHERE `matchID` = " . $value
        );

        if (!$selectQuery) {
            throw new \UnexpectedValueException($_language->module['query_select_failed']);
        }

        $get = mysqli_fetch_array($selectQuery);

        $returnArray['html'] .= '<option value="' . $get['team1'] . '">' . getteam($get['team1'], 'name') . '</option>';
        $returnArray['html'] .= '<option value="' . $get['team2'] . '">' . getteam($get['team2'], 'name') . '</option>';

    } else if ($category == 'team') {

        $returnArray['html'] .= '<option value="0" class="italic">' . $_language->module['ticket_no_player'] . '</option>';

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    a.`userID` AS `userID`,
                    b.`nickname` AS `nickname`
                FROM `" . PREFIX . "cups_teams_member` a
                JOIN `" . PREFIX . "user` b ON a.userID = b.userID
                WHERE a.`teamID` = " . $value." AND a.`active` = 1
                ORDER BY b.`userID` ASC"
        );

        if (!$selectQuery) {
            throw new \UnexpectedValueException($_language->module['query_select_failed']);
        }

        while ($get = mysqli_fetch_array($selectQuery)) {
            $returnArray['html'] .= '<option value="' . $get['userID'] . '">' . $get['nickname'] . '</option>';
        }

    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
