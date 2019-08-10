<?php

function getChallongeApiKey() {

    $settingsFile = __DIR__ . '/../../cup/settings.php';

    if (!file_exists($settingsFile)) {
        throw new \Exception('unknown_settings_file');
    }

    include($settingsFile);

    if (!isset($challonge_api_key)) {
        throw new \Exception('unknown_challonge_api_key');
    }

    if (empty($challonge_api_key)) {
        throw new \Exception('challonge_api_key_is_not_set');
    }

    return $challonge_api_key;

}

function getChallongeApiObject() {

    $challonge_api_key = getChallongeApiKey();

    include(__DIR__ . '/../../cup/api/challonge.php');

    $challonge_api = new \ChallongeAPI($challonge_api_key);
    return $challonge_api;

}

function getChallongeUrl($cup_id) {

    if (!validate_int($cup_id, true)) {
        throw new \Exception('unknown_cup_id');
    }

    $selectQuery = cup_query(
        "SELECT
                `challonge_url`
            FROM `" . PREFIX . "cups`
            WHERE `cupID` = " . $cup_id,
        __FILE__
    );

    $get = mysqli_fetch_array($selectQuery);

    return $get['challonge_url'];

}

function getChallongeTournamentId($challonge_url) {

    if (is_null($challonge_url) || empty($challonge_url)) {
        throw new \Exception('unknown_challonge_url');
    }

    $tournament_id = '';

    $parsed_url = parse_url($challonge_url, PHP_URL_HOST);
    $domainArray = explode('.', $parsed_url);
    if (count($domainArray) > 2) {
        $tournament_id = $domainArray[0] . '-';
    }

    $domainArray = explode('/', $challonge_url);

    $tournament_id .= $domainArray[count($domainArray) - 1];

    return $tournament_id;

}

function getChallongeTournament($tournament_id) {

    $challonge_api = getChallongeApiObject();

    return $challonge_api->getTournament($tournament_id);

}
