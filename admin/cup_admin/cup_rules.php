<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    if (validate_array($_POST, true)) {

        try {

            if(isset($_POST['submitAddRule']) || isset($_POST['submitEditRule'])) {

                $name = isset($_POST['name']) ?
                    getinput($_POST['name']) : '';

                if(empty($name)) {
                    throw new \Exception($_language->module['error_rule_no_name']);
                }

                $ruletext = isset($_POST['ruletext']) ?
                    getinput($_POST['ruletext']) : '';

                if(empty($ruletext)) {
                    throw new \Exception($_language->module['error_rule_no_text']);
                }

                $game_id = (isset($_POST['game']) && validate_int($_POST['game'])) ?
                    (int)$_POST['game'] : 0;

                if($game_id < 1) {
                    throw new \Exception($_language->module['error_rule_no_game']);
                }

                if(isset($_POST['submitAddRule'])) {

                    $saveQuery = mysqli_query(
                        $_database,
                        "INSERT INTO ".PREFIX."cups_rules 
                            (
                                `name`,
                                `gameID`,
                                `text`,
                                `date`
                            )
                            VALUES
                            (
                                '" . $name . "',
                                " . $game_id . ",
                                '" . $ruletext . "',
                                " . time() . "
                            )"
                    );

                    if (!$saveQuery) {
                        throw new \Exception();
                    }

                    $rule_id = mysqli_insert_id($_database);

                } else {

                    $rule_id = (isset($_POST['rule_id']) && validate_int($_POST['rule_id'])) ?
                        (int)$_POST['rule_id'] : 0;

                    if($rule_id < 1) {
                        throw new \Exception($_language->module['unknown_rule_id']);
                    }

                    $updateQuery = mysqli_query(
                        $_database,
                        "UPDATE ".PREFIX."cups_rules 
                            SET name = '" . $name . "', 
                                gameID = " . $game_id . ",
                                text = '" . $ruletext . "', 
                                date = " . time() . " 
                            WHERE ruleID = " . $rule_id
                    );

                    if (!$updateQuery) {
                        throw new \Exception();
                    }

                }

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: admincenter.php?site=cup&mod=rules');

    } else {

        $rule_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
            (int)$_GET['id'] : 0;

        if ($getAction == 'add') {

            $data_array = array();
            $data_array['$name'] = '';
            $data_array['$games'] = getGamesAsOptionList('csg', FALSE);
            $data_array['$text'] = '';
            $data_array['$ruleID'] = 0;
            $data_array['$postName'] = 'submitAddRule';
            $rules_add = $GLOBALS["_template_cup"]->replaceTemplate("cup_rules_action", $data_array);
            echo $rules_add;

        } else if ($getAction == 'edit') {

            if ($rule_id < 1) {
                throw new \Exception($_language->module['unknown_rule_id']);
            }

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database, 
                    "SELECT * FROM `".PREFIX."cups_rules` 
                        WHERE ruleID = " . $rule_id
                )
            );

            $data_array = array();
            $data_array['$name'] = $ds['name'];
            $data_array['$games'] = getGamesAsOptionList($ds['gameID'], FALSE);
            $data_array['$text'] = $ds['text'];
            $data_array['$ruleID'] = $rule_id;
            $data_array['$postName'] = 'submitEditRule';
            $rules_edit = $GLOBALS["_template_cup"]->replaceTemplate("cup_rules_action", $data_array);
            echo $rules_edit;

        } else if ($getAction == 'delete') {

            try {

                if ($rule_id < 1) {
                    throw new \Exception($_language->module['unknown_rule_id']);
                }

                $deleteQuery = mysqli_query(
                    $_database,
                    "DELETE FROM `" . PREFIX . "cups_rules`
                        WHERE ruleID = " . $rule_id
                );

                if (!$deleteQuery) {
                    throw new \Exception($_language->module['query_delete_failed']);
                }

                $text = 'Regel #' . $rule_id . ' wurde gel&ouml;scht';
                $_SESSION['successArray'][] = $text;

            } catch (Exception $e) {
                $_SESSION['errorArray'][] = $e->getMessage();
            }

            header('Location: admincenter.php?site=cup&mod=rules');

        } else {

            $ergebnis = mysqli_query(
                $_database,
                "SELECT
                        a.ruleID AS ruleID,
                        a.name AS name,
                        a.date AS date,
                        b.short AS game
                    FROM `".PREFIX."cups_rules` a
                    JOIN `".PREFIX."games` b ON a.gameID = b.gameID
                    ORDER BY a.gameID ASC, a.name ASC"
            );

            if (mysqli_num_rows($ergebnis) > 0) {

                $content = '';

                while($ds = mysqli_fetch_array($ergebnis)) {

                    $data_array = array();
                    $data_array['$name'] = $ds['name'];
                    $data_array['$game'] = $ds['game'];
                    $data_array['$date'] = getformatdatetime($ds['date']);
                    $data_array['$rule_id'] = $ds['ruleID'];
                    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_rules_list", $data_array);

                }

            } else {
                $content = '<tr><td colspan="4">'.$_language->module['no_rules'].'</td></tr>';
            }

            $data_array = array();
            $data_array['$content'] = $content;
            $rules_home = $GLOBALS["_template_cup"]->replaceTemplate("cup_rules_home", $data_array);
            echo $rules_home;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}