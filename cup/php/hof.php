<?php

try {

    $_language->readModule('hof');

    $hof_content = '';

    /**
     * Erstplatzierte Teams
     */
    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                cp.`teamID` AS `primary_id`,
                c.`mode` AS `cup_mode`
            FROM `" . PREFIX . "cups_platzierungen` cp
            LEFT JOIN `" . PREFIX . "cups` c ON cp.`cupID` = c.`cupID`
            WHERE cp.`platzierung` = '1'"
    );

    if (!$selectQuery) {
        throw new \Exception($_language->module['query_select_failed']);
    }

    if (mysqli_num_rows($selectQuery) > 0) {

        while ($get = mysqli_fetch_array($selectQuery)) {

            $primary_id = $get['primary_id'];

            if ($get['cup_mode'] == '1on1') {
                $primary_url = 'index.php?site=profile&amp;id=' . $primary_id;
                $name = getnickname($primary_id);
                $logo = getuserpic($primary_id, true);
            } else {
                $primary_url = 'index.php?site=teams&amp;action=details&amp;id=' . $primary_id;
                $teamArray = getteam($primary_id);
                $name = $teamArray['name'];
                $logo = $teamArray['logotype'];
            }

            $data_array = array();
            $data_array['$primary_url'] = $primary_url;
            $data_array['$name'] = $name;
            $data_array['$logo'] = $logo;
            $hof_content .= $GLOBALS["_template_cup"]->replaceTemplate("hof_content", $data_array);

        }

    }

    if (empty($hof_content)) {
        $hof_content = '<div class="col-sm-12">' . showWarning($_language->module['no_hof_content']) . '</div>';
    }

    $data_array = array();
    $data_array['$hof_content'] = $hof_content;
    $navigation = $GLOBALS["_template_cup"]->replaceTemplate("hof_home", $data_array);
    echo $navigation;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
