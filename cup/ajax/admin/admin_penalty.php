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

    if (empty($getAction)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    if ($getAction == 'getPenaltyDetails') {

        $penalty_id = (isset($_GET['penalty_id']) && validate_int($_GET['penalty_id'], true)) ?
            (int)$_GET['penalty_id'] : 0;

        if ($penalty_id < 1) {
            throw new \Exception('unknown_penalty_id');
        }

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    cp.`adminID`,
                    cp.`date`,
                    cp.`duration_time`,
                    cp.`teamID`,
                    cp.`userID`,
                    cp.`comment`,
                    cp.`reasonID`,
                    cpc.`name_de`,
                    cpc.`name_uk`,
                    cpc.`points`,
                    cpc.`lifetime`,
                    u.`nickname`,
                    ct.`name` AS `team_name`
                FROM `" . PREFIX . "cups_penalty` cp
                JOIN `" . PREFIX . "cups_penalty_category` cpc ON cp.`reasonID` = cpc.`reasonID`
                LEFT JOIN  `" . PREFIX . "user` u ON u.`userID` = cp.`userID`
                LEFT JOIN  `" . PREFIX . "cups_teams` ct ON ct.`teamID` = cp.`teamID`
                WHERE `ppID` = " . $penalty_id
        );

        if (!$selectQuery) {
            $returnArray['message'][] = mysqli_error($_database);
            throw new \Exception($_language->module['query_select_failed']);
        }

        $get = mysqli_fetch_array($selectQuery);

        if ($get['userID'] > 0) {
            $receiver_url = $hp_url . '/index.php?site=profile&amp;id=' . $get['userID'];
            $receiver_name = $get['nickname'];
        } else {
            $receiver_url = $hp_url . '/index.php?site=teams&amp;action=details&amp;id=' . $get['teamID'];
            $receiver_name = $get['team_name'];
        }

        $receiver = '<a href="' . $receiver_url . '" target="_blank" class="blue">' . $receiver_name . '</a>';

        $returnArray['details'] = array(
            'receiver' => $receiver,
            'duration' => getformatdatetime($get['date']),
            'comment' => $get['comment'],
            'category' => array(
                'name_de' => $get['name_de'],
                'name_uk' => $get['name_uk']
            )
        );

    } else {
        throw new \Exception($_language->module['unknown_action']);
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
