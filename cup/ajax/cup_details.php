<?php

try {

    $_language->readModule('cups');

    $cup_id = (isset($_GET['cup_id']) && validate_int($_GET['cup_id'])) ? 
        (int)$_GET['cup_id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['no_cup']);
    }

    //
    // Cup Array
    $cupArray = getcup($cup_id, 'all');

    checkCupDetails($cupArray, $cup_id);

    $time_now = time();
    $content = '';

    $getPage = (isset($_GET['page'])) ?
        getinput($_GET['page']) : 'home';

    if (empty($getPage)) {
        $getPage = 'home';
    }

    //
    // Cup Details Content
    $contentFile = __DIR__ . '/../php/includes/cup_details_' . $getPage . '.php';
    if (file_exists($contentFile)) {
        include($contentFile);
    }

    //
    // Cup Sponsoren
    $content .= getSponsorsByCupIdAsPanelBody($cup_id);

    //
    // Cup Streams
    $content .= getStreamsByCupIdAsListGroup($cup_id);

    // Admin Anmeldung
    $cup_footer = getCupStatusContainer($cupArray);

    // Cup Hits
    setCupHitsByPage($cup_id, $getPage);

    echo $content;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
