<?php

function get_streaminfo($stream_id, $cat = '') {

    if (!validate_int($stream_id)) {
        return (empty($cat)) ?
            array() : '';
    }

    global $_database;

    $info = mysqli_query(
        $_database,
        "SELECT * FROM `" . PREFIX . "liveshow`
            WHERE `livID` = " . $stream_id
    );

    if (!$info) {
        return (empty($cat)) ?
            array() : '';
    }

    if (mysqli_num_rows($info) < 1) {
        return (empty($cat)) ?
            array() : '';
    }

    $db = mysqli_fetch_array($info);

    $returnArray = array(
        'stream_id' => $db['livID'],
        'cronjob_id' => $db['cronjobID'],
        'name' => $db['title'],
        'title' => $db['title'],
        'active' => $db['active'],
        'hits' => $db['hits'],
        'online' => $db['online'],
        'viewer' => $db['viewer'],
        'game' => $db['game'],
        'preview' => $db['preview']
    );

    if (empty($cat)) {
        return $returnArray;
    } else {
        return $returnArray[$cat];
    }

}

function getembed($id, $cat = '') {

    if (empty($cat)) {
        return '';
    }

    $allowedCategories = array(
        'youtube',
        'twitch_player',
        'twitch_chat'
    );

    if (!in_array($cat, $allowedCategories)) {
        return '';
    }

    if ($cat == 'youtube') {
        $returnValue = '<iframe src="https://www.youtube.com/embed/' . $id . '?rel=0" allowfullscreen></iframe>';
    } else if ($cat == 'twitch_player') {
        $returnValue = '<iframe src="https://player.twitch.tv/?channel=' . $id . '" class="stream_embed" allowfullscreen scrolling="no"></iframe>';
    } else if ($cat == 'twitch_chat') {
        $returnValue = '<iframe frameborder="0" scrolling="no" class="chat_embed" id="chat_embed" src="https://www.twitch.tv/embed/' . $id . '/chat"></iframe>';
    }

    return $returnValue;

}
