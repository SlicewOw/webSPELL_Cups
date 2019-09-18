<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array(),
    'html' => ''
);

try {

    $_language->readModule('cups_home');

    $upcomingCup = '';

    $cup_id = (isset($_GET[getConstNameCupIdWithUnderscore()]) && validate_int($_GET[getConstNameCupIdWithUnderscore()], true)) ?
        (int)$_GET[getConstNameCupIdWithUnderscore()] : 0;

    if ($cup_id < 1) {
        throw new \UnexpectedValueException('ERROR');
    }

    $cupArray = getcup($cup_id);
    $getGame = getGame($cupArray['game']);

    $timeLeft = 0;
    $timeNow = time();

    if (preg_match('/register/', $cupArray[getConstNamePhase()])) {
        $timeLeft = $cupArray['checkin'] - $timeNow;
    } else if (preg_match('/checkin/', $cupArray[getConstNamePhase()]) || $cupArray[getConstNamePhase()] == 'finished') {
        $timeLeft = $cupArray[getConstNameStart()] - $timeNow;
    } else if (($cupArray[getConstNameStart()] - $timeNow) > 0) {
        $timeLeft = $cupArray[getConstNameStart()] - $timeNow;
    }

    $data_array = array();
    $data_array['$image_url'] = $image_url;
    $data_array['$cup_id'] = $cup_id;
    $data_array['$game_tag'] = $cupArray['game'];
    $data_array['$game_name'] = $getGame['name'];
    $data_array['$size'] = $cupArray['size'];
    $data_array['$anz_teams'] = getcup($cup_id, 'anz_teams');
    $data_array['$anz_teams_checkedin'] = getcup($cup_id, 'anz_teams_checkedin');
    $data_array['$date_checkin'] = getformatdatetime($cupArray['checkin']);
    $data_array['$date_start'] = getformatdatetime($cupArray[getConstNameStart()]);
    $detailList = $GLOBALS["_template_cup"]->replaceTemplate("home_upcomingcup_details", $data_array);

    $data_array = array();
    $data_array['$timeLeft'] = $timeLeft;
    $data_array['$status'] = str_replace(
        '%cup_id%',
        $cup_id,
        $_language->module['status_' . $cupArray[getConstNamePhase()]]
    );
    $data_array['$cupName'] = $cupArray['name'];
    $data_array['$detailList'] = $detailList;
    $upcomingCup = $GLOBALS["_template_cup"]->replaceTemplate("home_upcomingcup", $data_array);

    $returnArray['html'] = $upcomingCup;

    $returnArray['status'] = true;

} catch (Exception $e) {
    setLog('', $e->getMessage(), __FILE__, $e->getLine());
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
