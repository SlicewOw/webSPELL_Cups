<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {

    if (!$loggedin) {
        throw new \UnexpectedValueException('access_denied');
    }

    $postAction = (isset($_POST['action'])) ?
        getinput($_POST['action']) : '';

    if (empty($postAction) || ($postAction != 'seen')) {
        throw new \UnexpectedValueException('unknown_action');
    }

    $notify_id = (isset($_POST['notify_id']) && validate_int($_POST['notify_id'], true)) ?
        (int)$_POST['notify_id'] : 0;

    if ($notify_id < 1) {
        throw new \UnexpectedValueException('unknown_notification');
    }

    $returnArray['notifyID'] = $notify_id;

    $query = cup_query(
        "SELECT
                `receiver_id`
            FROM `" . PREFIX . "user_notifications`
            WHERE `notifyID` = " . $notify_id,
        __FILE__
    );

    $get = mysqli_fetch_array($query);

    if ($userID != $get['receiver_id']) {
        throw new \UnexpectedValueException('update_notification_failed_different_user');
    }

    $updateQuery = cup_query(
        "UPDATE `" . PREFIX . "user_notifications`
            SET notify_seen = 1,
                notify_seen_date = " . time() . "
            WHERE notifyID = " . $notify_id,
        __FILE__
    );

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
