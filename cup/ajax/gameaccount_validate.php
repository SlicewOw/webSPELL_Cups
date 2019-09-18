<?php

$returnArray = getDefaultReturnArray();

try {

    $returnArray['data'] = array(
        'gameaccount' => '',
        'gameaccID' => 0,
        'steamcommunity_id' => '',
        'admin' => array()
    );

    $_language->readModule('gameaccounts');

    if (!isset($loggedin) || !$loggedin) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $getGame = (isset($_GET['game'])) ?
        getinput($_GET['game']) : '';

    //
    // Init
    $steamcommunity_url = '';
    $steamcommunity_nick = '';
    $steamcommunity_id = '';

    //
    // CSGO Account Validation
    $selectQuery = cup_query(
        "SELECT
                COUNT(*) AS `exist`,
                `gameaccID` AS `gameaccount_id`,
                `value`
            FROM `".PREFIX."cups_gameaccounts`
            WHERE `userID` = " . $userID . " AND `category` = 'csg' AND `deleted` = 0",
        __FILE__
    );

    $get = mysqli_fetch_array($selectQuery);

    if ($get['exist'] != 1) {
        throw new \UnexpectedValueException($_language->module['unknown_gameaccount']);
    }

    $gameaccount_id = $get['gameaccount_id'];

    $steam64_id = $get['value'];

    //
    // Get Steam API Data
    $SteamDataArray = getCSGOAccountInfo($steam64_id);

    if (!validate_array($SteamDataArray)) {
        throw new \UnexpectedValueException($_language->module['error_failed_steamrequest']);
    }

    if (!isset($SteamDataArray['status']) || ($SteamDataArray['status'] != 1)) {
        throw new \UnexpectedValueException($_language->module['error_failed_steamrequest']);
    }

    //
    // Steam API Data
    if (!is_array($SteamDataArray)) {
        throw new \UnexpectedValueException($_language->module['error_failed_steamrequest']);
    }

    if (!isset($SteamDataArray['steam_profile'])) {
        throw new \UnexpectedValueException($_language->module['error_failed_steamrequest']);
    }

    //
    // URL
    $steamcommunity_url = '';
    if (isset($SteamDataArray['steam_profile']['profileurl'])) {
        $steamcommunity_url = $SteamDataArray['steam_profile']['profileurl'];
    } else if ($steam64_id > 0) {
        $steamcommunity_url = 'https://steamcommunity.com/profiles/' . $steam64_id;
    } else {

        if (!in_array('error_profileurl', $returnArray['error'])) {
            throw new \UnexpectedValueException($_language->module['error_profileurl']);
        }

    }

    //
    // Nickname
    $steamcommunity_nick = '';
    if (isset($SteamDataArray['steam_profile']['personaname'])) {
        $steamcommunity_nick = $SteamDataArray['steam_profile']['personaname'];
    } else {

        if (!in_array('error_personaname', $returnArray['error'])) {
            throw new \UnexpectedValueException($_language->module['error_personaname']);
        }

    }

    //
    // Steam64 ID
    $steamcommunity_id = '';
    if (isset($SteamDataArray['steam_profile']['steamid'])) {
        $steamcommunity_id = $SteamDataArray['steam_profile']['steamid'];
    } else {

        if(!in_array('error_steamid', $returnArray['error'])) {
            throw new \UnexpectedValueException($_language->module['error_steamid']);
        }

    }

    $returnArray['data'] = array(
        'gameaccount_id' => $gameaccount_id,
        'steam_id' => $steam64_id,
        'name' => $steamcommunity_nick,
        'steamcommunity_id' => $steamcommunity_id,
        'steamcommunity_url' => $steamcommunity_url
    );

    if (!empty($steamcommunity_nick) && !empty($steamcommunity_url)) {
        $returnArray['status'] = TRUE;
    }

} catch (Exception $e) {
    setLog('', $e->getMessage(), __FILE__, $e->getLine());
    $returnArray['error'][] = $e->getMessage();
}

echo json_encode($returnArray);
