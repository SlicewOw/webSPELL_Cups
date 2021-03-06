<?php

$returnArray = array(
    getConstNameStatus() => FALSE
);

try {

    include(__DIR__ . "/../../_mysql.php");
    include(__DIR__ . "/../../_settings.php");
    include(__DIR__ . "/../../_functions.php");

    $cronjob_id = (isset($_GET[getConstNameCronjobId()]) && validate_int($_GET[getConstNameCronjobId()], true)) ?
        (int)$_GET[getConstNameCronjobId()] : 0;

    if ($cronjob_id < 1) {
        throw new \UnexpectedValueException('unknown_cronjob');
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
        throw new \UnexpectedValueException('no_streams_available_cronjob_' . $cronjob_id);
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
        throw new \UnexpectedValueException('unknown_attribute_result');
    }

    //
    // Data Array
    $json_array = json_decode($result, true);

    if (isset($_GET[getConstNameDebug()]) && validate_int($_GET[getConstNameDebug()], true)) {

        $returnArray['twitch_id'] = $idArray;

        if($_GET[getConstNameDebug()] >= 2) {

            $returnArray['twitch_respond'] = $json_array;

            if($_GET[getConstNameDebug()] >= 3) {
                $returnArray['get_data_url'] = $json_url;
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
        $twitch_id = $json_array[getConstNameStreams()][$x]['channel']['name'];

        $setValueArray = array();
        $setValueArray[] = '`online` = 1';
        $setValueArray[] = '`date` = ' . time();

        //
        // preview
        $pic = isset($json_array[getConstNameStreams()][$x]['preview']['medium']) ?
            $json_array[getConstNameStreams()][$x]['preview']['medium'] : '';

        if (!empty($pic)) {

            if (!validate_url($pic)) {
                throw new \UnexpectedValueException('unknown_parameter_of_stream_thumb (`pic`=\'' . $pic . '\')');
            } else if (@copy($pic, __DIR__ . '/../../images/cup/streams/' . $twitch_id . '.jpg')) {
                $setValueArray[] = 'preview = \'' . addslashes($twitch_id) . '.jpg\'';
            } else {
                $returnArray['message'][] = 'copy_of_image_failed: ' . $twitch_id;
            }

        }

        //
        // save stream details
        if (isset($json_array[getConstNameStreams()][$x][getConstNameViewers()])) {
            $setValueArray[] = '`viewer` = ' . (int)$json_array[getConstNameStreams()][$x][getConstNameViewers()];
        }

        if (isset($json_array[getConstNameStreams()][$x][getConstNameViewers()])) {
            $setValueArray[] = '`game` = \'' . addslashes($json_array[getConstNameStreams()][$x]['game']) . '\'';
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
                    throw new \UnexpectedValueException('unknown_twitch_id');
                }

                $json_url = 'https://api.twitch.tv/kraken/channels/' . $twitch_id;
                if ($json_data = getAPIData($json_url, 'twitch')) {

                    $twitchData = json_decode($json_data, TRUE);
                    if (!isset($twitchData[getConstNameStatus()]) || ($twitchData[getConstNameStatus()] != '404')) {
                        throw new \UnexpectedValueException('stream_is_existing_do_not_delete');
                    }

                    $deleteQuery = cup_query(
                        "DELETE FROM `". PREFIX . "liveshow`
                            WHERE `id` = '" . $twitch_id . "'",
                        __FILE__
                    );

                }

            } catch (Exception $e) {
                setLog('Twitch CJ Error', $e->getMessage(), __FILE__, $e->getLine());
            }

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

    $returnArray[getConstNameStatus()] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
