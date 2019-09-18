<?php

/**
 * General
 **/

function getCupDefaultLanguage() {

    $fallback_language = 'de';

    $settingsFile = __DIR__ . '/../../cup/settings.php';

    if (!file_exists($settingsFile)) {
        throw new \UnexpectedValueException('unknown_settings_file');
    }

    include($settingsFile);

    if (!isset($default_language)) {
        return $fallback_language;
    }

    return $default_language;

}

function updateCupStatistics() {

    $query = cup_query(
        "SELECT
                `teamID`
            FROM `" . PREFIX . "cups_teilnehmer`
            WHERE `checked_in` = 1
            GROUP BY `teamID`",
        __FILE__
    );

    while ($get = mysqli_fetch_array($query)) {

        $team_id = $get['teamID'];

        $updateCupAwardsCategories = array(
            'anz_cups',
            'anz_matches'
        );

        foreach ($updateCupAwardsCategories as $category) {

            $statistics = getteam($team_id, $category);

            $subQuery = cup_query(
                "SELECT
                        `awardID`,
                        `name`
                    FROM `" . PREFIX . "cups_awards_category`
                    WHERE " . $statistics . " >= `" . $category . "`
                    ORDER BY `" . $category . "` DESC
                    LIMIT 0, 1",
                __FILE__
            );

            $subget = mysqli_fetch_array($subQuery);

            if (empty($subget['awardID'])) {
                continue;
            }

            $award_id = $subget['awardID'];

            $selectAwardQuery = cup_query(
                "SELECT
                        `awardID`
                    FROM `" . PREFIX . "cups_awards`
                    WHERE `teamID` = " . $team_id . " AND award = " . $award_id,
                __FILE__
            );

            $verifyIfAwardIsAlreadySet = mysqli_num_rows($selectAwardQuery);
            if ($verifyIfAwardIsAlreadySet > 0) {
                continue;
            }

            $insertQuery = cup_query(
                "INSERT INTO `" . PREFIX . "cups_awards`
                    (
                        `teamID`,
                        `cupID`,
                        `award`,
                        `date`
                    )
                    VALUES
                    (
                        " . $team_id . ",
                        0,
                        " . $award_id . ",
                        " . time() . "
                    )",
                __FILE__
            );

            $teamname = getteam($get['teamID'], 'name');

            setCupTeamLog($team_id, $teamname, 'award_received_' . $award_id);

        }

    }

}

function getDiscordAuthUrl() {

    $discordCredentialArray = getDiscordCredentials();

    global $hp_url;

    $discordUrlAttributeArray = array();
    $discordUrlAttributeArray[] = 'client_id=' . $discordCredentialArray['id'];
    $discordUrlAttributeArray[] = 'response_type=code';
    $discordUrlAttributeArray[] = 'scope=email%20identify';
    $discordUrlAttributeArray[] = 'state=' . $discordCredentialArray['secret'];
    $discordUrlAttributeArray[] = 'redirect_uri=' . urlencode($hp_url);

    $discord_url = 'https://discordapp.com/api/oauth2/authorize?' . implode('&amp;', $discordUrlAttributeArray);
    return $discord_url;

}

function getDiscordCredentials() {

    $settingsFile = __DIR__ . '/../../cup/settings.php';

    if (!file_exists($settingsFile)) {
        throw new \UnexpectedValueException('unknown_settings_file');
    }

    include($settingsFile);

    if (!isset($discord_client_id) || empty($discord_client_id)) {
        throw new \UnexpectedValueException('unknown_discord_client_id');
    }

    if (!isset($discord_client_secret) || empty($discord_client_secret)) {
        throw new \UnexpectedValueException('unknown_discord_client_secret');
    }

    $discordArray = array(
        'id' => $discord_client_id,
        'secret' => $discord_client_secret
    );

    return $discordArray;

}

function discordApiRequest($url, $post=FALSE, $headers=array()) {

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($ch);

    if ($post) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }

    $headers[] = 'Accept: application/json';

    if (isset($_SESSION['access_token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['access_token'];
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    return json_decode($response);

}

function cup_query($query, $file, $line = 0) {

    global $_database;
    global $_mysql_querys;
    global $_language;

    $_language->readModule('index', true);

    if (stristr(str_replace(' ', '', $query), "unionselect") === false and
        stristr(str_replace(' ', '', $query), "union(select") === false
    ) {
        $_mysql_querys[ ] = $query;
        if (empty($query)) {
            return false;
        }

        $result = $_database->query($query);

        if (!$result) {
            setLog($query, 'Query failed!', addslashes($file), $line);
            throw new \UnexpectedValueException($_language->module['query_failed']);
        }

        return $result;

    } else {
        die();
    }

}

function setLog($query, $message, $file, $line) {

    global $_database;

    if (!validate_int($line, true)) {
        $line = 0;
    }

    $insertQuery = mysqli_query(
        $_database,
        "INSERT INTO `" . PREFIX . "cups_logs`
            (
                `query`,
                `message`,
                `date`,
                `file`,
                `line`
            )
            VALUES
            (
                '" . $query . "',
                '" . $message . "',
                " . time() . ",
                '" . $file . "',
                " . $line . "
            )"
    );

}

function setCupHitsByPage($cup_id, $page) {

    if (!validate_int($cup_id, true)) {
        return '';
    }

    $column = (empty($page) || ($page == 'home')) ?
        'hits' : 'hits_' . $page;

    global $_database;

    $updateQuery = mysqli_query(
        $_database,
        "UPDATE `" . PREFIX . "cups`
            SET `" . $column . "` = `" . $column . "` + 1
            WHERE `cupID` = " . $cup_id
    );

}

function getSponsorsByCupIdAsPanelBody($cup_id) {

    if (!validate_int($cup_id, true)) {
        return '';
    }

    global $_database;

    $selectSponsorsQuery = mysqli_query(
        $_database,
        "SELECT
                cs.`sponsorID`,
                s.`name`,
                s.`url`,
                s.`banner_small`
            FROM `" . PREFIX . "cups_sponsors` cs
            JOIN `" . PREFIX . "sponsors` s ON cs.`sponsorID` = s.`sponsorID`
            WHERE cs.`cupID` = " . $cup_id . " and s.`displayed` = 1"
    );

    if (!$selectSponsorsQuery || (mysqli_num_rows($selectSponsorsQuery) < 1)) {
        return '';
    }

    $content_sponsors = '';
    while ($db = mysqli_fetch_array($selectSponsorsQuery)) {

        $linkAttributeArray = array();
        $linkAttributeArray[] = 'href="' . $db['url'] . '"';
        $linkAttributeArray[] = 'target="_blank"';
        $linkAttributeArray[] = 'title="' . $db['name'] . '"';
        $linkAttributeArray[] = 'class="pull-left"';

        $banner_url = getSponsorImage($db['sponsorID'], true, 'small');

        $content_sponsors .= '<a ' . implode(' ', $linkAttributeArray) . '><img src="' . $banner_url . '" alt="' . $db['name'] . '" /></a>';

    }

    $data_array = array();
    $data_array['$panel_type'] = 'panel-default';
    $data_array['$panel_title'] = 'Sponsoren';
    $data_array['$panel_content'] = $content_sponsors;
    return $GLOBALS["_template_cup"]->replaceTemplate("panel_body", $data_array);

}

function getStreamsByCupIdAsListGroup($cup_id) {

    if (!validate_int($cup_id, true)) {
        return '';
    }

    global $_database;

    $selectSponsorsQuery = mysqli_query(
        $_database,
        "SELECT
                `livID`
            FROM `" . PREFIX . "cups_streams`
            WHERE `cupID` = " . $cup_id
    );

    if (!$selectSponsorsQuery || (mysqli_num_rows($selectSponsorsQuery) < 1)) {
        return '';
    }

    global $_language, $hp_url;

    $content_streams = '';
    while ($db = mysqli_fetch_array($selectSponsorsQuery)) {

        $streamArray = get_streaminfo($db['livID'], '');
        if (validate_array($streamArray, true)) {

            $stream_info = $streamArray['title'];
            if (get_streaminfo($db['livID'], 'online')) {
                $stream_info .= '<span class="pull-right">';
                if (!empty($streamArray['game'])) {
                    $stream_info .= $streamArray['game'].' / ';
                }
                $stream_info .= $streamArray['viewer'].' '.$_language->module['stream_viewer'].'</span>';
            } else {
                $stream_info .= '<span class="pull-right grey italic">offline</span>';
            }

            $linkAttributeArray = array();
            $linkAttributeArray[] = 'href="' . $hp_url . '/index.php?site=streams&amp;action=show&amp;livID='.$db['livID'] . '"';
            $linkAttributeArray[] = 'target="_blank"';
            $linkAttributeArray[] = 'title="' . $streamArray['title'] . '"';
            $linkAttributeArray[] = 'class="list-group-item"';

            $content_streams .= '<a ' . implode(' ', $linkAttributeArray) . '>' . $stream_info . '</a>';

        }

    }

    if (empty($content_streams)) {
        return '';
    }

    $data_array = array();
    $data_array['$panel_type'] = 'panel-default';
    $data_array['$panel_title'] = 'Streams';
    $data_array['$panel_content'] = $content_streams;
    return $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

}

function checkCupDetails($cup_array, $cup_id) {

    global $_language;

    $_language->readModule('cups', true);

    if (!validate_array($cup_array)) {
        throw new \UnexpectedValueException($_language->module['no_cup']);
    }

    if (!isset($cup_array['id']) || ($cup_array['id'] != $cup_id)) {
        throw new \UnexpectedValueException($_language->module['no_cup']);
    }

    global $userID;

    if (($cup_array['admin'] == 1) && !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['no_cup']);
    }

    return TRUE;

}

function getCupStatusContainer($cup_array) {

    global $_language, $loggedin;

    $_language->readModule('cups', true);

    if (!$loggedin) {
        return '<div class="list-group-item alert-info bold center">' . $_language->module['login'] . '</div>';
    }

    if (($cup_array['phase'] == 'running') || ($cup_array['phase'] == 'finished')) {
        return '';
    }

    global $userID;

    $cup_id = $cup_array['id'];

    if (preg_match('/register/', $cup_array['phase'])) {

        //
        // Team Admin: Registrierung
        if (cup_checkin($cup_id, $userID, 'is_registered')) {

            $infoText = ($cup_array['mode'] == '1on1') ?
                'enter_cup_ok_1on1' : 'enter_cup_ok';

            $link = '<div class="list-group-item alert-success center">' . $_language->module[$infoText] . '</div>';

        } else {
            $link = '<a class="list-group-item alert-info bold center" href="index.php?site=cup&amp;action=joincup&amp;id=' . $cup_id . '">' . $_language->module['enter_cup'] . '</a>';
        }

    } else if ($cup_array['phase'] == 'admin_checkin') {

        //
        // Team Admin: Check-In
        if (cup_checkin($cup_id, $userID, 'is_checked_in')) {

            $infoText = ($cup_array['mode'] == '1on1') ?
                'enter_cup_checkin_ok_1on1' : 'enter_cup_checkin_ok';

            $link = '<div class="list-group-item alert-success center">' . $_language->module[$infoText] . '</div>';

        } else if (!cup_checkin($cup_id, $userID, 'is_registered')) {
            $link = '<a class="list-group-item alert-info bold center" href="index.php?site=cup&amp;action=joincup&amp;id=' . $cup_id . '">' . $_language->module['enter_cup'] . '</a>';
        } else {

            $infoText = ($cup_array['mode'] == '1on1') ?
                'checkin_confirm_text_1on1' : 'checkin_confirm_text';

            $data_array = array();
            $data_array['$cup_id'] = $cup_id;
            $data_array['$confirm_text'] = $_language->module[$infoText];
            $data_array['$checkin_mode'] = ($cup_array['mode'] == '1on1') ?
                'Check-In' : 'Team Check-In';
            $link = $GLOBALS["_template_cup"]->replaceTemplate("cup_checkin_policy", $data_array);

        }

    } else if (preg_match('/checkin/', $cup_array['phase'])) {

        //
        // Team: Check-In
        if (cup_checkin($cup_id, $userID, 'is_checked_in')) {
            $link = '<div class="list-group-item alert-success center">' . $_language->module['enter_cup_checkin_ok'] . '</div>';
        } else if (!cup_checkin($cup_id, $userID, 'is_registered')) {
            $link = '<a class="list-group-item alert-info bold center" href="index.php?site=cup&amp;action=joincup&amp;id=' . $cup_id . '">' . $_language->module['enter_cup'] . '</a>';
        } else if ($cup_array['mode'] == '1on1') {

            $infoText = ($cup_array['mode'] == '1on1') ?
                'checkin_confirm_text_1on1' : 'checkin_confirm_text';

            $data_array = array();
            $data_array['$cup_id'] = $cup_id;
            $data_array['$confirm_text'] = $_language->module[$infoText];
            $data_array['$checkin_mode'] = ($cup_array['mode'] == '1on1') ?
                'Check-In' : 'Team Check-In';
            $link = $GLOBALS["_template_cup"]->replaceTemplate("cup_checkin_policy", $data_array);

        } else {
            $link = '<div class="list-group-item alert-info center">' . $_language->module['enter_cup'] . '</div>';
        }

    } else {
        $link = '<a class="list-group-item alert-success bold center" href="index.php?site=teams&action=add">' . $_language->module['add_team'] . '</a>';
    }

    return '<div class="list-group">' . $link . '</div>';

}

function getSelectDateTime($type, $selected = '') {

    $returnArray = array(
        'days' => '',
        'months' => '',
        'years' => '',
        'hours' => '',
        'minutes' => ''
    );

    for ($x = 1; $x < 32; $x++) {
        $value = ($x<10) ?
            '0' . $x : $x;
        $returnArray['days'] .= '<option value="' . $x . '">' . $value . '</option>';
        if ($x<13) {
            $returnArray['months'] .= '<option value="' . $x . '">' . $value . '</option>';
        }
        if ($x<25) {
            $value = (($x - 1)<10) ?
                '0' . ($x - 1) : ($x - 1);
            $returnArray['hours'] .= '<option value="' . ($x - 1) . '">' . $value . '</option>';
        }
    }

    $activeYear = date('Y') + 1;
    for ($x=1960; $x <= $activeYear; $x++) {
        $returnArray['years'] .= '<option value="' . $x . '">' . $x . '</option>';
    }

    $returnArray['minutes'] .= '<option value="00">00</option>';
    $returnArray['minutes'] .= '<option value="15">15</option>';
    $returnArray['minutes'] .= '<option value="30">30</option>';
    $returnArray['minutes'] .= '<option value="45">45</option>';

    if (!empty($selected)) {

        if ($type == 'all') {

            $selectedDate = $selected;
            $actual_minutes = date('i', $selectedDate);

        } else {

            if (isset($returnArray[$type])) {

                $returnArray[$type] = str_replace(
                    'value=""',
                    'value=""',
                    $returnArray[$type]
                );

            }

        }

    } else {

        if (empty($selectedDate) || (validate_int($selectedDate, true))) {
            $selectedDate = time();
        }

        $actual_minutes = date('i');

    }

    $returnArray['days'] = str_replace(
        'value="'.date('j', $selectedDate).'"',
        'value="'.date('j', $selectedDate).'" selected="selected"',
        $returnArray['days']
    );

    $returnArray['months'] = str_replace(
        'value="'.date('n', $selectedDate).'"',
        'value="'.date('n', $selectedDate).'" selected="selected"',
        $returnArray['months']
    );

    $returnArray['years'] = str_replace(
        'value="'.date('Y', $selectedDate).'"',
        'value="'.date('Y', $selectedDate).'" selected="selected"',
        $returnArray['years']
    );

    $returnArray['hours'] = str_replace(
        'value="'.date('G', $selectedDate).'"',
        'value="'.date('G', $selectedDate).'" selected="selected"',
        $returnArray['hours']
    );

    if ($actual_minutes < 15) {
        $minute = '00';
    } else if ($actual_minutes < 30) {
        $minute = '15';
    } else if ($actual_minutes < 45) {
        $minute = '30';
    } else if ($actual_minutes < 59) {
        $minute = '45';
    } else {
        $minute = '00';
    }

    $returnArray['minutes'] = str_replace(
        'value="'.$minute.'"',
        'value="'.$minute.'" selected="selected"',
        $returnArray['minutes']
    );

    if ($type == 'all') {
        return $returnArray;
    } else {

        if (isset($returnArray[$type])) {
            return $returnArray[$type];
        } else {
            return NULL;
        }

    }

}

function getCommentCount($comment_id, $type = 'ne') {

    global $_database;

    $returnValue = 0;

    if (!validate_int($comment_id, true)) {
        return $returnValue;
    }

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                COUNT(*) AS `count`
            FROM `" . PREFIX . "comments`
            WHERE `type` = '".$type."' AND `parentID` = " . $comment_id
    );

    if (!$selectQuery) {
        return $returnValue;
    }

    $get = mysqli_fetch_array($selectQuery);

    return $get['count'];

}

function getsponsor($sponsor_id, $cat = '') {

    if (!validate_int($sponsor_id, true)) {
        return false;
    }

    global $_database;

    $info = mysqli_query(
        $_database,
        "SELECT * FROM `" . PREFIX . "sponsors`
            WHERE `sponsorID` = " . $sponsor_id
    );

    if (!$info) {
        return false;
    }

    if (mysqli_num_rows($info) != 1) {
        return false;
    }

    $db = mysqli_fetch_array($info);

    $returnArray = array(
        'name' => $db['name'],
        'url' => $db['url'],
        'banner' => $db['banner'],
        'banner_small' => $db['banner_small']
    );

    if (empty($cat)) {
        return $returnArray;
    } else {
        return (isset($returnArray[$cat])) ?
            $returnArray[$cat] : '';
    }

}

function getSponsorsAsOptions($selected_id = 0, $addEmptySponsorOption = TRUE, $squadIdToSkipSponsors = 0) {

    global $_database, $_language;

    $_language->readModule('squads', false, true);

    $whereClauseArray = array();

    if (validate_int($squadIdToSkipSponsors, true)) {

        $sponsorQuery = mysqli_query(
            $_database,
            "SELECT
                    sq.`sponsor_id` AS `sponsor_id`
                FROM `" . PREFIX . "squads_sponsor` sq
                WHERE sq.`squad_id` = " . $squadIdToSkipSponsors
        );

        if (!$sponsorQuery) {
            setLog('getSponsorsAsOptions: squads_sponsor_query_select_failed', __FILE__, 1, false);
            return '<option value="0">Query failed.</option>';
        }

        $skipSponsorsArray = array();
        while ($get = mysqli_fetch_array($sponsorQuery)) {
            $skipSponsorsArray[] = $get['sponsor_id'];
        }

        if (validate_array($skipSponsorsArray, true)) {
            $whereClauseArray[] = 'sponsorID NOT IN (' . implode(', ', $skipSponsorsArray) . ')';
        }

    }

    $whereClause = (validate_array($whereClauseArray, true)) ?
        'WHERE ' . implode(' AND ', $whereClauseArray) : '';

    $selectQuery = mysqli_query(
        $_database,
        "SELECT * FROM `" . PREFIX . "sponsors`
            " . $whereClause . "
            ORDER BY `displayed` DESC, `name` ASC"
    );

    if (!$selectQuery) {
        setLog('getSponsorsAsOptions: sponsors_query_select_failed (' . $whereClause . ')', __FILE__, 0, false);
        return '<option value="0">Query failed.</option>';
    }

    $sponsorOptions = '';

    if ($addEmptySponsorOption) {
        $sponsorOptions .= '<option value="0">-- / --</option>';
    }

    if (mysqli_num_rows($selectQuery) < 1) {
        return '<option value="0">' . $_language->module['no_sponsor'] . '</option>';
    }

    while ($get = mysqli_fetch_array($selectQuery)) {
        $sponsorOptions .= '<option value="' . $get['sponsorID'] . '">' . $get['name'] . '</option>';
    }

    if (validate_int($selected_id, true)) {

        $sponsorOptions = str_replace(
            'value="' . $selected_id . '"',
            'value="' . $selected_id . '" selected="selected"',
            $sponsorOptions
        );

    }

    return $sponsorOptions;

}

function getAPIData($json_url, $cat = '') {

    //
    // Open Data URL
    $ch = curl_init();

    if (empty($cat)) {
        return array();
    }

    $allowedCategories = array(
        'twitch'
    );

    if (!in_array($cat, $allowedCategories)) {
        return array();
    }

    //
    // API Keys
    include(__DIR__ . '/../../cup/settings.php');

    if ($cat == 'twitch') {

        if (empty($twitch_client_id)) {
            return array();
        }

        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Client-ID: ' . $twitch_client_id
            )
        );

    }

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $json_url);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;

}

function setPlayerLog($user_id, $parent_id, $text) {

    if (!validate_int($user_id)) {
        return;
    }

    if (!validate_int($parent_id)) {
        return;
    }

    if (empty($text)) {
        return;
    }

    $username = getnickname($user_id);

    global $_database;

    $saveQuery = mysqli_query(
        $_database,
        "INSERT INTO `" . PREFIX . "user_log`
            (
                `userID`,
                `username`,
                `date`,
                `parent_id`,
                `action`
            )
            VALUES
            (
                " . $user_id . ",
                '" . $username . "',
                " . time() . ",
                " . $parent_id . ",
                '" . $text . "'
            )"
    );

}

function setHits($table, $primary_key, $parent_id, $only_trending = FALSE) {

    try {

        if (empty($primary_key)) {
            throw new \UnexpectedValueException('unknown_primary_key');
        }

        if (!validate_int($parent_id, true)) {
            throw new \UnexpectedValueException('unknown_parent_id');
        }

        if (!is_bool($only_trending)) {
            throw new \UnexpectedValueException('unknown_parameter_trending');
        }

        global $_database, $getSite;

        $month = date('n');
        $year = date('Y');

        if ($table == 'cups_matches_playoff') {
            $local_getSite = $table;
        } else if ($getSite == 'sethits') {
            $local_getSite = $table;
        } else {
            $local_getSite = $getSite;
        }

        $whereClauseArray = array();
        $whereClauseArray[] = "site = '" . $local_getSite . "'";
        $whereClauseArray[] = "parent_id = " . $parent_id;
        $whereClauseArray[] = "month = " . $month;
        $whereClauseArray[] = "year = " . $year;

        $whereClause = implode(' AND ', $whereClauseArray);

        $query = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . $table . "`
                SET hits = hits + 1
                WHERE `" . $primary_key . "` = " . $parent_id
        );

        if (!$query) {
            throw new \UnexpectedValueException('update_query_failed');
        }

    } catch (Exception $e) {}

}

function setNotification($receiver_id, $parent_url, $parent_id, $message) {

    if (!validate_int($receiver_id)) {
        return;
    }

    if (empty($parent_url)) {
        return;
    }

    if (!validate_int($parent_id)) {
        return;
    }

    if (empty($message)) {
        return;
    }

    //
    // Receiver ID      = Empfänger
    // Transmitter ID   = Sender

    global $userID;

    $saveQuery = cup_query(
        "INSERT INTO `" . PREFIX . "user_notifications`
            (
                `receiver_id`,
                `transmitter_id`,
                `parent_url`,
                `parent_id`,
                `date`,
                `message`
            )
            VALUES
            (
                " . $receiver_id . ",
                " . $userID . ",
                '" . $parent_url . "',
                " . $parent_id . ",
                " . time() . ",
                '" . $message . "'
            )",
        __FILE__
    );

    if (!$saveQuery) {
        return FALSE;
    }

}

function getParentIdByValue($value_name, $isIntegerParentId = TRUE) {

    global $_language;

    $_language->readModule('index', true, false);

    if (!isset($_GET[$value_name])) {
        return -1;
    }

    if ($isIntegerParentId) {

        if (!validate_int($_GET[$value_name], true)) {
            throw new \UnexpectedValueException($_language->module['unknown_unique_id_type']);
        }

    }

    return (int)$_GET[$value_name];

}

function checkIfContentExists($primary_id, $primary_name, $table) {

    try {

        if (!isset($primary_id)) {
            return FALSE;
        }

        if (!validate_int($primary_id, true)) {

            if ($primary_id == 0) {
                return FALSE;
            }

            throw new \UnexpectedValueException('unknown_parameter_primary_id');

        }

        if (!isset($primary_name) || empty($primary_name)) {
            throw new \UnexpectedValueException('unknown_parameter_primary_name');
        }

        if (!isset($table) || empty($table)) {
            throw new \UnexpectedValueException('unknown_parameter_table');
        }

        global $_database;

        $whereClauseArray = array();
        $whereClauseArray[] = '`' . $primary_name . '` = ' . $primary_id;

        $whereClause = implode(' AND ', $whereClauseArray);

        $query = mysqli_query(
            $_database,
            "SELECT
                    COUNT(*) AS `exist`
                FROM `" . PREFIX . $table . "`
                WHERE " . $whereClause
        );

        if (!$query) {
            throw new \UnexpectedValueException('query_failed (table=' . $table . ', ' . $whereClause . ')');
        }

        $checkIf = mysqli_fetch_array($query);

        if (!$checkIf['exist']) {
            return FALSE;
        } else {
            return TRUE;
        }

    } catch (Exception $e) {
        return FALSE;
    }

}

function updateUserVisitorStatistic($user_id) {

    if (!validate_int($user_id, true)) {
        return;
    }

    global $userID, $_database;

    if (($userID == $user_id) || ($userID < 1)) {
        return;
    }

    $updateQuery = mysqli_query(
        $_database,
        "UPDATE `" . PREFIX . "user`
            SET visits = visits + 1
            WHERE userID = " . $user_id
    );

    $whereClauseArray = array();
    $whereClauseArray[] = '`userID` = ' . $user_id;
    $whereClauseArray[] = '`visitor` = ' . $userID;

    $whereClause = implode(' AND ', $whereClauseArray);

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                `visitID`
            FROM `" . PREFIX . "user_visitors`
            WHERE " . $whereClause
    );

    if (!$selectQuery) {
        return;
    } else {

        $rowExists = mysqli_num_rows($selectQuery);

        $date = time();

        if ($rowExists == 1) {

            $updateQuery = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "user_visitors`
                    SET date = " . $date . "
                    WHERE " . $whereClause
            );

        } else {

            $insertQuery = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "user_visitors`
                    (
                        userID,
                        visitor,
                        date
                    )
                    VALUES
                    (
                        " . $user_id . ",
                        " . $userID . ",
                        " . $date . "
                    )"
            );

        }

    }

}

function getAge($user_id) {

    if (!validate_int($user_id, true)) {
        return 0;
    }

    global $_database;

    $get = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    `birthday`,
                    DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(birthday)), '%Y') 'age'
                FROM `" . PREFIX . "user`
                WHERE userID = " . (int)$user_id
        )
    );

    $age = (int) $get['age'];

    $birthday = strtotime($get['birthday']);
    if (date('Y', $birthday) == 2000) {

        if ((date('d', $birthday) == date('d')) && (date('m', $birthday) == date('m'))) {
            $age++;
        }

    }

    return (int) $age;

}

function getGame($game_id, $cat = '') {

    if (validate_int($game_id)) {
        $whereClause = 'g.`gameID` = ' . $game_id;
    } else {
        $whereClause = 'g.`tag` = \'' . getinput($game_id) . '\'';
    }

    $selectQuery = cup_query(
        "SELECT
                COUNT(`gameID`) AS `exist`,
                g.*
            FROM `" . PREFIX . "games` g
            WHERE " . $whereClause,
        __FILE__
    );

    $get = mysqli_fetch_array($selectQuery);

    if ($get['exist'] != 1) {
        return (empty($cat)) ? array() : '';
    }

    $returnArray = array(
        "id" => $get['gameID'],
        "name" => $get['name'],
        "tag" => $get['tag'],
        "short" => $get['short'],
        "icon" => getGameIcon($get['tag'], true)
    );

    if (empty($cat) || ($cat == 'all')) {
        return $returnArray;
    } else {
        return (isset($returnArray[$cat])) ?
            $returnArray[$cat] : '';
    }

}

function getuserlist($selected = '') {
    global $_database;

    $users = '<option value="0">-- / --</option>';
    $user_id = mysqli_query(
        $_database,
        "SELECT userID, nickname FROM ".PREFIX."user
            WHERE banned IS NULL
            ORDER BY nickname ASC"
    );

    while ($ds = mysqli_fetch_array($user_id)) {
        $users .= '<option value="'.$ds['userID'].'">'.$ds['nickname'].' (#'.$ds['userID'].')</option>';
    }

    if(!empty($selected)) {
        $users = str_replace(
            'value="'.$selected.'"',
            'value="'.$selected.'" selected="selected"',
            $users
        );
    }

    return $users;
}

function getPlayerPosition($position, $cat = '', $game_id = NULL, $addPublicOption = FALSE, $idAsAttribute = FALSE) {

    /*
     * $game_id : NULL, wenn Game nicht beruecksichtigt werden soll
     *				  	>=0, wenn Game einbezogen werden soll
     */

    global $_database;

    if (empty($cat) || ($cat == 'name') || ($cat == 'tag')) {

        $whereClauseArray = array();

        $whereClauseArray[] = (validate_int($position, true)) ?
            'positionID = ' . (int)$position : 'tag = \'' . $position . '\'';

        if (validate_int($game_id, true)) {
            $whereClauseArray[] = 'game_id = ' . $game_id;
        } else if (!validate_int($position, true)) {
            $whereClauseArray[] = 'game_id IS NULL';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = cup_query(
            "SELECT
                    `name`,
                    `tag`,
                    `game_id`
                FROM `" . PREFIX . "user_position_static`
                WHERE " . $whereClause,
            __FILE__
        );

        $ds = mysqli_fetch_array($selectQuery);

        if ($cat == 'tag') {
            return $ds['tag'];
        }

        $returnValue = $ds['name'];

        if (validate_int($ds['game_id'], true)) {
            $returnValue .= ' ' . getGame($ds['game_id'], 'short');
        }

        return $returnValue;

    } else if ($cat == 'list') {

        $returnValue = '';

        if ($addPublicOption) {
            $returnValue .= '<option value="0">Public</option>';
        }

        $whereClauseArray = array();

        $whereClause = (validate_array($whereClauseArray, true)) ?
            'WHERE ' . implode(' AND ', $whereClauseArray) : '';

        $query = cup_query(
            "SELECT
                    ups.`positionID`,
                    ups.`tag`,
                    ups.`name`,
                    ups.`game_id`,
                    g.`tag` AS `game_tag`,
                    g.`short` AS `game_short`
                FROM `" . PREFIX . "user_position_static` ups
                LEFT JOIN `" . PREFIX . "games` g ON ups.`game_id` = g.`gameID`
                " . $whereClause . "
                ORDER BY `sort` ASC",
            __FILE__
        );

        while ($ds = mysqli_fetch_array($query)) {

            $option = $ds['name'];
            if (!is_null($ds['game_id']) && ($ds['game_id'] > 0)) {
                $option .= (!empty($ds['game_short'])) ?
                    ' (' . $ds['game_short'] . ')' :
                    ' (' . $ds['game_tag'] . ')';
            }

            $optionValue = ($idAsAttribute) ?
                $ds['positionID'] : $ds['tag'];

            $returnValue .= '<option value="' . $optionValue . '">' . $option . '</option>';

        }

        $returnValue = selectOptionByValue($returnValue, $position);

        return $returnValue;

    } else if ($cat == 'id') {

        if (empty($position) || (strlen($position) != 2)) {
            return 0;
        }

        $whereClause = 'tag = \'' . $position . '\'';

        if (validate_int($game_id, true)) {
            $whereClause .= ' AND game_id = ' . $game_id;
        } else {
            $whereClause .= ' AND game_id IS NULL';
        }

        $selectQuery = cup_query(
            "SELECT
                    `positionID`
                FROM `" . PREFIX . "user_position_static`
                WHERE " . $whereClause,
            __FILE__
        );

        $ds = mysqli_fetch_array($selectQuery);

        $returnValue = $ds['positionID'];
        return $returnValue;

    } else if ($cat == 'array') {

        $basicArray = array(
            'id' => 0,
            'name' => '',
            'tag' => '',
            'game_id' => 0,
            'sort' => 0
        );

        $whereClauseArray = array();

        if (validate_int($position, true)) {
            $whereClauseArray[] = '`positionID` = ' . $position;
        } else if (strlen($position) == 2) {
            $whereClauseArray[] = '`tag` = \'' . $position . '\'';
        } else {
            throw new \UnexpectedValueException('unknown_parameter_position');
        }

        if (validate_int($game_id, true)) {
            $whereClauseArray[] = '`game_id` = ' . $game_id;
        } else {
            $whereClauseArray[] = '`game_id` IS NULL';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = cup_query(
            "SELECT * FROM `" . PREFIX . "user_position_static`
                WHERE " . $whereClause,
            __FILE__
        );

        $ds = mysqli_fetch_array($selectQuery);

        $basicArray['id'] = $ds['positionID'];
        $basicArray['name'] = $ds['name'];
        $basicArray['tag'] = $ds['tag'];
        $basicArray['game_id'] = $ds['game_id'];
        $basicArray['sort'] = $ds['sort'];

        return $basicArray;

    } else {
        return FALSE;
    }

}

function CommunityID2SteamID($communityid) {

    $steamid = 'STEAM_1:';
    $z = ($communityid - 76561197960265728) / 2;
    $steamid.= ($z - floor($z) == 0.5) ? '1:' : '0:';
    $steamid .= floor($z);
    return $steamid;

}

function SteamID2CommunityID($steamid) {

    try {

        $parts = explode(
            ':',
            str_replace(
                'STEAM_',
                '' ,
                strtoupper($steamid)
            )
        );

        if (count($parts) != 3) {
            throw new \UnexpectedValueException('wrong_steamid_format');
        }

        $unique_id = bcadd(bcadd(bcmul($parts[2] + '', '2'), '76561197960265728'), $parts[1] + '');

        $checkArray = explode('.', $unique_id);
        if (count($checkArray) > 1) {
            $unique_id = $checkArray[0];
        }

        if (strlen($unique_id) != 17) {
            throw new \UnexpectedValueException('wrong_steamid_length');
        }

        return $unique_id;

    } catch (Exception $e) {
        return -1;
    }

}

function getuserposition($position_tag) {

    if (empty($position_tag)) {
        return '';
    }

    global $_database;

    $get = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    `name`
                FROM `" . PREFIX . "user_position_static`
                WHERE tag = '" . $position_tag . "'"
        )
    );

    return $get['name'];

}

function cup_checkin($cup_id, $user_id, $cat = '') {

    global $_database;

    if (!validate_int($cup_id)) {
        return FALSE;
    }

    if (!validate_int($user_id)) {
        return FALSE;
    }

    if ($cat == 'is_registered') {
        $checkinValue = 0;
    } else if ($cat == 'is_checked_in') {
        $checkinValue = 1;
    } else {
        $checkinValue = -1;
    }

    if ($checkinValue < 0) {
        return FALSE;
    }

    $i = 0;

    $cupMode = getcup($cup_id, 'mode');

    if ($cupMode == '1on1') {

        $whereClauseArray = array();
        $whereClauseArray[] = '`cupID` = ' . $cup_id;
        $whereClauseArray[] = '`teamID` = ' . $user_id;
        $whereClauseArray[] = '`checked_in` = ' . $checkinValue;

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    COUNT(*) AS `exist`
                FROM `" . PREFIX . "cups_teilnehmer`
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            return FALSE;
        }

        $checkIf = mysqli_fetch_array($selectQuery);

        return ($checkIf['exist'] == 1) ?
            TRUE : FALSE;

    } else {

        $get_id = mysqli_query(
            $_database,
            "SELECT
                    `teamID`
                FROM `" . PREFIX . "cups_teilnehmer`
                WHERE `cupID` = " . $cup_id . " AND `checked_in` = " . $checkinValue
        );

        while ($te = mysqli_fetch_array($get_id)) {

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            COUNT(*) AS `anz`
                        FROM `".PREFIX."cups_teams_member`
                        WHERE `userID` = " . $user_id . " AND `teamID` = " . $te['teamID'] . " AND `active` = 1"
                )
            );

            if ($get['anz'] > 0) {
                $i++;
            }

        }

        return ($i > 0) ? TRUE : FALSE;

    }

}

/* Cup Informationen */
function getcup($id, $cat = '') {

    $returnValueAsArray = true;

    try {

        if (!validate_int($id, true)) {
            throw new \UnexpectedValueException('error_unknown_parameter_cup_id');
        }

        if (!checkIfContentExists($id, 'cupID', 'cups')) {
            throw new \UnexpectedValueException('unknown_cup (' . $id . ', ' . $cat . ')');
        }

        global $_database, $userID;

        $whereClauseArray = array();
        $whereClauseArray[] = '`cupID` = ' . $id;

        if (($cat == 'anz_teams') || ($cat == 'anz_teams_checkedin')) {

            $returnValueAsArray = false;

            //
            // return : Anzahl Teams im Cup

            if ($cat == 'anz_teams_checkedin') {
                $whereClauseArray[] = '`checked_in` = 1';
            }

            $whereClause = implode(' AND ', $whereClauseArray);

            $selectQuery = mysqli_query(
                $_database,
                "SELECT
                        `teamID`
                    FROM `" . PREFIX . "cups_teilnehmer`
                    WHERE " . $whereClause
            );

            if (!$selectQuery) {
                throw new \UnexpectedValueException('cups_teilnehmer_query_select_failed');
            }

            $anz = mysqli_num_rows($selectQuery);
            return $anz;

        } else if ($cat == 'rand_teams') {

            //
            // return : Array mit zufällig sortierten Teams

            $whereClause = implode(' AND ', $whereClauseArray);

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            `max_size`
                        FROM `" . PREFIX . "cups`
                        WHERE " . $whereClause
                )
            );

            $size = $get['max_size'];

            $whereClauseArray[] = '`checked_in` = 1';
            $whereClause = implode(' AND ', $whereClauseArray);

            $info = mysqli_query(
                $_database,
                "SELECT
                        `teamID`
                    FROM `" . PREFIX . "cups_teilnehmer`
                    WHERE " . $whereClause
            );

            if (!$info) {
                throw new \UnexpectedValueException('cups_teilnehmer_query_select_failed');
            }

            $getTeamCountCheckedIn = mysqli_num_rows($info);

            if ($getTeamCountCheckedIn < 1) {
                return array();
            }

            $arr[] = array();

            $arr = array_fill(0, $size, 0);

            $i=0;
            while ($ds = mysqli_fetch_array($info)) {
                $arr[$i++] = $ds['teamID'];
            }

            shuffle($arr);
            return $arr;

        } else if ($cat != 'all' && !empty($cat)) {

            //
            // return : Spezifische Abfrage der Kategorie

            $whereClause = implode(' AND ', $whereClauseArray);

            $selectQuery = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "cups`
                    WHERE " . $whereClause
            );

            if (!$selectQuery) {
                throw new \UnexpectedValueException('cups_query_select_failed');
            }

            $get = mysqli_fetch_array($selectQuery);

            $returnValue = (isset($get[$cat])) ?
                $get[$cat] : '';

        } else {

            $timeNow = time();

            $whereClause = implode(' AND ', $whereClauseArray);

            $query = cup_query(
                "SELECT * FROM `" . PREFIX . "cups`
                    WHERE " . $whereClause,
                __FILE__
            );

            $get = mysqli_fetch_array($query);

            if (!validate_int($get['cupID'], true)) {
                throw new \UnexpectedValueException('unknown_cup_id (all, ' . $whereClause . ')');
            }

            $cup_id = $get['cupID'];
            $cup_mode = $get['mode'];

            $mode = explode('on', $cup_mode);
            $maxSize = $mode[0];

            $anzRunden = log($get['max_size'], 2);

            if ($get['status'] == 1) {

                //
                // Phase: Anmeldung & Check-In

                $cupPhase = '';
                if ($timeNow <= $get['checkin_date']) {

                    //
                    // Phase: Anmeldung
                    if ($cup_mode == '1on1') {
                        $cupPhase = 'register_player';
                    } else if (isinteam($userID, 0, 'admin')) {
                        $cupPhase = 'admin_register';
                    } else if (isinteam($userID, 0, '')) {
                        $cupPhase = 'register_team';
                    }

                } else if ($timeNow <= $get['start_date']) {

                    //
                    // Phase: Check-In
                    if ($cup_mode == '1on1') {
                        $cupPhase = 'checkin_player';
                    } else if (isinteam($userID, 0, 'admin')) {
                        $cupPhase = 'admin_checkin';
                    } else if (isinteam($userID, 0, '')) {
                        $cupPhase = 'checkin_team';
                    }

                } else {
                    $cupPhase = 'running';
                }

                if (empty($cupPhase)) {
                    $cupPhase = 'no_team';
                }

            } else {

                //
                // Phase: Cup laeuft bereits
                $cupPhase = ($get['status'] < 4) ?
                    'running' : 'finished';

            }

            $hits_total = 0;
            $hits_total += $get['hits'];
            $hits_total += $get['hits_teams'];
            $hits_total += $get['hits_groups'];
            $hits_total += $get['hits_bracket'];
            $hits_total += $get['hits_rules'];

            $settingsArray = array(
                'format' => array(),
                'challonge' => array()
            );

            $settingsArray['challonge']['state'] = $get['challonge_api'];
            $settingsArray['challonge']['url'] = $get['challonge_url'];

            $selectQuery = cup_query(
                "SELECT
                        `round`,
                        `format`
                    FROM `" . PREFIX . "cups_settings`
                    WHERE `cup_id` = " . $cup_id . "
                    ORDER BY `round` ASC",
                __FILE__
            );

            if (mysqli_num_rows($selectQuery) > 0) {

                while ($getFormat = mysqli_fetch_array($selectQuery)) {
                    $settingsArray['format'][$getFormat['round']] = $getFormat['format'];
                }

            }

            $returnValue = array(
                "id"            => $cup_id,
                "name"          => $get['name'],
                "platform"      => $get['platform'],
                "images"        => array(
                    "icon"      => getCupIcon($cup_id, true),
                    "banner"    => getCupBanner($cup_id, true)
                ),
                "registration"  => $get['registration'],        // Registration? Open/Invite/Closed
                "priority"      => $get['priority'],            // Prioritization? normal/main
                "elimination"   => $get['elimination'],         // Elimination? Single/Double/Swiss/...
                "phase"         => $cupPhase,
                "checkin"       => $get['checkin_date'],
                "start"         => $get['start_date'],
                "game"          => $get['game'],                // Game-Tag (e.g. "csg")
                "server"        => $get['server'],              // Server enabeld? 1/0
                "bot"           => $get['bot'],                 // Bot enabled? 1/0
                "map_vote"      => $get['mapvote_enable'],      // Map-Vote? 1/0
                "mappool"       => $get['mappool'],             // Map-Pool ID
                "mode"          => $cup_mode,                   // 1on1, 2on2, 5on5
                "max_mode"      => $maxSize,                    // 1=1on1, 2=2on2, 5=5on5
                "rule_id"       => $get['ruleID'],
                "size"          => $get['max_size'],            // 2, 4, 8, 16, 32, 64
                "teams" => array(
                    "registered" => getcup($cup_id, 'anz_teams'),
                    "checked_in" => getcup($cup_id, 'anz_teams_checkedin')
                ),
                "anz_runden"    => $anzRunden,                  // 1 (2er Bracket), 2 (4er Bracket), 3, 4, 5, 6
                "max_pps"       => $get['max_penalty'],
                "groupstage"    => $get['groupstage'],          // Groupstage enabled? 1/0
                "status"        => $get['status'],              // Cup State: 1=open, 2=groupstage, 3=playoffs, 4=finished
                "hits"          => $hits_total,
                "hits_detail"   => array(
                    "home"      => $get['hits'],
                    "teams"     => $get['hits_teams'],
                    "groups"    => $get['hits_groups'],
                    "bracket"   => $get['hits_bracket'],
                    "rules"     => $get['hits_rules']
                ),
                "settings"      => $settingsArray,
                "description"   => $get['description'],
                "saved"         => $get['saved'],               // Cup is public? 1/0
                "admin"         => $get['admin_visible']        // Admin only? 1/0
            );

        }

    } catch (Exception $e) {

        if ($returnValueAsArray) {
            return array();
        } else {
            return 0;
        }

    }

    return $returnValue;

}
function getcups($cat = '', $selected = '') {
    global $_database, $userID;

    $activeGame = '';

    if (empty($cat)) {

        $where_clause = (!iscupadmin($userID)) ?
            ' WHERE a.admin_visible = \'0\'' : '';

        $ergebnis = mysqli_query(
            $_database,
            "SELECT
                    a.cupID AS cup_id,
                    a.name AS cup_name,
                    a.admin_visible AS cup_isVisible,
                    b.name AS game_name
                FROM `".PREFIX."cups` a
                JOIN `".PREFIX."games` b ON a.gameID = b.gameID
                ".$where_clause."
                ORDER BY a.gameID ASC, a.admin_visible ASC, a.name ASC"
        );

        $returnValue = '';
        while($ds = mysqli_fetch_array($ergebnis)) {

            if(empty($activeGame) || ($activeGame != $ds['game_name'])) {
                if(!empty($activeGame)) {
                    $returnValue .= '</optgroup>';
                }
                $activeGame = $ds['game_name'];
                $returnValue .= '<optgroup label="'.$activeGame.'">';
            }

            $cupName = $ds['cup_name'];
            if($ds['cup_isVisible']) {
                $cupName .= ' (Admin Cup)';
            }
            $returnValue .= '<option value="'.$ds['cup_id'].'">'.$cupName.'</option>';

        }

        $returnValue .= '</optgroup>';

    } else if($cat == 'active_cups') {

        $where_clause = (!iscupadmin($userID)) ? ' AND admin_visible = \'0\'' : '';

        $ergebnis = mysqli_query(
            $_database,
            "SELECT
                    a.cupID AS cup_id,
                    a.name AS cup_name,
                    a.admin_visible AS cup_isVisible,
                    b.name AS game_name
                FROM `".PREFIX."cups` a
                JOIN `".PREFIX."games` b ON a.gameID = b.gameID
                WHERE start_date >= '".time()."'".$where_clause."
                ORDER BY a.gameID ASC, a.admin_visible ASC, a.name ASC"
        );

        $returnValue = '<option value="0">-- / --</option>';
        while($ds = mysqli_fetch_array($ergebnis)) {

            if(empty($activeGame) || ($activeGame != $ds['game_name'])) {
                if(!empty($activeGame)) {
                    $returnValue .= '</optgroup>';
                }
                $activeGame = $ds['game_name'];
                $returnValue .= '<optgroup label="'.$activeGame.'">';
            }

            $cupName = $ds['cup_name'];
            if($ds['cup_isVisible']) {
                $cupName .= ' (Admin Cup)';
            }
            $returnValue .= '<option value="'.$ds['cup_id'].'">'.$cupName.'</option>';

        }

        $returnValue .= '</optgroup>';

    }
    return $returnValue;
}

/* Cup Kontrolle */
function cup($cupID, $teamID, $cat) {

    global $_database;

    if (!validate_int($cupID, true)) {
        return FALSE;
    }

    if (!validate_int($teamID, true)) {
        return FALSE;
    }

    if (empty($cat)) {
        return FALSE;
    }

    $cupMode = getcup($cupID, 'mode');

    if ($cat == 'join') {

        /**
         * Return value:
         * true  = user/team can join cup
         * false = user/team joined cup already
         */

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    COUNT(*) AS `anz`
                FROM `" . PREFIX . "cups_teilnehmer`
                WHERE `cupID` = " . $cupID . " AND `teamID` = " . $teamID
        );

        if (!$selectQuery) {
            return FALSE;
        }

        $get = mysqli_fetch_array($selectQuery);

        $returnValue = TRUE;
        if ($get['anz'] == 1) {
            // Team ist bereits angemeldet
            $returnValue = FALSE;
        } else {

            if ($cupMode == '1on1') {
                return TRUE;
            } else {

                $ergebnis = mysqli_query(
                    $_database,
                    "SELECT
                            `userID`
                        FROM `" . PREFIX . "cups_teams_member`
                        WHERE `teamID` = " . $teamID . " AND `active` = 1"
                );

                if (!$ergebnis) {
                    return FALSE;
                }

                while ($ds = mysqli_fetch_array($ergebnis)) {

                    $team = mysqli_query(
                        $_database,
                        "SELECT
                                `teamID`
                            FROM `" . PREFIX . "cups_teams_member`
                            WHERE userID = ".$ds['userID']." AND active = 1"
                    );

                    if (!$team) {
                        return FALSE;
                    }

                    while ($dx = mysqli_fetch_array($team)) {

                        $get = mysqli_fetch_array(
                            mysqli_query(
                                $_database,
                                "SELECT
                                        COUNT(*) AS `anz`
                                    FROM `" . PREFIX . "cups_teilnehmer`
                                    WHERE `cupID` = " . $cupID . " AND `teamID` = " . $dx['teamID']
                            )
                        );

                        if ($get['anz'] > 0) {
                            // Mitglied des Teams ist bereits beim Cup angemeldet
                            return FALSE;
                        }

                    }
                }

            }

        }

    } else if ($cat == 'gameaccount') {

        $returnValue = TRUE;

        $cup = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT game, mode FROM `".PREFIX."cups`
                    WHERE cupID = ".$cupID
            )
        );

        if($cup['mode'] == '1on1') {

            $user_id = $teamID;

            $where_clause = 'userID = \''.$user_id.'\' AND category = \''.$cup['game'].'\' AND active = \'1\' AND deleted = \'0\'';

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database, "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts`
                        WHERE ".$where_clause
                )
            );

            if($get['anz'] == 0) {
                // Mitglied hat keinen Gameaccount fuer dieses Spiel
                $returnValue = FALSE;
            }

        } else {

            $ergebnis = mysqli_query(
                $_database,
                "SELECT userID FROM `".PREFIX."cups_teams_member`
                    WHERE teamID = ".$teamID." AND kickID = 0 AND active = 1"
            );
            while($ds = mysqli_fetch_array($ergebnis)) {

                $where_clause = 'userID = '.$ds['userID'].' AND category = \''.$cup['game'].'\' AND active = 1 AND deleted = 0';

                $get = mysqli_fetch_array(
                    mysqli_query(
                        $_database, "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts`
                            WHERE ".$where_clause
                    )
                );

                if($get['anz'] == 0) {
                    // Mitglied hat keinen Gameaccount fuer dieses Spiel
                    $returnValue = FALSE;
                    break;
                }
            }

        }

    }
    return $returnValue;
}

/* Team Informationen */
function getteam($id, $cat = '') {

    global $_database, $userID;

    if ($cat == 'admin') {
        $info = mysqli_query(
            $_database,
            "SELECT userID FROM `".PREFIX."cups_teams`
                WHERE teamID = " . $id . " AND deleted = 0"
        );
        if (mysqli_num_rows($info)) {
            $ds = mysqli_fetch_array($info);
            $returnValue = $ds['userID'];
        } else {
            $returnValue = FALSE;
        }
    } else if ($cat == 'teamID') {

        $info = mysqli_query(
            $_database,
            "SELECT
                    `teamID`
                FROM `" . PREFIX . "cups_teams`
                WHERE userID = " . $id . " AND deleted = '0'"
        );

        if (mysqli_num_rows($info)) {
            $IDs = array();
            while ($ds = mysqli_fetch_array($info)) {
                $IDs[] = $ds['teamID'];
            }
            $returnValue = $IDs;
        } else {
            $returnValue = FALSE;
        }

    } else if ($cat == 'anz_matches') {

        $whereClauseArray = array();
        $whereClauseArray[] = '`team1` = ' . $id;
        $whereClauseArray[] = '`team2` = ' . $id;

        $whereClause = implode(' OR ', $whereClauseArray);

        $anz1 = mysqli_num_rows(
            mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "cups_gruppen`
                    WHERE " . $whereClause
            )
        );

        $anz2 = mysqli_num_rows(
            mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE " . $whereClause
            )
        );

        $returnValue = ($anz1 + $anz2);

    } else if ($cat == 'anz_cups') {

        $whereClauseArray = array();
        $whereClauseArray[] = 'ct.`teamID` = ' . $id;
        $whereClauseArray[] = 'ct.`checked_in` = 1';
        $whereClauseArray[] = 'c.`mode` != \'1on1\'';

        $whereClause = implode(' AND ', $whereClauseArray);

        $anz = mysqli_num_rows(
            mysqli_query(
                $_database,
                "SELECT
                        ct.`ID`
                    FROM `" . PREFIX . "cups_teilnehmer` ct
                    LEFT JOIN `" . PREFIX . "cups` c ON ct.`cupID` = c.`cupID`
                    WHERE " . $whereClause
            )
        );

        $returnValue = $anz;

    } else if ($cat == 'anz_member') {
        $anz = mysqli_num_rows(
            mysqli_query(
                $_database,
                "SELECT
                        `userID`
                    FROM `" . PREFIX . "cups_teams_member`
                    WHERE `teamID` = " . $id . " AND active = 1"
            )
        );
        $returnValue = $anz;
    } else if ($cat == 'anz_pps') {
        $anz = mysqli_num_rows(
            mysqli_query(
                $_database,
                "SELECT
                        `ppID`
                    FROM `" . PREFIX . "cups_penalty`
                    WHERE `teamID` = " . $id
            )
        );
        $returnValue = $anz;
    } else if ($cat == 'name_exist' || $cat == 'tag_exist') {

        $whereClauseArray = array();
        $whereClauseArray[] = '`deleted` = 0';

        if ($cat == 'name_exist') {
            $whereClauseArray[] = '`name` = \'' . $id . '\'';
        } else {
            $whereClauseArray[] = '`tag` = \'' . $id . '\'';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    `teamID`
                FROM `" . PREFIX . "cups_teams`
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            return FALSE;
        }

        $anz = mysqli_num_rows($selectQuery);

        $returnValue = ($anz > 0) ? FALSE : TRUE;

    } else if ($cat == 'team_ids') {
        $anz = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `team1`,
                        `team2`
                    FROM `" . PREFIX . "cups_matches_playoff`
                    WHERE `matchID` = " . $id
            )
        );
        $returnValue = array($anz['team1'], $anz['team2']);
    } else {

        $team_id	= 0;
        $date		= '';
        $name		= '';
        $tag		= '';
        $admin_id	= '';
        $anzMember	= 0;
        $hp			= '';
        $logotype	= '';
        $hits		= '';
        $deleted	= '';
        $isVisible 	= 0;
        $userArray  = array();
        $anzMatches = 0;

        if ($id > 0) {

            $info = mysqli_query(
                $_database,
                "SELECT * FROM `".PREFIX."cups_teams`
                    WHERE teamID = " . $id
            );
            if (mysqli_num_rows($info) == 1) {

                $ds = mysqli_fetch_array($info);
                if (($ds['deleted'] == 0) || (iscupadmin($userID))) {
                    $isVisible = 1;
                }

                $team_id	= $ds['teamID'];
                $date		= $ds['date'];
                $name		= $ds['name'];
                $tag		= $ds['tag'];
                $admin_id	= $ds['userID'];
                $anzMember	= getteam($team_id, 'anz_member');
                $hp			= $ds['hp'];
                $logotype	= getCupTeamImage($team_id, true);
                $hits		= $ds['hits'];
                $deleted	= $ds['deleted'];

                $anzMatches = getteam($team_id, 'anz_matches');

                $query = mysqli_query(
                    $_database,
                    "SELECT
                            `userID`
                        FROM `" . PREFIX . "cups_teams_member`
                        WHERE `teamID` = " . $team_id . " AND `active` = 1
                        ORDER BY `position` DESC"
                );

                while ($get = mysqli_fetch_array($query)) {
                    $userArray[] = $get['userID'];
                }

            }

        }

        $returnValue = array(
            "team_id" => $team_id,
            "date" => $date,
            "name" => $name,
            "tag" => $tag,
            "admin_id" => $admin_id,
            "anz_member" => $anzMember,
            "hp" => $hp,
            "logotype" => $logotype,
            "hits" => $hits,
            "deleted" => $deleted,
            "visible" => $isVisible,
            "member" => $userArray,
            "statistic" => array(
                "anz_matches" => $anzMatches
            )
        );

        if (!empty($cat) && isset($returnValue[$cat])) {
            $returnValue = $returnValue[$cat];
        }

    }

    return $returnValue;

}
function getteams($selected = '') {

	global $_database;

	$info = mysqli_query(
		$_database,
		"SELECT teamID, name FROM `".PREFIX."cups_teams`
			ORDER BY name ASC"
	);

	$teams = '<option value="0">-- / --</option>';
	if(mysqli_num_rows($info)) {
		while($ds = mysqli_fetch_array($info)) {
			$teams .= '<option value="'.$ds['teamID'].'">'.$ds['name'].'</option>';
		}
	}

    return selectOptionByValue($teams, $selected, true);

}
function getcupteams($user_id = '', $default = '') {
	global $_database;
	$where_clause = (!empty($user_id)) ? ' WHERE userID = \''.$user_id.'\'' : '';
	$teams = (empty($default)) ? '<option value="0">- / -</option>' : '';
	$team_id = mysqli_query($_database, "SELECT teamID, name FROM `".PREFIX."cups_teams`".$where_clause." ORDER BY name");
	while($ds = mysqli_fetch_array($team_id)) {
		$teams .= '<option value="'.$ds['teamID'].'">'.$ds['name'].' (ID: '.$ds['teamID'].')</option>';
	}
	return $teams;
}
function setCupTeamLog($team_id, $team_name, $text, $kicked_id = 0, $parent_id = 0) {

    global $_database, $userID;

    $saveQuery = mysqli_query(
        $_database,
        "INSERT INTO `" . PREFIX . "cups_teams_log`
            (
                `teamID`,
                `teamName`,
                `date`,
                `user_id`,
                `parent_id`,
                `kicked_id`,
                `action`
            )
            VALUES
            (
                " . $team_id . ",
                '" . $team_name . "',
                " . time() . ",
                " . $userID . ",
                " . $parent_id . ",
                " . $kicked_id . ",
                '" . $text . "'
            )"
    );

}


function getScreenshotCategoriesAsOptions($game_id, $showEmptyOption = FALSE, $selected_id = 0) {

    $returnValue = '';

    if (!validate_int($game_id, true)) {
        return '<option value="0">-- / --</option>';
    }

    if ($showEmptyOption) {
        $returnValue .= '<option value="0">-- / --</option>';
    }

    global $_database;

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                `categoryID`,
                `name`
            FROM `" . PREFIX . "cups_matches_playoff_screens_category`
            WHERE game_id = " . $game_id . "
            ORDER BY `name` ASC"
    );

    if (!$selectQuery) {
        return '<option value="0">-- / --</option>';
    }

    if (mysqli_num_rows($selectQuery) < 1) {
        $returnValue .= '<option value="0">-- / --</option>';
    }

    while ($get = mysqli_fetch_array($selectQuery)) {
        $returnValue .= '<option value="' . $get['categoryID'] . '">' . $get['name'] . '</option>';
    }

    return selectOptionByValue($returnValue, $selected_id, true);

}

/* Gameaccount Informationen */
function gameaccount($userID, $value, $game) {
    global $_database;

    if($value == 'exist') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts`
                    WHERE userID = '".$userID."' AND category = '".$game."' AND deleted = 0"
            )
        );

        $returnValue = ($get['anz'] > 0) ? TRUE : FALSE;

    } elseif($value == 'still_exist') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts`
                    WHERE category = '".$game."' AND value = '".$userID."' AND deleted = 0"
            )
        );

        $returnValue = ($get['anz'] > 0) ? TRUE : FALSE;

    } elseif($value == 'deleted_seen') {

        $updateQuery = mysqli_query(
            $_database,
            "UPDATE `".PREFIX."cups_gameaccounts`
                SET deleted_seen = '1'
                WHERE userID = '".$userID."' AND deleted = '1'"
        );

        $returnValue = ($updateQuery) ? TRUE : FALSE;

    } elseif($value == 'not_active') {

        if(iscupadmin($userID)) {

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts`
                        WHERE active = 0 AND deleted = 1"
                )
            );

            $returnValue = $ds['anz'];

        } else {
            $returnValue = FALSE;
        }

    } elseif($value == 'count') {

        $ds = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts`
                    WHERE userID = ".$userID." AND active = 1 AND deleted = 0"
            )
        );

        $returnValue = $ds['anz'];

    } elseif($value == 'get') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT value FROM `".PREFIX."cups_gameaccounts`
                    WHERE
                            userID = '".$userID."'
                        AND
                            category = '".$game."'
                        AND
                            active = '1'
                        AND
                            deleted = '0'"
            )
        );

        return $get['value'];

    } elseif($value == 'steam64') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT `value` FROM `".PREFIX."cups_gameaccounts`
                    WHERE
                            userID = " . $userID . "
                        AND
                            category = 'csg'
                        AND
                            active = 1
                        AND
                            deleted = 0"
            )
        );

        return $get['value'];

    } elseif($value == 'validated') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        b.validated AS isValidated
                    FROM `".PREFIX."cups_gameaccounts` a
                    JOIN `".PREFIX."cups_gameaccounts_csgo` b ON a.gameaccID = b.gameaccID
                    WHERE a.userID = '".$userID."' AND a.category = '".$game."' AND a.deleted = '0'")
        );

        return $get['isValidated'];

    } else {

        if($game == 'csg' || $game == 'css' || $game == 'cs') {
            $value = strtolower($value);
            $value = str_replace('steam_', '', $value);
        }

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_gameaccounts`
                    WHERE value = '".$value."' AND active = '1' AND deleted = '0'"
            )
        );

        $returnValue = ($get['anz'] > 0) ? TRUE : FALSE;

    }
    return $returnValue;
}
function getCSGOAccountInfo($steam64_id, $multipleAccounts = FALSE) {

    $returnArray = array(
        'status' => FALSE,
        'error' => array(),
        'steam_profile' => array(),
        'vac_status' => array(),
        'csgo_stats' => array()
    );

    try {

        global $userID;

        $steamCommunityIdArray = array();

        if ($multipleAccounts) {
            $steamCommunityIdArray = explode(',', $steam64_id);
        } else {
            $steamCommunityIdArray[] = $steam64_id;
        }

        if(!validate_array($steamCommunityIdArray, true)) {
            throw new \UnexpectedValueException('unknown_steam_id_array');
        }

        $anzSteamAccount = count($steamCommunityIdArray);

        //
        // Steam API Key
        include(__DIR__ . '/../../cup/settings.php');

        if (empty($steam_api_key)) {
            throw new \UnexpectedValueException('unknown_steam_api_key');
        }

        //
        // API URL
        $base_url = 'http://api.steampowered.com/';
        $profile_url = $base_url . 'ISteamUser/GetPlayerSummaries/v0002/?key=' . $steam_api_key . '&steamids=';
        $vac_status_url = $base_url . 'ISteamUser/GetPlayerBans/v1/?key=' . $steam_api_key . '&steamids=';
        $csgo_stats_url = $base_url . 'ISteamUserStats/GetUserStatsForGame/v0002/?appid=730&key=' . $steam_api_key . '&steamid=';

        for ($n = 0; $n < $anzSteamAccount; $n++) {

            $steam64_id = $steamCommunityIdArray[$n];

            if (strlen($steam64_id) != 17) {
                throw new \UnexpectedValueException('wrong_steam64_id_length, ' . $steam64_id);
            }

        }

        $steam64_ids = implode(',', $steamCommunityIdArray);

        //
        // Steam Profile
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $profile_url . $steam64_ids);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($resultArray = @json_decode($result, true)) {

            if ($multipleAccounts) {
                $returnArray['steam_profile'] = (isset($resultArray['response']['players'][0])) ?
                    $resultArray['response']['players'] : array();
            } else {
                $returnArray['steam_profile'] = (isset($resultArray['response']['players'][0])) ?
                    $resultArray['response']['players'][0] : '';
            }

            //
            // VAC Status
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $vac_status_url . $steam64_ids);
            $result = curl_exec($ch);
            curl_close($ch);

            $resultArray = json_decode($result, true);

            if ($multipleAccounts) {
                $returnArray['vac_status'] = (isset($resultArray['players'][0])) ?
                    $resultArray['players'] : array();
            } else {
                $returnArray['vac_status'] = (isset($resultArray['players'][0])) ?
                    $resultArray['players'][0] : '';
            }

            for ($n = 0; $n < $anzSteamAccount; $n++) {

                //
                // CS:GO Stats Status
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $csgo_stats_url . $steamCommunityIdArray[$n]);
                $result = curl_exec($ch);
                curl_close($ch);

                $resultArray = json_decode($result, true);

                $timePlayed = (isset($resultArray['playerstats']['stats'][2])) ?
                    $resultArray['playerstats']['stats'][2] : array('value' => 0);

                $returnArray['csgo_stats'] = array(
                    'time_played'	=> array(
                        'sec'	=> $timePlayed['value'],
                        'hours'	=> (int)($timePlayed['value'] / 60 / 60)
                    )
                );

            }

        } else {
            $returnArray['error'][] = 'bad_request';
        }

        $returnArray['status'] = TRUE;

    } catch (Exception $e) {
        $returnArray['error'][] = $e->getMessage();
    }

    return $returnArray;

}
function updateCSGOGameaccount($user_id) {

    global $_database;

    if (!validate_int($user_id)) {
        return FALSE;
    }

    try {

        $whereClauseArray = array();
        $whereClauseArray[] = 'a.`userID` = ' . $user_id;
        $whereClauseArray[] = 'a.`category` = \'csg\'';
        $whereClauseArray[] = 'a.`active` = 1';

        $whereClause = implode(' AND ', $whereClauseArray);

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS `exist` FROM `".PREFIX."cups_gameaccounts` a
                    WHERE " . $whereClause
            )
        );

        if ($checkIf['exist'] != 1) {
            return;
        }

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    a.`gameaccID` AS `gameaccount_id`,
                    a.`value` AS `steam64_id`
                FROM `" . PREFIX . "cups_gameaccounts` a
                JOIN `" . PREFIX . "cups_gameaccounts_csgo` b ON a.gameaccID = b.gameaccID
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            throw new \UnexpectedValueException('cups_gameaccounts_query_failed_select (' . $whereClause . ')');
        }

        $getAccount = mysqli_fetch_array($selectQuery);

        $gameaccount_id = $getAccount['gameaccount_id'];

        if (!validate_int($gameaccount_id, true)) {
            throw new \UnexpectedValueException('unknown_gameaccount_id');
        }

        $steam64_id = $getAccount['steam64_id'];

        $accountDetails = getCSGOAccountInfo($steam64_id);
        if (!isset($accountDetails['status']) || !$accountDetails['status']) {
            throw new \UnexpectedValueException('getCSGOAccountInfo_failed');
        }

        $hours_played = $accountDetails['csgo_stats']['time_played']['hours'];

        $isBanned = (empty($accountDetails['vac_status']['VACBanned'])) ? 0 : 1;

        $bannDate = 0;
        if ($isBanned) {
            $dateNow = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
            $bannDays = 86400 * $accountDetails['vac_status']['DaysSinceLastBan'];
            $bannDate = $dateNow - $bannDays;
        }

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `anz`
                    FROM `" . PREFIX . "cups_gameaccounts_csgo`
                    WHERE `gameaccID` = " . $gameaccount_id
            )
        );

        if ($get['anz'] != 1) {

            $query = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "cups_gameaccounts_csgo`
                    (
                        `gameaccID`
                    )
                    VALUES
                    (
                        " . $gameaccount_id . "
                    )"
            );

            if (!query) {
                throw new \UnexpectedValueException('query_failed_insert (empty_csgo_account)');
            }

        }

        $insertValuesArray = array();
        $insertValuesArray[] = '`date` = ' . time();
        $insertValuesArray[] = '`vac_bann` = ' . $isBanned;
        $insertValuesArray[] = '`bann_date` = ' . $bannDate;

        if (validate_int($hours_played)) {
            $insertValuesArray[] = '`hours` = ' . $hours_played;
        }

        $insertValues = implode(', ', $insertValuesArray);

        $query = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . "cups_gameaccounts_csgo`
                SET " . $insertValues . "
                WHERE `gameaccID` = " . $gameaccount_id
        );

        if (!$query) {
            throw new \UnexpectedValueException('cups_gameaccounts_csgo_query_failed_update (' . $insertValues . ')');
        }

    } catch(Exception $e) {
        return FALSE;
    }

    return TRUE;

}

/* IMAGE */
function imagemaxsize($file, $maxsize_x, $maxsize_y) {

    $is_size = getimagesize($file);
    $is_size_x = $is_size[0];
    $is_size_y = $is_size[1];

    if (($maxsize_x == $maxsize_y) && ($is_size_x == $is_size_y) && ($is_size_x <= $maxsize_x) && ($is_size_y <= $maxsize_y)) {
        // Bild ist quadratisch
        $returnValue = TRUE;
    } else if (($is_size_x <= $maxsize_x) && ($is_size_y <= $maxsize_y)) {
        // Bild ist kleiner als maximale Seitenlängen
        $returnValue = TRUE;
    } else {
        $returnValue = FALSE;
    }
    return $returnValue;

}
function isimage($file) {
    $imageArray = array(
        'image/jpeg',
        'image/gif',
        'image/png'
    );
    $returnValue = (in_array($file, $imageArray)) ? TRUE : FALSE;
    return $returnValue;
}
function getImage($file) {
    if ($file['type'] === 'image/jpeg') {
        $returnArray['extension'] = '.jpg';
    } else if ($file['type'] === 'image/gif') {
        $returnArray['extension'] = '.gif';
    } else {
        $returnArray['extension'] = '.png';
    }
    return $returnArray;
}

/* Regeln */
function getrules($rule_id = 0, $cat = '', $throwOnFailure = FALSE) {

    global $_database, $_language;

    $_language->readModule('cups', true, false);

    if($cat == 'list') {

        $returnValue = '';

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `exist`
                    FROM `".PREFIX."cups_rules`"
            )
        );

        if($checkIf['exist'] < 1) {

            if($throwOnFailure) {
                throw new \UnexpectedValueException($_language->module['no_rules']);
            } else {
                return '';
            }

        }

        $query = mysqli_query(
            $_database,
            "SELECT
                    a.ruleID AS rule_id,
                    a.name AS name,
                    b.tag AS tag,
                    b.name AS game
                FROM `".PREFIX."cups_rules` a
                JOIN `".PREFIX."games` b ON a.gameID = b.gameID
                ORDER BY a.gameID ASC, a.name ASC"
        );

        while($get = mysqli_fetch_array($query)) {

            if(empty($category) || ($category != $get['tag'])) {

                $category = $get['tag'];

                if (!empty($category)) {
                    $returnValue .= '</optgroup>';
                }

                $returnValue .= '<optgroup label="'.$get['game'].'">';

            }

            $returnValue .= '<option value="'.$get['rule_id'].'">'.$get['name'].'</option>';

        }

        $returnValue .= '</optgroup>';

        $returnValue = selectOptionByValue($returnValue, $rule_id);

    } else {

        if(!validate_int($rule_id)) {
            return array();
        }

        $ds = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT ruleID, name, text, date FROM `".PREFIX."cups_rules`
                    WHERE ruleID = " . $rule_id
            )
        );

        $returnValue = array(
            "rule_id" => $rule_id,
            "name" => $ds['name'],
            "text" => $ds['text'],
            "date" => $ds['date']
        );

        if(!empty($cat)) {

            $returnValue = (isset($returnValue[$cat])) ?
                $returnValue[$cat] : '';

        }

    }

    return $returnValue;

}
function getrulecategories() {
    global $_database;
    $reasons = '';
    $reason_id = mysqli_query(
        $_database,
        "SELECT reasonID, name_de, name_uk, points FROM `".PREFIX."cups_penalty_category`
            ORDER BY points DESC"
    );
    while ($ds = mysqli_fetch_array($reason_id)) {
        $name = $ds['name_de'].' ('.$ds['points'].')';
        $reasons .= '<option value="'.$ds['reasonID'].'">' . $name . '</option>';
    }
    return $reasons;
}

/* Penalty */
function getUserPenalty($user_id) {

    if(!validate_int($user_id)) {
        return 100;
    }

    $time_now = time();

    global $_database;

    $get_pp = mysqli_query(
        $_database,
        "SELECT
                reasonID
            FROM ".PREFIX."cups_penalty
            WHERE duration_time > " . $time_now . " AND userID = " . $user_id . " AND deleted = 0"
    );
    if(mysqli_num_rows($get_pp) < 1) {
        return 0;
    }

    $points = 0;
    while($get = mysqli_fetch_array($get_pp)) {
        $points += getpenalty($get['reasonID'], 'points');
    }

    return $points;

}
function getPenaltyCategories($selected = 0) {

    global $_database, $_language;

    $_language->readModule('teams', true);

    $ds = mysqli_query(
        $_database,
        "SELECT
                `reasonID`,
                `name_de`,
                `lifetime`,
                `points`
            FROM `" . PREFIX . "cups_penalty_category`
            ORDER BY `lifetime` ASC, `points` ASC"
    );

    $returnValue = '';
    while ($get = mysqli_fetch_array($ds)) {

        $name = $get['name_de'];

        if ($get['lifetime'] == 1) {
            $name .= ' (' . $_language->module['lifetime'] . ')';
        } else {
            $name .= ' (' . $get['points'] . ' ' . $_language->module['penalties'] . ')';
        }

        $returnValue .= '<option value="' . $get['reasonID'] . '">' . $name . '</option>"';

    }

    $returnValue = selectOptionByValue($returnValue, $selected);

    return $returnValue;

}
function getPenaltyCategory($id, $cat = '') {

    global $_database;

    $ds = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT * FROM `" . PREFIX . "cups_penalty_category`
                WHERE `reasonID` = " . (int)$id
        )
    );

    $returnValue = array(
        "reason_id"	=> $ds['reasonID'],
        "name_de" => $ds['name_de'],
        "name_de" => $ds['name_uk'],
        "points" => $ds['points'],
        "lifetime" => $ds['lifetime']
    );

    if (!empty($cat)) {
        $returnValue = (isset($returnValue[$cat])) ?
            $returnValue[$cat] : '';
    }

    return $returnValue;

}
function getpenalty($id, $cat = '') {

    global $_database;

    if ($cat == 'team_delete') {

        $memberArray = getteam($id, 'member');

        $whereClause = 'duration_time >= ' . time() . ' AND (teamID = ' . (int)$id;

        if (validate_array($memberArray, true)) {
            $whereClause .= ' OR userID IN (' . implode(',', $memberArray) . ')';
        }

        $whereClause .= ')';

        $ds = mysqli_query(
            $_database,
            "SELECT
                    `reasonID`
                FROM `" . PREFIX . "cups_penalty`
                WHERE " . $whereClause . " AND deleted = 0"
        );

        $reasonArray = array();
        while($info = mysqli_fetch_array($ds)) {
            $reasonArray[] = $info['reasonID'];
        }

        if(count($reasonArray) > 0) {

            $dx = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT SUM(points) AS `counter` FROM `".PREFIX."cups_penalty_category`
                        WHERE reasonID IN (" . implode(',', $reasonArray) . ")"
                )
            );

            $returnValue = $dx['counter'];

        } else {
            $returnValue = 0;
        }

    } else {

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    cp.*,
                    cpc.`points`,
                    cpc.`lifetime`,
                    cpc.`name_de` AS `category_de`,
                    cpc.`name_uk` AS `category_uk`
                FROM `" . PREFIX . "cups_penalty` cp
                LEFT JOIN `" . PREFIX . "cups_penalty_category` cpc ON cp.`reasonID` = cpc.`reasonID`
                WHERE cp.`ppID` = " . (int)$id
        );

        if (!$selectQuery) {
            return (!empty($cat)) ?
                '' : array();
        }

        $ds = mysqli_fetch_array($selectQuery);

        $returnValue = array(
            "penalty_id" => $ds['ppID'],
            "admin_id" => $ds['adminID'],
            "date" => $ds['date'],
            "expires" => $ds['duration_time'],
            "team_id" => $ds['teamID'],
            "user_id" => $ds['userID'],
            "reason_id" => $ds['reasonID'],
            "comment" => $ds['comment'],
            "deleted" => $ds['deleted'],
            "category_name" => array(
                "de" => $ds['category_de'],
                "uk" => $ds['category_uk']
            ),
            "points" => $ds['points'],
            "lifetime" => $ds['lifetime']
        );

        if (!empty($cat)) {
            $returnValue = (isset($returnValue[$cat])) ?
                $returnValue[$cat] : '';
        }

    }

    return $returnValue;

}

/* Map Pool */
function getMaps($pool_id) {

    if (!validate_int($pool_id)) {
        return array();
    }

    global $_database;

    $get = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    `maps`
                FROM `" . PREFIX . "cups_mappool`
                WHERE `mappoolID` = " . $pool_id
        )
    );

    return unserialize($get['maps']);

}
function getMapsAsOptions($pool_id, $selected_id = '', $addEmptyOption = FALSE) {

    if (!validate_int($pool_id)) {
        return '<option value="0">- / -</option>';
    }

    $mapArray = getMaps($pool_id);

    $returnValue = '';

    if ($addEmptyOption) {
        $returnValue .= '<option value="0">- / -</option>';
    }

    $anzMaps = count($mapArray);

    if ($anzMaps < 1) {
        return '<option value="0">- / -</option>';
    }

    for ($x = 0; $x < $anzMaps; $x++) {
        $map = $mapArray[$x];
        $returnValue .= '<option value="' . $map . '">' . $map . '</option>';
    }

    $returnValue = selectOptionByValue($returnValue, $selected_id);

    return $returnValue;

}
function getMappool($pool_id = 0, $cat = 'list') {

    global $_language;

    $_language->readModule('cups', true);

    $returnValue = '';

    if ($cat == 'list') {

        $query = cup_query(
            "SELECT
                a.mappoolID AS pool_id,
                a.name AS name,
                a.maps AS maps,
                b.tag AS tag,
                b.name AS game
            FROM `".PREFIX."cups_mappool` a
            JOIN `".PREFIX."games` b ON a.gameID = b.gameID
            ORDER BY game ASC, name ASC",
            __FILE__
        );

        $returnValue .= '<option value="0">'.$_language->module['no_mappool2'].'</option>';

        $category = '';
        while ($get = mysqli_fetch_array($query)) {

            if (empty($category) || ($category != $get['tag'])) {
                $category = $get['tag'];
                if (!empty($category)) {
                    $returnValue .= '</optgroup>';
                }
                $returnValue .= '<optgroup label="'.$get['game'].'">';
            }

            $mapArray = unserialize($get['maps']);
            $anzMaps = count($mapArray);
            $returnValue .= '<option value="'.$get['pool_id'].'">'.$get['name'].' ('.$anzMaps.' Maps)</option>';

        }

        $returnValue .= '</optgroup>';

        $returnValue = selectOptionByValue($returnValue, $pool_id);

    }

    return $returnValue;

}

/**
 * Prizes
 */
function savePrize($cup_id, $prize, $placement) {

    if (!validate_int($cup_id, true)) {
        throw new \UnexpectedValueException('unknown_cup_id');
    }

    if (empty($prize)) {
        throw new \UnexpectedValueException('unknown_prize');
    }

    if (!validate_int($placement, true)) {
        throw new \UnexpectedValueException('unknown_placemenet');
    }

    $insertQuery = cup_query(
        "INSERT INTO `" . PREFIX . "cups_prizes`
            (
                `cup_id`,
                `prize`,
                `placement`
            )
            VALUES
            (
                " . $cup_id . ",
                '" . $prize . "',
                " . $placement . "
            )",
        __FILE__
    );

}

/**
 * Support
 */
function ticket($userID, $ticketID, $cat) {
    global $_database;

    $returnValue = FALSE;

    if (($cat == 'user') || ($cat == 'admin')) {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `userID`,
                        `adminID`
                    FROM `" . PREFIX . "cups_supporttickets`
                    WHERE `ticketID` = " . $ticketID
            )
        );
        $returnValue = ($userID == $get['userID']) ? TRUE : FALSE;
        if (!$returnValue) {
            $returnValue = ($userID == $get['adminID']) ? TRUE : FALSE;
        }

    }

    return $returnValue;

}
function getticket($ticketID, $cat) {
    global $_database;
    $returnValue = '';
    if ($cat == 'name') {
        $get = mysqli_fetch_array(
            mysqli_query($_database, "SELECT name FROM `".PREFIX."cups_supporttickets`  WHERE ticketID = '".$ticketID."'")
        );
        $returnValue = $get['name'];
    } else if ($cat == 'category') {
        $get = mysqli_fetch_array(
            mysqli_query($_database, "SELECT name_de FROM `".PREFIX."cups_supporttickets_category` WHERE categoryID = '".$ticketID."'")
        );
        $returnValue = $get['name_de'];
    } else if ($cat == 'admin') {
        $get = mysqli_fetch_array(
            mysqli_query($_database, "SELECT adminID FROM `".PREFIX."cups_supporttickets`  WHERE ticketID = '".$ticketID."'")
        );
        $returnValue = $get['adminID'];
    } else if ($cat == 'new_answer') {
        $return_value = 0;
        /*
        $anz = mysqli_num_rows(
            mysqli_query($_database, "SELECT ticketID FROM `".PREFIX."cups_supporttickets_content`  WHERE ticketID = '".$ticketID."' AND new = '1'")
        );
        if($anz) {
            $return_value++;
        }
        */
        $returnValue = $return_value;
    } else if ($cat == 'new_answer_admin') {
        $return_value = 0;
        /*
        $anz = mysqli_num_rows(
            mysqli_query($_database, "SELECT ticketID FROM `".PREFIX."cups_supporttickets_content`  WHERE ticketID = '".$ticketID."' AND new_admin = '1'")
        );
        if($anz) {
            $return_value++;
        }
        */
        $returnValue = $return_value;
    } else if ($cat == 'anz_admin_tickets') {
        $returnValue = mysqli_num_rows(
            mysqli_query($_database, "SELECT adminID FROM `".PREFIX."cups_supporttickets`  WHERE adminID = '".$ticketID."'")
        );
    } else if ($cat == 'status') {
        $returnValue = mysqli_num_rows(
            mysqli_query($_database, "SELECT ticketID FROM `".PREFIX."cups_supporttickets`  WHERE status = '".$ticketID."'")
        );
    }
    return $returnValue;
}
function getticket_anz($status, $user_id, $cat) {

    global $_database;

    $returnValue = 0;

    $whereClauseArray = array();

    if ($cat == 'anz_new_answer') {

        $whereClause = '(userID = '.$user_id;

        $teamArray = getteam($user_id, 'teamID');
        if (validate_array($teamArray, true)) {

            $teamString = implode(', ', $teamArray);

            $whereClause .= ' OR teamID IN (' . $teamString . ')';
            $whereClause .= ' OR opponentID IN (' . $teamString . ')';

        }

        $whereClause .= ')';

        $whereClauseArray[] = $whereClause;

    } else if ($cat == 'anz_new_answer_admin') {

        if (validate_int($user_id, true)) {
            $whereClauseArray[] = '`adminID` = ' . $user_id;
        }

    } else {
        return -1;
    }

    if (validate_int($status, true)) {
        $whereClauseArray[] = '`status` = ' . $status;
    } else {
        $whereClauseArray[] = '`status` < 3';
    }

    if (!validate_array($whereClauseArray, true)) {
        return -1;
    }

    $whereClause = implode(' AND ', $whereClauseArray);

    $query = mysqli_query(
        $_database,
        "SELECT
                `ticketID`,
                `categoryID`,
                `status`
            FROM `" . PREFIX . "cups_supporttickets`
            WHERE " . $whereClause
    );

    if (!$query) {
        return -1;
    }

    $baseWhereClauseArray = array();
    $baseWhereClauseArray[] = '(`ticket_seen_date` IS NULL OR `ticket_seen_date` < ' . time() . ')';

    while ($ds = mysqli_fetch_array($query)) {

        $ticket_id = $ds['ticketID'];
        $admin = ($cat == 'anz_new_answer_admin') ?
            1 : 0;

        $whereClauseArray = array();
        $whereClauseArray[] = '`ticket_id` = ' . $ticket_id;
        $whereClauseArray[] = '`primary_id` = ' . $user_id;
        $whereClauseArray[] = '`admin` = ' . $admin;

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    `ticket_seen_date`
                FROM `" . PREFIX . "cups_supporttickets_status`
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            return -1;
        }

        $status_counter = mysqli_num_rows($selectQuery);

        $statusGet = mysqli_fetch_array($selectQuery);

        /**
         * Es muss noch der letzte Ticket Content geholt werden (Datum) und verglichen werden anstelle des time()
         */

        if ($status_counter == 0) {
            insertTicketStatus($ticket_id, $user_id, $admin, -1);
            $returnValue++;
        } else {

            $whereClauseArray = array();
            $whereClauseArray[] = '`ticketID` = ' . $ticket_id;

            $whereClause = implode(' AND ', $whereClauseArray);

            $selectQuery = mysqli_query(
                $_database,
                "SELECT
                        `date`
                    FROM `" . PREFIX . "cups_supporttickets_content`
                    WHERE " . $whereClause . "
                    ORDER BY date DESC
                    LIMIT 0, 1"
            );

            if (!$selectQuery) {
                return -1;
            }

            $contentGet = mysqli_fetch_array($selectQuery);

            if (is_null($statusGet['ticket_seen_date']) || ($statusGet['ticket_seen_date'] < $contentGet['date'])) {
                $returnValue++;
            }

        }

    }

    return $returnValue;

}

function getticketcategories($selected = '', $addEmptyOption = TRUE) {

    global $_database;

    $categories = '';

    if ($addEmptyOption) {
       $categories .= '<option value="0" class="italic">-- / --</option>';
    }

    $info = mysqli_query(
        $_database,
        "SELECT categoryID, name_de FROM `".PREFIX."cups_supporttickets_category`
        ORDER BY name_de ASC"
    );

    if (!$info) {
        return $categories;
    }

    if (mysqli_num_rows($info) < 1) {
        return $categories;
    }

    while($ds = mysqli_fetch_array($info)) {
        $categories .= '<option value="' . $ds['categoryID'] . '">' . $ds['name_de'] . '</option>';
    }

    if (validate_int($selected, true)) {

        $categories = str_replace(
            'value="' . $selected . '"',
            'value="' . $selected . '" selected="selected"',
            $categories
        );

    }

    return $categories;

}
function getTicketAccess($ticket_id) {

    global $_database, $userID;

    $returnValue = FALSE;

    $teamArray = getteam($userID, 'teamID');

    $andWhereClauseArray = array();
    $andWhereClauseArray[] = '`ticketID` = '.$ticket_id;

    $orWhereClauseArray = array();
    $orWhereClauseArray[] = '`userID` = ' . $userID;

    if (validate_array($teamArray, true)) {

        $teamString = implode(', ', $teamArray);

        $orWhereClauseArray[] = '`teamID` IN ('.$teamString.')';
        $orWhereClauseArray[] = '`opponentID` IN ('.$teamString.')';

    }

    $andWhereClauseArray[] = '(' . implode(' OR ', $orWhereClauseArray) . ')';

    $whereClause = implode(' AND ', $andWhereClauseArray);

    $query = mysqli_query(
        $_database,
        "SELECT
                COUNT(*) AS `access`
            FROM `" . PREFIX . "cups_supporttickets`
            WHERE " . $whereClause
    );

    if (!$query) {
        return FALSE;
    }

    $get = mysqli_fetch_array($query);
    $returnValue = $get['access'];

    return $returnValue;

}
function getTicketSeenDate($ticket_id, $primary_id, $admin = 0) {

    global $_database, $_language;

    $whereClauseArray = array();
    $whereClauseArray[] = '`ticket_id` = ' . $ticket_id;
    $whereClauseArray[] = '`primary_id` = ' . $primary_id;
    $whereClauseArray[] = '`admin` = ' . $admin;

    $whereClause = implode(' AND ', $whereClauseArray);

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                COUNT(*) AS `exists`,
                `ticket_seen_date`
            FROM `" . PREFIX . "cups_supporttickets_status`
            WHERE " . $whereClause
    );

    if (!$selectQuery) {
        throw new \UnexpectedValueException($_language->module['query_select_failed']);
    }

    $checkIf = mysqli_fetch_array($selectQuery);

    if ($checkIf['exists'] != 1) {
        return -1;
    } else {
        return $checkIf['ticket_seen_date'];
    }

}
function insertTicketStatus($ticket_id, $primary_id, $admin = 0, $date = 1) {

    global $_database, $_language;

    if ($date == 1) {
        $ticket_seen_attribute = ', `ticket_seen_date`';
        $ticket_seen_date = ', ' . (time() + 20);
    } else if (validate_int($date, true) && ($date > 1)) {
        $ticket_seen_attribute = ', `ticket_seen_date`';
        $ticket_seen_date = ', ' . $date;
    } else {
        $ticket_seen_attribute = '';
        $ticket_seen_date = '';
    }

    $insertQuery = mysqli_query(
        $_database,
        "INSERT INTO `" . PREFIX . "cups_supporttickets_status`
            (
                `ticket_id`,
                `primary_id`,
                `admin`
                " . $ticket_seen_attribute . "
            )
            VALUES
            (
                " . $ticket_id . ",
                " . $primary_id . ",
                " . $admin . "
                " . $ticket_seen_date . "
            )"
    );

    if (!$insertQuery) {
        throw new \UnexpectedValueException($_language->module['query_insert_failed']);
    }

}
function setTicketSeenDate($ticket_id, $primary_id, $admin = 0) {

    global $_database, $_language;

    $whereClauseArray = array();
    $whereClauseArray[] = '`ticket_id` = ' . $ticket_id;
    $whereClauseArray[] = '`primary_id` = ' . $primary_id;
    $whereClauseArray[] = '`admin` = ' . $admin;

    $whereClause = implode(' AND ', $whereClauseArray);

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                COUNT(*) AS `exists`
            FROM `" . PREFIX . "cups_supporttickets_status`
            WHERE " . $whereClause
    );

    if (!$selectQuery) {
        throw new \UnexpectedValueException($_language->module['query_select_failed']);
    }

    $checkIf = mysqli_fetch_array($selectQuery);

    if ($checkIf['exists'] != 1) {
        insertTicketStatus($ticket_id, $primary_id, $admin);
    }

    $updateQuery = mysqli_query(
        $_database,
        "UPDATE `" . PREFIX . "cups_supporttickets_status`
            SET `ticket_seen_date` = " . time() . "
            WHERE " . $whereClause
    );

    if (!$updateQuery) {
        throw new \UnexpectedValueException($_language->module['query_update_failed']);
    }

}

function getSupportTemplatesAsOptions($select_template = '') {

    $supportTemplateArray = array(
        'default',
        'bug_report',
        'gameaccount',
        'match_protest',
        'support'
    );

    $options = '';
    foreach ($supportTemplateArray as $template) {
        $options .= '<option value="' . $template . '">' . $template . '</option>';
    }

    if (!empty($select_template)) {
        $options = str_replace(
            'value="' . $select_template . '"',
            'value="' . $select_template . '" selected="selected"',
            $options
        );
    }

    return $options;

}

/* Awards */
function getawardcat($award_id, $cat = '') {

    $selectQuery = cup_query(
        $_database,
        "SELECT * FROM `" . PREFIX . "cups_awards_category`
            WHERE `awardID` = " . $award_id,
        __FILE__
    );

    $get = mysqli_fetch_array($selectQuery);

    $returnArray = array(
        'id' => $get['awardID'],
        'name' => $get['name'],
        'icon' => $get['icon'],
        'platzierung' => $get['platzierung'],
        'anz_matches' => $get['anz_matches'],
        'anz_cups' => $get['anz_cups'],
        'description' => $get['description']
    );

    if (empty($cat) || ($cat == 'all')) {
        return $returnArray;
    } else {
        return (isset($returnArray[$cat])) ?
            $returnArray[$cat] : '';
    }

}

/* Cup Options */
function getCupOption($cat = '') {

    global $_language;
    $_language->readModule('cups', true, true);

    if ($cat == 'size') {
        $returnValue = '';
        $returnValue .= '<option value="2">2</option>';
        $returnValue .= '<option value="4">4</option>';
        $returnValue .= '<option value="8">8</option>';
        $returnValue .= '<option value="16">16</option>';
        $returnValue .= '<option value="20" disabled="disabled">20</option>';
        $returnValue .= '<option value="30" disabled="disabled">30</option>';
        $returnValue .= '<option value="32">32</option>';
        $returnValue .= '<option value="64">64</option>	';
    } else if ($cat == 'penalty') {
        $returnValue = '';
        $returnValue .= '<option value="0">0</option>';
        $returnValue .= '<option value="6">6</option>';
        $returnValue .= '<option value="12">12</option>';
        $returnValue .= '<option value="18">18</option>';
        $returnValue .= '<option value="24">24</option>';
    } else if ($cat == 'mode') {
        $returnValue = '';
        $returnValue .= '<option value="1on1">1on1</option>';
        $returnValue .= '<option value="2on2">2on2</option>';
        $returnValue .= '<option value="3on3">3on3</option>';
        $returnValue .= '<option value="4on4">4on4</option>';
        $returnValue .= '<option value="5on5">5on5</option>';
        $returnValue .= '<option value="11on11">11on11</option>';
    } else if ($cat == 'csg_rounds') {
        $returnValue = '';
        $returnValue .= '<option value="18">18 (MR9)</option>';
        $returnValue .= '<option value="30" selected="selected">30 (MR15)</option>';
    } else if ($cat == 'csg_overtime') {
        $returnValue = '';
        $returnValue .= '<option value="10_16">10 (MR5) - 16.000$</option>';
        $returnValue .= '<option value="6_16">6 (MR3) - 16.000$</option>';
        $returnValue .= '<option value="6_10">6 (MR3) - 10.000$</option>';
    } else if ($cat == 'registration') {
        $returnValue = '';
        $returnValue .= '<option value="open">' . $_language->module['open'] . '</option>';
        $returnValue .= '<option value="invite">' . $_language->module['invite'] . '</option>';
        $returnValue .= '<option value="closed">' . $_language->module['closed'] . '</option>';
    } else if ($cat == 'rounds') {
        $returnValue = '';
        $returnValue .= '<option value="bo1">Best-of-One (Bo1)</option>';
        $returnValue .= '<option value="bo3">Best-of-Three (Bo3)</option>';
        $returnValue .= '<option value="bo5">Best-of-Five (Bo5)</option>';
        $returnValue .= '<option value="bo7">Best-of-Seven (Bo7)</option>';
    } else if ($cat == 'priority') {
        $returnValue = '';
        $returnValue .= '<option value="normal">' . $_language->module['normal'] . '</option>';
        $returnValue .= '<option value="main">' . $_language->module['main'] . '</option>';
    } else if ($cat == 'elimination') {
        $returnValue = '';
        $returnValue .= '<option value="single">' . $_language->module['single_elimination'] . '</option>';
        $returnValue .= '<option value="double" disabled="disabled">' . $_language->module['double_elimination'] . '</option>';
    } else if ($cat == 'platform') {
        $returnValue = '';
        $returnValue .= '<option value="PC">' . $_language->module['pc'] . '</option>';
        $returnValue .= '<option value="Xbox">' . $_language->module['xbox'] . '</option>';
        $returnValue .= '<option value="PlayStation 4">' . $_language->module['playstation4'] . '</option>';
        $returnValue .= '<option value="iOS">' . $_language->module['ios'] . '</option>';
        $returnValue .= '<option value="Android">' . $_language->module['android'] . '</option>';
        $returnValue .= '<option value="Nintendo Switch">' . $_language->module['nintendo_switch'] . '</option>';
    } else {

        $returnValue = array(
            'size' => getCupOption('size'),
            'penalty' => getCupOption('penalty'),
            'mode' => getCupOption('mode'),
            'csg_rounds' => getCupOption('csg_rounds'),
            'csg_overtime' => getCupOption('csg_overtime'),
            'registration' => getCupOption('registration'),
            'priority' => getCupOption('priority'),
            'elimination' => getCupOption('elimination'),
            'rounds' => getCupOption('rounds'),
            'platform' => getCupOption('platform'),
            'true_false' => '<option value="1">' . $_language->module['yes'] . '</option><option value="0">' . $_language->module['no'] . '</option>'
        );

    }

    return $returnValue;

}

/* STATISTICS */
function getStatistic($cat, $minValue, $maxValue, $anz = 5, $arrayAsReturn = TRUE) {

    global $_database;

    $returnArray = array();

    if ($cat == 'anz_cups') {

        $query = mysqli_query(
            $_database,
            "SELECT
                  COUNT(*) AS count_anz,
                  teamID AS team_id
                FROM `" . PREFIX . "cups_teilnehmer`
                WHERE checked_in = 1
                GROUP BY teamID
                HAVING (COUNT(*) < " . $maxValue . " AND COUNT(*) > " . $minValue . ")
                ORDER BY COUNT(*) DESC"
        );

        while($get = mysqli_fetch_array($query)) {

            $name = '';
            if($get['team_id'] > 0) {
                $name = getteam($get['team_id'], 'name');
            }

            $returnArray[] = array(
                'id'    => $get['team_id'],
                'name'  => $name,
                'count' => $get['count_anz']
            );

        }

    } else if($cat == 'anz_matches') {
        /**
         * WiP
         */
    } else if($cat == 'platzierung') {
        /**
         * WiP
         */
    }

    return $returnArray;

}

/* CONVERT */
function convert2gameaccount($game_tag, $value) {

    $replaceArray = array(
        'csg',
        'css',
        'cs'
    );

    $value = trim($value);

    if (in_array($game_tag, $replaceArray)) {
        $value = str_replace('steam_', '', strtolower($value));
    } else if (substr(strtolower($value), 0, 6) == 'steam_') {
        $value = str_replace('steam_', '', strtolower($value));
    }

    return $value;

}
