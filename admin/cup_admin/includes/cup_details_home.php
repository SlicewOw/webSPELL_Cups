<?php

try {

    if (!isset($content)) {
        $content = '';
    }

    $cup_maps = '';
    if ($cupArray['mappool'] > 0) {

        $cupMaps = getMaps($cupArray['mappool']);

        if (validate_array($cupMaps, true)) {
            $maps = implode(', ', $cupMaps);
            $cup_maps .= '<span class="list-group-item">' . $_language->module['maps'] . ': ' . $maps . '</span>';
        }

    }

    $data_array = array();
    $data_array['$game'] = getgamename($cupArray['game']);
    $data_array['$platform'] = $cupArray['platform'];
    $data_array['$mode'] = $cupArray['mode'];
    $data_array['$date_checkin'] = getformatdatetime($cupArray[getConstNameCheckIn()]);
    $data_array['$date_start'] = getformatdatetime($cupArray[getConstNameStart()]);
    $data_array['$teams_registered'] = $cupArray['teams']['registered'];
    $data_array['$teams_checkedin'] = $cupArray['teams']['checked_in'];
    $data_array['$size'] = $cupArray['size'];
    $data_array['$pps_max'] = $cupArray['max_pps'];
    $data_array['$hits_total'] = $cupArray['hits'];
    $data_array['$hits_home'] = $cupArray['hits_detail']['home'];
    $data_array['$hits_teams'] = $cupArray['hits_detail']['teams'];
    $data_array['$hits_groups'] = $cupArray['hits_detail']['groups'];
    $data_array['$hits_bracket'] = $cupArray['hits_detail']['bracket'];
    $data_array['$hits_rules'] = $cupArray['hits_detail']['rules'];
    $data_array['$cup_maps'] = $cup_maps;
    $data_array['$description'] = $cupArray['description'];
    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_home", $data_array);

    /**
     * Admin hinzufuegen
     **/

    $content_admin_add = '';

    $admins = mysqli_query(
        $_database,
        "SELECT
                a.`userID` AS `user_id`,
                b.`nickname` AS `nickname`
            FROM `" . PREFIX . "cups_admin` a
            JOIN `" . PREFIX . "user` b ON a.`userID` = b.`userID`
            WHERE `cupID` = " . $cup_id
    );

    if (!$admins) {
        throw new \UnexpectedValueException($_language->module['query_select_failed']);
    }

    $userWhereClauseArray = array();
    $userWhereClauseArray[] = '`banned` IS NULL';
    $userWhereClauseArray[] = '`activated` = \'1\'';

    if (mysqli_num_rows($admins) > 0) {

        $userIdArray = array();

        while ($db = mysqli_fetch_array($admins)) {

            $user_id = $db['user_id'];
            $profile_url = 'index.php?site=profile&amp;id='.$user_id;

            $nickname = $db['nickname'];

            $info_admin = '<a class="btn btn-default btn-xs" href="'.$profile_url.'" target="_blank">'.$_language->module['view'].'</a>';
            $info_admin .= ' <button class="btn btn-default btn-xs" type="submit" name="deleteAdmin_'.$cup_id.'_'.$user_id.'">'.$_language->module['delete'].'</button>';

            $content_admin_add .= '<div class="list-group-item" target="_blank">'.$nickname.'<span class="pull-right">'.$info_admin.'</span></div>';

            $userIdArray[] = $user_id;

        }

        $userWhereClauseArray[] = 'userID NOT IN (' . implode(', ', $userIdArray) . ')';

    } else {
        $content_admin_add = '<div class="list-group-item">'.$_language->module['no_admin'].'</div>';
        $where_clause = '';
    }

    $select_admin_add = '<option value="0">-- / --</option>';

    $users = mysqli_query(
        $_database,
        "SELECT
                a.`userID` AS `userID`,
                b.`nickname` AS `nickname`
            FROM `" . PREFIX . "cups_team` a
            JOIN `" . PREFIX . "user` b ON a.`userID` = b.`userID`
            ORDER BY a.`userID` ASC"
    );

    if (!$users) {
        throw new \UnexpectedValueException($_language->module['query_select_failed']);
    }

    if (mysqli_num_rows($users) > 0) {

        $select_admin_add .= '<optgroup label="Cup Admins">';

        while ($db = mysqli_fetch_array($users)) {
            $select_admin_add .= '<option value="' . $db['userID'] . '">' . $db['nickname'] . ' <small>(ID: ' . $db['userID'] . ')</small></option>';
        }

        $select_admin_add .= '</optgroup>';

    }

    $select_admin_add .= '<optgroup label="User">';

    $userWhereClause = implode(' AND ', $userWhereClauseArray);

    $users = mysqli_query(
        $_database,
        "SELECT
                `userID`,
                `nickname`
            FROM `" . PREFIX . "user`
            WHERE " . $userWhereClause . "
            ORDER BY `nickname` ASC"
    );

    if (!$users) {
        throw new \UnexpectedValueException($_language->module['query_select_failed']);
    }

    while ($db = mysqli_fetch_array($users)) {
        $select_admin_add .= '<option value="' . $db['userID'] . '">' . $db['nickname'] . ' <small>(ID: ' . $db['userID'] . ')</small></option>';
    }

    $select_admin_add .= '</optgroup>';

    $data_array = array();
    $data_array['$cupID'] = $cup_id;
    $data_array['$title_admin_add'] = 'Cup Admins';
    $data_array['$submit_admin_add'] = 'submit_user_to_admin';
    $data_array['$selectName'] = 'admin_id';
    $data_array['$content_admin_add'] = $content_admin_add;
    $data_array['$select_admin_add'] = $select_admin_add;
    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cups_admin_add", $data_array);

    /**
     * Streams hinzufuegen
     **/

    $content_admin_add 	= '';
    $streams = mysqli_query(
        $_database,
        "SELECT
                `livID`
            FROM `" . PREFIX . "cups_streams`
            WHERE `cupID` = " . $cup_id
    );

    $streamWhereClauseArray = array();
    $streamWhereClauseArray[] = '`active` = 1';

    if (mysqli_num_rows($streams) > 0) {

        $cupStreamArray = array();

        while($db = mysqli_fetch_array($streams)) {

            $streamArray = get_streaminfo($db['livID']);

            if (validate_array($streamArray, true)) {

                $url_detail	= 'index.php?site=streams&amp;id='.$db['livID'];
                $info_stream = '<a class="btn btn-default btn-xs" href="'.$url_detail.'" target="_blank">'.$streamArray['title'].'</a>';
                $info_stream .= ' <button class="btn btn-default btn-xs" type="submit" name="deleteStream_'.$cup_id.'_'.$db['livID'].'">'.$_language->module['delete'].'</button>';

                $content_admin_add .= '<div class="list-group-item">'.$streamArray['title'].'<span class="pull-right">'.$info_stream.'</span></div>';

                $cupStreamArray[] = $streamArray['stream_id'];

            }

        }

        if (validate_array($cupStreamArray, true)) {
            $streamWhereClauseArray[] = '`livID` NOT IN (' . implode(', ', $cupStreamArray) . ')';
        }

    } else {
        $content_admin_add = '<div class="list-group-item">'.$_language->module['no_stream'].'</div>';
    }

    $streamWhereClause = implode(' AND ', $streamWhereClauseArray);

    $select_admin_add = '<option value="0">-- / --</option>';
    $streams = mysqli_query(
        $_database,
        "SELECT
                `livID`,
                `title`
            FROM `" . PREFIX . "liveshow`
            WHERE " . $streamWhereClause . "
            ORDER BY `title` ASC"
    );

    if (!$streams) {
        throw new \UnexpectedValueException($_language->module['query_select_failed'] . ' (liveshow)');
    }

    while ($db = mysqli_fetch_array($streams)) {
        $select_admin_add .= '<option value="' . $db['livID'] . '">' . $db['title'] . ' <small>(ID: ' . $db['livID'] . ')</small></option>';
    }

    $data_array = array();
    $data_array['$cupID'] = $cup_id;
    $data_array['$title_admin_add'] = 'Cup Streams';
    $data_array['$submit_admin_add'] = 'submit_streams';
    $data_array['$selectName'] = 'stream_id';
    $data_array['$content_admin_add'] = $content_admin_add;
    $data_array['$select_admin_add'] = $select_admin_add;
    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cups_admin_add", $data_array);

    /**
     * Sponsor hinzufuegen
     **/

    $content_admin_add 	= '';
    $sponsors = mysqli_query(
        $_database,
        "SELECT
                `sponsorID`
            FROM `" . PREFIX . "cups_sponsors`
            WHERE `cupID` = " . $cup_id
    );

    $sponsorWhereClauseArray = array();

    if (mysqli_num_rows($sponsors) > 0) {

        $cupSponsorArray = array();

        while ($db = mysqli_fetch_array($sponsors)) {

            $sponsorArray = getsponsor($db['sponsorID']);

            $info_sponsor = '';
            $info_sponsor .= ' <a class="btn btn-default btn-xs" href="'.$sponsorArray['url'].'" target="_blank">'.$_language->module['view'].'</a>';
            $info_sponsor .= ' <button class="btn btn-default btn-xs" type="submit" name="deleteSponsor_'.$cup_id.'_'.$db['sponsorID'].'">'.$_language->module['delete'].'</button>';
            $content_admin_add .= '<div class="list-group-item">'.$sponsorArray['name'].'<span class="pull-right">'.$info_sponsor.'</span></div>';

            $cupSponsorArray[] = $db['sponsorID'];

        }

        if (validate_array($cupSponsorArray, true)) {
            $sponsorWhereClauseArray[] = '`sponsorID` NOT IN (' . implode(', ', $cupSponsorArray) . ')';
        }

    } else {
        $content_admin_add = '<div class="list-group-item">'.$_language->module['no_sponsor'].'</div>';
    }

    $sponsorWhereClause = (validate_array($sponsorWhereClauseArray, true)) ?
        'WHERE ' . implode(' AND ', $sponsorWhereClauseArray) : '';

    $select_admin_add = '<option value="0">-- / --</option>';
    $sponsors = mysqli_query(
        $_database,
        "SELECT
                `sponsorID`,
                `name`
            FROM `" . PREFIX . "sponsors`
            " . $sponsorWhereClause . "
            ORDER BY `name` ASC"
    );

    if (!$sponsors) {
        throw new \UnexpectedValueException($_language->module['query_select_failed'] . ' (sponsor)');
    }

    while ($db = mysqli_fetch_array($sponsors)) {
        $select_admin_add .= '<option value="' . $db['sponsorID'] . '">' . $db['name'] . '</option>';
    }

    $data_array = array();
    $data_array['$cupID'] = $cup_id;
    $data_array['$title_admin_add'] = 'Cup Sponsoren';
    $data_array['$submit_admin_add'] = 'submit_sponsor';
    $data_array['$selectName'] = 'sponsor_id';
    $data_array['$content_admin_add'] = $content_admin_add;
    $data_array['$select_admin_add'] = $select_admin_add;
    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cups_admin_add", $data_array);

} catch (Exception $e) {
    $content = showError($e->getMessage());
}
