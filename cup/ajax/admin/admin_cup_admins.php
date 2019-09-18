<?php

$returnArray = getDefaultReturnArray();

try {

    $_language->readModule('cups', false, true);

    if ($getAction == 'table') {

        $includePath = __DIR__ . '/../../../admin/cup_admin/includes/admin_team.php';

        if (!file_exists($includePath)) {
            throw new \UnexpectedValueException($_language->module['unknown_file']);
        }

        include($includePath);

        if (!isset($adminList) || empty($adminList)) {
            throw new \UnexpectedValueException($_language->module['unknown_action']);
        }

        $returnArray['html'] = $adminList;

    } else {

        $postAction = (isset($_POST['action'])) ?
            getinput($_POST['action']) : '';

        if (empty($postAction)) {
            throw new \UnexpectedValueException($_language->module['unknown_action']);
        }

        $admin_id = (isset($_POST['admin_id']) && validate_int($_POST['admin_id'])) ?
            (int)$_POST['admin_id'] : 0;

        if ($admin_id < 1) {
            throw new \UnexpectedValueException($_language->module['unknown_admin']);
        }

        $user_id = (isset($_POST['user_id']) && validate_int($_POST['user_id'])) ?
            (int)$_POST['user_id'] : 0;

        if ($user_id < 1) {
            throw new \UnexpectedValueException($_language->module['unknown_user']);
        }

        if ($postAction == 'addAdmin' || $postAction == 'editAdmin') {

            $position = (isset($_POST['position'])) ?
                getinput($_POST['position']) : '';

            if (empty($position)) {
                throw new \UnexpectedValueException($_language->module['unknown_position']);
            }

            $description = (isset($_POST['description'])) ?
                getinput($_POST['description']) : '';

            if ($postAction == 'addAdmin') {

                $query = mysqli_query(
                    $_database,
                    "INSERT INTO `" . PREFIX . "cups_team`
                        (
                            `userID`,
                            `date`,
                            `position`,
                            `description`
                        )
                        VALUES
                        (
                            " . $user_id . ",
                            " . time() . ",
                            '" . $position . "',
                            '" . $description . "'
                        )"
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['query_insert_failed']);
                }

            } else {

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_team`
                        SET `date` = " . time() . ",
                            `position` = '" . $position . "',
                            `description` = '" . $description . "'
                        WHERE `userID` = " . $user_id
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['query_update_failed']);
                }

            }

            $returnArray['message'][] = $_language->module['cup_admin_saved'];

        } else if ($postAction == 'deleteAdmin') {

            $query = mysqli_query(
                $_database,
                "DELETE FROM `" . PREFIX . "cups_team`
                    WHERE `userID` = " . $user_id
            );

            if (!$query) {
                throw new \UnexpectedValueException($_language->module['query_delete_failed']);
            }

        } else {
            throw new \UnexpectedValueException($_language->module['unknown_action']);
        }

    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
