<?php

try {

    $_language->readModule('cups');

    if (!$loggedin) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'index.php?site=support';

        try {

            if (isset($_POST['submit'])) {

                $name = (isset($_POST['name'])) ?
                    getinput($_POST['name']) : '';

                if (empty($name)) {
                    throw new \UnexpectedValueException($_language->module['ticket_error_1']);
                }

                $category_id = (isset($_POST['categoryID']) && validate_int($_POST['categoryID'])) ?
                    (int)$_POST['categoryID'] : 0;

                $team_id = (isset($_POST['teamID']) && validate_int($_POST['teamID'])) ?
                    (int)$_POST['teamID'] : 0;

                $opponent_id = (isset($_POST['opponentID']) && validate_int($_POST['opponentID'])) ?
                    (int)$_POST['opponentID'] : 0;

                $cup_id = (isset($_POST['cupID']) && validate_int($_POST['cupID'])) ?
                    (int)$_POST['cupID'] : 0;

                $match_id = (isset($_POST['matchID']) && validate_int($_POST['matchID'])) ?
                    (int)$_POST['matchID'] : 0;

                $text = (isset($_POST['text'])) ?
                    getinput($_POST['text']) : '';

                if (empty($text)) {
                    throw new \UnexpectedValueException($_language->module['ticket_error_2']);
                }

                $query = mysqli_query(
                    $_database,
                    "INSERT INTO `" . PREFIX . "cups_supporttickets`
                        (
                            `start_date`,
                            `userID`,
                            `name`,
                            `categoryID`,
                            `teamID`,
                            `cupID`,
                            `opponentID`,
                            `matchID`,
                            `text`
                        )
                        VALUES
                        (
                            " . time() . ",
                            " . $userID . ",
                            '" . $name . "',
                            " . $category_id . ",
                            " . $team_id . ",
                            " . $cup_id . ",
                            " . $opponent_id . ",
                            " . $match_id . ",
                            '" . $text . "'
                        )"
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['query_insert_failed']);
                }

                $ticket_id = mysqli_insert_id($_database);

                $setStatusFlagArray = array(
                    $userID,
                    $team_id,
                    $opponent_id
                );

                $setFlagCount = count($setStatusFlagArray);
                for ($i = 0; $i < $setFlagCount; $i++) {

                    $primary_id = $setStatusFlagArray[$i];
                    if ($primary_id > 0) {
                        insertTicketStatus($ticket_id, $primary_id);
                    }

                }

                $_language->readModule('formvalidation', true, true);

                $serverPath = 'images/cup/ticket_screenshots/';
                $globalPath = __DIR__ . '/../../../' . $serverPath;

                $upload = new \webspell\HttpUpload('screenshot');
                if ($upload->hasFile() && ($upload->hasError() === false)) {

                    $mime_types = array('image/jpeg', 'image/png', 'image/gif');

                    if (!$upload->supportedMimeType($mime_types)) {
                        throw new \UnexpectedValueException($_language->module['unsupported_image_type']);
                    }

                    $imageInformation = getimagesize($upload->getTempFile());

                    if (!is_array($imageInformation)) {
                        throw new \UnexpectedValueException($_language->module['broken_image']);
                    }

                    switch ($imageInformation[2]) {
                        case 1:
                            $type = '.gif';
                            break;
                        case 3:
                            $type = '.png';
                            break;
                        default:
                            $type = '.jpg';
                            break;
                    }

                    $fileName = convert2filename($ticket_id) . $type;

                    if (!$upload->saveAs($serverPath . $fileName, true)) {
                        throw new \UnexpectedValueException($_language->module['broken_image']);
                    }

                    @chmod($globalPath . $fileName, 0777);

                    $query = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "cups_supporttickets`
                            SET `screenshot` = '" . $fileName . "'
                            WHERE `ticketID` = " . $ticket_id
                    );

                    if (!$query) {
                        throw new \UnexpectedValueException($_language->module['query_update_failed']);
                    }

                }

                $parent_url .= '&action=details&id=' . $ticket_id;

            } else {
                throw new \UnexpectedValueException($_language->module['unknown_action']);
            }

        } catch (Exception $e) {

            $_SESSION['ticketErrorArray'][] = $e->getMessage();

            $parent_url .= '&action=new_ticket';

        }

        header('Location: ' . $parent_url);

    } else {

        $error = '';
        if (isset($_SESSION['ticketErrorArray'])) {

            if (validate_array($_SESSION['ticketErrorArray'], true)) {
                $error = showError(implode('<br />', $_SESSION['ticketErrorArray']));
            }

            unset($_SESSION['ticketErrorArray']);

        }

        $teams = '<option value="0" class="italic">'.$_language->module['ticket_no_team'].'</option>';
        $teams .= getcupteams('', 1);

        $cups = '<option value="0" class="italic">'.$_language->module['ticket_no_cup'].'</option>';
        $cups .= getcups();

        $opponent_id = (isset($_GET['opponentID']) && validate_int($_GET['opponentID'])) ?
            (int)$_GET['opponentID'] : 0;

        $match_id = (isset($_GET['matchID']) && validate_int($_GET['matchID'])) ?
            (int)$_GET['matchID'] : 0;

        $data_array = array();
        $data_array['$error'] = $error;
        $data_array['$name'] = '';
        $data_array['$categories'] = getticketcategories();
        $data_array['$teams'] = $teams;
        $data_array['$cups'] = $cups;
        $data_array['$text'] = '';
        $data_array['$opponentID'] = $opponent_id;
        $data_array['$matchID'] = $match_id;
        $ticket_add = $GLOBALS["_template_cup"]->replaceTemplate("ticket_add", $data_array);
        echo $ticket_add;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
