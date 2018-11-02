<?php

if ($loggedin) {

    $_language->readModule('login');

    $contentLogin = '';

    $cupAdminAccess = (iscupadmin($userID)) ? TRUE : FALSE;

    $contentLogin .= isinteam($userID, 0, '') ?
        '<a href="index.php?site=teams&amp;action=show">Team Control Panel</a> - ' : '';

    $contentLogin .= '<a href="index.php?site=teams&amp;action=add">' . $_language->module['register_team'] . '</a>';

    $support_admin = '';
    if ($cupAdminAccess) {
        $anz = getticket_anz(0, $userID, 'anz_new_answer_admin');
    } else {
        $anz = getticket_anz(0, $userID, 'anz_new_answer');
    }

    if ($anz > 0) {
        $newTicketCounter = ' <span class="bold">('.$anz.')</span>';
    } else {
        $newTicketCounter = '';
    }

    $contentLogin .= ' - <a href="index.php?site=support">Support' . $newTicketCounter . '</a>';

    echo $contentLogin;

} else {

    include(__DIR__ .'/../../sc_language.php');

}
