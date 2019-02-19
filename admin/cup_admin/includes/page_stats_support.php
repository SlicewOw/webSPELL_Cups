<?php

$selectQuery = cup_query(
    "SELECT
            `ticketID`
        FROM `" . PREFIX . "cups_supporttickets`",
    __FILE__
);

$totalTickets = mysqli_num_rows($selectQuery);

$ticket_adm_list = '<div class="list-group-item">' . $_language->module['total_count'] . '<span class="pull-right grey">' . $totalTickets . ' Tickets</span></div>';

$info = cup_query(
    "SELECT
            `adminID`,
            COUNT(`adminID`) AS `anz`
        FROM `" . PREFIX . "cups_supporttickets`
        WHERE `adminID` != 0
        GROUP BY `adminID`
        ORDER BY COUNT(`adminID`) DESC
        LIMIT 0, ".($maxEntries - 1),
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {

    $ticket_adm_list .= '<a href="admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id='.$ds['adminID'].'" class="list-group-item">';
    $ticket_adm_list .= getnickname($ds['adminID']);
    $ticket_adm_list .= '<span class="pull-right grey">'.$ds['anz'].' Tickets</span>';
    $ticket_adm_list .= '</a>';

}

$ticket_usr_list = '';

$info = cup_query(
    "SELECT
            `userID`,
            COUNT(`userID`) AS `anz`
        FROM `" . PREFIX . "cups_supporttickets`
        WHERE `userID` != 0
        GROUP BY `userID`
        ORDER BY COUNT(`userID`) DESC
        LIMIT 0, ".$maxEntries,
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {

    $ticket_usr_list .= '<a href="admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id='.$ds['userID'].'" class="list-group-item">';
    $ticket_usr_list .= getnickname($ds['userID']);
    $ticket_usr_list .= '<span class="pull-right grey">'.$ds['anz'].' Tickets</span>';
    $ticket_usr_list .= '</a>';
}

$ticket_cat_list = '';

$info = cup_query(
    "SELECT
            `categoryID`,
            COUNT(`categoryID`) AS `anz`
        FROM `" . PREFIX . "cups_supporttickets`
        GROUP BY `categoryID`
        ORDER BY COUNT(`categoryID`) DESC
        LIMIT 0, " . $maxEntries,
    __FILE__
);

while ($ds = mysqli_fetch_array($info)) {

    $ticketCategory = getticket($ds['categoryID'], 'category');
    if (!empty($ticketCategory)) {

        $ticket_cat_list .= '<div class="list-group-item">';
        $ticket_cat_list .= $ticketCategory;
        $ticket_cat_list .= '<span class="pull-right grey">' . $ds['anz'] . ' (' . (int)(($ds['anz'] / $totalTickets) * 100) . '%)</span>';
        $ticket_cat_list .= '</div>';

    }

}
