<?php

if (!isset($content)) {
    $content = '';
}

try {

    if (!iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (!isset($cup_id)) {
        $cup_id = (isset($_GET['id']) && validate_int($_GET['id'])) ? 
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
        'value="'.$cupArray['registration'].'"',
        'value="'.$cupArray['registration'].'" selected="selected"',
        $cupOptions['registration']
    );

    $cupSaved = str_replace(
        'value="'.$cupArray['saved'].'"',
        'value="'.$cupArray['saved'].'" selected="selected"',
        $cupOptions['true_false']
    );

    $adminOnly = str_replace(
        'value="'.$cupArray['admin'].'"',
        'value="'.$cupArray['admin'].'" selected="selected"',
        $cupOptions['true_false']
    );

    $mapVote = str_replace(
        'value="'.$cupArray['map_vote'].'"',
        'value="'.$cupArray['map_vote'].'" selected="selected"',
        $cupOptions['true_false']
    );

    $mapPool = getMappool(
        $cupArray['mappool'],
        'list'
    );

    $matchRoundFormat = '';

    for ($x = 0; $x < $cupArray['anz_runden']; $x++) {

        $round = ($x + 1);

        $matchRoundFormat .= '<div class="col-sm-3">';
        $matchRoundFormat .= '<div class="form-group">';
        $matchRoundFormat .= '<label>Runde ' . $round . '</label>';
        $matchRoundFormat .= '<select name="round[]" class="form-control">';
        $matchRoundFormat .= $cupOptions['rounds'];

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    `format`
                FROM `" . PREFIX . "cups_settings`
                WHERE `cup_id` = " . $cup_id . " AND `round` = " . $round
        );

        if ($selectQuery && mysqli_num_rows($selectQuery) == 1) {

            $getFormat = mysqli_fetch_array($selectQuery);

            $matchRoundFormat .= str_replace(
                'value="' . $getFormat['format'] . '"',
                'value="' . $getFormat['format'] . '" selected="selected"',
                $matchRoundFormat
            );

        }

        $matchRoundFormat .= '</select>';
        $matchRoundFormat .= '</div>';
        $matchRoundFormat .= '</div>';

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
