<?php

try {

    if (!isset($content)) {
        $content = '';
    }

    $selectPrizesQuery = cup_query(
        "SELECT * FROM `" . PREFIX . "cups_prizes`
            WHERE `cup_id` = " . $cup_id . "
            ORDER BY `placement` ASC",
        __FILE__
    );

    if (mysqli_num_rows($selectPrizesQuery) > 0) {

        $prize_list = '';

        while ($get = mysqli_fetch_array($selectPrizesQuery)) {

            $prize_text = $get['placement'] . '. ' . $get['prize'];

            $prize_list .= '<div class="list-group-item">' . $prize_text . '</div>';

        }

        $data_array = array();
        $data_array['$panel_type'] = 'panel-default';
        $data_array['$panel_title'] = $_language->module['prizes'];
        $data_array['$panel_content'] = $prize_list;
        $content .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

    } else {
        $content .= showInfo($_language->module['no_prize']);
    }

} catch(Exception $e) {
    $content .= showError($e->getMessage());
}