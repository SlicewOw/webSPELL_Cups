<?php

$_language->readModule('login');

if ($loggedin) {

    $username = '<a href="index.php?site=profile&amp;id=' . $userID . '"><strong>' . strip_tags(getnickname($userID)) . '</strong></a>';

    if ($getavatar = getavatar($userID)) {
        $l_avatar = '<img src="images/avatars/' . $getavatar . '" alt="Avatar">';
    } else {
        $l_avatar = $_language->module[ 'n_a' ];
    }

    $anz = getnewmessages($userID);
    if ($anz) {
        $newmessages = $anz;
    } else {
        $newmessages = '';
    }

    if (isanyadmin($userID)) {
        $admin = '<li class="divider"></li><li><a href="admin/admincenter.php" target="_blank" class="alert-danger">' .
            $_language->module[ 'admin' ] . '</a></li>';
    } else {
        $admin = '';
    }

    $data_array = array();
    $data_array['$username'] = $username;
    $data_array['$l_avatar'] = $l_avatar;
    $data_array['$newmessages'] = $newmessages;
    $data_array['$admin'] = $admin;
    $data_array['$cashbox'] = '';
    $logged = $GLOBALS["_template"]->replaceTemplate("logged", $data_array);
    echo $logged;

} else {

    include(__DIR__ . '/sc_discord.php');

    $_SESSION[ 'ws_sessiontest' ] = true;

    $data_array = array();
    $data_array['$discord_login'] = (isset($sc_discord)) ? $sc_discord : '';
    $loginform = $GLOBALS["_template_cup"]->replaceTemplate("login", $data_array);
    echo $loginform;

}
