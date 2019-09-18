<?php

/**
 * Cup System written by SlicewOw - myRisk
 * Copyright (c) by SlicewOw
 **/

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    $mod = (isset($_GET['mod'])) ?
        getinput($_GET['mod']) : 'cup';

    $cup_base_path = __DIR__ . '/../cup/php/';

    if ($mod == 'cup') {

        updateCupStatistics();

        $data_array = array();
        $data_array['$overview'] = (empty($getAction)) ?
            'btn-info white darkshadow' : 'btn-default';
        $data_array['$archive'] = ($getAction == 'archive') ?
            'btn-info white darkshadow' : 'btn-default';
        $data_array['$add'] = ($getAction == 'add') ?
            'btn-info white darkshadow' : 'btn-default';
        $cups_controls = $GLOBALS["_template_cup"]->replaceTemplate("cups_controls_admin", $data_array);
        echo $cups_controls;

        $getStatus = (isset($_GET['status'])) ?
            getinput($_GET['status']) : '';

        $cupDetailsFile = __DIR__ . '/cup_admin/cup_' . $getAction . '.php';
        if (file_exists($cupDetailsFile)) {
            include($cupDetailsFile);
        } else if (($getAction == "cup") || ($getAction == "status")) {
            // Detail Ansicht des Cups
            include(__DIR__ . '/cup_admin/cup_admin_details.php');
        } else if ($getAction == "teamadd" || $getAction == "playeradd") {
            // Team zu einem Cup hinzufuegen (durch Admin)
            include(__DIR__ . '/cup_admin/cup_admin_joincup.php');
        } else if ( $getAction == "start" && !empty($getStatus) && isset($_GET['id']) ) {

            if ($getStatus == "playoffs") {
                // Cup starten und Playoffs erstellen
                include(__DIR__ . '/cup_admin/includes/cup_start_playoffs.php');
            } else {
                throw new \UnexpectedValueException($_language->module['access_denied']);
            }

        } else if ( $getAction == "finish" && !empty($getStatus) && isset($_GET['id']) ) {

            if($getStatus == "playoffs") {
                // Cup beenden
                include(__DIR__ . '/cup_admin/cup_finish_playoffs.php');
            } else {
                // Fehler
                include(__DIR__ . '/../error.php');
            }

        } else {
            // Cup Admin Details
            include(__DIR__ . '/cup_admin/cup_details.php');
        }

    } else if ($mod == 'support') {
        include(__DIR__ . '/cup_admin/ticket_admin.php');
    } else if (file_exists(__DIR__ . '/cup_admin/cup_' . $mod . '.php')) {
        include (__DIR__ . '/cup_admin/cup_' . $mod . '.php');
    } else if (file_exists(__DIR__ . '/cup_admin/' . $mod . '.php')) {
        include(__DIR__ . '/cup_admin/' . $mod . '.php');
    } else {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
