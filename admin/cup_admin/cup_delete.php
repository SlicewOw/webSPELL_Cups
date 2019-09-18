<?php

try {

    $_language->readModule('cups', false, true);

    if (!($_language->module['login'] && iscupadmin($userID))) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \UnexpectedValueException($_language->module['unknown_cup']);
    }

    if (!checkIfContentExists($cup_id, getConstNameCupId(), 'cups')) {
        throw new \UnexpectedValueException($_language->module['unknown_cup']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=cup';

        try {

            if (isset($_POST['delete'])) {

                $query = mysqli_query(
                    $_database,
                    "DELETE FROM `" . PREFIX . "cups`
                        WHERE `cupID` = " . $cup_id
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_lanugage->module['query_delete_failed']);
                }

                $text = 'Cup #' . $cup_id . ' wurde gel&ouml;scht';
                $_SESSION['successArray'][] = $text;

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $db = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `name`
                    FROM `" . PREFIX . "cups`
                    WHERE `cupID` = " . $cup_id
            )
        );

        $data_array = array();
        $data_array['$text'] = str_replace(
            '%cup_name%',
            $db['name'],
            $_language->module['cup_delete_info']
        );
        $data_array['$cupID'] = $cup_id;
        $cups_delete = $GLOBALS["_template_cup"]->replaceTemplate("cups_delete", $data_array);
        echo $cups_delete;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
