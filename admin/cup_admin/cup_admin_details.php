<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    if ($getAction == 'status') {

        $status_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
            (int)$_GET['id'] : 0;

        if ($status_id < 1) {
            throw new \UnexpectedValueException($_language->module['access_denied']);
        }

        $ergebnis = cup_query(
            "SELECT * FROM `" . PREFIX . "cups`
                WHERE `status` = " . $status_id . "
                ORDER BY `start_date` ASC",
            __FILE__
        );

        if (!mysqli_num_rows($ergebnis)) {
            throw new \UnexpectedValueException($_language->module['no_cup']);
        }

        while ($ds = mysqli_fetch_array($ergebnis)) {

            $cup_id = $ds[getConstNameCupId()];
            $name = $ds['name'];

            $infoArray = array();

            $infoArray[] = '<font class="uppercase">' . getshortname($ds['game'], 1) . '</font>';
            if ($status_id == 1) {
                $infoArray[] = $_language->module['checkin'] . ': ' . getformatdatetime($ds['checkin_date']);
                $infoArray[] = $_language->module[getConstNameStart()] . ': ' . getformatdatetime($ds['start_date']);
            } else {
                $infoArray[] = $_language->module['status'] . ': ' . $_language->module['cup_status_' . $status_id];
            }

            $cup_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=' . $cup_id;
            $link = '<a href="' . $cup_url . '">' . $_language->module['view'] . '</a>';

            $data_array = array();
            $data_array['$cupID'] = $cupID;
            $data_array['$name'] = $name;
            $data_array['$info'] = implode(' / ', $infoArray);
            $data_array['$link'] = $link;
            $cups_home = $GLOBALS["_template_cup"]->replaceTemplate("cups_home", $data_array);
            echo $cups_home;

        }

    } else {

        $debug = 0;

        $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
            (int)$_GET['id'] : 0;

        if ($cup_id < 1) {
            throw new \UnexpectedValueException($_language->module['access_denied']);
        }

        if (!checkIfContentExists($cup_id, getConstNameCupId(), 'cups')) {
            throw new \UnexpectedValueException($_language->module['unknown_cup']);
        }

        $getPage = (isset($_GET['page'])) ?
            getinput($_GET['page']) : 'home';

        $pageArray = array(
            'activity',
            'bracket',
            'groups',
            'home',
            'images',
            'rules',
            'settings',
            'teams'
        );

        if (!in_array($getPage, $pageArray)) {
            throw new \UnexpectedValueException($_language->module['access_denied']);
        }

        if (($getPage == 'home') && validate_array($_POST, true)) {

            $parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id;

            try {

                if (isset($_POST['submitCupDescription'])) {

                    $description = getinput($_POST['cupDescription']);

                    $updateQuery = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "cups`
                            SET `description` = '" . $description . "'
                            WHERE `cupID` = " . $cup_id
                    );

                    if (!$updateQuery) {
                        throw new \UnexpectedValueException($_language->module['query_select_failed']);
                    }

                } else if (isset($_POST['submit_user_to_admin'])) {

                    $user_id = (isset($_POST['admin_id']) && validate_int($_POST['admin_id'], true)) ?
                        (int)$_POST['admin_id'] : 0;

                    if ($user_id < 1) {
                        throw new \UnexpectedValueException($_language->module['unknown_admin']);
                    }

                    $ergebnis = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "cups_admin`
                            (
                                `userID`,
                                `cupID`,
                                `rights`
                            )
                            VALUES
                            (
                                " . $user_id . ",
                                " . $cup_id . ",
                                'admin'
                            )"
                    );

                    if (!$ergebnis) {
                        throw new \UnexpectedValueException($_language->module['query_insert_failed']);
                    }

                } else if (isset($_POST['submit_streams'])) {

                    $stream_id = (isset($_POST['stream_id']) && validate_int($_POST['stream_id'], true)) ?
                        (int)$_POST['stream_id'] : 0;

                    if ($stream_id < 1) {
                        throw new \UnexpectedValueException($_language->module['unknown_stream']);
                    }

                    $ergebnis = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "cups_streams`
                            (
                                `cupID`,
                                `livID`
                            )
                            VALUES
                            (
                                " . $cup_id . ",
                                " . $stream_id . "
                            )"
                    );

                    if (!$ergebnis) {
                        throw new \UnexpectedValueException($_language->module['query_insert_failed']);
                    }

                } else if (isset($_POST['submit_sponsor'])) {

                    $sponsor_id = (isset($_POST['sponsor_id']) && validate_int($_POST['sponsor_id'], true)) ?
                        (int)$_POST['sponsor_id'] : 0;

                    if ($sponsor_id < 1) {
                        throw new \UnexpectedValueException($_language->module['unknown_sponsor']);
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
                        throw new \UnexpectedValueException($_language->module['query_insert_failed']);
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
                                $deleteAction = $postArray[0];
                                $cup_id = $postArray[1];
                                $value_id = $postArray[2];
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
                            throw new \UnexpectedValueException($_language->module['query_insert_failed']);
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
                            throw new \UnexpectedValueException($_language->module['query_insert_failed']);
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
                            throw new \UnexpectedValueException($_language->module['query_insert_failed']);
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
                            throw new \UnexpectedValueException($_language->module['query_insert_failed']);
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
            $navi_images = ($getPage == 'images') ?
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
            if ($cupArray['groupstage'] == 1) {
                $groupstage_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=' . $cup_id . '&amp;page=groups';
                $groupstage_navi = '<div class="btn-group" role="group"><a href="' . $groupstage_url . '" class="btn ' . $navi_groups . '">' . $_language->module['groupstage'] . '</a></div>';
            }

            $content = '';

            if (isset($cupArray['saved']) && !$cupArray['saved']) {
                $content .= showInfo($_language->module['cup_not_saved']);
            }

            if ($cupArray['status'] == 1) {

                $start_url_page = ($cupArray['groupstage'] == 1) ?
                    '&amp;action=start&amp;status=groupstage' : '&amp;action=start&amp;status=playoffs';

                $start_url = 'admincenter.php?site=cup&amp;mod=cup' . $start_url_page . '&amp;id=' . $cup_id;
                $cup_footer = '<a class="btn btn-success btn-sm white darkshadow" href="' . $start_url . '">' . $_language->module['cup_admin_status_' . $cupArray['status']].'</a>';

            } else if ($cupArray['status'] == 2) {
                $footer_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=status&amp;id=' . $cup_id;
                $cup_footer = '<a class="btn btn-success btn-sm white darkshadow" href="' . $footer_url . '">'.$_language->module['cup_admin_status_' . $cupArray['status']].'</a>';
            } else if ($cupArray['status'] == 3) {
                $footer_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=finish&amp;status=playoffs&amp;id=' . $cup_id;
                $cup_footer = '<a class="btn btn-success btn-sm white darkshadow" href="' . $footer_url . '">'.$_language->module['cup_admin_status_' . $cupArray['status']].'</a>';
            } else {
                $cup_footer = '<span class="btn btn-default btn-sm">'.$_language->module['cup_admin_status_' . $cupArray['status']].'</span>';
            }

            /**
             * Cup Details Content
             */
            $contentPath = __DIR__ . '/includes/cup_details_' . $getPage . '.php';
            if (file_exists($contentPath)) {
                include($contentPath);
            } else {
                $content .= showError($_language->module['access_denied']);
            }

            $data_array = array();
            $data_array['$image_url'] = $image_url;
            $data_array['$error'] = $error;
            $data_array['$baseURL'] = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=' . $cup_id;
            $data_array['$cup_id'] = $cup_id;
            $data_array['$cupname'] = $cupArray['name'];
            $data_array['$navi_home'] = $navi_home;
            $data_array['$navi_settings'] = $navi_settings;
            $data_array['$navi_images'] = $navi_images;
            $data_array['$navi_teams'] = $navi_teams;
            $data_array['$team_tab_txt'] = ($cupArray['mode'] != '1on1') ?
                'Teams' : 'Players';
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
