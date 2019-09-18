<?php

try {

    $liveshow_show = '';

    $topstream = mysqli_query(
        $_database,
        "SELECT
                `livID`,
                `id`
            FROM `" . PREFIX . "liveshow`
            WHERE `active` = 1 AND `online` = 1
            ORDER BY `prioritization` DESC, `viewer` DESC
            LIMIT 0, 1"
    );

    if (!$topstream) {
        throw new \UnexpectedValueException('liveshow_query_select_failed');
    }

    if (mysqli_num_rows($topstream) < 1) {
        throw new \UnexpectedValueException('no_stream_available');
    }

    $tops = mysqli_fetch_array($topstream);

    setHits('liveshow', 'livID', $tops['livID'], false);

    $data_array = array();
    $data_array['$code'] = getembed($tops['id'], 'twitch_player');
    $data_array['$chat'] = getembed($tops['id'], 'twitch_chat');
    $data_array['$adm'] = '';
    $liveshow_show = $GLOBALS["_template_cup"]->replaceTemplate("liveshow_show", $data_array);

} catch (Exception $e) {}
