<?php

try {

    $cup_id = getParentIdByValue('id', true);

    if (!isset($cupArray) || !validate_array($cupArray, true)) {
        $cupArray = getcup($cup_id, 'all');
    }

    checkCupDetails($cupArray, $cup_id);

    $cupname = $cupArray['name'];

    if (isset($cupArray['images']['icon']) && !empty($cupArray['images']['icon'])) {
        $cupname = '<img src="' . $cupArray['images']['icon'] . '" alt="" /> ' . $cupname;
    }

    $getPage = (isset($_GET['page'])) ?
        getinput($_GET['page']) : 'home';

    $allowedPagesArray = array(
        'home',
        'prizes',
        'teams',
        'groups',
        'bracket',
        'rules'
    );

    if (!in_array($getPage, $allowedPagesArray)) {
        header('Location: index.php?site=cup&action=details&id=' . $cup_id);
    }

    $cupInfoMessage = '';
    if ($cupArray['admin'] == 1) {
        $cupInfoMessage = showInfo($_language->module['admin_only']);
    }

    $time_now = time();

    $navi_home = 'btn-default';
    $navi_prizes = 'btn-default';
    $navi_teams = 'btn-default';
    $navi_groups = 'btn-default';
    $navi_bracket = 'btn-default';
    $navi_rules = 'btn-default';

    $groupstage_navi = '';
    if ($cupArray['groupstage'] == 1) {

        if ($getPage == 'groups') {
            $navi_groups = 'btn-info';
        }

        $btnAttributeArray = array();
        $btnAttributeArray[] = 'id="btn_groups"';
        $btnAttributeArray[] = 'onclick="getCupContent(' . $cup_id . ', \'groups\');"';
        $btnAttributeArray[] = 'class="btn btn-sm '.$navi_groups.'"';

        $groupstage_navi = '<div class="btn-group" role="group"><button ' . implode(' ', $btnAttributeArray) . '>'.$_language->module['groupstage'].'</button></div>';

    }

    //
    // Countdown
    $anmeldung = '';
    if (preg_match('/register/', $cupArray['phase'])) {
        // Registration
        $anmeldung = '<div class="panel panel-default"><div class="panel-body center">Anmeldung: <span id="cup_details_countdown"></span></div></div>';
    } else if (preg_match('/checkin/', $cupArray['phase'])) {
        // Check-In
        $anmeldung = '<div class="panel panel-default"><div class="panel-body center">Check-In: <span id="cup_details_countdown"></span></div></div>';
    }

    $content = $anmeldung;

    if (empty($getPage)) {
        $getPage = 'home';
    }

    //
    // Cup Details Content
    $cupDetailsContent = __DIR__ . '/cup_details_' . $getPage . '.php';
    if (file_exists($cupDetailsContent)) {
        include($cupDetailsContent);
    }

    //
    // Cup Sponsoren
    $content .= getSponsorsByCupIdAsPanelBody($cup_id);

    //
    // Cup Streams
    $content .= getStreamsByCupIdAsListGroup($cup_id);

    //
    // Cup Anmeldung
    $cup_footer = getCupStatusContainer($cupArray);

    // Cup Hits
    setCupHitsByPage($cup_id, $getPage);

    $data_array = array();
    $data_array['$image_url'] = $image_url;
    $data_array['$error'] = $cupInfoMessage;
    $data_array['$cupID'] = $cup_id;
    $data_array['$navTeams'] = ($cupArray['mode'] == '1on1') ? $_language->module['player'] : 'Teams';
    $data_array['$cupname'] = $cupname;
    $data_array['$navi_home'] = $navi_home;
    $data_array['$navi_prizes'] = $navi_prizes;
    $data_array['$navi_teams'] = $navi_teams;
    $data_array['$groupstage_navi'] = $groupstage_navi;
    $data_array['$navi_bracket'] = $navi_bracket;
    $data_array['$navi_rules'] = $navi_rules;
    $data_array['$content'] = $content;
    $data_array['$cup_footer'] = $cup_footer;
    $cups_details_home = $GLOBALS["_template_cup"]->replaceTemplate("cups_details_home", $data_array);
    echo $cups_details_home;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
