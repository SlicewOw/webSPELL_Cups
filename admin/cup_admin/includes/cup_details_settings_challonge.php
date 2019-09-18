<?php

try {

    if (!isset($cup_id)) {
        throw new \UnexpectedValueException('unknown_cup_id');
    }

    if (!isset($cupArray) || !validate_array($cupArray, true)) {
        $cupArray = getcup($cup_id);
    }

    $challonge_api_options = '<option value="0">' . $_language->module['no'] . '</option>';
    $challonge_api_options .= '<option value="1">' . $_language->module['yes'] . '</option>';


    $settingsArray = $cupArray['settings'];

    if (isset($settingsArray['challonge']['state'])) {

        $challonge_api_options = str_replace(
            'value="' . $settingsArray['challonge']['state'] . '"',
            'value="' . $settingsArray['challonge']['state'] . '" selected="selected"',
            $challonge_api_options
        );

    }

    $challonge_url = '';
    if (isset($settingsArray['challonge']['url'])) {
        $challonge_url = $settingsArray['challonge']['url'];
    }

    $data_array = array();
    $data_array['$cup_id'] = $cup_id;
    $data_array['$challongeApiOptions'] = $challonge_api_options;
    $data_array['$challongeUrl'] = $challonge_url;
    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_settings_challonge", $data_array);

} catch (Exception $e) {
    echo showError($e->getMessage());
}