<?php

$returnArray = getDefaultReturnArray();

try {

    $_language->readModule('support', false, true);

    if (!iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $ticketStatus = (isset($_GET['status']) && validate_int($_GET['status'], true)) ?
        (int)$_GET['status'] : 0;

    if ($ticketStatus > 3) {
        $ticketStatus = 3;
    } else if ($ticketStatus < 0) {
        $ticketStatus = 1;
    }

    if ($ticketStatus > 0) {

        if ($getAction != 'update') {
            throw new \UnexpectedValueException($_language->module['unknown_action']);
        }

        $whereClauseArray = array();

        $pastTime = (isset($_GET['pastTime']) && validate_int($_GET['pastTime'])) ?
            (int)$_GET['pastTime'] : 0;

        $whereClauseArray[] = '`status` = ' . $ticketStatus;

        if ($pastTime > 0) {
            $whereClauseArray[] = '`start_date` >= ' . $pastTime;
        } else if ($ticketStatus > 2) {

            //
            // Zeige nur Tickets innerhalb des vergangenen Monats
            // 60sec * 60min * 24h * 31d = 2678400
            // 60sec * 60min * 24h * 15d = 1296000
            $whereClauseArray[] = '`start_date` >= ' . (time() - 1296000);

        }

        $ticketCategory = (isset($_GET['cat']) && validate_int($_GET['cat'], true)) ?
            (int)$_GET['cat'] : 0;

        if ($ticketCategory > 0) {
            $whereClauseArray[] = '`categoryID` = ' . $ticketCategory;
        }

        $whereClause = (validate_array($whereClauseArray, true)) ?
            'WHERE ' . implode(' AND ', $whereClauseArray) : '';

        $ergebnis = mysqli_query(
            $_database,
            "SELECT
                    `ticketID`,
                    `userID`,
                    `adminID`,
                    `name`,
                    `categoryID`,
                    `start_date`,
                    `status`
                FROM `" . PREFIX . "cups_supporttickets`
                " . $whereClause . "
                ORDER BY `start_date` DESC, `categoryID` ASC"
        );

        if (!$ergebnis) {
            throw new \UnexpectedValueException($_language->module['query_select_failed']);
        }

        if (mysqli_num_rows($ergebnis) > 0) {

            $ticket_content = '';

            $n = 1;
            while ($ds = mysqli_fetch_array($ergebnis)) {

                $ticket_id = $ds['ticketID'];

                //
                // Admin URL?
                $url = ($getSite == 'support') ?
                    'index.php?site=support&amp;action=details&amp;id=' . $ticket_id :
                    'admincenter.php?site=cup&amp;mod=support&amp;action=details&amp;id=' . $ticket_id;

                $takeTicket = '';
                if ($ticketStatus == 1) {
                    $takeTicket = '<button type="button" id="ticket_' . $ticket_id . '" class="btn btn-danger btn-xs" onclick="takeTicket(' . $ticket_id . ');">';
                    $takeTicket .= $_language->module['ticket_take'];
                    $takeTicket .= '</button> ';
                }

                $data_array = array();
                $data_array['$n'] = $n++;
                $data_array['$hp_url'] = $hp_url;
                $data_array['$ticket_id'] = $ticket_id;
                $data_array['$isNewAnswer'] = (getticket($ticket_id, 'new_answer_admin') > 0) ? ' class="alert-danger"' : '';
                $data_array['$url'] = $url;
                $data_array['$name'] = $ds['name'];
                $data_array['$user_id'] = $ds['userID'];
                $data_array['$username'] = getnickname($ds['userID']);
                $data_array['$admin'] = ($ds['adminID'] > 0) ? getnickname($ds['adminID']) : '';
                $data_array['$category'] = getticket($ds['categoryID'], 'category');
                $data_array['$date'] = getformatdatetime($ds['start_date']);
                $data_array['$takeTicket'] = $takeTicket;
                $returnArray['html'] .= $GLOBALS["_template_cup"]->replaceTemplate("ticket_admin_list", $data_array);

            }

        } else {
            $returnArray['html'] = '<tr><td colspan="7">'.$_language->module['no_ticket'].'</td></tr>';
        }

    } else {

        if ($getAction == 'admin_take') {

            $ticket_id = (isset($_POST['ticket_id']) && validate_int($_POST['ticket_id'], true)) ?
                (int)$_POST['ticket_id'] : 0;

            if ($ticket_id < 1) {
                throw new \UnexpectedValueException($_language->module['unknown_ticket']);
            }

            $admin_id = (isset($_POST['admin_id']) && validate_int($_POST['admin_id'])) ?
                (int)$_POST['admin_id'] : 0;

            if ($admin_id < 1) {
                throw new \UnexpectedValueException($_language->module['unknown_admin']);
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
                throw new \UnexpectedValueException($_language->module['query_update_failed']);
            }

            $insertQuery = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "cups_supporttickets_status`
                    (
                        `ticket_id`,
                        `primary_id`,
                        `admin`
                    )
                    VALUES
                    (
                        " . $ticket_id . ",
                        " . $admin_id . ",
                        1
                    )"
            );

            if (!$insertQuery) {
                print_r(mysqli_error($_database));
                throw new \UnexpectedValueException($_language->module['query_insert_failed']);
            }

            $adminName = getnickname($admin_id);

            $returnArray['data'] = array(
                'username' => $adminName
            );

        } else {
            throw new \UnexpectedValueException($_language->module['unknown_action']);
        }

    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
