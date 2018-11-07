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

    if (!validate_array($_POST, true)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    $postAction = (isset($_POST['action'])) ?
        getinput($_POST['action']) : '';

    if (empty($postAction)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    if ($postAction == 'saveCupSettings') {

        $cup_id = (isset($_POST['cup_id']) && validate_int($_POST['cup_id'])) ?
            (int)$_POST['cup_id'] : 0;

        if ($cup_id < 1) {
            throw new \Exception('unknown_cup_id');
        }

        $registration = (isset($_POST['registerFormat'])) ?
            $_POST['registerFormat'] : 'open';

        $mapVoteEnable = (isset($_POST['mapVoteEnable']) && is_numeric($_POST['mapVoteEnable'])) ?
            (int)$_POST['mapVoteEnable'] : 0;

        $mapPool_id = (isset($_POST['mapPool_id']) && is_numeric($_POST['mapPool_id'])) ?
            (int)$_POST['mapPool_id'] : 0;

        $saved = (isset($_POST['cupSaved']) && is_numeric($_POST['cupSaved'])) ?
            (int)$_POST['cupSaved'] : 0;

        $admin = (isset($_POST['adminVisible']) && is_numeric($_POST['adminVisible'])) ?
            (int)$_POST['adminVisible'] : 0;

        $updateQuery = mysqli_query(
            $_database,
            "UPDATE `".PREFIX."cups`
                SET	registration = '".$registration."',
                    mapvote_enable = ".$mapVoteEnable.",
                    mappool = ".$mapPool_id.",
                    saved = ".$saved.",
                    admin_visible = ".$admin."
                WHERE cupID = ".$cup_id
        );

        if(!$updateQuery) {
            throw new \Exception('cannot_save_cup_settings');
        }

        $returnArray['message'][] = $_language->module['cup_settings_saved'];

    } else {
        throw new \Exception($_language->module['unknown_action']);
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
