<?php

try {

    $_language->readModule('gameaccounts');

    if (!$loggedin) {
        throw new \Exception($_language->module['login']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'index.php?site=gameaccount';

        //
        // Gameaccount Klasse
        systeminc('classes/gameaccounts');

        try {

            if (isset($_POST['submitValidationCSGO'])) {

                $gameaccount_id = (isset($_POST['gameacc_id']) && validate_int($_POST['gameacc_id'])) ?
                    (int)$_POST['gameacc_id'] : 0;

                if ($gameaccount_id < 1) {
                    throw new \Exception($_language->module['error_gameaccount_id_type']);
                }

                $query = mysqli_query(
                    $_database,
                    "UPDATE `".PREFIX."cups_gameaccounts_csgo`
                        SET `validated` = 1
                        WHERE `gameaccID` = " . $gameaccount_id
                );

                if (!$query) {
                    throw new \Exception($_language->module['query_failed_update']);
                }

            } else if(isset($_POST['activateMCAccount'])) {

                //
                // Minecraft Account Activation

                $get = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT gameaccID, value FROM `".PREFIX."cups_gameaccounts`
                            WHERE userID = " . $userID . " AND category = 'mc' AND active = 1"
                    )
                );

                if(empty($get['value'])) {
                    throw new \Exception($_language->module['error_gameaccount_value']);
                }

                $gameaccount_id = $get['gameaccID'];
                $unique_id = '';
                $value = $get['value'];

                $checkIf = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_gameaccounts_mc`
                            WHERE gameaccID = " . $gameaccount_id
                    )
                );

                if($checkIf['exist'] != 1) {
                    throw new \Exception($_language->module['error_gameaccount_id_type']);
                }

                $subget = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT `unique_id` FROM `".PREFIX."cups_gameaccounts_mc`
                            WHERE gameaccID = " . $gameaccount_id
                    )
                );

                $curl_url = 'http://bot.myrisk-gaming.de/cup/minecraft.php?site=mc_account_activation&value='.$value.'&unique_id='.$subget['unique_id'];
                $accountData = getAPIData($curl_url);
                $accountData = json_decode($accountData, true);

                $active = (isset($accountData['status']) && ($accountData['status'] == TRUE)) ?
                    1 : 0;

                $query = mysqli_query(
                    $_database,
                    "UPDATE `".PREFIX."cups_gameaccounts_mc`
                        SET active = " . $active . ", 
                            date = " . time() . "
                        WHERE gameaccID = " . $gameaccount_id
                );

                if(!$query) {
                    throw new \Exception($_language->module['query_failed_update']);
                }

            } else if(isset($_POST['submitAddGameaccount']) || isset($_POST['submitEditGameaccount'])) {

                $gameaccount = new \myrisk\gameaccount();

                if(isset($_POST['gameaccount_id']) && validate_int($_POST['gameaccount_id'])) {
                    $gameaccount_id = (int)$_POST['gameaccount_id'];
                } else {
                    $gameaccount_id = null;
                }

                if(isset($_POST['game_id']) && validate_int($_POST['game_id'])) {
                    $game_id = $_POST['game_id'];
                } else if(isset($_POST['game_tag'])) {
                    $game_id = getGame($_POST['game_tag'], 'id');
                } else {
                    $game_id = null;
                }

                if(isset($_POST['id'])) {
                    $var_value = $_POST['id'];
                } else {
                    $var_value = null;
                }

                $gameaccount->insertGameaccount(
                    $gameaccount_id,
                    $game_id, 
                    $var_value
                );

                $_SESSION['successArray'][] = $_language->module['query_saved_insert'];

            } else {

                $arrayKeys = array_keys($_POST);
                $postArray = explode('_', $arrayKeys[0]);

                if((count($postArray) < 1) || !isset($postArray[0])) {
                    throw new \Exception($_language->module['error']);
                }

                if($postArray[0] == 'deleteGameacc') {

                    $gameacc_id = $postArray[1];
                    $ds = mysqli_fetch_array(
                        mysqli_query(
                            $_database, 
                            "SELECT userID FROM ".PREFIX."cups_gameaccounts 
                                WHERE gameaccID = " . $gameacc_id
                        )
                    );

                    if($userID != $ds['userID']) {
                        throw new \Exception($_language->module['error']);
                    }

                    try {

                        $gameaccount = new \myrisk\gameaccount();
                        $gameaccount->setGameaccountID($gameacc_id);
                        $gameaccount->deleteGameaccount();

                        $_SESSION['successArray'][] = $_language->module['query_saved_delete'];

                    } catch(Exception $e) {
                        $_SESSION['errorArray'][] = $e->getMessage();
                    }

                }

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $games = getGamesAsOptionList('csg', FALSE);

        $gameaccount_id = (isset($_GET['id']) && validate_int($_GET['id'])) ?
            (int)$_GET['id'] : 0;

        if ($getAction == 'edit') {

            if ($gameaccount_id < 1) {
                throw new \Exception($_language->module['error_gameaccount_id_type']);
            }

            $info = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "cups_gameaccounts`
                    WHERE gameaccID = " . $gameaccount_id . " AND deleted = 0"
            );

            if (!$info || (mysqli_num_rows($info) != 1)) {
                throw new \Exception($_language->module['error_gameaccount_id_type']);
            }

            $ds = mysqli_fetch_array($info);

            $error = (!empty($error)) ?
                showError($error) : '';

            $game = getgamename($ds['category']);
            $id = $ds['value'];

            $data_array = array();
            $data_array['$error'] = $error;
            $data_array['$gameaccID'] = $gameaccount_id;
            $data_array['$game'] = $game;
            $data_array['$game_tag'] = $ds['category'];
            $data_array['$id'] = $id;
            $gameacc_edit = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_edit", $data_array);
            echo $gameacc_edit;

        } else {

            $info_gameacc = '';

            //
            // Gameaccount/-s, die nicht aktiviert wurden?
            // wenn ja, Text-Ausgabe
            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            COUNT(*) AS `anz`
                        FROM `" . PREFIX . "cups_gameaccounts`
                        WHERE `userID` = " . $userID . " AND `active` = 0 AND `deleted` = 0"
                )
            );
            if ($get['anz'] > 0) {
                $info_gameacc .= showInfo($_language->module['gameacc_wait'], true);
            }

            //
            // Gameaccount/-s, die geloescht wurden?
            // wenn ja, zeige Info-Text und setze "deleted_seen" Flag
            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            COUNT(*) AS `anz`
                        FROM `" . PREFIX . "cups_gameaccounts`
                        WHERE `userID` = " . $userID . " AND `deleted` = 1 AND `deleted_seen` = 0"
                )
            );
            if ($get['anz'] > 0) {
                $info_gameacc .= showError($_language->module['gameacc_wrong_id'], true);
                gameaccount($userID, 'deleted_seen', '');
            }

            $info = mysqli_query(
                $_database,
                "SELECT
                        `gameaccID`,
                        `category`,
                        `value`,
                        `active`
                    FROM `".PREFIX."cups_gameaccounts`
                    WHERE userID = " . $userID . " AND deleted = 0"
            );
            if ($info && (mysqli_num_rows($info) > 0) {

                $n = 1;

                $gameaccounts = '';
                while ($ds = mysqli_fetch_array($info)) {

                    $gameacc_id = $ds['gameaccID'];

                    $category = $ds['category'];
                    $game = getgamename($category);

                    $active = '';
                    $validate = '';

                    $active .= '<form method="post">';

                    if ($category == 'mc') {

                        //
                        // Minecraft Account Aktivierung

                        $subget = mysqli_fetch_array(
                            mysqli_query(
                                $_database,
                                "SELECT * FROM `" . PREFIX . "cups_gameaccounts_mc`
                                    WHERE gameaccID = " . $gameacc_id
                            )
                        );

                        if (empty($subget['active']) || ($subget['active'] != 1)) {
                            $active .= ' <button class="btn btn-info btn-xs white darkshadow" type="submit" name="activateMCAccount">' . $_language->module['gameacc_activate_mc'] . '</button>';
                        }

                    } else if (($category == 'csg') && (gameaccount($userID, 'validated', 'csg') == 0)) {

                        //
                        // CS:GO Account Validierung
                        $active .= ' <button type="button" class="btn btn-danger btn-xs" onclick="validateCSGOAccount('.$n.');" id="validateCSGOAccountButton">' . $_language->module['validate_account'] . '</button>';

                        $data_array = array();
                        $data_array['$image_url'] = $image_url;
                        $data_array['$n'] = $n;
                        $data_array['$gameaccount'] = '';
                        $data_array['$gameacc_id'] = 0;
                        $data_array['$unique_id'] = '';
                        $gameaccount_validation_csgo = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_validation_csgo", $data_array);
                        $validate = $gameaccount_validation_csgo;

                    }

                    //
                    // status
                    if ($ds['active'] == 1) {
                        $active .= ' <span class="btn btn-success btn-xs">' . $_language->module['gameacc_active'] . '</span>';
                    } else {
                        $active .= ' <span class="btn btn-danger btn-xs">' . $_language->module['gameacc_active_no'] . '</span>';
                    }

                    //
                    // edit
                    $active .= ' <a class="btn btn-default btn-xs" href="index.php?site=gameaccount&amp;action=edit&amp;id=' . $gameacc_id . '#content">' . $_language->module['edit'] . '</a>';

                    //
                    // delete
                    $active .= ' <button class="btn btn-default btn-xs" type="submit" name="deleteGameacc_' . $gameacc_id . '">' . $_language->module['delete'] . '</button>';

                    $active .= '</form>';

                    $data_array = array();
                    $data_array['$n'] = $n;
                    $data_array['$game'] = $game;
                    $data_array['$value'] = $ds['value'];
                    $data_array['$active'] = $active;
                    $data_array['$validate'] = $validate;
                    $gameaccounts .= $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_container", $data_array);

                    $n++;

                }

            } else {
                $gameaccounts = '<tr><td colspan="3">' . $_language->module['no_gameacc'] . '</td></tr>';
            }

            $id = '';

            //
            // SteamID Finder
            $data_array = array();
            $data_array['$image_url'] = $image_url;
            $steam_finder = $GLOBALS["_template_cup"]->replaceTemplate("steam_finder", $data_array);
            echo $steam_finder;

            //
            // Gameaccount Home
            $data_array = array();
            $data_array['$info_gameacc'] = $info_gameacc;
            $data_array['$gameaccounts'] = $gameaccounts;
            $data_array['$games'] = $games;
            $data_array['$id'] = $id;
            $gameacc_home = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_home", $data_array);
            echo $gameacc_home;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
