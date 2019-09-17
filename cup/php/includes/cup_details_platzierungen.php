<?php

try {

    if (!isset($content)) {
        $content = '';
    }

    $getCupPlacementsQuery = cup_query(
        "SELECT
                cp.`teamID` AS `team_id`,
                cp.`platzierung` AS `team_placement`,
                c.`mode` AS `cup_mode`
            FROM `" . PREFIX . "cups_platzierungen` cp
            JOIN `" . PREFIX . "cups` c ON cp.`cupID` = c.`cupID`
            WHERE cp.`cupID` = " . $cup_id . "
            ORDER BY cp.`platzierung` ASC",
        __FILE__
    );

    if (mysqli_num_rows($getCupPlacementsQuery) > 0) {

        $cupPlacementList = '';

        $firstThreeTeamsArray = array();

        for ($x = 1; $x < 4; $x++) {

            $firstThreeTeamsArray[$x] = array(
                'url' => '',
                'name' => '',
                'logotype' => '',
                'visible' => 'display: none; '
            );

        }

        $placement_list = '';

        while ($get = mysqli_fetch_array($getCupPlacementsQuery)) {

            $team_id = $get['team_id'];

            if ($get['cup_mode'] == '1on1') {
                $url = 'index.php?site=profile&amp;id=' . $team_id;
                $name = getnickname($team_id);
                $logotype = getuserpic($team_id, true);
            } else {
                $url = 'index.php?site=teams&amp;action=details&amp;id=' . $team_id;
                $name = getteam($team_id, 'name');
                $logotype = getCupTeamImage($team_id, true);
            }

            $placement = $get['team_placement'];
            if ($placement < 4) {

                $imageAttributeArray = array();
                $imageAttributeArray[] = 'src="' . $logotype . '"';
                $imageAttributeArray[] = 'alt="' . $name . '"';
                $imageAttributeArray[] = 'title="' . $name . '"';

                $cssStyleArray = array();
                $cssStyleArray[] = 'width: 50px;';
                $cssStyleArray[] = 'height: 50px;';
                $cssStyleArray[] = 'margin: 10px auto 0 auto;';
                $cssStyleArray[] = 'display: block;';
                $cssStyleArray[] = 'border-radius: 50px;';

                $imageAttributeArray[] = 'style="' . implode(' ', $cssStyleArray) . '"';

                $firstThreeTeamsArray[$placement] = array(
                    'url' => $url,
                    'name' => $name,
                    'logotpye' => '<img ' . implode(' ', $imageAttributeArray) . ' />',
                    'visible' => ''
                );

            } else {

                $placement_text = $placement . '. - ' . $name;

                $placement_list .= '<a href="' . $url . '" class="list-group-item">' . $placement_text . '</a>';

            }

        }

        $data_array = array();
        for ($x = 1; $x < 4; $x++) {
            $data_array['$isVisible' . $x] = $firstThreeTeamsArray[$x]['visible'];
            $data_array['$teamLink' . $x] = $firstThreeTeamsArray[$x]['url'];
            $data_array['$teamName' . $x] = $firstThreeTeamsArray[$x]['name'];
            $data_array['$teamLogotype' . $x] = $firstThreeTeamsArray[$x]['logotpye'];
        }
        $data_array['$placements'] = $placement_list;
        $cupPlacementList .= $GLOBALS["_template_cup"]->replaceTemplate("cups_details_placement", $data_array);

        $data_array = array();
        $data_array['$panel_type'] = 'panel-success';
        $data_array['$panel_title'] = $_language->module['cup_placements'];
        $data_array['$panel_content'] = $cupPlacementList;
        $content .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

    }

} catch (Exception $e) {
    $content .= showError($e->getMessage());
}

