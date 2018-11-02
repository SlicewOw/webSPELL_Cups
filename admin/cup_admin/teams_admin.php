<?php

try {

    if (!isset($_GET['id']) || !validate_int($_GET['id'])) {
        throw new \Exception($_language->module['not_loggedin']);
    }

    $unique_id = (int)$_GET['id'];

    if (isinteam($userID, 0, '') && ($userID == $unique_id)) {

        // Teams Ansicht (Status: Mitglied)
        $info = mysqli_query(
            $_database,
            "SELECT * FROM ".PREFIX."cups_teams
                WHERE userID = " . $unique_id . " AND deleted = 0"
        );

        while($ds = mysqli_fetch_array($info)) {

            $name = $ds['name'];
            $date = getformatdatetime($ds['join_date']);
            $link = $cup_url . '/index.php?site=teams&amp;action=join&amp;id='.$ds['teamID'].'&amp;pw='.$ds['password'];
            $admin = '<a href="index.php?site=teams&amp;action=details&amp;id='.$ds['teamID'].'">'.$_language->module['team_view'].'</a>';
            $admin .=  '- <a href="index.php?site=teams&amp;action=leave&amp;id='.$ds['teamID'].'">'.$_language->module['team_leave'].'</a>';

            $data_array = array();
            $data_array['$name'] 	= $name;
            $data_array['$date'] 	= $date;
            $data_array['$link'] 	= $link;
            $data_array['$admin'] 	= $admin;
            $teams_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_panel_list", $data_array);
            echo $teams_list;

        }

    } else if (isinteam($userID, $unique_id, 'admin')) {

        // Teams Ansicht (Status: Admin)
        $team_id = $unique_id;

        if(validate_array($_POST, true)) {

            try {

                if(isset($_POST['submitTeamSocials'])) {

                    if(!isset($_POST['socials']) || !validate_array($_POST['socials'], true)) {
                        throw new \Exception('no_changes');
                    }

                    $socialArray = $_POST['socials'];

                    $socialTypeArray = array_keys($socialArray);

                    $anzSocials = count($socialTypeArray);
                    for($x=0;$x<$anzSocials;$x++) {

                        //
                        // Social Network ID
                        $type_id = $socialTypeArray[$x];

                        if(!isset($socialArray[$type_id])) {
                            throw new \Exception('unknown_social_type');
                        }

                        if(!empty($socialArray[$type_id])) {

                            //
                            // Value des Sozialen Netzwerks
                            $social_value = $socialArray[$type_id];

                            //
                            // Kontrolle, ob Eintrag vorhanden
                            $checkIf = mysqli_fetch_array(
                                mysqli_query(
                                    $_database,
                                    "SELECT `value`, COUNT(*) AS exist FROM `".PREFIX."cups_teams_social`
                                        WHERE teamID = ".$team_id." AND category_id = ".$type_id
                                )
                            );

                            $get = mysqli_fetch_array(
                                mysqli_query(
                                    $_database,
                                    "SELECT `is_url` FROM `".PREFIX."user_socials_types`
                                        WHERE typeID = ".$type_id
                                )
                            );

                            $setValue = TRUE;

                            if($get['is_url']) {

                                //
                                // Sofern Soziales Netzwerk eine URL erwartet
                                // checke, ob eine URL vorliegt
                                if(!validate_url($social_value)) {
                                    $setValue = FALSE;
                                }

                            }

                            if($setValue) {

                                if(($checkIf['value'] != $social_value) && ($checkIf['exist'] == 1)) {

                                    //
                                    // Update des Sozialen Netzwerkes
                                    $saveQuery = mysqli_query(
                                        $_database,
                                        "UPDATE `".PREFIX."cups_teams_social`
                                            SET value = '".$social_value."',
                                                date = ".time()."
                                            WHERE teamID = ".$team_id." AND category_id = ".$type_id
                                    );

                                } else {

                                    //
                                    // Speichern des Sozialen Netzwerkes
                                    $saveQuery = mysqli_query(
                                        $_database,
                                        "INSERT INTO `".PREFIX."cups_teams_social`
                                            (
                                                `teamID`,
                                                `category_id`,
                                                `value`,
                                                `date`
                                            )
                                            VALUES
                                            (
                                                ".$team_id.",
                                                ".$type_id.",
                                                '".$social_value."',
                                                ".time()."
                                            )"
                                    );

                                }

                            }

                        } else {

                            $checkIf = mysqli_fetch_array(
                                mysqli_query(
                                    $_database,
                                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams_social`
                                        WHERE teamID = " . $team_id . " AND category_id = " . $type_id
                                )
                            );

                            if(($checkIf['exist'] == 1)) {

                                //
                                // Löschen des Eintrages, wenn Value leer
                                // und Eintrag in Datenbank gespeichert
                                $deleteQuery = mysqli_query(
                                    $_database,
                                    "DELETE FROM `".PREFIX."cups_teams_social`
                                        WHERE teamID = ".$team_id." AND category_id = ".$type_id
                                );

                            }

                        }

                    }

                }

            } catch(Exception $e) {
                $_SESSION['errorArray'][] = $e->getMessage();
            }

            header('Location: index.php?site=teams&action=admin&id='.$team_id);

        } else {

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT * FROM `" . PREFIX . "cups_teams` 
                        WHERE teamID = " . $team_id . " AND deleted = 0"
                )
            );

            $showMessage = (isset($_GET['message'])) ? 
                showMessage($_language->module[$_GET['message']]) : '';

            if (($ds['deleted'] == 1) && !iscupadmin($userID)) {
                throw new \Exception($_language->module['no_access']);
            }

            if ($ds['deleted'] == 1) {
                echo showError($_language->module['team_deleted']);
            }

            $base_url = 'index.php?site=teams&amp;action=';

            $link = $cup_url . '/' . $base_url . 'join&amp;id=' . $team_id . '&amp;pw=' . $ds['password'];

            $admin = '';
            $admin .= ' <a class="btn btn-default btn-sm" href="' . $base_url . 'details&amp;id=' . $team_id . '">' . $_language->module['team_view'] . '</a>';
            $admin .= ' <a class="btn btn-default btn-sm" href="' . $base_url . 'log&amp;id=' . $team_id . '">Team Log</a>';
            $admin .= ' <a class="btn btn-info btn-sm white darkshadow" href="' . $base_url . 'edit&amp;id=' . $team_id . '">' . $_language->module['team_edit'] . '</a>';
            $admin .= ' <a class="btn btn-danger btn-sm white darkshadow" href="' . $base_url . 'delete&amp;id=' . $team_id . '">' . $_language->module['team_del'] . '</a>';

            $teamSocials = '';

            $socialQuery = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "user_socials_types`
                    WHERE cup = 1
                    ORDER BY sort ASC"
            );
            while($get = mysqli_fetch_array($socialQuery)) {

                $category_id = $get['typeID'];

                $getTeamSocial = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT * FROM `".PREFIX."cups_teams_social`
                            WHERE category_id = ".$category_id." AND teamID = " . $team_id
                    )
                );

                $inputValue = $getTeamSocial['value'];
                if(empty($inputValue) && (strtolower($get['name']) == 'homepage')) {
                    $inputValue = $ds['hp'];
                }

                $data_array = array();
                $data_array['$label'] = $get['name'];
                $data_array['$inputType'] = ($get['is_url']) ? 'url' : 'text';
                $data_array['$inputName'] = 'socials['.$category_id.']';
                $data_array['$inputValue'] = $inputValue;
                $data_array['$inputPlaceholder'] = $get['placeholder_team'];
                $teamSocials .= $GLOBALS["_template_cup"]->replaceTemplate("form_group", $data_array);

            }

            $data_array = array();
            $data_array['$team_id'] = $team_id;
            $data_array['$admin'] = $admin;
            $data_array['$name'] = $ds['name'];
            $data_array['$date'] = getformatdate($ds['date']);
            $data_array['$teamSocials'] = $teamSocials;
            $data_array['$link'] = $link;
            $data_array['$password'] = $ds['password'];
            $teams_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_panel_list", $data_array);
            echo $teams_list;

            $penalty = '';

            $logo = '<img src="' . getCupTeamImage($team_id, true) . '" alt="" />';

            $members = '';
            $changeAdminList = '';

            $mem = mysqli_query(
                $_database,
                "SELECT
                        a.userID AS `user_id`,
                        a.join_date AS `date_join`,
                        b.name AS `position`,
                        c.nickname AS `nickname`,
                        c.firstname AS `firstname`,
                        c.lastname AS `lastname`
                    FROM `" . PREFIX . "cups_teams_member` a
                    JOIN `" . PREFIX . "cups_teams_position` b ON a.position = b.positionID
                    JOIN `" . PREFIX . "user` c ON a.userID = c.userID
                    WHERE teamID = " . $team_id . " AND active = 1
                    ORDER BY b.sort ASC, a.join_date ASC"
            );

            while ($dc = mysqli_fetch_array($mem)) {

                $user_id = $dc['user_id'];
                $profile_url = 'index.php?site=profile&amp;id=' . $user_id;

                $name = '';

                if (!empty($dc['firstname'])) {
                    $name .= $dc['firstname'] . ' "';
                }

                $name .= '<a href="' . $profile_url . '" class="blue">' . getoutput($dc['nickname']) . '</a>';

                if (!empty($dc['firstname'])) {
                    $name .= '"';
                }

                $date = '';

                $links = '<a href="' . $profile_url . '" class="btn btn-default btn-xs">' . $_language->module['view_profile'] . '</a>';
                if ($loggedin && ($userID != $user_id)) {

                    $deleteMemberURL = 'index.php?site=teams&amp;action=delete&amp;id=' . $team_id . '&amp;uID=' . $user_id;
                    $links .= ' <a class="btn btn-default btn-xs" href="' . $deleteMemberURL . '">' . $_language->module['team_delmember'] . '</a>';

                    $changeAdminList .= '<option value="' . $user_id . '">' . $dc['nickname'] . '</option>';

                }

                $data_array = array();
                $data_array['$name'] = $name;
                $data_array['$position'] = $dc['position'];
                $data_array['$date'] = getformatdatetime($dc['date_join']);
                $data_array['$links'] = $links;
                $members .= $GLOBALS["_template_cup"]->replaceTemplate("teams_details_member", $data_array);

            }

            if (!empty($changeAdminList)) {
                $changeAdmin = '';
                $changeAdmin .= '<div class="panel-footer">';
                $changeAdmin .= '<form method="post" action="' . $base_url . 'details&amp;id=' . $team_id . '"><div class="form-inline">';
                $changeAdmin .= '<select class="form-control" name="changeAdminSelect">' . $changeAdminList . '</select>';
                $changeAdmin .= ' <button type="submit" class="btn btn-info btn-sm white darkshadow" name="submitAdminChange">' . $_language->module['change_admin'] . '</button>';
                $changeAdmin .= '</div></form>';
                $changeAdmin .= '</div>';
            } else {
                $changeAdmin = '';
            }

            $team_award = mysqli_query(
                $_database,
                "SELECT a.awardID, a.cupID, a.award, b.name FROM `" . PREFIX . "cups_awards` a
                    LEFT JOIN `".PREFIX."cups` b ON a.cupID = b.cupID
                    WHERE teamID = " . $team_id . "
                    ORDER BY award ASC 
                    LIMIT 0, 10"
            );
            if (mysqli_num_rows($team_award)) {
                $team_awards = '';
                while ($dx = mysqli_fetch_array($team_award)) {
                    $info = '<a href="index.php?site=cup&amp;action=details&amp;id='.$dx['cupID'].'">'.$dx['name'].'</a>';
                    $info .= '<span class="pull-right">' . $dx['award'] . '</span>';
                    $team_awards .= '<div class="list-group-item">' . $info . '</div>';
                }
            } else {
                $team_awards = '<div class="list-group-item">' . $_language->module['no_award'] . '</div>';
            }

            $team_cup = mysqli_query(
                $_database,
                "SELECT
                        `cupID`
                    FROM " . PREFIX . "cups_teilnehmer
                    WHERE teamID = " . $team_id . " AND checked_in = 1
                    ORDER BY cupID DESC
                    LIMIT 0, 10"
            );

            if (mysqli_num_rows($team_cup)) {
                $played_cups = '';
                while ($dx = mysqli_fetch_array($team_cup)) {
                    $played_cups .= '<div class="list-group-item">' . getcup($dx['cupID'], 'name') . '</div>';
                }
            } else {
                $played_cups = '<div class="list-group-item">' . $_language->module['no_cup'] . '</div>';
            }

            $log = '';
            $log .= ' <a class="btn btn-default btn-xs" href="index.php?site=teams&amp;action=log&amp;id=' . $team_id . '">Team Log</a>';

            $data_array = array();
            $data_array['$teamID'] = $team_id;
            $data_array['$penalty'] = $penalty;
            $data_array['$logo'] = $logo;
            $data_array['$members'] = $members;
            $data_array['$changeAdmin'] = $changeAdmin;
            $data_array['$team_awards'] = $team_awards;
            $data_array['$played_cups'] = $played_cups;
            $teams_list = $GLOBALS["_template_cup"]->replaceTemplate("teams_details", $data_array);
            echo $teams_list;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
