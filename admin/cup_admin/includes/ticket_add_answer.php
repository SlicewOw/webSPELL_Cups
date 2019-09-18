<?php

try {

    $_language->readModule('support', false, true);

    $maxStatus = 3;

    $ticket_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($ticket_id < 1) {
        throw new \UnexpectedValueException($_language->module['unknown_action']);
    }

    if (!checkIfContentExists($ticket_id, 'ticketID', 'cups_supporttickets')) {
        throw new \UnexpectedValueException($_language->module['unknown_action']);
    }

    $cupAdminAcess = iscupadmin($userID);

    if (!iscupadmin($userID) || mb_substr(basename($_SERVER[ 'REQUEST_URI' ]), 0, 15) != "admincenter.php") {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $temp_status = 1;
    $temp_status2 = 1;

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=support&action=details&id=' . $ticket_id;

        try {

            if (isset($_POST['admin_submit'])) {

                $admin_id = (isset($_POST['adminID']) && validate_int($_POST['adminID'])) ?
                    (int)$_POST['adminID'] : 0;

                if ($admin_id < 1) {
                    throw new \UnexpectedValueException($_language->module['login']);
                }

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_supporttickets`
                        SET `take_date` = " . time() . ",
                            `adminID` = " . $admin_id . ",
                            `status` = 2
                        WHERE `ticketID` = " . $ticket_id
                );

                if (!$query) {
                    throw new \UnexpectedValueException('Query failed - Update');
                }

            } else if (isset($_POST['close_submit'])) {

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_supporttickets`
                        SET `closed_date` = " . time() . ",
                            `closed_by_id` = " . $userID . ",
                            `status` = " . $maxStatus . "
                        WHERE `ticketID` = " . $ticket_id
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['cannot_close_ticket']);
                }

            } else if (isset($_POST['submitTicketAnswer'])) {

                if (!isset($_POST['text']) || empty($_POST['text'])) {
                    throw new \UnexpectedValueException($_language->module['ticket_error_2']);
                }

                $setAdmin = (!getticket($ticket_id, 'admin')) ?
                    ', take_date = ' . time() . ', adminID = ' . $userID :
                    '';

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `".PREFIX."cups_supporttickets`
                        SET 	status = 2" . $setAdmin . "
                        WHERE 	ticketID = " . $ticket_id
                );

                if (!$updateQuery) {
                    throw new \UnexpectedValueException($_language->module['ticket_not_saved']);
                }

                $db = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT * FROM ".PREFIX."cups_supporttickets
                            WHERE ticketID = " . $ticket_id
                    )
                );

                // mail to user
                $ToEmail = getemail($db['userID']);

                $message = str_replace(
                    array('%admin_name%'),
                    array(getnickname($db['userID'])),
                    $_language->module['ticket_email_message']
                );

                $sendmail = \webspell\Email::sendEmail(
                    $admin_email,
                    'myRisk Gaming e.V.',
                    $ToEmail,
                    $_language->module['ticket_email_title'],
                    $message
                );

                if ($sendmail['result'] == 'fail') {
                    $_SESSION['errorArray'][] = $_language->module['email_not_send'];
                }

                $query = mysqli_query(
                    $_database,
                    "INSERT INTO `" . PREFIX . "cups_supporttickets_content`
                        (
                            `ticketID`,
                            `date`,
                            `userID`,
                            `text`
                        )
                        VALUES
                        (
                            " . $ticket_id . ",
                            " . time() . ",
                            " . $userID . ",
                            '" . getinput($_POST['text']) . "'
                        )"
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['ticket_not_saved']);
                }

                $_SESSION['successArray'][] = $_language->module['ticket_saved'];

            } else if (isset($_POST['reopen_ticket'])) {

                $ticket_id = (isset($_POST['ticket_id']) && validate_int($_POST['ticket_id'])) ?
                    (int)$_POST['ticket_id'] : 0;

                if ($ticket_id < 1) {
                    throw new \UnexpectedValueException($_language->module['ticket_not_saved']);
                }

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_supporttickets`
                        SET `status` = 2
                        WHERE `ticketID` = " . $ticket_id
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['ticket_not_saved']);
                }

                $nickname = getnickname($userID);

                $subquery = mysqli_query(
                    $_database,
                    "INSERT INTO `".PREFIX."cups_supporttickets_content`
                        (
                            `ticketID`,
                            `date`,
                            `userID`,
                            text`
                        )
                        VALUES
                        (
                            " . $ticket_id . ",
                            " . time() . ",
                            4,
                            'Ticket wurde durch " . $nickname . " erneut ge&ouml;ffnet.'
                        )"
                );

                if (!$subquery) {
                    throw new \UnexpectedValueException($_language->module['ticket_not_saved']);
                }

                $_SESSION['successArray'][] = $_language->module['ticket_saved'];

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
                        a.ticketID AS ticket_id,
                        a.userID AS ticket_userID,
                        a.adminID AS ticket_adminID,
                        a.name AS ticket_name,
                        a.text AS ticket_text,
                        a.status AS ticket_status,
                        a.teamID AS ticket_teamID,
                        a.opponentID AS ticket_opponentID,
                        a.cupID AS ticket_cupID,
                        a.matchID AS ticket_matchID,
                        a.start_date AS ticket_date_start,
                        a.closed_date AS ticket_date_closed,
                        a.closed_by_id AS ticket_closed_by,
                        a.screenshot AS ticket_screenshot,
                        b.name_de AS category_name
                    FROM `".PREFIX."cups_supporttickets` a
                    JOIN `".PREFIX."cups_supporttickets_category` b
                    WHERE a.ticketID = " . $ticket_id
            )
        );

        $admin = '';

        $urlArray = array();
        for ($x = 0; $x <= $maxStatus; $x++) {
            $urlArray[] = 'admincenter.php?site=cup&amp;mod=support&amp;action=admin';
        }

        if ($db['ticket_adminID'] == 0) {
            $admin .= '<form method="post">';
            $admin .= '<input type="hidden" name="adminID" value="'.$userID.'" />';
            $admin .= '<input type="submit" name="admin_submit" class="btn btn-info btn-sm white darkshadow" value="'.$_language->module['ticket_take'].'" />';
            $admin .= '</form>';
        }

        if ($db['ticket_status'] < $maxStatus) {
            $admin .= '<form method="post">';
            $admin .= '<input type="hidden" name="ticketID" value="'.$ticket_id.'" />';
            $admin .= '<input type="submit" name="close_submit" class="btn btn-info btn-sm white darkshadow" value="'.$_language->module['ticket_close'].'" />';
            $admin .= '</form>';
        } else {
            $temp_status = 0;
        }

        if (!empty($admin)) {
            $admin .= '<br /><br />';
        }

        $categoryName = $db['category_name'];

        $ticket_name = '';

        if ($categoryName == 'Match-Konflikt') {
            $ticket_name .= 'Match-Konflikt - '.getteam($db['ticket_teamID'], 'name').' vs. '.getteam($db['ticket_opponentID'], 'name').' // ';
        }

        $ticket_name .= $db['ticket_name'];

        $imageLink = $image_url . '/cup/ticket_screenshots/';
        $userLink = $hp_url . '/index.php?site=profile&amp;id=';

        $teamLink = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;id=';

        //
        // Ticket Details
        $ticket_details = $_language->module['ticket_opened'].' '.getformatdatetime($db['ticket_date_start']);
        $ticket_details .= ' / User: <a href="'.$userLink.$db['ticket_userID'].'" class="blue" target="_blank">'.getnickname($db['ticket_userID']).'</a>';
        if ($db['ticket_adminID'] != '0') {
            $ticket_details .= ' / Admin: <a href="'.$userLink.$db['ticket_adminID'].'" class="blue" target="_blank">'.getnickname($db['ticket_adminID']).'</a>';
        }
        $ticket_details .= ' / '.$categoryName;

        //
        // Status
        // 1: offen
        // 2: in Bearbeitung
        // 3: geschlossen
        $ticket_status = $_language->module['ticket_status_'.$db['ticket_status']];

        //
        // Ticket Info Details
        $ticketInfoArray = array();

        if (!empty($db['ticket_screenshot'])) {
            if (file_exists(__DIR__ . '/../../images/cup/ticket_screenshots/' . $db['ticket_screenshot'])) {
                $ticketInfoArray[] = 'Screenshot: <a href="' . $imageLink . $db['ticket_screenshot'] . '" target="_blank" class="blue">' . $db['ticket_screenshot'] . '</a>';
            } else {
                $ticketInfoArray[] = 'Screenshot: <del>' . $db['ticket_screenshot'] . '</del>';
            }
        }

        if ($db['ticket_teamID'] > 0) {
            $ticketInfoArray[] = 'Team: <a target="_blank" href="' . $teamLink . $db['ticket_teamID'] . '" class="blue">' . getteam($db['ticket_teamID'], 'name') . '</a>';
        }

        if ($db['ticket_opponentID'] > 0) {
            $ticketInfoArray[] = 'Opponent: <a target="_blank" href="' . $teamLink . $db['ticket_opponentID'] . '" class="blue">' . getteam($db['ticket_opponentID'], 'name') . '</a>';
        }

        if ($db['ticket_cupID'] > 0) {
            $ticketInfoArray[] = 'Cup: <a target="_blank" href="index.php?site=cup&amp;action=details&amp;id=' . $db['ticket_cupID'] . '" class="blue">' . getcup($db['ticket_cupID'], 'name') . '</a>';
        }

        if ($db['ticket_matchID'] > 0) {
            $matchArray = getmatch($db['ticket_matchID']);
            $match_url = $cup_url . '/index.php?site=cup&amp;action=match&amp;id=' . $matchArray[getConstNameCupIdWithUnderscore()] . '&amp;mID=' . $db['ticket_matchID'];
            $ticketInfoArray[] = 'Match: <a href="' . $match_url . '" target="_blank" class="blue">#' . $db['ticket_matchID'] . '</a>';
        }

        $ticket_info = '';
        if (validate_array($ticketInfoArray, true)) {
            $ticket_info = '<div class="list-group-item">' . implode(' / ', $ticketInfoArray) . '</div>';
        }

        $ticket_text = getoutput($db['ticket_text']);

        $ticketSeenFlag = getTicketSeenDate($ticket_id, $userID, 1);

        $history = '';

        $answers = '';
        $info = mysqli_query(
            $_database,
            "SELECT
                    `contentID`,
                    `date`,
                    `userID`,
                    `text`
                FROM `" . PREFIX . "cups_supporttickets_content`
                WHERE ticketID = " . $ticket_id . "
                ORDER BY date ASC"
        );

        if (!$info) {
            throw new \UnexpectedValueException($_language->module['query_select_failed']);
        }

        while ($ds = mysqli_fetch_array($info)) {

            if ($ds['userID'] == 4) {
                $panel_class = 'panel-info';
            } else {
                $panel_class = 'panel-default';
            }

            $detailsArray = array();

            $detailsArray[] = $_language->module['ticket_answer_on'] . ' ' . getformatdatetime($ds['date']);
            $detailsArray[] = $_language->module['ticket_by'] . ' ' . getnickname($ds['userID']);

            if ($ticketSeenFlag < $ds['date']) {
                $detailsArray[] = '<span class="italic">ungelesen</span>';
            }

            $text = getoutput($ds['text']);

            $data_array = array();
            $data_array['$panel_class'] = $panel_class;
            $data_array['$ticket_id'] = $ds['contentID'];
            $data_array['$details'] = implode(' / ', $detailsArray);
            $data_array['$text'] = $text;
            $answers .= $GLOBALS["_template_cup"]->replaceTemplate("ticket_answer", $data_array);


            $history .= '<a href="#ticketAnswer_'.$ds['contentID'].'" class="list-group-item">';

            if (iscupadmin($ds['userID'])) {

                $history .= '<span>'.getnickname($ds['userID']).'</span>';
                $history .= '<span class="pull-right grey ten">'.getformatdatetime($ds['date']).'</span>';

            } else {

                $history .= '<span class="grey ten">'.getformatdatetime($ds['date']).'</span>';
                $history .= '<span class="pull-right">'.getnickname($ds['userID']).'</span>';

            }

            $history .= '</a>';

        }

        if ($db['ticket_status'] == $maxStatus) {

            $closedTitle = getformatdatetime($db['ticket_date_closed']);
            if ($db['ticket_closed_by'] == 1) {
                $closedTitle .= ' / '.$_language->module['closed_by'].' '.getnickname($db['ticket_closed_by']);
            }

            $data_array = array();
            $data_array['$panel_class'] = 'panel-default';
            $data_array['$ticket_id'] = 0;
            $data_array['$details'] = $closedTitle;
            $data_array['$text'] = $_language->module['ticket_closed'];
            $answers .= $GLOBALS["_template_cup"]->replaceTemplate("ticket_answer", $data_array);

            $answers .= '<form method="post">';
            $answers .= '<input type="hidden" name="ticket_id" value="'.$ticket_id.'" />';
            $answers .= '<input type="submit" name="reopen_ticket" class="btn btn-info btn-sm white darkshadow" value="'.$_language->module['ticket_reopen'].'" />';
            $answers .= '</form>';

        }

        $text = '';

        $ticket_add_answer = '';
        if ($temp_status) {

            $text = '';
            $text .= '<p><br /></p>';
            $text .= '<p><b>Mit freundlichen Gr&uuml;&szlig;en</b><br />';
            $text .= getnickname($userID) . '<br /><i class="grey">Support Team</i></p>';

            $data_array = array();
            $data_array['$textfromClass'] = '';
            $data_array['$ticketID'] = $ticket_id;
            $data_array['$text'] = $text;
            $ticket_add_answer = $GLOBALS["_template_cup"]->replaceTemplate("ticket_add_answer", $data_array);

        }

        if ($temp_status2) {

            $data_array = array();
            $data_array['$admin'] = $admin;
            $data_array['$ticket_name'] = $ticket_name;
            $data_array['$ticket_status'] = $ticket_status;
            $data_array['$ticket_details'] = $ticket_details;
            $data_array['$ticket_info'] = $ticket_info;
            $data_array['$ticket_text'] = $ticket_text;
            $data_array['$answers'] = $answers;
            $data_array['$ticket_add_answer'] = $ticket_add_answer;

            $admin_info = '';

            $getUser = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT * FROM `" . PREFIX . "user`
                        WHERE userID = " . $db['ticket_userID']
                )
            );

            $admin_info .= '<div class="list-group-item">'.$getUser['firstname'].' '.$getUser['lastname'].'</div>';
            $admin_info .= '<div class="list-group-item">'.$getUser['email'].'</div>';
            $admin_info .= '<div class="list-group-item">Geburtstag: '.getformatdate(strtotime($getUser['birthday'])).'</div>';
            $admin_info .= '<div class="list-group-item">Registriert: '.getformatdatetime($getUser['registerdate']).'</div>';
            $admin_info .= '<div class="list-group-item">Letzter Login: '.getformatdatetime($getUser['lastlogin']).'</div>';

            $userLogLink = 'admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id=';
            $teamDetailsLink = 'admincenter.php?site=cup&mod=teams&action=active&id=';

            $admin_info .= '<a href="' . $userLogLink . $db['ticket_userID'] . '" target="_blank" class="list-group-item">&raquo; Gameaccounts</a>';

            $admin_info .= '<div class="list-group-item alert-danger">Teams</div>';

            $query = mysqli_query(
                $_database,
                "SELECT
                        b.teamID AS team_id,
                        b.name AS name
                    FROM `" . PREFIX . "cups_teams_member` a
                    JOIN `" . PREFIX . "cups_teams` b ON a.teamID = b.teamID
                    WHERE a.userID = " . $db['ticket_userID'] . " AND active = 1"
            );
            while ($get = mysqli_fetch_array($query)) {
                $admin_info .= '<a href="'.$teamDetailsLink.$get['team_id'].'" target="_blank" class="list-group-item">'.$get['name'].'</a>';
            }

            $data_array['$admin_info'] = $admin_info;
            $data_array['$history'] = $history;
            $ticket_add_answer_home = $GLOBALS["_template_cup"]->replaceTemplate("ticket_add_answer_home_admin", $data_array);
            echo $ticket_add_answer_home;

        }

        setTicketSeenDate($ticket_id, $userID, 1);

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
