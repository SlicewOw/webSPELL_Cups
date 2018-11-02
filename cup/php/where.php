<?php

$_language->readModule('cups');

$mod_original = !empty($getSite) ? $getSite : 'home';

$value = $_language->module['where_info'];

$unique_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
    (int)$_GET['id'] : 0;

if ($mod_original === 'lostpassword') {

    $value .= '<a href="index.php?site='.$mod_original.'#content">'.$_language->module['where_lostpassword'].'</a>';

} else if ($mod_original === 'cup_admin') {

    $value .= '<a href="index.php?site='.$mod_original.'#content">Cup Admin</a>';

    $mod = isset($_GET['mod']) ? $_GET['mod'] : '';
    $admin = isset($_GET['admin']) ? $_GET['admin'] : '';
    if (($unique_id > 0) && ($mod == 'cup')) {

        $base_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=' . $unique_id;

        $value .= ' / <a href="' . $base_url . '#content">' . getcup($unique_id, 'name') . '</a>';
        $value .= ' / <a href="' . $base_url . '&amp;page='.$getPage.'#content">' . ucfirst($getPage) . '</a>';

    } else if ($mod == 'penalty') {

        $base_url = 'admincenter.php?site=cup&amp;mod=penalty';

        $value .= ' / <a href="'.$base_url.'#content">'.$_language->module['penalty'].'</a>';

        if (!empty($getAction)) {
            $value .= ' / <a href="'.$base_url.'&amp;action='.$getAction.'#content">'.ucfirst($getAction).'</a>';
        }

        if (!empty($admin)) {
            $value .= ' / <a href="'.$base_url.'&amp;admin='.$admin.'#content">'.ucfirst($admin).'</a>';
        }

    }

} else if ($mod_original === 'teams') {

    $value .= '<a href="index.php?site='.$mod_original.'#content">Teams</a>';
    if ($getAction == 'add') {
        $value .= ' / '.$_language->module['where_team_new'];
    } else if ($getAction == 'join') {
        $value .= ' / '.$_language->module['where_team_join'];
    } else if ($unique_id > 0) {

        $teamArray = getteam($unique_id);

        $value .= ' / <a href="index.php?site=teams&amp;action=show">'.$_language->module['where_overview'].'</a>';

        if ($getAction == 'details') {
            $value .= ' / '.$teamArray['name'];
        } else if ($getAction == 'log') {
            $value .= ' / <a href="index.php?site=teams&amp;action=details&amp;id='.$unique_id.'#content">'.$teamArray['name'].'</a> / Log';
        }

    } else {
        $value .= ' / '.$_language->module['where_overview'];
    }

} else if ($mod_original === 'cup') {

    $base_url = 'index.php?site=cup&amp;action=';

    $value .= '<a href="index.php?site='.$mod_original.'#content">Cups</a>';
    $getAction = isset($getAction) ? $getAction : '';
    if (($getAction == 'joincup' || $getAction == 'details') && ($unique_id > 0)) {

        $getCupPage = (isset($_GET['page'])) ?
            getinput($_GET['page']) : 'home';

        $value .= ' / <a href="' . $base_url . 'details&amp;id=' . $unique_id . '#content">' . getcup($unique_id, 'name') . '</a>';
        $value .= ' / <a href="' . $base_url . 'details&amp;id=' . $unique_id . '&amp;page=' . $getCupPage . '#content">' . ucfirst($getCupPage) . '</a>';

    } else if ($getAction == 'match') {

        if ($unique_id > 0) {

            $value .= ' / <a href="' . $base_url . 'details&amp;id=' . $unique_id . '#content">' . getcup($unique_id, 'name') . '</a>';

            if (isset($_GET['mID']) && is_numeric($_GET['mID'])) {

                $match_id = (int)$_GET['mID'];

                $value .= ' / <a href="' . $base_url . 'details&amp;id=' . $cup_id . '&amp;page=bracket#content">Bracket</a>';
                $value .= ' / <a href="' . $base_url . 'match&amp;id=' . $cup_id . '&amp;mID=' . $match_id . '#content">Match</a>';

            }

        }

    }

} else if ($mod_original === 'support') {

    $base_url = 'index.php?site=support&amp;action=';

    $value .= '<a href="index.php?site='.$mod_original.'#content">Support</a>';
    $getAction = isset($getAction) ? $getAction : '';
    if ($getAction == 'admin') {

        $value .= ' / <a href="'.$base_url.'admin#content">Admin</a>';

        if (isset($_GET['status']) && is_numeric($_GET['status'])) {
            $status_id = (int)$_GET['status'];
        } else {
            $status_id = 1;
        }

        $value .= ' / <a href="'.$base_url.'admin&amp;status='.$status_id.'#content">'.$_language->module['ticket_status_'.$status_id].'</a>';

    } else if ($getAction == 'details' && ($unique_id > 0)) {

        if ($loggedin && iscupadmin($userID)) {
            $value .= ' / <a href="' . $base_url . 'admin#content">Admin</a>';
        }
        $value .= ' / <a href="' . $base_url . 'details&amp;id=' . $unique_id . '#content">Ticket #' . $unique_id . '</a>';
    }

} else if ($mod_original === 'news_comments') {

    $value .= '<a href="index.php?site=news#content">News</a>';

    if ($unique_id > 0) {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                      b.`rubric` AS `rubric_name`
                    FROM `" . PREFIX . "news` a
                    JOIN `" . PREFIX . "news_rubrics` b ON a.`rubric` = b.`rubricID`
                    WHERE `newsID` = " . $unique_id
            )
        );

        $value .= ' / ' . $get['rubric_name'];
        $value .= ' / News #' . $unique_id;

    }

} else {

    $text = str_replace(
        '_',
        ' ',
        $mod_original
    );

    $value .= '<a href="index.php?site=' . $mod_original . '#content">' . ucfirst($text) . '</a>';

}

echo $value;
