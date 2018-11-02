<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    $points = '';
    for ($i = 1; $i < 13; $i++) {
        $points .= '<option value="'.$i.'">'.$i.'</option>';
    }

    $admin = isset($_GET['admin']) ?
        getinput($_GET['admin']) : '';
	
	if (validate_array($_POST, true)) {
		
		$parent_url = 'admincenter.php?site=cup&mod=penalty';

        try {
         
            if (isset($_POST['submitAddPenalty']) || isset($_POST['submitEditPenalty'])) {

                $user_id = (isset($_POST['user_id']) && validate_int($_POST['user_id'], true)) ?
                    (int)$_POST['user_id'] : 0;

                $team_id = (isset($_POST['team_id']) && validate_int($_POST['team_id'], true)) ?
                    (int)$_POST['team_id'] : 0;

                if (($user_id < 1) && ($team_id < 1)) {
                    throw new \Exception($_language->module['error_penalty_no_parent_id']);
                }
                
                $reason_id = (isset($_POST['reason_id']) && validate_int($_POST['reason_id'], true)) ?
                    (int)$_POST['reason_id'] : 0;

                if ($reason_id < 1) {
                    throw new \Exception($_language->module['error_penalty_no_reason']);
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
                            throw new \Exception($_language->module['error_penalty_no_points']);
                        }

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
                        throw new \Exception($_language->module['query_insert_failed']);
                    }

                    $ppID = mysqli_insert_id($_database);

                } else {

                    $penalty_id = (isset($_POST['penalty_id']) && is_numeric($_POST['penalty_id'])) ?
                        (int)$_POST['penalty_id'] : 0;

                    if ($penalty_id < 1) {
                        throw new \Exception($_language->module['unknown_penalty_id']);
                    }

                    $query = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "cups_penalty`
                            SET `teamID` = " . $team_id . ",
                                `userID` = " . $user_id . ", 
                                `reasonID` = " . $reason_id . ", 
                                `comment` = '" . $comment . "'
                            WHERE ppID = " . $penalty_id
                    );

                    if (!$query) {
                        throw new \Exception($_language->module['query_update_failed']);
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
                        throw new \Exception($_language->module['query_insert_failed']);
                    }

                } else {

                    $reason_id = (isset($_POST['reason_id']) && is_numeric($_POST['reason_id'])) ?
                        (int)$_POST['reason_id'] : 0;

                    if ($reason_id < 1) {
                        throw new \Exception($_language->module['unknown_reason_id']);
                    }

                    $query = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "cups_penalty_category` 
                            SET	name_de = '".$name_de."', 
                                name_uk = '".$name_uk."', 
                                points = " . $point . ", 
                                lifetime = " . $lifetime . " 
                            WHERE reasonID = " . $reason_id
                    );

                    if (!$query) {
                        throw new \Exception($_language->module['query_update_failed']);
                    }

                }

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
                throw new \Exception($_language->module['unknown_penalty_id']);
            }
            
            $updateQuery = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "cups_penalty` 
                    SET deleted = 1 
                    WHERE ppID = " . $ppID
            );

            if (!$query) {
                throw new \Exception($_language->module['query_delete_failed']);
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
                              `points`, 
                              `lifetime` 
                            FROM `" . PREFIX . "cups_penalty_category`
                            ORDER BY `points` ASC, `name_de` ASC"
                    );

                    if (!$get_pp) {
                        throw new \Exception($_language->module['query_delete_failed']);
                    }

                    if (mysqli_num_rows($get_pp)) {

                        $penalty_category_list = '';
                        while ($ds = mysqli_fetch_array($get_pp)) {

                            $reason_id = $ds['reasonID'];

                            $lifetime = ($ds['lifetime'] == 1) ? $_language->module['yes'] : $_language->module['no'];

                            $link = 'admincenter.php?site=cup&amp;mod=penalty&amp;admin=view&amp;id=' . $reason_id;

                            $data_array = array();
                            $data_array['$ppID'] = $reason_id;
                            $data_array['$name'] = $ds['name_de'];
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
                    throw new \Exception($_language->module['query_select_failed']);
                }

                if (mysqli_num_rows($get_pp) > 0) {

                    $user_counter = 0;
                    $team_counter = 0;
                    
                    $user_penalty_list = '';
                    $team_penalty_list = '';
                    
                    while ($ds = mysqli_fetch_array($get_pp)) {

                        $ppID = $ds['ppID'];

                        if ($ds['teamID'] > 0) {
                            $profile = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;teamID=' . $ds['teamID'];
                            $name = $ds['team_name'];
                        } else {
                            $profile = 'admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id=' . $ds['userID'];
                            $name = $ds['username'];
                        }

                        $data_array = array();
                        $data_array['$ppID'] = $ppID;
                        $data_array['$profile'] = $profile;
                        $data_array['$name'] = $name;
                        $data_array['$reason_name'] = $ds['reason_name'];
                        $data_array['$admin_profile'] = $hp_url . '/index.php?site=profile&amp;id=' . $ds['adminID'];
                        $data_array['$admin'] = $ds['adminname'];
                        $data_array['$duration'] = getformatdatetime($ds['duration_time']);
                        
                        if ($ds['teamID'] > 0) {
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
