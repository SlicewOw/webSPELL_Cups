<?php

if (iscupadmin($userID) && $loggedin && ($getSite == 'cup_admin') && ($getPage == 'bracket')) {
    include($dir_cup . 'admin/cup_matches_admin.php');
}

if (($getSite == 'cup') && ($getAction == 'details') && isset($_GET['id'])) {

    $cup_id = (validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id > 0) {
        $cupArray = getcup($cup_id);
    }

}
