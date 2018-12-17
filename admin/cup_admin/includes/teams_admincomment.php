<?php

try {

    if (!iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (!isset($teamID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?' . $_SERVER['QUERY_STRING'];

        try {

            if (isset($_POST['saveAdminComment'])) {

                $comment = getinput($_POST['admin_comment']);

                $insertQuery = mysqli_query(
                    $_database,
                    "INSERT INTO `" . PREFIX . "cups_teams_comments`
                        (
                            `teamID`,
                            `date`,
                            `userID`,
                            `comment`
                        )
                        VALUES
                        (
                            " . $teamID . ",
                            " . time() . ",
                            " . $userID . ",
                            '" . $comment . "'
                        )"
                );

                if (!$insertQuery) {
                    throw new \Exception($_language->module['query_insert_failed']);
                }

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: index.php?');

    } else {

        $commentList = '';

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `anz`
                    FROM `" . PREFIX . "cups_teams_comments`
                    WHERE `teamID` = " . $teamID
            )
        );

        if ($get['anz'] > 0) {

            $selectQuery = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "cups_teams_comments`
                    WHERE `teamID` = " . $teamID
            );

            if (!$selectQuery) {
                throw new \Exception($_language->module['query_select_failed']);
            }

            while ($get = mysqli_fetch_array($selectQuery)) {

                $commentList .= '<div class="list-group-item">';
                $commentList .= getoutput($get['comment']);
                $commentList .= '<br /><hr />'.getformatdatetime($get['date']).' - '.getnickname($get['userID']);
                $commentList .= '</div>';

            }

        } else {
            $commentList = '<div class="list-group-item">- / -</div>';
        }

        $data_array = array();
        $data_array['$commentList'] = $commentList;
        $data_array['$team_id'] = $teamID;
        $cup_teams_admin = $GLOBALS["_template_cup"]->replaceTemplate("cup_teams_admin_comment", $data_array);
        echo $cup_teams_admin;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
