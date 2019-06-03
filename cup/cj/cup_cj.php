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

                $filePath = __DIR__ . '/../../images/cup/ticket_screenshots/'.$ds['screenshot'];
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

                $filePath = __DIR__ . '/../../images/cup/match_screenshots/' . $ds['file'];
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
        updateCupStatistics();

    } catch (Exception $e) {}

    $returnArray['status'] = TRUE;

} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
