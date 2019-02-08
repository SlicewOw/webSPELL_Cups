<?php

if (!isset($content)) {
    $content = '';
}

try {

    $getCupPlacementsQuery = mysqli_query(
        $_database,
        "SELECT
                cp.`teamID` AS `team_id`,
                cp.`platzierung` AS `team_placement`,
                c.`mode` AS `cup_mode`
            FROM `" . PREFIX . "cups_platzierungen` cp
            JOIN `" . PREFIX . "cups` c ON cp.`cupID` = c.`cupID`
            WHERE cp.`cupID` = " . $cup_id . "
            ORDER BY cp.`platzierung` ASC"
    );

    if (!$getCupPlacementsQuery) {

    }

    if (mysqli_num_rows($getCupPlacementsQuery) > 0) {

        $cupPlacementList = '';

        while ($get = mysqli_fetch_array($getCupPlacementsQuery)) {

            $team_id = $get['team_id'];

            $name = ($get['cup_mode'] != '1on1') ?
                getteam($team_id, 'name') : getnickname($team_id);

            $data_array = array();
            $data_array['$team_id'] = $team_id;
            $data_array['$placement'] = $get['team_placement'] . '.';
            $data_array['$name'] = $name;
            $cupPlacementList .= $GLOBALS["_template_cup"]->replaceTemplate("cups_details_placement", $data_array);

        }

        $data_array = array();
        $data_array['$panel_type'] = 'panel-success';
        $data_array['$panel_title'] = $_language->module['cup_placements'];
        $data_array['$panel_content'] = $cupPlacementList;
        $content .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

    }

} catch (Exception $e) {
    $content .= showError($e->getMessage());
}

