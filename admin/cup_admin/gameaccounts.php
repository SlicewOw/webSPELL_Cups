<?php

try {

    $_language->readModule('gameaccounts', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    if (validate_array($_POST, true)) {

        //
        // Gameaccount Klasse
        systeminc('classes/gameaccounts');

        $parent_url = 'admincenter.php?site=cup&mod=gameaccounts';

        try {

            if(isset($_POST['submitGameaccActive'])) {

                $gameaccount_id = (isset($_POST['gameaccount_id']) && validate_int($_POST['gameaccount_id'])) ?
                    (int)$_POST['gameaccount_id'] : 0;

                if ($gameaccount_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_gameaccount']);
                }

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_gameaccounts`
                        SET active = 1
                        WHERE gameaccID = " . $gameaccount_id . " AND deleted = 0"
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['query_failed']);
                }

                $user_id = (isset($_POST['user_id']) && validate_int($_POST['user_id'])) ?
                    (int)$_POST['user_id'] : 0;

                if ($user_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_user']);
                }

                $messageText = 'Dein Gameaccount wurde von ' . getnickname($userID) . ' aktiviert';
                setNotification($user_id, 'index.php?site=gameaccount', $gameaccount_id, $messageText);

                $_SESSION['successArray'][]  = $_language->module['gameaccount_activated'];

                $parent_url .= '&action=log&user_id=' . $user_id;

            } else if (isset($_POST['deleteGameaccActive'])) {

                $gameaccount_id = (isset($_POST['gameaccount_id']) && validate_int($_POST['gameaccount_id'])) ?
                    (int)$_POST['gameaccount_id'] : 0;

                if ($gameaccount_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_gameaccount']);
                }

                $get = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT userID FROM `".PREFIX."cups_gameaccounts`
                            WHERE gameaccID = " . $gameaccount_id
                    )
                );

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_gameaccounts`
                        SET active = 0,
                            deleted = 1,
                            deleted_seen = 0
                        WHERE gameaccID = " . $gameaccount_id
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['query_failed']);
                }

                $messageTitle = $_language->module['gameaccount_deleted_title'];
                $messageText = $_language->module['gameaccount_deleted_text'];
                sendmessage($get['userID'], $messageTitle, $messageText);

                setNotification($get['userID'], 'index.php?site=gameaccount', $gameaccount_id, $messageText);

                $_SESSION['successArray'][] = $_language->module['query_deleted'];

            } else if (isset($_POST['addUserProfile'])) {

                $user_id = (isset($_POST['user_id']) && validate_int($_POST['user_id'])) ?
                    (int)$_POST['user_id'] : 0;

                if ($user_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_user']);
                }

                $category = (isset($_POST['category'])) ?
                    getinput($_POST['category']) : '';

                if (empty($category)) {
                    throw new \UnexpectedValueException($_language->module['category_missing']);
                }

                $profile_url = (isset($_POST['profile_url']) && validate_url($_POST['profile_url'])) ?
                    getinput($_POST['profile_url']) : '';

                if (empty($profile_url)) {
                    throw new \UnexpectedValueException($_language->module['profile_url_missing']);
                }

                $query = mysqli_query(
                    $_database,
                    "INSERT INTO `".PREFIX."cups_gameaccounts_profiles`
                        (
                            `user_id`,
                            `category`,
                            `url`,
                            `date`
                        )
                        VALUES
                        (
                            " . $user_id . ",
                            '" . $category . "',
                            '" . $profile_url . "',
                            " . time() . "
                        )"
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['query_failed']);
                }

                $_SESSION['successArray'][] = $_language->module['query_saved'];

                $parent_url .= '&action=log&profiles&user_id=' . $user_id;

            } else if (isset($_POST['submitBannedAccount'])) {

                $game_id = (isset($_POST['bannedGame']) && validate_int($_POST['bannedGame'])) ?
                    (int)$_POST['bannedGame'] : 0;

                if ($game_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_game']);
                }

                $game_tag = getGame($game_id, 'tag');

                $value = (isset($_POST['bannedValue'])) ?
                    getinput($_POST['bannedValue']) : '';

                if (empty($value)) {
                    throw new \UnexpectedValueException($_language->module['unknown_value']);
                }

                $gameaccount = new \myrisk\gameaccount();
                $gameaccount->setGame($game_id);
                $gameaccount->setValue($value);

                $description = (isset($_POST['bannedInfo'])) ?
                    getinput($_POST['bannedInfo']) : '';

                $insertQuery = mysqli_query(
                    $_database,
                    "INSERT INTO `" . PREFIX . "cups_gameaccounts_banned`
                        (
                            `game`,
                            `game_id`,
                            `value`,
                            `description`,
                            `date`
                        )
                        VALUES
                        (
                            '" . $game_tag . "',
                            " . $game_id . ",
                            '" . $gameaccount->getValue() . "',
                            '" . $description . "',
                            " . time() . "
                        )"
                );

                if (!$insertQuery) {
                    throw new \UnexpectedValueException($_language->module['query_failed']);
                }

                $parent_url .= '&action=bannedaccounts';

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $data_array = array();
        $data_array['$isOverview'] = (empty($getAction)) ?
            'btn-info white darkshadow' : 'btn-default';
        $data_array['$isMultiaccount'] = ($getAction == 'multiaccounts') ?
            'btn-info white darkshadow' : 'btn-default';
        $data_array['$isBannedaccount'] = ($getAction == 'bannedaccounts') ?
            'btn-info white darkshadow' : 'btn-default';
        $data_array['$games'] = getGamesAsOptionList('csg', false);
        $data_array['$image_url'] = $image_url;
        $searchPanel = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_search", $data_array);
        echo $searchPanel;

        if ($getAction == 'active') {

            $gameaccount_id = (isset($_GET['id']) && validate_int($_GET['id'])) ?
                (int)$_GET['id'] : 0;

            if($gameaccount_id < 1) {
                throw new \UnexpectedValueException($_language->module['unknown_gameaccount']);
            }

            $infoQuery = mysqli_query(
                $_database,
                "SELECT * FROM `".PREFIX."cups_gameaccounts` c
                    JOIN `".PREFIX."user` u ON u.`userID` = c.`userID`
                    WHERE c.`gameaccID` = " . $gameaccount_id . " AND c.`deleted` = 0"
            );

            if(mysqli_num_rows($infoQuery) != 1) {
                throw new \UnexpectedValueException($_language->module['unknown_gameaccount']);
            }

            $ds = mysqli_fetch_array($infoQuery);

            $gameaccountList = '';

            $old = mysqli_query(
                $_database,
                "SELECT * FROM `".PREFIX."cups_gameaccounts`
                    WHERE category = '" . $ds['category'] . "' AND userID = " . $ds['userID'] . " AND deleted = 1
                    ORDER BY date DESC"
            );
            if(mysqli_num_rows($old)) {

                $steam_url = 'https://steamcommunity.com/profiles/';

                while($dc = mysqli_fetch_array($old)) {

                    $old_value = ($ds['category'] == 'csg') ?
                        '<a href="' . $steam_url . $dc['value'] . '" class="blue" target="_blank">' . $dc['value'] . '</a>' : $dc['value'];

                    $gameaccountList .= '<tr>';
                    $gameaccountList .= '<td>' . $old_value . '</td>';
                    $gameaccountList .= '<td>hinzuge&uuml;gt am '.getformatdatetime($dc['date']).'</td>';
                    $gameaccountList .= '</tr>';

                }

            } else {
                $gameaccountList = '<tr><td>' . $_language->module['no_actions'] . '</td></tr>';
            }

            $data_array = array();
            $data_array['$nickname'] 		= $ds['nickname'];
            $data_array['$date'] 			= getformatdatetime($ds['date']);
            $data_array['$game'] 			= getgamename($ds['category']);
            $data_array['$value'] 			= $ds['value'];
            $data_array['$user_id'] 		= $ds['userID'];
            $data_array['$gameacc_id'] 		= $gameaccount_id;
            $data_array['$gameaccountList'] = $gameaccountList;
            $gameaccounts_activate = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_activate", $data_array);
            echo $gameaccounts_activate;

        } else if ($getAction == 'log') {

            if (isset($_GET['deleteProfile'])) {

                $user_id = (isset($_GET['user_id']) && validate_int($_GET['user_id'], true)) ?
                    (int)$_GET['user_id'] : 0;

                if($user_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_user']);
                }

                $parent_url = 'admincenter.php?site=cup&mod=gameaccounts&action=log&profiles&user_id=' . $user_id;

                $profile_id = (isset($_GET['deleteProfile']) && validate_int($_GET['deleteProfile'], true)) ?
                    (int)$_GET['deleteProfile'] : 0;

                if ($profile_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_user']);
                }

                $get = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SEELCT category FROM `".PREFIX."cups_gameaccounts_profiles`
                            WHERE profileID = " . $profile_id
                    )
                );

                $query = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_gameaccounts_profiles`
                        SET deleted = 1
                        WHERE profileID = " . $profile_id
                );

                if (!$query) {
                    throw new \UnexpectedValueException($_language->module['query_failed']);
                }

                $_SESSION['successArray'][] = $_language->module['query_saved'];

                header('Location: ' . $parent_url);

            } else {

                include(__DIR__ . '/includes/gameaccount_log.php');

            }

        } else if (file_exists(__DIR__ . '/includes/gameaccount_' . $getAction . '.php')) {

            include(__DIR__ . '/includes/gameaccount_' . $getAction . '.php');

        } else {

            $getPage = (isset($_GET['page']) && validate_int($_GET['page'], true)) ?
                (int)$_GET['page'] : 1;

            $getPages=1;

            $cat = isset($_POST['cat']) ?
                ' AND a.`category` = "' . getinput($_POST['cat']) . '"' : '';

            $cat = isset($_GET['cat']) ?
                ' AND a.`category` = "' . getinput($_GET['cat']) . '"' : $cat;

            $getShow = isset($_GET['show']) ?
                getinput($_GET['show']) : '';

            $getCat = isset($_GET['cat']) ?
                getinput($_GET['cat']) : '';

            $join_query = '';
            if($getShow == 'vac') {
                $join_query = 'JOIN `'.PREFIX.'cups_gameaccounts_csgo` b ON a.gameaccID = b.gameaccID';
                $cat .= ' AND b.vac_bann = 1';
            }

            $gesamt = mysqli_num_rows(
                mysqli_query(
                    $_database,
                    "SELECT
                            a.`gameaccID` AS gameaccID
                        FROM `".PREFIX."cups_gameaccounts` a
                        ".$join_query."
                        WHERE (a.`deleted` = 0 OR a.`smurf` = 1)" . $cat
                )
            );

            $max = 30;
            $getPages = ceil($gesamt/$max);

            $base_url = 'admincenter.php?site=cup&amp;mod=gameaccounts';

            if($getPages>1) {

                $pagelink = '';

                if(!empty($getCat)) {
                    $pagelink = '&amp;cat='.$getCat;
                }

                if(!empty($getShow)) {
                    $pagelink = '&amp;show='.$getShow;
                }

                $getPage_link = makepagelink($base_url . $pagelink, $getPage, $getPages);

            } else {
                $getPage_link = '';
            }

            $start = ($getPage == "1") ? 0 : (int)($getPage * $max - $max);

            $ergebnis = mysqli_query(
                $_database,
                "SELECT * FROM `".PREFIX."cups_gameaccounts` a
                    " . $join_query . "
                    WHERE (a.`deleted` = 0 OR a.`smurf` = 1)".$cat."
                    ORDER BY a.`active` ASC, a.`date` DESC
                    LIMIT " . $start . ", " . $max
            );

            $games = '<option value="'.$base_url.'" selected="selected">Games</option>';
            $inf = mysqli_query(
                $_database,
                "SELECT name, tag FROM ".PREFIX."games
                    WHERE active = 1
                    ORDER BY name ASC"
            );
            while($dx = mysqli_fetch_array($inf)) {
                $games .= '<option value="'.$base_url.'&amp;cat='.$dx['tag'].'">'.$dx['name'].'</option>';
            }

            if(!empty($getCat)) {
                $games = str_replace(
                    $getCat.'">',
                    $getCat.'" selected="selected">',
                    $games
                );
            }

            if($gesamt) {

                $gameaccounts = '';
                while($ds = mysqli_fetch_array($ergebnis)) {

                    $admin = '';

                    $changed = '';
                    if($ds['category'] == 'csg') {

                        $get = mysqli_fetch_array(
                            mysqli_query(
                                $_database,
                                "SELECT
                                    validated,
                                    date
                                FROM `".PREFIX."cups_gameaccounts_csgo`
                                WHERE gameaccID = " . $ds['gameaccID']
                            )
                        );

                        $changed = (!empty($get['date']) && ($get['date'] > 0)) ?
                            getformatdatetime($get['date']) : '';

                        $admin .= ($get['validated']) ?
                            $_language->module['validate_done'] : $_language->module['validate_missing'];

                    }

                    if(($ds['active'] == 0)) {

                        if($ds['smurf'] == 0) {
                            $url = $base_url . '&amp;action=active&amp;id=' . $ds['gameaccID'];
                            $admin .= ' <a class="btn btn-info btn-xs white darkshadow" href="'.$url.'">'.$_language->module['inactive'].'</a>';
                        } else {
                            $admin .= ' <span class="btn btn-warning btn-xs white darkshadow">Smurf</span>';
                        }

                    } else {
                        $admin .= ' <span class="btn btn-default btn-xs">'.$_language->module['active'].'</span>';
                    }

                    $admin .= ' <a href="'.$base_url.'&amp;action=log&amp;user_id='.$ds['userID'].'" class="btn btn-default btn-xs">User-Log</a>';

                    $data_array = array();
                    $data_array['$nickname'] 	= getnickname($ds['userID']);
                    $data_array['$game'] 		= getgamename($ds['category']);
                    $data_array['$date'] 		= getformatdatetime($ds['date']);
                    $data_array['$value'] 		= $ds['value'];
                    $data_array['$changed'] 	= $changed;
                    $data_array['$admin'] 		= $admin;
                    $gameaccounts .= $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_list", $data_array);

                }

            } else {
                $gameaccounts = '<tr><td colspan="6">'.$_language->module['no_gameaccount'].'</td></tr>';
            }

            $selectedAll = ' selected="selected"';
            $selectedVAC = '';
            if($getShow == 'vac') {
                $selectedAll = '';
                $selectedVAC = ' selected="selected"';
            }

            $data_array = array();
            $data_array['$games'] 			= $games;
            $data_array['$selectedAll'] 	= $selectedAll;
            $data_array['$selectedVAC'] 	= $selectedVAC;
            $data_array['$gameaccounts'] 	= $gameaccounts;
            $data_array['$page_link'] 		= $getPage_link;
            $gameaccount_home_admin = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_admin_home", $data_array);
            echo $gameaccount_home_admin;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
