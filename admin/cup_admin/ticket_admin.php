<?php

try {

    $_language->readModule('support', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    $status = (isset($_GET['status']) && validate_int($_GET['status'], true)) ?
        (int)$_GET['status'] : 1;

    if ($status < 1) {
        $status = 1;
    }

    //
    // Status
    // 1: offen
    // 2: in Bearbeitung
    // 3: geschlossen
    $maxStatus = 3;
    if ($status > 3) {
        $status = 3;
    }

    $base_url = 'admincenter.php?site=cup&amp;mod=support&amp;action=admin&amp;status=';

    $url = ($getSite == 'support') ?
        'index.php?site=support&amp;action=admin' :
        'admincenter.php?site=cup&amp;mod=support&amp;action=admin';

    $ticketStatusArray = array();
    for ($x = 1; $x < ($maxStatus + 1); $x++) {
        $ticketStatusArray[$x] = 'btn-default';
    }

    $status_title = $_language->module['ticket_status_' . $status];
    if ($getAction == 'admin') {
        $ticketStatusArray[$status] = 'btn-info white darkshadow';
    }

    $cat = isset($_GET['cat']) ?
        getinput($_GET['cat']) : '';

    $data_array = array();
    $data_array['$image_url'] = $image_url;
    $data_array['$status'] = $status;
    $data_array['$user_id'] = $userID;
    for ($x = 1; $x < ($maxStatus + 1); $x++) {
        $data_array['$ticket_status' . $x] = $ticketStatusArray[$x];
        $data_array['$ticket_url' . $x] = $url . '&amp;status=' . $x;
    }
    $data_array['$archive_url'] = str_replace(
        'action=admin',
        'action=archive',
        $url
    );
    $data_array['$ticket_status4'] = ($getAction == 'admin_add') ?
        'btn-info white darkshadow' : 'btn-default';
    $data_array['$ticket_url4'] = $url . '_add';
    $data_array['$updateSupportContainer'] = ($getAction == 'admin_add' || $getAction == 'details') ?
        'false' : 'true';
    $ticket_menu = $GLOBALS["_template_cup"]->replaceTemplate("ticket_menu", $data_array);
    echo $ticket_menu;

    if ($getAction == 'details') {

        //
        // Bestehendes Ticket
        include(__DIR__ . '/includes/ticket_add_answer.php');

    } else if ($getAction == 'admin_add') {

        //
        // Admin Ticket erstellen
        include($dir_cup . 'ticket_add_admin.php');

    } else if ($getAction == 'archive') {

        //
        // Admin Ticket erstellen
        include(__DIR__ . '/includes/ticket_archive.php');

    } else {

        $ticket_categories = '<option value="0">'.$_language->module['category'].'</option>';
        $ticket_categories .= getticketcategories($cat, false);

        $timeNow = time();

        // 60sec * 60min * 24h * 31d
        $milliSeconds = $timeNow - ((($x * 7) + 7) * 60 * 60 * 24);

        $ticket_histories = '<option value="0">-- / --</option>';
        for ($x = 0; $x < 20; $x++) {

            $timeScale = ((($x * 7) + 7) / 7) . ' Woche/-n';

            $ticket_histories .= '<option value="' . $milliSeconds . '">' . $timeScale . '</option>';

        }

        $data_array = array();
        $data_array['$image_url'] = $image_url;
        $data_array['$status'] = $status;
        $data_array['$user_id'] = $userID;
        $data_array['$ticket_categories'] = $ticket_categories;
        $data_array['$ticket_histories'] = $ticket_histories;
        $data_array['$status_title'] = $status_title;
        $ticket_home_admin = $GLOBALS["_template_cup"]->replaceTemplate("ticket_admin_home", $data_array);
        echo $ticket_home_admin;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}