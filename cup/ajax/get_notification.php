<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array(),
    'notification' => array(
        'badge' => 0,
        'container' => ''
    )
);

try {

    $_language->readModule('login');

    $whereClauseArray = array();
    $whereClauseArray[] = '`receiver_id` = ' . $userID;
    $whereClauseArray[] = '`notify_seen` = 0';

    $whereClause = implode(' AND ', $whereClauseArray);

    $selectQuery = cup_query(
        "SELECT
                COUNT(*) AS `anz`
            FROM `" . PREFIX . "user_notifications`
            WHERE " . $whereClause,
        __FILE__
    );

    $get = mysqli_fetch_array($selectQuery);

    $anz = $get['anz'];

    if ($anz > 0) {

        $returnArray['notification']['badge'] = $anz;

        $query = cup_query(
            "SELECT * FROM `" . PREFIX . "user_notifications`
                WHERE " . $whereClause . "
                ORDER BY `date` DESC
                LIMIT 0, 6",
            __FILE__
        );

        while ($get = mysqli_fetch_array($query)) {

            $returnArray['notification']['container'] .= '<li><a href="' . $get['parent_url'] . '" onclick="updateNotifyStatus('.$get['notifyID'].');">';
            $returnArray['notification']['container'] .= $get['message'] . '<br />';
            $returnArray['notification']['container'] .= '<span class="fs_ten grey">' . getnickname($get['transmitter_id']) . ' - ' . getformatdate($get['date']) . '</span>';
            $returnArray['notification']['container'] .= '</a></li>';

        }

    }

    if ($anz < 6) {

        $whereClauseArray = array();
        $whereClauseArray[] = '`receiver_id` = ' . $userID;
        $whereClauseArray[] = '`notify_seen` = 1';

        $whereClause = implode(' AND ', $whereClauseArray);

        $showOldNotify = 6 - $anz;

        $selectQuery = cup_query(
            "SELECT * FROM `" . PREFIX . "user_notifications`
                WHERE " . $whereClause . "
                ORDER BY  `date` DESC
                LIMIT 0, " . $showOldNotify,
            __FILE__
        );

        if (mysqli_num_rows($selectQuery) > 0) {

            while ($get = mysqli_fetch_array($selectQuery)) {

                $returnArray['notification']['container'] .= '<li><a href="' . $get['parent_url'] . '">';
                $returnArray['notification']['container'] .= $get['message'] . '<br />';
                $returnArray['notification']['container'] .= '<span class="fs_ten grey">' . getnickname($get['transmitter_id']) . ' - ' . getformatdate($get['date']) . '</span>';
                $returnArray['notification']['container'] .= '</a></li>';

            }

        }

    }

    if ($returnArray['notification']['badge'] < 1) {
        $returnArray['notification']['container'] = '<li><a href="#">' . $_language->module['no_notification'] . '</a></li>';
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
