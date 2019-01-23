<?php

try {

    $team_award = mysqli_query(
        $_database,
        "SELECT
                a.awardID,
                a.award,
                a.cupID AS cup_id,
                b.name AS cup_name,
                c.name AS award_name,
                c.icon AS award_icon
            FROM `" . PREFIX . "cups_awards` a
            LEFT JOIN `".PREFIX."cups` b ON a.cupID = b.cupID
            LEFT JOIN `".PREFIX."cups_awards_category` c ON a.award = c.awardID
            WHERE a.teamID = " . $team_id . "
            ORDER BY a.date DESC
            LIMIT 0, 10"
    );
    if (mysqli_num_rows($team_award)) {

        $team_awards = '';
        while ($dx = mysqli_fetch_array($team_award)) {

            if ($dx['cup_id'] > 0) {
                $info = $dx['cup_name'];
                $info .= '<span class="pull-right">' . $dx['award'] . '</span>';
            } else {
                $info = '<div style="background: url(' . $image_url . '/cup/' . $dx['award_icon'] . '_small.png) no-repeat left; padding: 0 0 0 20px;">' . $dx['award_name'] . '</div>';
            }

            $team_awards .= '<div class="list-group-item">' . $info . '</div>';

        }

    } else {
        $team_awards = '<div class="list-group-item">' . $_language->module['no_award'] . '</div>';
    }

} catch ( Exception $e) {
    echo showError($e->getMessage());
}
