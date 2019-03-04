<?php

try {

    $_language->readModule('login', true);

    if ($loggedin) {

        $admin = '';
        if (isanyadmin($userID)) {
            $admin = '<li><a href="' . $admin_url . '" target="_blank">' . $_language->module['admin'] . '</a></li>';
        }

        $anz = getnewmessages($userID);
        $newmessages = ($anz == '1') ?
            '1 '.$_language->module['nachricht'] : $anz.' '.$_language->module['nachrichten'];

        $badge = ($anz > 0) ? '<span class="badge">' . $anz . '</span> ' : '';

        $loginList = '';

        $gameacc_badge = '';
        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `value`
                    FROM `" . PREFIX . "cups_gameaccounts`
                    WHERE userID = " . $userID . " AND category = 'csg' AND deleted = 0"
            )
        );
        if(!empty($get['value'])) {

            $gameacc_badge = (gameaccount($userID, 'validated', 'csg') == 0) ? 
                ' <span class="badge">1</span>' : '';

        }

        $loginList .= ($getSite == 'gameaccount') ?
            '<li class="active"><a href="index.php?site=gameaccount">Gameaccounts'.$gameacc_badge.'</a></li>' :
            '<li><a href="index.php?site=gameaccount">Gameaccounts'.$gameacc_badge.'</a></li>';

        $support_admin = '';
        if (iscupadmin($userID)) {
            $anz = getticket_anz(0, $userID, 'anz_new_answer_admin');
        } else {
            $anz = getticket_anz(0, $userID, 'anz_new_answer');
        }

        if ($anz > 0) {
            $newTicketCounter = ' <span class="badge">'.$anz.'</span>';
        } else {
            $newTicketCounter = '';
        }

        $loginList .= ($getSite == 'support') ?
            '<li class="active"><a href="index.php?site=support#content">Support' . $newTicketCounter . '</a></li>' :
            '<li><a href="index.php?site=support#content">Support' . $newTicketCounter . '</a></li>';

        $loginList .= $admin;

        $data_array = array();
        $data_array['$userID'] = $userID;
        $data_array['$username'] = strip_tags(getnickname($userID));
        $data_array['$newmessages'] = $newmessages;
        $data_array['$l_avatar'] = getavatar($userID, true);
        $data_array['$loginList'] = $loginList;
        $data_array['$badge'] = $badge;
        $login = $GLOBALS["_template_cup"]->replaceTemplate("sc_logged", $data_array);

        if (!isset($showLogin) || ($showLogin == TRUE)) {
            echo $login;
        }

    } else {

        $loginRegister = ($getSite == 'register') ?
            ' class="active"' : '';

        $isLogin = (($getSite == 'login') || ($getSite == 'lostpassword')) ?
            ' class="active"' : '';

        $_SESSION['ws_sessiontest'] = true;

        $data_array = array();
        $data_array['$loginRegister'] = $loginRegister;
        $data_array['$isLogin'] = $isLogin;
        $login = $GLOBALS["_template_cup"]->replaceTemplate("sc_login", $data_array);

        if (!isset($showLogin) || ($showLogin == TRUE)) {
            echo $login;
        }

    }

} catch (Exception $e) {}
