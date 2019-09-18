<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    $cup_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=';
    $gameaccount_url = 'admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=active&amp;id=';
    $ticket_url = 'admincenter.php?site=cup&amp;mod=support&amp;action=details&amp;id=';

    $selectQuery = cup_query(
        "SELECT
                `cupID`,
                `name`,
                `checkin_date`
            FROM `" . PREFIX . "cups`
            WHERE `status` > 1 AND `status` < 4
            ORDER BY `checkin_date` ASC",
        __FILE__
    );

    $runningCupsCount = mysqli_num_rows($selectQuery);
    if ($runningCupsCount > 0) {

        $running_cups_list = '';

        while ($get = mysqli_fetch_array($selectQuery)) {

            $cup_details_url = $cup_url . $get['cupID'];

            $cup_description = $get['name'];
            $cup_description .= '<span class="pull-right grey">' . getformatdatetime($get['checkin_date']) . '</span>';

            $linkAttributeArray = array();
            $linkAttributeArray[] = 'href="' . $cup_details_url . '"';
            $linkAttributeArray[] = 'class="list-group-item"';

            $running_cups_list .= '<a ' . implode(' ', $linkAttributeArray) . '>' . $cup_description . '</a>';

        }

    } else {
        $running_cups_list = '<div class="list-group-item grey italic">' . $_language->module['no_running_cups'] . '</div>';
    }

    /**
     * Open Support Tickets
     */

    $selectQuery = cup_query(
        "SELECT
                cs.`ticketID`,
                cs.`start_date`,
                u.`nickname`,
                csc.`name_de` AS `category_name_de`,
                csc.`name_uk` AS `category_name_uk`
            FROM `" . PREFIX . "cups_supporttickets` cs
            JOIN `" . PREFIX . "user` u ON cs.`userID` = u.`userID`
            JOIN `" . PREFIX . "cups_supporttickets_category` csc ON cs.`categoryID` = csc.`categoryID`
            WHERE cs.`status` = 1
            ORDER BY cs.`start_date` ASC",
        __FILE__
    );

    $openTicketsCount = mysqli_num_rows($selectQuery);
    if ($openTicketsCount > 0) {

        $ticket_open = '';

        while ($get = mysqli_fetch_array($selectQuery)) {

            $ticket_details_url = $ticket_url . $get['ticketID'];

            $ticket_description = $get['nickname'];
            $ticket_description .= '<span class="pull-right grey">' . $get['category_name_de'] . ' / ' . getformatdatetime($get['start_date']) . '</span>';

            $linkAttributeArray = array();
            $linkAttributeArray[] = 'href="' . $ticket_details_url . '"';
            $linkAttributeArray[] = 'class="list-group-item"';

            $ticket_open .= '<a ' . implode(' ', $linkAttributeArray) . '>' . $ticket_description . '</a>';

        }

    } else {
        $ticket_open = '<div class="list-group-item grey italic">' . $_language->module['no_tickets_open'] . '</div>';
    }

    /**
     * Support Tickets work in progress
     */

    $selectQuery = cup_query(
        "SELECT
                cs.`ticketID`,
                cs.`start_date`,
                u.`nickname`,
                csc.`name_de` AS `category_name_de`,
                csc.`name_uk` AS `category_name_uk`
            FROM `" . PREFIX . "cups_supporttickets` cs
            JOIN `" . PREFIX . "user` u ON cs.`userID` = u.`userID`
            JOIN `" . PREFIX . "cups_supporttickets_category` csc ON cs.`categoryID` = csc.`categoryID`
            WHERE cs.`status` = 2 AND cs.`userID` = " . $userID . "
            ORDER BY cs.`start_date` ASC",
        __FILE__
    );

    $ticketsInProgressCount = mysqli_num_rows($selectQuery);
    if ($ticketsInProgressCount > 0) {

        $ticket_in_progress = '';

        while ($get = mysqli_fetch_array($selectQuery)) {

            $ticket_details_url = $ticket_url . $get['ticketID'];

            $ticket_description = $get['nickname'];
            $ticket_description .= '<span class="pull-right grey">' . $get['category_name_de'] . ' / ' . getformatdatetime($get['start_date']) . '</span>';

            $linkAttributeArray = array();
            $linkAttributeArray[] = 'href="' . $ticket_details_url . '"';
            $linkAttributeArray[] = 'class="list-group-item"';

            $ticket_in_progress .= '<a ' . implode(' ', $linkAttributeArray) . '>' . $ticket_description . '</a>';

        }

    } else {
        $ticket_in_progress = '<div class="list-group-item grey italic">' . $_language->module['no_tickets_in_progress'] . '</div>';
    }

    /**
     * Gameaccounts to be activated
     */

    $selectQuery = cup_query(
        "SELECT
                cg.`gameaccID`,
                cg.`userID`,
                cg.`date`,
                u.`nickname`,
                g.`short` AS `game_short`
            FROM `" . PREFIX . "cups_gameaccounts` cg
            JOIN `" . PREFIX . "user` u ON cg.`userID` = u.`userID`
            LEFT JOIN `" . PREFIX . "games` g ON cg.`category` = g.`tag`
            WHERE cg.`active` = 0 AND cg.`deleted` = 0
            ORDER BY cg.`date` ASC",
        __FILE__
    );

    $inactiveGameaccountCount = mysqli_num_rows($selectQuery);
    if ($inactiveGameaccountCount > 0) {

        $gameaccounts_not_activated = '';

        while ($get = mysqli_fetch_array($selectQuery)) {

            $activate_gameaccount_url = $gameaccount_url . $get['gameaccID'];

            $gameaccount_description = $get['nickname'];
            $gameaccount_description .= '<span class="pull-right grey">' . getformatdatetime($get['date']) . '</span>';

            $linkAttributeArray = array();
            $linkAttributeArray[] = 'href="' . $activate_gameaccount_url . '"';
            $linkAttributeArray[] = 'class="list-group-item"';

            $gameaccounts_not_activated .= '<a ' . implode(' ', $linkAttributeArray) . '>' . $gameaccount_description . '</a>';

        }

    } else {
        $gameaccounts_not_activated = '<div class="list-group-item grey italic">' . $_language->module['no_inactive_gameaccounts'] . '</div>';
    }

    $data_array = array();
    $data_array['$running_cups_count'] = $runningCupsCount;
    $data_array['$running_cups_list'] = $running_cups_list;
    $data_array['$open_tickets_count'] = $openTicketsCount;
    $data_array['$ticket_open'] = $ticket_open;
    $data_array['$tickets_in_progress_count'] = $ticketsInProgressCount;
    $data_array['$ticket_in_progress'] = $ticket_in_progress;
    $data_array['$inactive_gameaccount_count'] = $inactiveGameaccountCount;
    $data_array['$gameaccounts_not_activated'] = $gameaccounts_not_activated;
    $overview = $GLOBALS["_template_cup"]->replaceTemplate("admin_overview", $data_array);
    echo $overview;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
