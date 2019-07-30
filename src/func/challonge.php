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