<?php

try {

    $_language->readModule('cups');

    $query = mysqli_query(
        $_database,
        "SELECT DISTINCT
              ct.`userID` AS `user_id`,
              ct.`description` AS `description`,
              ups.`name` AS `position`,
              u.`firstname` AS `prename`,
              u.`lastname` AS `lastname`,
              u.`nickname` AS `nickname`
            FROM `" . PREFIX . "cups_team` ct
            LEFT JOIN `" . PREFIX . "user_position_static` ups ON ct.`position` = ups.`tag`
            LEFT JOIN `" . PREFIX . "user` u ON ct.`userID` = u.`userID`
            ORDER BY ct.`position` ASC"
    );

    if (!$query) {
        throw new \UnexpectedValueException($_language->module['query_select_failed']);
    }

    $adminList = '';
    $activePosition = '';

    if (mysqli_num_rows($query) < 1) {
        throw new \UnexpectedValueException($_language->module['no_admin']);
    }

    while ($get = mysqli_fetch_array($query)) {

        if (empty($adminList)) {
            $activePosition = $get['position'];
        }

        if ($activePosition != $get['position']) {

            $data_array = array();
            $data_array['$title'] = $activePosition;
            $data_array['$adminList'] = $adminList;
            $team_home = $GLOBALS["_template_cup"]->replaceTemplate("admins_home", $data_array);
            echo $team_home;

            $adminList = '';
            $activePosition = $get['position'];

        }

        $user_id = $get['user_id'];
        $nickname = $get['nickname'];

        $data_array = array();
        $data_array['$userpic'] = '<img src="' . getuserpic($user_id, true) . '" alt="' . $nickname . '" title="' . $nickname . '" />';
        $data_array['$user_id'] = $user_id;
        $data_array['$prename'] = $get['prename'];
        $data_array['$nickname'] = $nickname;
        $data_array['$lastname'] = $get['lastname'];
        $data_array['$position'] = $get['position'];
        $data_array['$description'] = getoutput($get['description']);
        $adminList .= $GLOBALS["_template_cup"]->replaceTemplate("admins_list", $data_array);

    }

    if (!empty($adminList)) {

        $data_array = array();
        $data_array['$title'] = $activePosition;
        $data_array['$adminList'] = $adminList;
        $team_home = $GLOBALS["_template_cup"]->replaceTemplate("admins_home", $data_array);
        echo $team_home;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
