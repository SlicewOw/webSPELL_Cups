<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    if ($getAction == 'status') {

        $status_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
            (int)$_GET['id'] : 0;

        if ($status_id < 1) {
            throw new \Exception($_language->module['access_denied']);
        }

        $ergebnis = mysqli_query(
            $_database, 
            "SELECT * FROM `" . PREFIX . "cups` 
                WHERE `status` = " . $status_id . " 
                ORDER BY `start_date` ASC"
        );

        if (!$ergebnis) {
            throw new \Exception($_language->module['query_select_failed']);
        }

        if (!mysqli_num_rows($ergebnis)) {
            throw new \Exception($_language->module['no_cup']);
        }

        while ($ds = mysqli_fetch_array($ergebnis)) {

            $cup_id = $ds['cupID'];
            $name = $ds['name'];

            $info = '<font class="uppercase">'.getshortname($ds['game'], 1).'</font>';
            if($status_id == 1) {
                $info .= ' / Check-In: '.getformatdatetime($ds['checkin_date']);
                $info .= ' / Start: '.getformatdatetime($ds['start_date']);
            } else {
                $info .= ' / Status: '.$_language->module['cup_status_'.$status_id];
            }

            $link = '<a href="admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id='.$cup_id.'">'.$_language->module['view'].'</a>';

            $data_array = array();
            $data_array['$cupID'] = $cupID;
            $data_array['$name'] = $name;
            $data_array['$info'] = $info;
            $data_array['$link'] = $link;
            $cups_home = $GLOBALS["_template_cup"]->replaceTemplate("cups_home", $data_array);
            echo $cups_home;

        }

    } else {

        $debug = 0;

        $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
            (int)$_GET['id'] : 0;

        if ($cup_id < 1) {
            throw new \Exception($_language->module['access_denied']);
        }

        if (!checkIfContentExists($cup_id, 'cupID', 'cups')) {
            throw new \Exception($_language->module['unknown_cup']);
        }

        $getPage = (isset($_GET['page'])) ? 
            getinput($_GET['page']) : 'home';

        $pageArray = array(
            'activity',
            'bracket',
            'groups',
            'home',
            'rules',
            'settings',
            'teams'
        );

        if (!in_array($getPage, $pageArray)) {
            throw new \Exception($_language->module['access_denied']);
        }

        if (($getPage == 'home') && validate_array($_POST, true)) {

            $parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id;

            try {

                if (isset($_POST['submitCupDescription'])) {

                    $description = getinput($_POST['cupDescription']);

                    $updateQuery = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "cups` 
                            SET description = '" . $description . "' 
                            WHERE cupID = " . $cup_id
                    );

                    if (!$updateQuery) {
                        throw new \Exception($_language->module['query_select_failed']);
                    }

                } else if (isset($_POST['submit_user_to_admin'])) {

                    $user_id = (isset($_POST['admin_id']) && validate_int($_POST['admin_id'], true)) ?
                        (int)$_POST['admin_id'] : 0;

                    if ($user_id < 1) {
                        throw new \Exception($_language->module['unknown_admin']);
                    }

                    $ergebnis = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "cups_admin`
                            (
                                userID, 
                                cupID, 
                                rights
                            ) 
                            VALUES 
                            (
                                " . $user_id . ", 
                                " . $cup_id . ", 
                                'admin'
                            )"
                    );

                    if (!$ergebnis) {
                        throw new \Exception($_language->module['query_insert_failed']);
                    }

                } else if (isset($_POST['submit_streams'])) {

                    $stream_id = (isset($_POST['stream_id']) && validate_int($_POST['stream_id'], true)) ?
                        (int)$_POST['stream_id'] : 0;

                    if ($stream_id < 1) {
                        throw new \Exception($_language->module['unknown_stream']);
                    }

                    $ergebnis = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "cups_streams`
                            (
                                cupID, 
                                livID
                            ) 
                            VALUES 
                            (
                                " . $cup_id . ", 
                                " . $stream_id . "
                            )"
                    );

                    if (!$ergebnis) {
                        throw new \Exception($_language->module['query_insert_failed']);
                    }

                } else if (isset($_POST['submit_sponsor'])) {

                    $sponsor_id = (isset($_POST['sponsor_id']) && validate_int($_POST['sponsor_id'], true)) ?
                        (int)$_POST['sponsor_id'] : 0;

                    if ($sponsor_id < 1) {
                        throw new \Exception($_language->module['unknown_sponsor']);
                    }

                    $ergebnis = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "cups_sponsors`
                            (
                                `cupID`, 
                                `sponsorID`
                            ) 
                            VALUES 
                            (
                                " . $cup_id . ", 
                                " . $sponsor_id . "
                            )"
                    );

                    if (!$ergebnis) {
                        throw new \Exception($_language->module['query_insert_failed']);
                    }

                } else {

                    $deleteAction = '';

                    $searchArray = array(
                        'deleteAdmin',
                        'deleteStream',
                        'deleteSponsor',
                        'deleteTeam'
                    );
                    $anzSearch = count($searchArray);

                    $postKeys = array_keys($_POST);
                    $anzPost = count($_POST);
                    for ($x = 0; $x < $anzPost; $x++) {

                        for ($n = 0; $n < $anzSearch; $n++) {

                            $postArray = explode('_', $postKeys[$x]);
                            if ($postArray[0] == $searchArray[$n]) {
                                $deleteAction 	= $postArray[0];
                                $cup_id 		= $postArray[1];
                                $value_id 		= $postArray[2];
                                break;
                            }

                        }

                    }

                    $whereClauseArray = array();
                    $whereClauseArray[] = '`cupID` = ' . $cup_id;

                    if ($deleteAction == 'deleteStream') {

                        $whereClauseArray[] = '`livID` = ' . $value_id;

                        $whereClause = implode(' AND ', $whereClauseArray);

                        $deleteQuery = mysqli_query(
                            $_database,
                            "DELETE FROM `" . PREFIX . "cups_streams` 
                                WHERE " . $whereClause
                        );

                        if (!$deleteQuery) {
                            throw new \Exception($_language->module['query_insert_failed']);
                        }

                    } else if ($deleteAction == 'deleteSponsor') {

                        $whereClauseArray[] = '`sponsorID` = ' . $value_id;

                        $whereClause = implode(' AND ', $whereClauseArray);

                        $deleteQuery = mysqli_query(
                            $_database,
                            "DELETE FROM `" . PREFIX . "cups_sponsors` 
                                WHERE " . $whereClause
                        );

                        if (!$deleteQuery) {
                            throw new \Exception($_language->module['query_insert_failed']);
                        }

                    } else if ($deleteAction == 'deleteAdmin') {

                        $whereClauseArray[] = '`userID` = ' . $value_id;

                        $whereClause = implode(' AND ', $whereClauseArray);

                        $deleteQuery = mysqli_query(
                            $_database,
                            "DELETE FROM `" . PREFIX . "cups_admin` 
                                WHERE " . $whereClause
                        );

                        if (!$deleteQuery) {
                            throw new \Exception($_language->module['query_insert_failed']);
                        }

                    } else if ($deleteAction == 'deleteTeam') {

                        $whereClauseArray[] = '`teamID` = ' . $value_id;

                        $whereClause = implode(' AND ', $whereClauseArray);

                        $deleteQuery = mysqli_query(
                            $_database,
                            "DELETE FROM `" . PREFIX . "cups_teilnehmer` 
                                WHERE " . $whereClause
                        );

                        if (!$deleteQuery) {
                            throw new \Exception($_language->module['query_insert_failed']);
                        }

                    }

                }

            } catch (Exception $e) {
                $_SESSION['errorArray'][] = $e->getMessage();
            }

            header('Location: ' . $parent_url);

        } else {

            //
            // Cup Array
            $cupArray = getcup($cup_id);

            if ($cupArray['admin'] == 1) {
                echo showInfo($_language->module['admin_only'], true);
            }

            $error = '';

            $navi_home = (empty($getPage) || (($getPage == 'home'))) ?
                'btn-info white darkshadow' : 'btn-default';
            $navi_settings = ($getPage == 'settings') ?
                'btn-info white darkshadow' : 'btn-default';
            $navi_teams = ($getPage == 'teams') ?
                'btn-info white darkshadow' : 'btn-default';
            $navi_groups = ($getPage == 'groups') ?
                'btn-info white darkshadow' : 'btn-default';
            $navi_bracket = ($getPage == 'bracket') ?
                'btn-info white darkshadow' : 'btn-default';
            $navi_rules = ($getPage == 'rules') ?
                'btn-info white darkshadow' : 'btn-default';

            $groupstage_navi = '';
            if($cupArray['groupstage'] == 1) {
                $groupstage_navi = '<div class="btn-group" role="group"><a href="admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id='.$cup_id.'&amp;page=groups" class="btn '.$navi_groups.'">'.$_language->module['groupstage'].'</a></div>';
            }

            $content = '';

            if (isset($cupArray['saved']) && !$cupArray['saved']) {
                $content .= showInfo($_language->module['cup_not_saved']);
            }

            //
            // Cup Details Content
            if (file_exists(__DIR__ . '/includes/cup_details_' . $getPage . '.php')) {
                include(__DIR__ . '/includes/cup_details_' . $getPage . '.php');
            } else {
                $content .= showError($_language->module['access_denied']);
            }

            $cup_footer = '';
            if ($cupArray['status'] == 1) { 

                $start_url = ($cupArray['groupstage'] == 1) ?
                    '&amp;action=start&amp;status=groupstage' : '&amp;action=start&amp;status=playoffs';

                $startURL 	= 'admincenter.php?site=cup&amp;mod=cup'.$start_url.'&amp;id='.$cup_id;
                $cup_footer .= '<div class="btn-group" role="group">';
                $cup_footer .= '<a class="btn btn-default btn-sm" href="'.$startURL.'">'.$_language->module['cup_status_' . $cupArray['status']].'</a>';
                $cup_footer .= '</div>';

                $editURL	= 'admincenter.php?site=cup&amp;mod=cup&amp;action=edit&amp;id='.$cup_id;
                $cup_footer .= '<div class="btn-group" role="group">';
                $cup_footer .= '<a class="btn btn-default btn-sm" href="'.$editURL.'">Cup '.$_language->module['edit'].'</a>';  
                $cup_footer .= '</div>';

                $deleteURL	= 'admincenter.php?site=cup&amp;mod=cup&amp;action=delete&amp;id='.$cup_id;
                $cup_footer .= '<div class="btn-group" role="group">';
                $cup_footer .= '<a class="btn btn-default btn-sm" href="'.$deleteURL.'">Cup '.$_language->module['delete'].'</a>'; 
                $cup_footer .= '</div>';

            } else if ($cupArray['status'] == 2) {
                $cup_footer .= '<a class="btn btn-default btn-sm" href="admincenter.php?site=cup&amp;mod=cup&amp;action=status&amp;id='.$cup_id.'">'.$_language->module['cup_status_' . $cupArray['status']].'</a>';
            } else if ($cupArray['status'] == 3) {
                $cup_footer .= '<a class="btn btn-default btn-sm" href="admincenter.php?site=cup&amp;mod=cup&amp;action=finish&amp;status=playoffs&amp;id='.$cup_id.'">'.$_language->module['cup_status_' . $cupArray['status']].'</a>';
            } else { 
                $cup_footer .= '<span class="btn btn-default btn-sm">'.$_language->module['cup_status_' . $cupArray['status']].'</span>'; 
            }

            $data_array = array();
            $data_array['$image_url'] = $image_url;
            $data_array['$error'] = $error;
            $data_array['$baseURL'] = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=' . $cup_id;
            $data_array['$cupID'] = $cup_id;
            $data_array['$cupname'] = $cupArray['name'];
            $data_array['$navi_home'] = $navi_home;
            $data_array['$navi_settings'] = $navi_settings;
            $data_array['$navi_teams'] = $navi_teams;
            $data_array['$groupstage_navi'] = $groupstage_navi;
            $data_array['$navi_bracket'] = $navi_bracket;
            $data_array['$navi_rules'] = $navi_rules;
            $data_array['$content'] = $content;
            $data_array['$cup_footer'] = $cup_footer;
            $cups_details_home = $GLOBALS["_template_cup"]->replaceTemplate("cups_details_home_admin", $data_array);
            echo $cups_details_home;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
