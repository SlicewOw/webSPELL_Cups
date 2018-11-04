<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {

    include(__DIR__ . "/../../_mysql.php");
    include(__DIR__ . "/../../_settings.php");
    include(__DIR__ . "/../../_functions.php");

    //
    // 1209600 entspricht 14 Tagen Differenz
    $diffTime = time() - 1209600;

    try {

        /*************
        Support Ticket
        *************/

        $info = mysqli_query(
            $_database, 
            "SELECT screenshot FROM `" . PREFIX . "cups_supporttickets` 
                WHERE 
                    (closed_date > 0 AND closed_date < " . $diffTime . ") 
                AND 
                    screenshot != ''"
        );

        if (!$info) {
            throw new \Exception(mysqli_error($_database));
        }

        if (mysqli_num_rows($info) > 0) {

            $ticketImagesDeleted = 0;

            while ($ds = mysqli_fetch_array($info)) {

                $filePath = $dir_global . 'images/cup/ticket_screenshots/'.$ds['screenshot'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                    $ticketImagesDeleted++;
                }

            }

        }

    } catch (Exception $e) {}

    try {

        /****************
        Match Screenshots
        ****************/

        $info = mysqli_query(
            $_database, 
            "SELECT 
                    `screenshotID`,
                    `file`
                FROM `" . PREFIX . "cups_matches_playoff_screens` 
                WHERE date < " . $diffTime
        );

        if (!$info) {
            throw new \Exception(mysqli_error($_database));
        }

        if (mysqli_num_rows($info) > 0) {

            $anzFiles = 0;

            while ($ds = mysqli_fetch_array($info)) {

                $filePath = $dir_global . 'images/cup/match_screenshots/' . $ds['file'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                    $anzFiles++;
                }

            }

        }

    } catch (Exception $e) {}

    try {

        /*********
        Cup Awards
        *********/

        $query = mysqli_query(
            $_database, 
            "SELECT 
                    `teamID` 
                FROM `" . PREFIX . "cups_teilnehmer` 
                WHERE `checked_in` = 1
                GROUP BY `teamID`"
        );

        if (!$query) {
            throw new \Exception('Query: ' . mysqli_error($_database));
        }

        while($get = mysqli_fetch_array($query)) {

            $team_id = $get['teamID'];

            $anz_matches = getteam($get['teamID'], 'anz_matches');

            $subQuery = mysqli_query(
                $_database, 
                "SELECT 
                        `awardID`, 
                        `name` 
                    FROM `" . PREFIX . "cups_awards_category` 
                    WHERE " . $anz_matches . " >= anz_matches 
                    ORDER BY `anz_matches  DESC
                    LIMIT 0, 1"
            );

            if (!$subQuery) {
                throw new \Exception('subQuery_1: ' . mysqli_error($_database));
            }

            $subget = mysqli_fetch_array($subQuery);

            if(!empty($subget['awardID'])) {

                $insertQuery = mysqli_query(
                    $_database, 
                    "INSERT INTO `".PREFIX."cups_awards`
                        (
                            `teamID`, 
                            `userID`, 
                            `cupID`, 
                            `award`,
                            `date`
                        )
                        VALUES
                        (
                            " . $team_id . ", 
                            0, 
                            0, 
                            " . $subget['awardID'] . ",
                            " . time() . "
                        )"
                );

                if (!$insertQuery) {
                    throw new \Exception('insertQuery_1: ' . mysqli_error($_database));
                }

                $teamname = getteam($get['teamID'], 'name');

                setCupTeamLog($team_id, $teamname, 'award_received_' . $subget['awardID']);

            }

            $anz_cups = getteam($get['teamID'], 'anz_cups');

            $subQuery = mysqli_query(
                $_database, 
                "SELECT 
                        `awardID`, 
                        `name` 
                    FROM `" . PREFIX . "cups_awards_category` 
                    WHERE " . $anz_cups . " >= anz_cups 
                    ORDER BY `anz_cups` ASC
                    LIMIT 0, 1"
            );

            if (!$subQuery) {
                throw new \Exception('subQuery_2: ' . mysqli_error($_database));
            }

            $subget = mysqli_fetch_array();

            if (!empty($subget['awardID'])) {

                $insertQuery = mysqli_query(
                    $_database, 
                    "INSERT INTO `".PREFIX."cups_awards`
                        (
                            `teamID`, 
                            `userID`, 
                            `cupID`, 
                            `award`,
                            `date`
                        )
                        VALUES
                        (
                            " . $team_id . ", 
                            0, 
                            0, 
                            " . $subget['awardID'] . ",
                            " . time() . "
                        )"
                );

                if (!$subquery) {
                    throw new \Exception('insertQuery_2: ' . mysqli_error($_database));
                }

                $teamname = getteam($get['teamID'], 'name');

                setCupTeamLog($team_id, $teamname, 'award_received_'.$subget['awardID']);

            }

        }

    } catch (Exception $e) {}

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
