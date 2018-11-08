<?php

try {

    $cup_id = getParentIdByValue('id', true);

    if (!isset($cupArray) || !validate_array($cupArray, true)) {
        $cupArray = getcup($cup_id, 'all');
    }

    if (!validate_array($cupArray)) {
        throw new \Exception($_language->module['no_cup']);
    }

    if (!isset($cupArray['id']) || ($cupArray['id'] != $cup_id)) {
        throw new \Exception($_language->module['no_cup']);
    }

    if (($cupArray['admin'] == 1) && !iscupadmin($userID)) {
        throw new \Exception($_language->module['no_cup']);
    }

    $error = '';
    if ($cupArray['admin'] == 1) {
        $error = showInfo($_language->module['admin_only']);
    }

    $time_now = time();

    $navi_home 	= 'btn-default';
    $navi_teams = 'btn-default';
    $navi_groups = 'btn-default';
    $navi_bracket = 'btn-default';
    $navi_rules = 'btn-default';

    $groupstage_navi = '';
    if ($cupArray['groupstage'] == 1) {
        if($getPage == 'groups') {
            $navi_groups = 'btn-info';
        }

        $btnAttributeArray = array();
        $btnAttributeArray[] = 'id="btn_groups"';
        $btnAttributeArray[] = 'onclick="getCupContent(' . $cup_id . ', \'groups\');"';
        $btnAttributeArray[] = 'class="btn btn-sm '.$navi_groups.'"';

        $groupstage_navi = '<div class="btn-group" role="group"><button ' . implode(' ', $btnAttributeArray) . '>'.$_language->module['groupstage'].'</button></div>';

    }

    //
    // Countdown
    $anmeldung = '';
    if (($cupArray['phase'] == 'admin_register') || ($cupArray['phase'] == 'register')) {
        // Registration
        $anmeldung = '<div class="panel panel-default"><div class="panel-body center">Anmeldung: <span id="cup_details_countdown"></span></div></div>';
        $date = getformatdatetime($cupArray['checkin']);
    } else if (($cupArray['phase'] == 'admin_checkin') || ($cupArray['phase'] == 'checkin')) {
        // Check-In
        $anmeldung = '<div class="panel panel-default"><div class="panel-body center">Check-In: <span id="cup_details_countdown"></span></div></div>';
        $date = getformatdatetime($cupArray['start']);
    }

    $content = $anmeldung;

    if (empty($getPage)) {
        $getPage = 'home';
    }

    //
    // Cup Details Content
    if (file_exists(__DIR__ . '/cup_details_' . $getPage . '.php')) {
        include(__DIR__ . '/cup_details_' . $getPage . '.php');
    }

    //
    // Cup Sponsoren
    $sponsors = mysqli_query(
        $_database,
        "SELECT
                cs.`sponsorID`,
                s.`name`,
                s.`url`,
                s.`banner_small`
            FROM `" . PREFIX . "cups_sponsors` cs
            JOIN `" . PREFIX . "sponsors` s ON cs.`sponsorID` = s.`sponsorID`
            WHERE cs.`cupID` = " . $cup_id
    );

    if ($sponsors) {

        if (mysqli_num_rows($sponsors)) {

            $content_sponsors = '';
            while ($db = mysqli_fetch_array($sponsors)) {

                $linkAttributeArray = array();
                $linkAttributeArray[] = 'href="' . $db['url'] . '"';
                $linkAttributeArray[] = 'target="_blank"';
                $linkAttributeArray[] = 'title="' . $db['name'] . '"';
                $linkAttributeArray[] = 'onclick="setHitsJS(\'sponsors\', ' . $db['sponsorID'] . ');"';
                $linkAttributeArray[] = 'class="pull-left"';

                $banner_url = getSponsorImage($db['sponsorID'], true, 'white');

                $content_sponsors .= '<a ' . implode(' ', $linkAttributeArray) . '><img src="' . $banner_url . '" alt="' . $db['name'] . '" /></a>';

            }

            $content .= '<div class="panel panel-default"><div class="panel-heading">Sponsoren</div><div class="panel-body">'.$content_sponsors.'<div class="clear"></div></div></div>';

        }

    }

    //
    // Cup Streams
    $streams = mysqli_query(
        $_database,
        "SELECT
                a.livID AS stream_id,
                b.title AS stream_title,
                b.online AS stream_status,
                b.viewer AS stream_viewer,
                b.game AS stream_game
            FROM `" . PREFIX . "cups_streams` a
            JOIN `" . PREFIX . "liveshow` b ON a.livID = b.livID
            WHERE a.`cupID` = " . $cup_id
    );

    if ($streams) {

        if(mysqli_num_rows($streams)) {

            $content_streams = '';
            while ($db = mysqli_fetch_array($streams)) {

                $stream_url = 'index.php?site=streams&amp;action=show&amp;id=' . $db['stream_id'];

                $stream_info = $db['stream_title'];

                if($db['stream_status']) {

                    $stream_info .= '<span class="pull-right">';

                    if(!empty($db['stream_game'])) {
                        $stream_info .= $streamArray['game'].' / ';
                    }

                    $stream_info .= $db['stream_viewer'].' '.$_language->module['stream_viewer'];
                    $stream_info .= '</span>';

                } else {
                    $stream_info .= '<span class="pull-right grey italic">offline</span>';
                }

                $content_streams .= '<a href="'.$stream_url.'" target="_blank" title="'.$db['stream_title'].'" class="list-group-item">'.$stream_info.'</a>';

            }

            $content .= '<div class="panel panel-default"><div class="panel-heading">Streams</div><div class="list-group">' . $content_streams . '</div></div>';

        }

    }

    //
    // Cup Anmeldung
    if ($loggedin) {

        if (($cupArray['phase'] == 'admin_register') || ($cupArray['phase'] == 'register')) {

            //
            // Team Admin: Registrierung
            if (cup_checkin($cup_id, $userID, 'is_registered')) {

                $infoText = ($cupArray['mode'] == '1on1') ?
                    'enter_cup_ok_1on1' : 'enter_cup_ok';

                $link = '<div class="list-group-item alert-success center">' . $_language->module[$infoText] . '</div>';

            } else {
                $link = '<a class="list-group-item alert-info bold center" href="index.php?site=cup&amp;action=joincup&amp;id=' . $cup_id . '">' . $_language->module['enter_cup'] . '</a>';
            }

        } else if ($cupArray['phase'] == 'admin_checkin') {

            //
            // Team Admin: Check-In
            if (cup_checkin($cup_id, $userID, 'is_checked_in')) {

                $infoText = ($cupArray['mode'] == '1on1') ?
                    'enter_cup_checkin_ok_1on1' : 'enter_cup_checkin_ok';

                $link = '<div class="list-group-item alert-success center">' . $_language->module[$infoText] . '</div>';

            } else if (!cup_checkin($cup_id, $userID, 'is_registered')) {
                $link = '<a class="list-group-item alert-info bold center" href="index.php?site=cup&amp;action=joincup&amp;id=' . $cup_id . '">' . $_language->module['enter_cup'] . '</a>';
            } else {

                $infoText = ($cupArray['mode'] == '1on1') ?
                    'checkin_confirm_text_1on1' : 'checkin_confirm_text';

                $data_array = array();
                $data_array['$cup_id'] = $cup_id;
                $data_array['$confirm_text'] = $_language->module[$infoText];
                $data_array['$checkin_mode'] = ($cupArray['mode'] == '1on1') ?
                    'Check-In' : 'Team Check-In';
                $link = $GLOBALS["_template_cup"]->replaceTemplate("cup_checkin_policy", $data_array);

            }

        } else if ($cupArray['phase'] == 'checkin') {

            //
            // Team: Check-In
            if (cup_checkin($cup_id, $userID, 'is_checked_in')) {
                $link = '<div class="list-group-item alert-success center">' . $_language->module['enter_cup_checkin_ok'] . '</div>';
            } else if (!cup_checkin($cup_id, $userID, 'is_registered')) {
                $link = '<a class="list-group-item alert-info bold center" href="index.php?site=cup&amp;action=joincup&amp;id=' . $cup_id . '">' . $_language->module['enter_cup'] . '</a>';
            } else if ($cupArray['mode'] == '1on1') {

                $infoText = ($cupArray['mode'] == '1on1') ?
                    'checkin_confirm_text_1on1' : 'checkin_confirm_text';

                $data_array = array();
                $data_array['$cup_id'] = $cup_id;
                $data_array['$confirm_text'] = $_language->module[$infoText];
                $data_array['$checkin_mode'] = ($cupArray['mode'] == '1on1') ?
                    'Check-In' : 'Team Check-In';
                $link = $GLOBALS["_template_cup"]->replaceTemplate("cup_checkin_policy", $data_array);

            } else {
                $link = '<div class="list-group-item alert-info center">' . $_language->module['enter_cup'] . '</div>';
            }

        } else if (($cupArray['phase'] == 'running') || ($cupArray['phase'] == 'finished')) {
            $link = '';
        } else {
            $link = '<a class="list-group-item alert-success bold center" href="index.php?site=teams&action=add">' . $_language->module['add_team'] . '</a>';
        }

    } else {
        $link = '<div class="list-group-item alert-info bold center">' . $_language->module['login'] . '</div>';
    }

    $cup_footer = (!empty($link)) ?
        '<div class="list-group">' . $link . '</div>' : '';

    //
    // Update Hits
    setHits('cups', 'cupID', $cup_id, false);

    $data_array = array();
    $data_array['$image_url'] = $image_url;
    $data_array['$error'] = $error;
    $data_array['$cupID'] = $cup_id;
    $data_array['$navTeams'] = ($cupArray['mode'] == '1on1') ? $_language->module['player'] : 'Teams';
    $data_array['$cupname'] = $cupArray['name'];
    $data_array['$navi_home'] = $navi_home;
    $data_array['$navi_teams'] = $navi_teams;
    $data_array['$groupstage_navi'] = $groupstage_navi;
    $data_array['$navi_bracket'] = $navi_bracket;
    $data_array['$navi_rules'] = $navi_rules;
    $data_array['$content'] = $content;
    $data_array['$cup_footer'] = $cup_footer;
    $cups_details_home = $GLOBALS["_template_cup"]->replaceTemplate("cups_details_home", $data_array);
    echo $cups_details_home;

} catch(Exception $e) {
    echo showError($e->getMessage());
}
