<?php

try {

    $_language->readModule('cups');

    if (!$loggedin) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    if ($getAction == 'new_ticket') {
        // Neues Ticket eroeffnen
        include(__DIR__ . '/includes/ticket_add.php');
    } else if ($getAction == 'details' && isset($_GET['id'])) {

        $ticket_id = (validate_int($_GET['id'], true)) ?
            (int)$_GET['id'] : 0;

        if ($ticket_id < 1) {
            throw new \UnexpectedValueException($_language->module['no_ticket_access']);
        }

        if (!getTicketAccess($ticket_id)) {
            throw new \UnexpectedValueException($_language->module['no_ticket_access']);
        }

        include(__DIR__ . '/includes/ticket_add_answer.php');

    } else {

        $teamArray = getteam($userID, 'teamID');

        $whereClause = '';
        if (validate_array($teamArray, true)) {

            $teamString = implode(', ', $teamArray);

            $whereClause .= ' OR a.teamID IN ('.$teamString.')';
            $whereClause .= ' OR a.opponentID IN ('.$teamString.')';

        }

        $ergebnis = mysqli_query(
            $_database,
            "SELECT
                    a.ticketID,
                    a.adminID,
                    a.name,
                    a.categoryID,
                    a.start_date,
                    a.userID,
                    a.teamID,
                    a.opponentID,
                    a.status,
                    b.name_de AS category_name
                FROM `".PREFIX."cups_supporttickets` a
                JOIN `".PREFIX."cups_supporttickets_category` b ON a.categoryID = b.categoryID
                WHERE a.userID = ".$userID." OR a.opponent_adminID = ".$userID.$whereClause."
                ORDER BY a.status ASC, a.start_date DESC"
        );
        if (mysqli_num_rows($ergebnis)) {

            $ticket_list = '';
            while ($ds = mysqli_fetch_array($ergebnis)) {

                $ticketID = $ds['ticketID'];

                if (getticket($ticketID, 'new_answer') > 0) {
                    $panel_class = 'alert-info';
                } else {
                    $panel_class = '';
                }

                switch($ds['status']) {
                    case 1:
                        $btnClass = ' btn-danger';
                        break;
                    case 2:
                        $btnClass = ' btn-warning';
                        break;
                    case 3:
                        $btnClass = ' btn-success';
                        break;
                    default:
                        $btnClass = '';
                        break;
                }
                $link = '<span class="btn'.$btnClass.' btn-xs">'.$_language->module['ticket_status_'.$ds['status']].'</span>';

                $data_array = array();
                $data_array['$panel_class'] = $panel_class;
                $data_array['$ticketID'] = $ticketID;
                $data_array['$name'] = $ds['name'];
                $data_array['$category'] = $ds['category_name'];
                $data_array['$adminName'] = ($ds['adminID'] > 0) ? getnickname($ds['adminID']) : '';
                $data_array['$date'] = getformatdatetime($ds['start_date']);
                $data_array['$link'] = $link;
                $ticket_list .= $GLOBALS["_template_cup"]->replaceTemplate("ticket_list", $data_array);

            }

        } else {
            $ticket_list = '<tr><td colspan="5">'.$_language->module['no_ticket'].'</td></tr>';
        }

        $data_array = array();
        $data_array['$ticketList'] = $ticket_list;
        $ticket_home = $GLOBALS["_template_cup"]->replaceTemplate("ticket_home", $data_array);
        echo $ticket_home;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
