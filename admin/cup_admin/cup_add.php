<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    $maxPreise = 6;

    function convertStringToDateInMs($string, $hours, $minutes) {
        $returnValue = strtotime($string);
        $returnValue += ((int)$hours * 3600);
        $returnValue += ((int)$minutes * 60);
        return $returnValue;
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=cup&action=add';

        try {

            if (isset($_POST['add'])) {

                if (!isset($_POST['cupname']) || empty($_POST['cupname'])) {
                    throw new \Exception($_language->module['cup_no_name']);
                }

                $cupname = getinput($_POST['cupname']);

                $priority = (isset($_POST['priority'])) ?
                    $_POST['priority'] : 'normal';

                $registration = (isset($_POST['registration'])) ?
                    $_POST['registration'] : 'open';

                $elimination = (isset($_POST['elimination'])) ?
                    $_POST['elimination'] : 'single';

                $date_checkin = convertStringToDateInMs($_POST['date_checkin'], $_POST['hour_ci'], $_POST['minute_ci']);
                $date_start = convertStringToDateInMs($_POST['date_start'], $_POST['hour'], $_POST['minute']);

                if ($date_checkin >= $date_start) {
                    $date_start = $date_checkin + 900;
                    $_SESSION['errorArray'][] = $_language->module['cup_start_time_fixed'];
                }

                $game_tag = (isset($_POST['game'])) ?
                    $_POST['game'] : 'csg';

                $gameArray = getGame($game_tag);

                $mode = (isset($_POST['mode'])) ? $_POST['mode'] : '5on5';

                $rule_id = (isset($_POST['ruleID']) && validate_int($_POST['ruleID'])) ?
                    (int)$_POST['ruleID'] : 0;

                $size = (isset($_POST['size']) && validate_int($_POST['size'])) ?
                    (int)$_POST['size'] : 32;

                $pps = (isset($_POST['max_pps']) && is_numeric($_POST['max_pps'])) ?
                    (int)$_POST['max_pps'] : 12;

                $admin_visible 	= (isset($_POST['admin_visible']) && is_numeric($_POST['admin_visible'])) ?
                    (int)$_POST['admin_visible'] : 0;

                $insertQuery = cup_query(
                    "INSERT INTO `" . PREFIX . "cups`
                        (
                            `priority`,
                            `name`,
                            `registration`,
                            `checkin_date`,
                            `start_date`,
                            `game`,
                            `gameID`,
                            `elimination`,
                            `mode`,
                            `ruleID`,
                            `max_size`,
                            `max_penalty`,
                            `admin_visible`
                        )
                        VALUES
                        (
                            '" . $priority . "',
                            '" . $cupname . "',
                            '" . $registration . "',
                            " . $date_checkin . ",
                            " . $date_start . ",
                            '" . $game_tag . "',
                            " . $gameArray['id'] . ",
                            '" . $elimination . "',
                            '" . $mode . "',
                            " . $rule_id . ",
                            " . $size . ",
                            " . $pps . ",
                            " . $admin_visible . "
                        )",
                    __FILE__
                );

                $cup_id = mysqli_insert_id($_database);

                //
                // Preise speichern
                for ($x = 1; $x < ($maxPreise + 1); $x++) {

                    if (isset($_POST['preis'][$x])) {

                        $preis = $_POST['preis'][$x];
                        if (!empty($preis)) {

                            $insertQuery = cup_query(
                                "INSERT INTO `" . PREFIX . "cups_preise`
                                    (
                                        `cupID`,
                                        `preis`,
                                        `platzierung`
                                    )
                                    VALUES
                                    (
                                        " . $cup_id . ",
                                        '".$preis."',
                                        " . $x . "
                                    )",
                                __FILE__
                            );

                        }

                    }

                }

                $parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id;

                $_SESSION['successArray'][] = $_language->module['cup_saved'];

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $cupOptions = getCupOption();

        $admin_only = '<option value="1">'.$_language->module['yes'].'</option><option value="0" selected="selected">'.$_language->module['no'].'</option>';

        $days = date('d');
        $months = date('m');
        $years = date('Y');

        $hours_ci = '';
        for ($i = 0; $i < 25; $i++) {
            $sel = '';
            if ($i == 19) { $sel = ' selected="selected"'; }
            $hours_ci .= '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';
        }

        $hours = '';
        for ($i = 0; $i < 25; $i++) {
            $hours .= '<option value="' . $i . '">' . $i . '</option>';
        }
        
        $hours_checkin = str_replace(
            'value="19"',
            'value="19" selected="selected"',
            $hours
        );
        
        $hours_start = str_replace(
            'value="20"',
            'value="20" selected="selected"',
            $hours
        );

        $minutes = '<option value="0">00</option><option value="15">15</option><option value="30">30</option><option value="45">45</option>';

        $games = getGamesAsOptionList('csg');

        $rules = getrules(0, 'list', true);

        $mode = str_replace(
            'value="5on5"',
            'value="5on5" selected="selected"',
            $cupOptions['mode']
        );

        $size = str_replace(
            'value="32"',
            'value="32" selected="selected"',
            $cupOptions['size']
        );

        $penalty = str_replace(
            'value="12"',
            'value="12" selected="selected"',
            $cupOptions['penalty']
        );

        $data_array = array();
        $data_array['$title'] = $_language->module['cup_add'];
        $data_array['$cupID'] = 0;
        $data_array['$error'] = '';
        $data_array['$cupname'] = '';
        $data_array['$admin_only'] = $admin_only;
        $data_array['$priority'] = $cupOptions['priority'];
        $data_array['$registration'] = $cupOptions['registration'];
        $data_array['$elimination'] = $cupOptions['elimination'];
        $data_array['$date_checkin'] = $years . '-' . $months . '-' . $days;
        $data_array['$hours_ci'] = $hours_checkin;
        $data_array['$minutes_ci'] = $minutes;
        $data_array['$date_start'] = $years . '-' . $months . '-' . $days;
        $data_array['$hours_sd'] = $hours_start;
        $data_array['$minutes_sd'] = $minutes;
        $data_array['$games'] = $games;
        $data_array['$mode'] = $mode;
        $data_array['$size'] = $size;
        $data_array['$rules'] = $rules;
        $data_array['$pps'] = $penalty;

        for ($x = 1; $x < ($maxPreise + 1); $x++) {
            $data_array['$preis'.$x] = '';
        }

        $data_array['$postName'] = 'add';
        $cups_add = $GLOBALS["_template_cup"]->replaceTemplate("cups_action", $data_array);
        echo $cups_add;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
