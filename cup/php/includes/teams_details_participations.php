<?php

try {

    $whereClauseArray = array();
    $whereClauseArray[] = 'ct.`teamID` = ' . $team_id;
    $whereClauseArray[] = 'ct.`checked_in` = 1';
    $whereClauseArray[] = 'c.`mode` != \'1on1\'';

    $whereClause = implode(' AND ', $whereClauseArray);

    $team_cup = mysqli_query(
        $_database,
        "SELECT
                ct.`cupID`,
                c.`name`
            FROM `" . PREFIX . "cups_teilnehmer` ct
            LEFT JOIN `" . PREFIX . "cups` c ON ct.`cupID` = c.`cupID`
            WHERE " . $whereClause . "
            ORDER BY c.`start_date` DESC
            LIMIT 0, 10"
    );
    if (mysqli_num_rows($team_cup) > 0) {

        $played_cups = '';
        while ($dx = mysqli_fetch_array($team_cup)) {

            $subget = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            `platzierung`
                        FROM `" . PREFIX . "cups_platzierungen`
                        WHERE `teamID` = " . $team_id . " AND `cupID` = " . $dx['cupID']
                )
            );

            $platz = (!empty($subget['platzierung'])) ? $subget['platzierung'] : '';

            $url = 'index.php?site=cup&amp;action=details&amp;id=' . $dx['cupID'];

            $played_cups .= '<a href="' . $url . '" class="list-group-item">';
            $played_cups .= $dx['name'];

            if (!empty($platz)) {
                $played_cups .= '<span class="pull-right">' . $platz . '</span>';
            }

            $played_cups .= '</a>';

        }

    } else {
        $played_cups = '<div class="list-group-item">' . $_language->module['no_cup'] . '</div>';
    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
