<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    if (!validate_array($_POST, true)) {
        throw new \UnexpectedValueException($_language->module['unknown_action']);
    }

    $postAction = (isset($_POST['action'])) ?
        getinput($_POST['action']) : '';

    if (empty($postAction)) {
        throw new \UnexpectedValueException($_language->module['unknown_action']);
    }

    if ($postAction == 'saveCupSettings') {

        $cup_id = (isset($_POST['cup_id']) && validate_int($_POST['cup_id'], true)) ?
            (int)$_POST['cup_id'] : 0;

        if ($cup_id < 1) {
            throw new \UnexpectedValueException('unknown_cup_id');
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

        $updateQuery = cup_query(
            "UPDATE `" . PREFIX . "cups`
                SET `registration` = '" . $registration . "',
                    `mapvote_enable` = " . $mapVoteEnable . ",
                    `mappool` = " . $mapPool_id . ",
                    `saved` = " . $saved . ",
                    `admin_visible` = " . $admin . "
                WHERE `cupID` = " . $cup_id,
            __FILE__
        );

        $deleteQuery = cup_query(
            "DELETE FROM `". PREFIX . "cups_settings`
                WHERE `cup_id` = " . $cup_id,
            __FILE__
        );

        $roundArray = (isset($_POST['round']) && validate_array($_POST['round'], true)) ?
            $_POST['round'] : array();

        $roundCounter = count($roundArray);
        if ($roundCounter > 0) {

            for ($x = 1; $x < ($roundCounter + 1); $x++) {

                $insertQuery = cup_query(
                    "INSERT INTO `" . PREFIX . "cups_settings`
                        (
                            `cup_id`,
                            `round`,
                            `format`
                        )
                        VALUES
                        (
                            " . $cup_id . ",
                            " . $x . ",
                            '" . $roundArray[$x - 1] . "'
                        )",
                    __FILE__
                );

            }

        }

        $returnArray['message'][] = $_language->module['cup_settings_saved'];

    } else if ($postAction == 'saveChallongeApiSettings') {

        $cup_id = (isset($_POST['cup_id']) && validate_int($_POST['cup_id'], true)) ?
            (int)$_POST['cup_id'] : 0;

        if ($cup_id < 1) {
            throw new \UnexpectedValueException('unknown_cup_id');
        }

        $activate_challonge_api = (isset($_POST['activate_challonge']) && validate_int($_POST['activate_challonge'])) ?
            (int)$_POST['activate_challonge'] : 0;

        if (($activate_challonge_api < 0) || ($activate_challonge_api > 1)) {
            $activate_challonge_api = 0;
        }

        $challonge_url = (isset($_POST['challonge_url']) && validate_url($_POST['challonge_url'])) ?
            getinput($_POST['challonge_url']) : '';

        if (empty($challonge_url)) {
            $activate_challonge_api = 0;
        }

        $setValuesArray = array();
        $setValuesArray[] = '`challonge_api` = ' . $activate_challonge_api;

        if (!empty($challonge_url)) {
            $setValuesArray[] = '`challonge_url` = \'' . $challonge_url . '\'';
        } else {
            $setValuesArray[] = '`challonge_url` = NULL';
        }

        if (count($setValuesArray) < 1) {
            throw new \UnexpectedValueException('cannot_set_any_value');
        }

        $updateQuery = cup_query(
            "UPDATE `" . PREFIX . "cups`
                SET " . implode(', ', $setValuesArray) . "
                WHERE `cupID` = " . $cup_id,
            __FILE__
        );

        $returnArray['message'][] = $_language->module['cup_settings_saved_challonge'];

    } else {
        throw new \UnexpectedValueException($_language->module['unknown_action']);
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
