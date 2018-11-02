<?php

$_language->readModule('cups');

if ($loggedin) {

    if ($getAction == 'new_ticket') {
        // Neues Ticket eroeffnen
        include($dir_cup.'ticket_add.php');
    } else if ($getAction == 'details' && isset($_GET['id']) && is_numeric($_GET['id'])) {

        $ticket_id = (int)$_GET['id'];
        // ticket($userID, $ticket_id, 'user')
        if (getTicketAccess($ticket_id) || iscupadmin($userID)) {
            // Neue Antwort auf Ticket schreiben
            include($dir_cup.'ticket_add_answer.php');
        } else {
            echo showError($_language->module['no_ticket_access']);
        }

    } else if ($getAction == 'admin' && iscupadmin($userID)) {
        // Adminbereich
        include($dir_cup.'admin/ticket_admin.php');	
    } else if ($getAction == 'admin_add' && iscupadmin($userID)) {
        // Adminbereich
        include($dir_cup.'ticket_add_admin.php');
    } else {

        $admin = '';
        if(iscupadmin($userID)) {
            $admin = ' <a class="btn btn-info btn-sm white darkshadow" href="index.php?site=support&amp;action=admin">Admin</a>';
        }

        $teamArray = getteam($userID, 'teamID');

        $whereClause = '';
        if(is_array($teamArray) && count($teamArray) > 0) {

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
                WHERE 
                    a.userID = ".$userID." OR a.opponent_adminID = ".$userID.$whereClause."
                ORDER BY a.status ASC, a.start_date DESC"
        );
        if(mysqli_num_rows($ergebnis)) {

            $ticket_list = '';
            while($ds = mysqli_fetch_array($ergebnis)) {

                $ticketID = $ds['ticketID'];

                if(iscupadmin($userID) && getticket($ticketID, 'new_answer_admin') > 0) {
                    $panel_class = 'alert-info';
                } elseif(!iscupadmin($userID) && getticket($ticketID, 'new_answer') > 0) {
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
                $data_array['$ticketID'] 	= $ticketID;
                $data_array['$name'] 		= $ds['name'];
                $data_array['$category'] 	= $ds['category_name'];
                $data_array['$adminName'] 	= ($ds['adminID'] > 0) ? getnickname($ds['adminID']) : '';
                $data_array['$date'] 		= getformatdatetime($ds['start_date']);
                $data_array['$link'] 		= $link;
                $ticket_list .= $GLOBALS["_template_cup"]->replaceTemplate("ticket_list", $data_array);

            }

        } else {
            $ticket_list = '<tr><td colspan="5">'.$_language->module['no_ticket'].'</td></tr>';
        }

        $data_array = array();
        $data_array['$admin'] 	    = $admin;
        $data_array['$ticketList']  = $ticket_list;
        $ticket_home = $GLOBALS["_template_cup"]->replaceTemplate("ticket_home", $data_array);
        echo $ticket_home;

    }

} else {
	echo $_language->module['login'];
}