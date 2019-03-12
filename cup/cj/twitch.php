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

    $info = cup_query(
        "SELECT
              `id`
            FROM `" . PREFIX . "liveshow`
            WHERE cronjobID = " . $cronjob_id . " AND type = 1",
        __FILE__
    );

    while ($db = mysqli_fetch_array($info)) {
        $idArray[] = strtolower($db['id']);
    }

    $anzStreams = count($idArray);
    if ($anzStreams < 1) {
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

    if (validate_array($result)) {
        throw new \Exception('unknown_attribute_result');
    }

    //
    // Data Array
    $json_array = json_decode($result, true);

    if (isset($_GET['debug']) && validate_int($_GET['debug'])) {

        if ($_GET['debug'] >= 1) {

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

        if (!empty($pic)) {

            if (!validate_url($pic)) {
                throw new \Exception('unknown_parameter_of_stream_thumb (`pic`=\'' . $pic . '\')');
            } else if (@copy($pic, __DIR__ . '/../../images/cup/streams/' . $twitch_id . '.jpg')) {
                $setValueArray[] = 'preview = \'' . addslashes($twitch_id) . '.jpg\'';
            } else {
                $returnArray['message'][] = 'copy_of_image_failed: ' . $twitch_id;
            }

        }

        //
        // save stream details
        if (isset($json_array['streams'][$x]['viewers'])) {
            $setValueArray[] = '`viewer` = ' . (int)$json_array['streams'][$x]['viewers'];
        }

        if (isset($json_array['streams'][$x]['viewers'])) {
            $setValueArray[] = '`game` = \'' . addslashes($json_array['streams'][$x]['game']) . '\'';
        }

        $setValue = implode(', ', $setValueArray);

        $updateQuery = cup_query(
            "UPDATE `" . PREFIX . "liveshow`
                SET " . $setValue . "
                WHERE `id` = '" . $twitch_id . "'",
            __FILE__
        );

        $anzStreamsArray['online']++;

        //
        // Delete from idArray
        $arrayKey = array_search($twitch_id, $idArray);
        unset($idArray[$arrayKey]);

    }

    //
    // set all offline streams
    $anzStreamsOffline = count($idArray);
    if ($anzStreamsOffline > 0) {

        $anzStreamsArray['offline'] = $anzStreamsOffline;

        $implodeArray = '\'' . implode('\',\'', $idArray) . '\'';

        $updateQuery = cup_query(
            "UPDATE `" . PREFIX . "liveshow`
                SET `online` = 0,
                    `viewer` = 0
                WHERE `id` IN (" . $implodeArray . ")",
            __FILE__
        );

        for ($x = 0; $x < $anzStreamsOffline; $x++) {

            try {

                $twitch_id = $idArray[$x];

                if (empty($twitch_id)) {
                    throw new \Exception('unknown_twitch_id');
                }

                $json_url = 'https://api.twitch.tv/kraken/channels/' . $twitch_id;
                if ($json_data = getAPIData($json_url, 'twitch')) {

                    $twitchData = json_decode($json_data, TRUE);
                    if (!isset($twitchData['status']) || ($twitchData['status'] != '404')) {
                        throw new \Exception('stream_is_existing_do_not_delete');
                    }

                    $deleteQuery = cup_query(
                        "DELETE FROM `". PREFIX . "liveshow`
                            WHERE `id` = '" . $twitch_id . "'",
                        __FILE__
                    );

                }

            } catch (Exception $e) {}

        }

    }

    $selectQurey = cup_query(
        "SELECT
                COUNT(*) AS `exist`
            FROM `" . PREFIX . "liveshow_cronjobs`
            WHERE `id` = " . $cronjob_id,
        __FILE__
    );

    $checkIf = mysqli_fetch_array($selectQurey);

    if ($checkIf['exist'] == 1) {

        $query = cup_query(
            "UPDATE `" . PREFIX . "liveshow_cronjobs`
                SET `hits` = `hits` + 1,
                    `date` = " . time() . "
                WHERE `id` = " . $cronjob_id,
        __FILE__
        );

    } else {

        $query = cup_query(
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
                )",
            __FILE__
        );

    }

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
