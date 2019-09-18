<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    $points = '';
    for ($i = 1; $i < 13; $i++) {
        $points .= '<option value="' . $i . '">' . $i . '</option>';
    }

    $admin = isset($_GET['admin']) ?
        getinput($_GET['admin']) : '';

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=cup&mod=penalty';

        try {

            if (isset($_POST['submitAddPenalty']) || isset($_POST['submitEditPenalty'])) {

                $user_id = (isset($_POST['user_id']) && validate_int($_POST['user_id'], true)) ?
                    (int)$_POST['user_id'] : 0;

                $team_id = (isset($_POST[getConstNameTeamIdWithUnderscore()]) && validate_int($_POST[getConstNameTeamIdWithUnderscore()], true)) ?
                    (int)$_POST[getConstNameTeamIdWithUnderscore()] : 0;

                if (($user_id < 1) && ($team_id < 1)) {
                    throw new \UnexpectedValueException($_language->module['error_penalty_no_parent_id']);
                }

                $reason_id = (isset($_POST['reason_id']) && validate_int($_POST['reason_id'], true)) ?
                    (int)$_POST['reason_id'] : 0;

                if ($reason_id < 1) {
                    throw new \UnexpectedValueException($_language->module['error_penalty_no_reason']);
                }

                $comment = (isset($_POST['comment'])) ?
                    getinput($_POST['comment']) : '';

                if (isset($_POST['submitAddPenalty'])) {

                    $isLifeTimeBann = getPenaltyCategory($reason_id, 'lifetime');

                    if ($isLifeTimeBann) {

                        $duration_time = time() + (3600 * 24 * 7 * 52 * 40);

                    } else {

                        $penalty_points = getPenaltyCategory($reason_id, 'points');

                        if (!validate_int($penalty_points, true)) {
                            throw new \UnexpectedValueException($_language->module['error_penalty_no_points']);
                        }

                        // 1 point = 1 week
                        $duration_time = time() + (3600 * 24 * 7 * $penalty_points);

                    }

                    $query = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "cups_penalty`
                            (
                                `adminID`,
                                `date`,
                                `duration_time`,
                                `teamID`,
                                `userID`,
                                `reasonID`,
                                `comment`
                            )
                            VALUES
                            (
                                " . $userID . ",
                                " . time() . ",
                                " . $duration_time . ",
                                " . $team_id . ",
                                " . $user_id . ",
                                " . $reason_id . ",
                                '" . $comment . "'
                            )"
                    );

                    if (!$query) {
                        throw new \UnexpectedValueException($_language->module['query_insert_failed']);
                    }

                    $ppID = mysqli_insert_id($_database);

                } else {

                    $penalty_id = (isset($_POST['penalty_id']) && is_numeric($_POST['penalty_id'])) ?
                        (int)$_POST['penalty_id'] : 0;

                    if ($penalty_id < 1) {
                        throw new \UnexpectedValueException($_language->module['unknown_penalty_id']);
                    }

                    $query = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "cups_penalty`
                            SET `teamID` = " . $team_id . ",
                                `userID` = " . $user_id . ",
                                `reasonID` = " . $reason_id . ",
                                `comment` = '" . $comment . "'
                            WHERE `ppID` = " . $penalty_id
                    );

                    if (!$query) {
                        throw new \UnexpectedValueException($_language->module['query_update_failed']);
                    }

                }

            } else if (isset($_POST['submitAddCategory']) || isset($_POST['submitEditCategory'])) {

                $parent_url .= '&action=category';

                $name_de = getinput($_POST['name_de']);
                $name_uk = getinput($_POST['name_uk']);

                $point = $_POST['points'];

                $lifetime = (isset($_POST['lifetime'])) ? 1 : 0;

                if (isset($_POST['submitAddCategory'])) {

                    $query = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "cups_penalty_category`
                            (
                                `name_de`,
                                `name_uk`,
                                `points`,
                                `lifetime`
                            )
                            VALUES
                            (
                                '" . $name_de . "',
                                '" . $name_uk . "',
                                " . $point . ",
                                " . $lifetime . "
                            )"
                    );

                    if (!$query) {
                        throw new \UnexpectedValueException($_language->module['query_insert_failed']);
                    }

                } else {

                    $reason_id = (isset($_POST['reason_id']) && is_numeric($_POST['reason_id'])) ?
                        (int)$_POST['reason_id'] : 0;

                    if ($reason_id < 1) {
                        throw new \UnexpectedValueException($_language->module['unknown_reason_id']);
                    }

                    $query = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "cups_penalty_category`
                            SET	`name_de` = '" . $name_de . "',
                                `name_uk` = '" . $name_uk . "',
                                `points` = " . $point . ",
                                `lifetime` = " . $lifetime . "
                            WHERE `reasonID` = " . $reason_id
                    );

                    if (!$query) {
                        throw new \UnexpectedValueException($_language->module['query_update_failed']);
                    }

                }

            } else if (isset($_POST['submitDeleteCategory'])) {

                $reason_id = (isset($_POST['reason_id']) && validate_int($_POST['reason_id'], true)) ?
                    (int)$_POST['reason_id'] : 0;

                if ($reason_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_reason_id']);
                }

                $deleteQuery = mysqli_query(
                    $_database,
                    "DELETE FROM `" . PREFIX . "cups_penalty_category`
                        WHERE `reasonID` = " . $reason_id
                );

                if (!$deleteQuery) {
                    throw new \UnexpectedValueException($_language->module['query_delete_failed']);
                }

                $deleteQuery = mysqli_query(
                    $_database,
                    "DELETE FROM `" . PREFIX . "cups_penalty`
                        WHERE `reasonID` = " . $reason_id
                );

                if (!$deleteQuery) {
                    throw new \UnexpectedValueException($_language->module['query_delete_failed']);
                }

            } else if (isset($_POST['submitDeletePenalty'])) {

                $penalty_id = (isset($_POST['penalty_details_id']) && validate_int($_POST['penalty_details_id'], true)) ?
                    (int)$_POST['penalty_details_id'] : 0;

                if ($penalty_id < 1) {
                    throw new \UnexpectedValueException($_language->module['unknown_penalty_id']);
                }

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_penalty`
                        SET `deleted` = 1
                        WHERE `ppID` = " . $penalty_id
                );

                if (!$updateQuery) {
                    throw new \UnexpectedValueException($_language->module['query_update_failed']);
                }

                $_SESSION['errorArray'][] = $_language->module['penalty_deleted'];

            } else {
                throw new \UnexpectedValueException($_language->module['unknown_action']);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        if ($admin == 'delete' && !isset($_GET['action'])) {

            $ppID = (isset($_GET['id']) && validate_int($_GET['id'])) ?
                (int)$_GET['id'] : 0;

            if ($ppID < 1) {
                throw new \UnexpectedValueException($_language->module['unknown_penalty_id']);
            }

            $updateQuery = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "cups_penalty`
                    SET `deleted` = 1
                    WHERE `ppID` = " . $ppID
            );

            if (!$query) {
                throw new \UnexpectedValueException($_language->module['query_delete_failed']);
            }

            header('Location: admincenter.php?site=cup&mod=penalty');

        } else {

            $overviewMenu = (empty($getAction)) ?
                'btn-info white darkshadow' : 'btn-default';

            $categoryMenu = ($getAction == 'category') ?
                'btn-info white darkshadow' : 'btn-default';

            $data_array = array();
            $data_array['$overviewMenu'] = $overviewMenu;
            $data_array['$categoryMenu'] = $categoryMenu;
            $data_array['$points'] = $points;
            $data_array['$users'] = getuserlist();
            $data_array['$teams'] = getteams();
            $data_array['$reasons'] = getPenaltyCategories();
            $penalty_menu = $GLOBALS["_template_cup"]->replaceTemplate("penalty_menu", $data_array);
            echo $penalty_menu;

            if (!empty($getAction) && !isset($_GET['admin'])) {

                $category_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
                    (int)$_GET['id'] : 0;

                if ($getAction == 'category') {

                    $points = '';
                    for ($i = 1; $i < 13; $i++) {
                        $points .= '<option value="' . $i . '">' . $i . '</option>';
                    }

                    $get_pp = mysqli_query(
                        $_database,
                        "SELECT
                              `reasonID`,
                              `name_de`,
                              `name_uk`,
                              `points`,
                              `lifetime`
                            FROM `" . PREFIX . "cups_penalty_category`
                            ORDER BY `points` ASC, `name_de` ASC"
                    );

                    if (!$get_pp) {
                        throw new \UnexpectedValueException($_language->module['query_delete_failed']);
                    }

                    if (mysqli_num_rows($get_pp)) {

                        $penalty_category_list = '';
                        while ($ds = mysqli_fetch_array($get_pp)) {

                            $reason_id = $ds['reasonID'];

                            $lifetime = ($ds['lifetime'] == 1) ? $_language->module['yes'] : $_language->module['no'];

                            $link = 'admincenter.php?site=cup&amp;mod=penalty&amp;admin=view&amp;id=' . $reason_id;

                            $data_array = array();
                            $data_array['$reason_id'] = $reason_id;
                            $data_array['$name_de'] = $ds['name_de'];
                            $data_array['$name_uk'] = $ds['name_uk'];
                            $data_array['$points'] = $ds['points'];
                            $data_array['$lifetime'] = $lifetime;
                            $data_array['$link'] = $link;
                            $penalty_category_list .= $GLOBALS["_template_cup"]->replaceTemplate("penalty_cat_list", $data_array);

                        }

                    } else {
                        $penalty_category_list = '<tr><td colspan="4">' . $_language->module['no_penalty_category'] . '</td></tr>';
                    }

                    $data_array = array();
                    $data_array['$penalty_list'] = $penalty_category_list;
                    $penalty_home = $GLOBALS["_template_cup"]->replaceTemplate("penalty_cat_home", $data_array);
                    echo $penalty_home;

                } else if ($getAction == 'category_edit') {

                    if ($category_id < 1) {
                        throw new \UnexpectedValueException($_language->module['unknown_id']);
                    }

                    $selectQuery = mysqli_query(
                        $_database,
                        "SELECT
                                `name_de`,
                                `name_uk`,
                                `points`,
                                `lifetime`
                            FROM `" . PREFIX . "cups_penalty_category`
                            WHERE `reasonID` = " . $category_id
                    );

                    if (!$selectQuery) {
                        throw new \UnexpectedValueException($_language->module['query_select_failed']);
                    }

                    $get = mysqli_fetch_array($selectQuery);

                    $points = str_replace(
                        'value="' . $get['points'] . '"',
                        'value="' . $get['points'] . '" selected="selected"',
                        $points
                    );

                    $data_array = array();
                    $data_array['$reason_id'] = $category_id;
                    $data_array['$name_de'] = $get['name_de'];
                    $data_array['$name_uk'] = $get['name_uk'];
                    $data_array['$points'] = $points;
                    $data_array['$checked'] = ($get['lifetime'] == 1) ?
                        ' checked="checked"' : '';
                    $category_edit = $GLOBALS["_template_cup"]->replaceTemplate("penalty_category_edit", $data_array);
                    echo $category_edit;

                } else if ($getAction == 'category_delete') {

                    if ($category_id < 1) {
                        throw new \UnexpectedValueException($_language->module['unknown_id']);
                    }

                    $selectQuery = mysqli_query(
                        $_database,
                        "SELECT
                                `name_de`,
                                `name_uk`,
                                `points`,
                                `lifetime`
                            FROM `" . PREFIX . "cups_penalty_category`
                            WHERE `reasonID` = " . $category_id
                    );

                    if (!$selectQuery) {
                        throw new \UnexpectedValueException($_language->module['query_select_failed']);
                    }

                    $get = mysqli_fetch_array($selectQuery);

                    $data_array = array();
                    $data_array['$reason_id'] = $category_id;
                    $data_array['$name_de'] = $get['name_de'];
                    $data_array['$name_uk'] = $get['name_uk'];
                    $data_array['$points'] = $get['points'];
                    $data_array['$checked'] = ($get['lifetime'] == 1) ?
                        ' checked="checked"' : '';
                    $category_delete = $GLOBALS["_template_cup"]->replaceTemplate("penalty_category_delete", $data_array);
                    echo $category_delete;

                } else {
                    throw new \UnexpectedValueException($_language->module['unknown_action']);
                }

            } else {

                $time_now = time();

                $get_pp = mysqli_query(
                    $_database,
                    "SELECT
                            a.`ppID`,
                            a.`date`,
                            a.`userID`,
                            a.`teamID`,
                            a.`adminID`,
                            a.`duration_time`,
                            a.`teamID`,
                            a.`reasonID`,
                            b.`username` AS username,
                            c.`username` AS adminname,
                            d.`name_de` AS reason_name,
                            t.`name` AS team_name
                        FROM `" . PREFIX . "cups_penalty` a
                        LEFT JOIN `" . PREFIX . "user` b ON a.userID = b.userID
                        LEFT JOIN `" . PREFIX . "cups_teams` t ON a.teamID = t.teamID
                        JOIN `" . PREFIX . "user` c ON a.adminID = c.userID
                        LEFT JOIN `" . PREFIX . "cups_penalty_category` d ON a.reasonID = d.reasonID
                        WHERE a.duration_time > " . $time_now . " AND a.deleted = 0
                        ORDER BY a.`teamID` ASC, a.`userID` ASC, a.`date` DESC"
                );

                if (!$get_pp) {
                    throw new \UnexpectedValueException($_language->module['query_select_failed']);
                }

                if (mysqli_num_rows($get_pp) > 0) {

                    $user_counter = 0;
                    $team_counter = 0;

                    $user_penalty_list = '';
                    $team_penalty_list = '';

                    while ($ds = mysqli_fetch_array($get_pp)) {

                        $penalty_id = $ds['ppID'];

                        if ($ds[getConstNameTeamId()] > 0) {
                            $profile = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;teamID=' . $ds[getConstNameTeamId()];
                            $name = $ds['team_name'];
                        } else {
                            $profile = 'admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id=' . $ds['userID'];
                            $name = $ds['username'];
                        }

                        $data_array = array();
                        $data_array['$penalty_id'] = $penalty_id;
                        $data_array['$profile'] = $profile;
                        $data_array['$name'] = $name;
                        $data_array['$reason_name'] = $ds['reason_name'];
                        $data_array['$admin_profile'] = $hp_url . '/index.php?site=profile&amp;id=' . $ds['adminID'];
                        $data_array['$admin'] = $ds['adminname'];
                        $data_array['$duration'] = getformatdatetime($ds['duration_time']);

                        if ($ds[getConstNameTeamId()] > 0) {
                            $team_penalty_list .= $GLOBALS["_template_cup"]->replaceTemplate("penalty_list", $data_array);
                            $team_counter++;
                        } else {
                            $user_penalty_list .= $GLOBALS["_template_cup"]->replaceTemplate("penalty_list", $data_array);
                            $user_counter++;
                        }

                    }

                    $data_array = array();
                    $data_array['$penalty_list'] = $user_penalty_list;
                    $userPenalties = $GLOBALS["_template_cup"]->replaceTemplate("penalty_panel", $data_array);

                    $data_array = array();
                    $data_array['$penalty_list'] = $team_penalty_list;
                    $teamPenalties = $GLOBALS["_template_cup"]->replaceTemplate("penalty_panel", $data_array);

                    $data_array = array();
                    $data_array['$user_counter'] = $user_counter;
                    $data_array['$team_counter'] = $team_counter;
                    $data_array['$userPenalties'] = $userPenalties;
                    $data_array['$teamPenalties'] = $teamPenalties;
                    $penalty_home = $GLOBALS["_template_cup"]->replaceTemplate("penalty_home", $data_array);
                    echo $penalty_home;

                } else {
                    echo showInfo($_language->module['no_penalty'], true);
                }

            }

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
