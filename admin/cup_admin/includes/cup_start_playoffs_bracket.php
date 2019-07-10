<?php

if (!$loggedin || !iscupadmin($userID)) {
    throw new \Exception($_language->module['access_denied']);
}

if (!validate_int($cup_id, true)) {
    throw new \Exception($_language->module['access_denied']);
}

if (!isset($cupArray) || !validate_array($cupArray, true)) {
    throw new \Exception($_language->module['access_denied']);
}

if (!isset($cupArray['id']) || ($cup_id != $cupArray['id'])) {
    throw new \Exception($_language->module['access_denied']);
}

$cup_teams = getcup($cup_id, 'rand_teams');

$arrlen = count($cup_teams);
if ($arrlen < 1) {
    throw new \Exception($_language->module['no_teams']);
}

$breakTimer = 0;
$maxBreakTimer = 10;

$break_while = true;
while ($break_while == true) {

    $shuffle_while = 0;
    for ($x = 0; $x < $arrlen; $x += 2) {

        $isTeam1 = (isset($cup_teams[$x]) && ($cup_teams[$x] == 0)) ?
            TRUE : FALSE;

        $isTeam2 = (isset($cup_teams[$x + 1]) && ($cup_teams[$x + 1] == 0)) ?
            TRUE : FALSE;

        if ($isTeam1 && $isTeam2) {
            $shuffle_while++;
        }

    }

    if ($shuffle_while > 0) {
        $cup_teams = getcup($cup_id, 'rand_teams');
    } else {
        break;
    }

    if ($breakTimer > $maxBreakTimer) {
        throw new \Exception('rand_teams failed');
    }

    $breakTimer++;

}

$cupArray['anz_teams'] = $cupArray['teams']['checked_in'];
$anzRunden = $cupArray['anz_runden'];
$anzMatches = $cupArray['size'] / 2;

$matchFormatPerRoundArray = (isset($cupArray['settings']['format']) && validate_array($cupArray['settings']['format'], true)) ?
    $cupArray['settings']['format'] : array();

for ($n = 1; $n < ($anzRunden + 1); $n++) {

    try {

        $format = (isset($matchFormatPerRoundArray[$n])) ?
            $matchFormatPerRoundArray[$n] : 'bo1';

        for ($i = 0; $i < $anzMatches; $i++) {

            //
            // 15min nach Cup-Start
            // start_date + (runde * 1h) + 15min
            $date = $cupArray['start'] + ((($n - 1) * 3600) + 900);

            //
            // Map Pool
            $mapList = getMaps($cupArray['mappool']);

            $mapArray = array(
                'open' => $mapList,
                'banned' => array(
                    'team1' => array(),
                    'team2' => array()
                ),
                'picked' => array(),
                'list' => $mapList
            );

            $maps = serialize($mapArray);

            //
            // Match Query
            $query1 = cup_query(
                "INSERT INTO `" . PREFIX . "cups_matches_playoff`
                    (
                        `cupID`,
                        `wb`,
                        `runde`,
                        `spiel`,
                        `format`,
                        `date`,
                        `maps`
                    )
                    VALUES
                    (
                        " . $cup_id . ",
                        1,
                        " . $n . ",
                        " . ($i + 1) . ",
                        '" . $format . "',
                        " . $date . ",
                        '" . $maps . "'
                    )",
                __FILE__
            );

            $match_id = mysqli_insert_id($_database);

            addMatchLog($match_id, 'match_created');

            //
            // Update Matches Round 1
            // Set Teams and Def-Wins
            if ($n == 1) {

                $team1_id = $i * 2;
                $team2_id = $team1_id + 1;

                if (!$cup_teams[$team1_id]) {
                    $team1 = 0;
                } else {
                    $team1 = $cup_teams[$team1_id];
                }

                if (!$cup_teams[$team2_id]) {
                    $team2 = 0;
                } else {
                    $team2 = $cup_teams[$team2_id];
                }

                $freilos1 = ($team1 == 0) ? 1 : 0;
                $freilos2 = ($team2 == 0) ? 1 : 0;

                $query2 = cup_query(
                    "UPDATE `" . PREFIX . "cups_matches_playoff`
                        SET `team1` = " . $team1 . ",
                            `team1_freilos` = " . $freilos1 . ",
                            `ergebnis1` = 0,
                            `team2` = " . $team2 . ",
                            `team2_freilos` = " . $freilos2 . ",
                            `ergebnis2` = 0,
                            `active` = 1
                        WHERE `matchID` = " . $match_id,
                    __FILE__
                );

            }

            if (($anzRunden > 1) && ($cupArray['anz_teams'] > 2)) {

                //
                // Spiel um Platz 3
                if ($n == $anzRunden) {

                    //
                    // Match Query
                    $query1 = cup_query(
                        "INSERT INTO `" . PREFIX . "cups_matches_playoff`
                            (
                                `cupID`,
                                `wb`,
                                `runde`,
                                `spiel`,
                                `format`,
                                `date`,
                                `maps`
                            )
                            VALUES
                            (
                                " . $cup_id . ",
                                0,
                                " . $n . ",
                                " . ($i + 1) . ",
                                '" . $format . "',
                                " . $date . ",
                                '" . $maps . "'
                            )",
                        __FILE__
                    );

                    $match_id = mysqli_insert_id($_database);

                    addMatchLog($match_id, 'match_created');

                }

            }

        }

        $anzMatches = $anzMatches / 2;

    } catch (Exception $e) {
        echo showError($e->getMessage());
    }

}
