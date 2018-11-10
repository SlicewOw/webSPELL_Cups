<?php

$returnArray = array(
    'status' => FALSE
);

try {

    include(__DIR__ . "/../../_mysql.php");
    include(__DIR__ . "/../../_settings.php");
    include(__DIR__ . "/../../_functions.php");

    $cronjob_id = (isset($_GET['cj_id']) && validate_int($_GET['cj_id'])) ?
        (int)$_GET['cj_id'] : 0;

    if ($cronjob_id < 1) {
        throw new \Exception('unknown_cronjob');
    }

    $idArray = array();

    $info = mysqli_query(
        $_database,
        "SELECT
              `id`
            FROM `" . PREFIX . "liveshow`
            WHERE cronjobID = " . $cronjob_id . " AND type = 1"
    );

    if (!$info) {
        throw new \Exception('info_query_failed');
    }

    while( $db = mysqli_fetch_array( $info ) ) {
        $idArray[] = strtolower($db['id']);
    }

    $anzStreams = count($idArray);
    if($anzStreams < 1) {
        throw new \Exception('no_streams_available_cronjob_' . $cronjob_id);
    }

    //
    // comma seperated string
    $url_extension = implode(',', $idArray);

    //
    // Data URL
    $json_url = 'https://api.twitch.tv/kraken/streams?channel=' . $url_extension;

    //
    // Open Data URL
    $result = getAPIData($json_url, 'twitch');

    //
    // Data Array
    $json_array = json_decode($result, true);

    if (isset($_GET['debug']) && validate_int($_GET['debug'])) {

        if($_GET['debug'] >= 1) {

            $returnArray['twitch_id'] = $idArray;

            if($_GET['debug'] >= 2) {

                $returnArray['twitch_respond'] = $json_array;

                if($_GET['debug'] >= 3) {
                    $returnArray['get_data_url'] = $json_url;
                }

            }

        }

    }

    $anzStreamsArray = array(
        'total'     => 0,
        'online'    => 0,
        'offline'   => 0
    );

    $anz = (isset($json_array['_total'])) ? 
        $json_array['_total'] : 0;

    for ($x = 0; $x < $anz; $x++) {

        //
        // twitch id
        $twitch_id = $json_array['streams'][$x]['channel']['name'];

        $setValueArray = array();
        $setValueArray[] = '`online` = 1';
        $setValueArray[] = '`date` = ' . time();

        //
        // preview
        $pic = isset($json_array['streams'][$x]['preview']['medium']) ?
            $json_array['streams'][$x]['preview']['medium'] : '';

        if (!empty($pic) && @copy($pic, __DIR__ . '/../../images/media/streams/' . $twitch_id . '.jpg')) {
            $setValueArray[] = 'preview = \'' . addslashes($twitch_id) . '.jpg\'';
        }

        //
        // save stream details
        if(isset($json_array['streams'][$x]['viewers'])) {
            $setValueArray[] = '`viewer` = ' . (int)$json_array['streams'][$x]['viewers'];
        }

        if(isset($json_array['streams'][$x]['viewers'])) {
            $setValueArray[] = '`game` = \'' . addslashes($json_array['streams'][$x]['game']) . '\'';
        }

        $setValue = implode(', ', $setValueArray);

        $updateQuery = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . "liveshow` 
                SET " . $setValue . "
                WHERE `id` = '" . $twitch_id . "'"
        );

        if (!$updateQuery) {
            throw new \Exception('update_stream_query_failed');
        }

        $anzStreamsArray['online']++;

        //
        // Delete from idArray
        $arrayKey = array_search($twitch_id, $idArray);
        unset($idArray[$arrayKey]);

    }

    //
    // set all offline streams
    $anzStreamsOffline = count($idArray);
    if($anzStreamsOffline > 0) {

        $anzStreamsArray['offline'] = $anzStreamsOffline;

        $implodeArray = '\'' . implode('\',\'', $idArray) . '\'';
        $updateQuery = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . "liveshow` 
                SET `online` = 0, 
                    `viewer` = 0 
                WHERE `id` IN (" . $implodeArray . ")"
        );

        if (!$updateQuery) {
            throw new \Exception('update_query_failed (' . $implodeArray . ')');
        }

        for ($x = 0; $x < $anzStreamsOffline; $x++) {

            try {

                $twitch_id = $idArray[$x];

                if (empty($twitch_id)) {
                    throw new \Exception('unknown_twitch_id');
                }

                $json_url = 'https://api.twitch.tv/kraken/channels/' . $twitch_id;
                if($json_data = getAPIData($json_url, 'twitch')) {

                    $twitchData = json_decode($json_data, TRUE);
                    if (!isset($twitchData['status']) || ($twitchData['status'] != '404')) {
                        throw new \Exception('stream_is_existing_do_not_delete');
                    }

                    $deleteQuery = mysqli_query(
                        $_database,
                        "DELETE FROM `". PREFIX . "liveshow`
                            WHERE id = '" . $twitch_id . "'"
                    );

                    if (!$deleteQuery) {
                        throw new \Exception('delete_stream_query_failed (' . $twitch_id . ')');
                    }

                }

            } catch (Exception $e) {}

        }

    }

    $checkIf = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT 
                    COUNT(*) AS `exist` 
                FROM `" . PREFIX . "liveshow_cronjobs` 
                WHERE `id` = " . $cronjob_id
        )
    );

    if ($checkIf['exist'] == 1) {

        $query = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . "liveshow_cronjobs` 
                SET hits = hits + 1, 
                    date = " . time() . " 
                WHERE id = " . $cronjob_id
        );

        if (!$query) {
            throw new \Exception('update_cj_query_failed');
        }

    } else {

        $query = mysqli_query(
            $_database,
            "INSERT INTO `" . PREFIX . "liveshow_cronjobs` 
                (
                    `id`,
                    `hits`,
                    `date`
                )
                VALUES
                (
                    " . $cronjob_id . ",
                    1,
                    " . time() . "
                )"
        );

        if (!$query) {
            throw new \Exception('insert_cj_query_failed');
        }

    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
