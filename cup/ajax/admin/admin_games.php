<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {

    $_language->readModule('games', false, true);

    if (!ispageadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (empty($getAction)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    $game_id = (isset($_GET['game_id']) && validate_int($_GET['game_id'], true)) ?
        (int)$_GET['game_id'] : 0;

    if ($game_id < 1) {
        throw new \Exception($_language->module['unknown_game']);
    }

    if ($getAction == 'setActiveMode' || $getAction == 'setAutoActiveMode') {

        $activeColumn = ($getAction == 'setActiveMode') ?
            'active' : 'cup_auto_active';

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `" . $activeColumn . "`
                    FROM `" . PREFIX . "games`
                    WHERE `gameID` = " . $game_id
            )
        );

        $new_value = (!empty($get[$activeColumn]) && ($get[$activeColumn] == 1)) ?
            0 : 1;

        $query = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . "games`
                SET " . $activeColumn . " = " . $new_value . "
                WHERE gameID = " . $game_id
        );

        if (!$query) {
            throw new \Exception($_language->module['query_update_failed']);
        }

        $returnArray['value'] = $new_value;

    } else if ($getAction == 'getDetails') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(gameID) AS exist,
                        gameID,
                        name,
                        tag,
                        short
                    FROM `".PREFIX."games`
                    WHERE gameID = " . $game_id
            )
        );

        if ($get['exist'] != 1) {
            throw new \Exception($_language->module['unknown_game']);
        }

        $imageTypeArray = array(
            'jpg',
            'gif',
            'png'
        );

        $icon_url = getGameIcon($get['tag'], true);
        $icon = (!empty($icon_url)) ?
            '<img src="' . $icon_url . '" alt="" />' : '';

        $returnArray['data'] = array(
            'gameID' => $get['gameID'],
            'name' => $get['name'],
            'tag' => $get['tag'],
            'short' => $get['short'],
            'icon' => $icon
        );

    } else {
        throw new \Exception($_language->module['unknown_action']);
    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
