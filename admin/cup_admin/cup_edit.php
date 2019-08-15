<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'])) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    if (!checkIfContentExists($cup_id, 'cupID', 'cups')) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }

    $maxPrices = 6;

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id;

        try {

            if (!isset($_POST['edit'])) {
                throw new \Exception($_language->module['unknown_action']);
            }

            $setValueArray = array();

            if (isset($_POST['challonge_url'])) {
                
                $challonge_url = (validate_url($_POST['challonge_url'])) ?
                    getinput($_POST['challonge_url']) : '';
                
                if (empty($challonge_url)) {
                    
                    $setValueArray[] = '`challonge_api` = 0';
                    $setValueArray[] = '`challonge_url` = NULL';
                    
                } else {
                    
                    $setValueArray[] = '`challonge_api` = 1';
                    $setValueArray[] = '`challonge_url` = \'' . $challonge_url . '\'';

                }

            } else {
                
                $cupname = (isset($_POST['cupname']) && !empty($_POST['cupname'])) ?
                    getinput($_POST['cupname']) : '';

                if (empty($cupname)) {
                    throw new \Exception($_language->module['cup_noname']);
                }

                $setValueArray[] = '`name` = \'' . $cupname . '\'';

                $priority = (isset($_POST['priority'])) ?
                    getinput($_POST['priority']) : 'normal';

                $setValueArray[] = '`priority` = \'' . $priority . '\'';

                $registration = (isset($_POST['registration'])) ?
                    getinput($_POST['registration']) : 'open';

                $setValueArray[] = '`registration` = \'' . $registration . '\'';

                $elimination = (isset($_POST['elimination'])) ?
                    getinput($_POST['elimination']) : 'single';

                $setValueArray[] = '`elimination` = \'' . $elimination . '\'';

                $date_checkin = convertStringToDateInMs($_POST['date_checkin'], $_POST['hour_ci'], $_POST['minute_ci']);

                $setValueArray[] = '`checkin_date` = ' . $date_checkin;

                $date_start = convertStringToDateInMs($_POST['date_start'], $_POST['hour'], $_POST['minute']);

                $setValueArray[] = '`start_date` = ' . $date_start;

                $game_tag = (isset($_POST['game'])) ? 
                    getinput($_POST['game']) : '';

                $setValueArray[] = '`game` = \'' . $gameArray['tag'] . '\'';
                $setValueArray[] = '`gameID` = ' . $gameArray['id'];

                if (empty($game_tag)) {
                    throw new \Exception($_language->module['unknown_game_tag']);
                }

                $gameArray = getGame($game_tag);

                $mode = (isset($_POST['mode'])) ? 
                    getinput($_POST['mode']) : '5on5';

                $setValueArray[] = '`mode` = \'' . $mode . '\'';

                $rule_id = (isset($_POST['ruleID']) && validate_int($_POST['ruleID'], true)) ?
                    (int)$_POST['ruleID'] : 0;

                $setValueArray[] = '`ruleID` = ' . $rule_id;

                $size = (isset($_POST['size']) && validate_int($_POST['size'])) ?
                    (int)$_POST['size'] : 32;

                $setValueArray[] = '`max_size` = ' . $size;

                $pps = (isset($_POST['max_pps']) && validate_int($_POST['max_pps'])) ?
                    (int)$_POST['max_pps'] : 12;

                $setValueArray[] = '`max_penalty` = ' . $pps;

                $admin_visible 	= (isset($_POST['admin_visible']) && validate_int($_POST['admin_visible'])) ?
                    (int)$_POST['admin_visible'] : 0;

                $setValueArray[] = '`admin_visible` = ' . $admin_visible;

            }

            if (!validate_array($setValueArray, true)) {
                throw new \Exception($_language->module['unexpected_empty_array']);
            }

            $query = cup_query(
                "UPDATE `" . PREFIX . "cups`
                    SET	" . implode(', ', $setValueArray) . "
                    WHERE cupID = " . $cup_id,
                __FILE__
            );

            $_SESSION['successArray'][] = $_language->module['query_saved'];

            $deleteQuery = cup_query(
                "DELETE FROM `".PREFIX."cups_prizes`
                    WHERE `cup_id` = " . $cup_id,
                __FILE__
            );

            //
            // Save prizes
            for ($x = 1; $x < ($maxPrices + 1); $x++) {

                if (!isset($_POST['prize'][$x])) {
                    continue;
                }

                $prize = $_POST['prize'][$x];

                if (empty($prize)) {
                    continue;
                }

                savePrize($cup_id, $prize, $x);

            }

        } catch (Exception $e) {

            $_SESSION['errorArray'][] = $e->getMessage();

            $parent_url = 'admincenter.php?site=cup&mod=cup&action=edit&id=' . $cup_id;

        }

        header('Location: ' . $parent_url);

    } else {

        $error = '';

        //
        // Cup Array
        $cupArray = getcup($cup_id);

        $cupOptions = getCupOption();

        $admin_only = $cupOptions['true_false'];

        $admin_only = str_replace(
            'value="' . $cupArray['admin'] . '"',
            'value="' . $cupArray['admin'] . '" selected="selected"',
            $admin_only
        );

        $priority = str_replace(
            'value="'.$cupArray['priority'].'"',
            'value="'.$cupArray['priority'].'" selected="selected"',
            $cupOptions['priority']
        );

        $registration = str_replace(
            'value="'.$cupArray['registration'].'"',
            'value="'.$cupArray['registration'].'" selected="selected"',
            $cupOptions['registration']
        );

        $elimination = str_replace(
            'value="'.$cupArray['elimination'].'"',
            'value="'.$cupArray['elimination'].'" selected="selected"',
            $cupOptions['elimination']
        );

        $hours = '';
        for ($i = 1; $i < 25; $i++) {
            $value = ($i < 10) ? '0' . $i : $i;
            $hours .= '<option value="' . $value . '">' . $value . '</option>';
        }

        $minutes = '';
        for ($i = 0; $i < 4; $i++) {
            $value = (($i * 15) > 0) ? ($i * 15) : '00';
            $minutes .= '<option value="' . $value . '">' . $value . '</option>';
        }

        $date_checkin = date('Y-m-d', $cupArray['checkin']);

        $hours_ci = str_replace(
            'value="'.date('H', $cupArray['checkin']).'"',
            'value="'.date('H', $cupArray['checkin']).'" selected="selected"',
            $hours
        );

        $minutes_ci = str_replace(
            'value="'.date('i', $cupArray['checkin']).'"',
            'value="'.date('i', $cupArray['checkin']).'" selected="selected"',
            $minutes
        );

        $date_start = date('Y-m-d', $cupArray['start']);

        $hours_sd = str_replace(
            'value="'.date('H', $cupArray['start']).'"',
            'value="'.date('H', $cupArray['start']).'" selected="selected"',
            $hours
        );

        $minutes_sd = str_replace(
            'value="'.date('i', $cupArray['start']).'"',
            'value="'.date('i', $cupArray['start']).'" selected="selected"',
            $minutes
        );

        $game_id = getGame($cupArray['game'], 'id');

        $games = getGamesAsOptionList($game_id, FALSE);

        $mode = str_replace(
            'value="'.$cupArray['mode'].'"',
            'value="'.$cupArray['mode'].'" selected="selected"',
            $cupOptions['mode']
        );

        $mappool = getMappool($cupArray['mappool'], 'list');

        $rules = getrules($cupArray['rule_id'], 'list', true);

        $size = str_replace(
            'value="'.$cupArray['size'].'"',
            'value="'.$cupArray['size'].'" selected="selected"',
            $cupOptions['size']
        );

        $pps = str_replace(
            'value="'.$cupArray['max_pps'].'"',
            'value="'.$cupArray['max_pps'].'" selected="selected"',
            $cupOptions['penalty']
        );

        $prizeArray = array();

        $prizeQuery = cup_query(
            "SELECT * FROM `" . PREFIX . "cups_prizes`
                WHERE `cup_id` = " . $cup_id,
            __FILE__
        );

        while ($dx = mysqli_fetch_array($prizeQuery)) {
            if (!empty($dx['prize'])) {
                $prizeArray[$dx['placement']] = $dx['prize'];
            }
        }

        $challonge_url = '';
        if (isset($cupArray['settings']['challonge']['url'])) {
            $challonge_url = $cupArray['settings']['challonge']['url'];
        }

        $data_array = array();
        $data_array['$title'] = $_language->module['cup_add'] . ' - ' . $cupArray['name'];
        $data_array['$cupID'] = $cup_id;
        $data_array['$error'] = $error;
        $data_array['$cupname'] = $cupArray['name'];
        $data_array['$admin_only'] = $admin_only;
        $data_array['$priority'] = $priority;
        $data_array['$registration'] = $registration;
        $data_array['$elimination'] = $elimination;
        $data_array['$date_checkin'] = $date_checkin;
        $data_array['$hours_ci'] = $hours_ci;
        $data_array['$minutes_ci'] = $minutes_ci;
        $data_array['$date_start'] = $date_start;
        $data_array['$hours_sd'] = $hours_sd;
        $data_array['$minutes_sd'] = $minutes_sd;
        $data_array['$games'] = $games;
        $data_array['$mode'] = $mode;
        $data_array['$rules'] = $rules;
        $data_array['$size'] = $size;
        $data_array['$pps'] = $pps;

        for ($x = 1; $x < ($maxPrices + 1); $x++) {
            $data_array['$prize' . $x] = (isset($prizeArray[$x])) ? $prizeArray[$x] : '';
        }

        $data_array['$postName'] = 'edit';
        $data_array['$challonge_url'] = $challonge_url;
        $cups_edit = $GLOBALS["_template_cup"]->replaceTemplate("cups_action", $data_array);
        echo $cups_edit;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
