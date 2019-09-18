<?php

$returnArray = array(
    getConstNameStatus() => FALSE,
    getConstNameMessage() => array(),
    getConstNameError() => array()
);

try {

    include(__DIR__ . "/../../_mysql.php");
    include(__DIR__ . "/../../_settings.php");
    include(__DIR__ . "/../../_functions.php");

    // Update von Accounts, die seit
    // 7 Tagen nicht aktualisiert wurden
    $time = time() - 604800;

    $gameaccountList = array(
        0 => array(),
        1 => array()
    );
    $totalCount = 0;

    $query = safe_query(
        "SELECT
                a.`gameaccID` AS `gameaccount_id`,
                b.`value` AS `steam64_id`
            FROM `" . PREFIX . "cups_gameaccounts_csgo` a
            JOIN `" . PREFIX . "cups_gameaccounts` b ON a.gameaccID = b.gameaccID
            WHERE b.`active` = 1 OR b.`smurf` = 1
            ORDER BY a.`date` ASC
            LIMIT 0, 20"
    );

    while ($get = mysqli_fetch_array($query)) {

        if($totalCount < 10) {
            $x = 0;
        } else {
            $x = 1;
        }

        $steam64_id = $get['steam64_id'];
        if (!in_array($steam64_id, $gameaccountList[$x])) {

            $gameaccountList[$x][] = $steam64_id;
            $gameaccountIDs[$x][] = $get['gameaccount_id'];
            $totalCount++;

        }

    }

    $anz = 0;

    $maxLists = 2;
    for ($n = 0; $n < $maxLists; $n++) {

        try {

            if (!isset($gameaccountList[$n]) || !validate_array($gameaccountList[$n])) {
                break;
            }

            $gameaccountListString = implode(',', $gameaccountList[$n]);
            $accountDetails = getCSGOAccountInfo($gameaccountListString, TRUE);

            if (validate_array($accountDetails[getConstNameError()], true)) {
                throw new \UnexpectedValueException(
                    'Gameaccount CJ Error: ' . $n . ', ' . implode(', ', $accountDetails[getConstNameError()])
                );
            }

            $anzAccounts = count($accountDetails['vac_status']);
            if ($anzAccounts > 0) {

                for ($x = 0; $x < $anzAccounts; $x++) {

                    $gameaccount_id = $gameaccountIDs[$n][$x];

                    $updateArray = array();

                    $updateArray[] = '`date` = ' . time();

                    $dataArray = $accountDetails['vac_status'][$x];

                    $steam64_id = $dataArray['SteamId'];

                    $isBanned = (empty($dataArray['VACBanned'])) ?
                        0 : 1;

                    $updateArray[] = '`vac_bann` = ' . $isBanned;

                    $bannDate = 0;
                    if ($isBanned) {

                        $dateNow = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
                        $bannDays = 86400 * $dataArray['DaysSinceLastBan'];

                        $updateArray[] = '`bann_date` = ' . ($dateNow - $bannDays);

                    }

                    if(isset($accountDetails['csgo_stats']['time_played']['hours'])) {
                        $updateArray[] = '`hours` = ' . $accountDetails['csgo_stats']['time_played']['hours'];
                    }

                    safe_query(
                        "UPDATE `" . PREFIX . "cups_gameaccounts_csgo`
                            SET " . implode(', ', $updateArray) . "
                            WHERE `gameaccID` = " . $gameaccount_id
                    );

                    $anz++;

                }

            }

            unset($accountDetails);

        } catch (Exception $e) {
            $returnArray[getConstNameError()][] = $e->getMessage();
        }

    }

    if ($anz != $totalCount) {
        $returnArray['message'][] = $text;
    } else {
        $returnArray['status'] = TRUE;
    }

} catch (Exception $e) {
    $returnArray[getConstNameError()][] = $e->getMessage();
}

echo json_encode($returnArray);
