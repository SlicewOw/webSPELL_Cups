<?php

try {

    if (!isset($content)) {
        $content = '';
    }

    if (!iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (!isset($cup_id) || !validate_int($_GET['id'], true)) {
        $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
            (int)$_GET['id'] : 0;
    }

    if ($cup_id < 1) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    if (!checkIfContentExists($cup_id, 'cupID', 'cups')) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    if (!isset($cupArray)) {
        $cupArray = getcup($cup_id);
    }

    $cupOptions = getCupOption();

    $cupSettingsArray = array();

    $registerFormat = str_replace(
        'value="' . $cupArray['registration'] . '"',
        'value="' . $cupArray['registration'] . '" selected="selected"',
        $cupOptions['registration']
    );

    $cupSaved = str_replace(
        'value="' . $cupArray['saved'] . '"',
        'value="' . $cupArray['saved'] . '" selected="selected"',
        $cupOptions['true_false']
    );

    $adminOnly = str_replace(
        'value="' . $cupArray['admin'] . '"',
        'value="' . $cupArray['admin'] . '" selected="selected"',
        $cupOptions['true_false']
    );

    $mapVote = str_replace(
        'value="' . $cupArray['map_vote'] . '"',
        'value="' . $cupArray['map_vote'] . '" selected="selected"',
        $cupOptions['true_false']
    );

    $mapPool = getMappool(
        $cupArray['mappool'],
        'list'
    );

    $matchRoundFormat = '';

    for ($x = 0; $x < $cupArray['anz_runden']; $x++) {

        $round = ($x + 1);

        $selectQuery = cup_query(
            "SELECT
                    `format`
                FROM `" . PREFIX . "cups_settings`
                WHERE `cup_id` = " . $cup_id . " AND `round` = " . $round,
            __FILE__
        );

        if (mysqli_num_rows($selectQuery) == 1) {

            $getFormat = mysqli_fetch_array($selectQuery);

            $round_options = str_replace(
                'value="' . $getFormat['format'] . '"',
                'value="' . $getFormat['format'] . '" selected="selected"',
                $cupOptions['rounds']
            );

        } else {
            $round_options = $cupOptions['rounds'];
        }

        $data_array = array();
        $data_array['$round'] = $round;
        $data_array['$roundOptions'] = $round_options;
        $matchRoundFormat .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_settings_map_round", $data_array);

    }

    $data_array = array();
    $data_array['$registerFormat'] = $registerFormat;
    $data_array['$cupSaved'] = $cupSaved;
    $data_array['$adminOnly'] = $adminOnly;
    $data_array['$mapVote'] = $mapVote;
    $data_array['$mapPool'] = $mapPool;
    $data_array['$matchRoundFormat'] = $matchRoundFormat;
    $data_array['$cup_id'] = $cup_id;
    $data_array['$cup_game'] = $cupArray['game'];
    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_settings", $data_array);

} catch (Exception $e) {
    $content .= showError($e->getMessage());
}
