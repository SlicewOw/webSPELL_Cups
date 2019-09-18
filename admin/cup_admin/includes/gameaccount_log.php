<?php

try {

    $_language->readModule('gameaccounts', false, true);

    if (!iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $user_id = (isset($_GET['user_id']) && validate_int($_GET['user_id'])) ?
        (int)$_GET['user_id'] : 0;

    if ($user_id < 1) {
        throw new \UnexpectedValueException($_language->module['unknown_user']);
    }

    $csgoGameaccount = array(
        'list' => array(),
        'data' => array()
    );

    $gameaccount_list = '';

    $query = mysqli_query(
        $_database,
        "SELECT * FROM `" . PREFIX . "cups_gameaccounts`
            WHERE `userID` = " . $user_id . "
            ORDER BY `active` DESC, `category` ASC, `date` DESC"
    );

    if (!$query) {
        throw new \UnexpectedValueException($_language->module['query_failed']);
    }

    $anz = mysqli_num_rows($query);
    if($anz > 0) {

        $i = 1;
        while($ds = mysqli_fetch_array($query)) {

            $steam64_id = $ds['value'];

            if($ds['category'] == 'csg' && !in_array($steam64_id, $csgoGameaccount['list'])) {

                $csgoGameaccount['data'][] = array(
                    'active' => $ds['active'],
                    'steam_id' => $ds['value'],
                    'steam64_id' => $steam64_id
                );

                $csgoGameaccount['list'][] = $steam64_id;

            }

            if ($ds['active'] == 1) {
                $status = '<span class="btn btn-success btn-xs">'.$_language->module['active'].'</span>';
            } else {

                $base_url = 'admincenter.php?site=cup&amp;mod=gameaccounts';
                $url = $base_url . '&amp;action=active&amp;id=' . $ds['gameaccID'];

                $status = '<a class="btn btn-info btn-xs white darkshadow" href="'.$url.'">'.$_language->module['inactive'].'</a>';

            }

            if ($ds['deleted'] == 1) {
                $status = '<span class="btn btn-danger btn-xs">gel&ouml;scht</span>';
            }

            if ($ds['category'] == 'csg') {

                $get = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT `validated` FROM `".PREFIX."cups_gameaccounts_csgo`
                            WHERE `gameaccID` = " . $ds['gameaccID']
                    )
                );

                $status .= ($get['validated']) ?
                    $_language->module['validate_done'] : $_language->module['validate_missing'];

            }

            if($ds['smurf'] == 1) {
                $status .= ' <span class="btn btn-default btn-xs">' . $_language->module['smurf'] . '</span>';
            }

            $actions = '';

            if ($ds['deleted'] == 1) {

                $changeSmurfTXT = (!$ds['smurf']) ? $_language->module['smurf'].'?' : $_language->module['no_smurf'].'?';
                $actions .= ' <button type="button" onclick="changeSmurfStatus(' . $ds['gameaccID'] . ');" class="btn btn-info btn-xs white darkshadow">'.$changeSmurfTXT.'</button>';

                $actions .= ' <button type="button" onclick="deleteGameacc('.$ds['gameaccID'].');" class="btn btn-default btn-xs">' . $_language->module['delete_completely'] . '</button>';

            }

            $gameaccount_list .= '<tr id="gameaccountList'.$ds['gameaccID'].'">
                <td>'.$i.'</td>
                <td>'.$ds['gameaccID'].'</td>
                <td>'.$ds['value'].'</td>
                <td>'.getgamename($ds['category']).'</td>
                <td>'.getformatdatetime($ds['date']).'</td>
                <td>'.$status.'</td>
                <td>'.$actions.'</td>
            </tr>';

            $i++;
        }

    } else {
        $gameaccount_list = '<tr><td colspan="7">' . $_language->module['no_gameaccount'] . '</td></tr>';
    }

    $csgo_list = '';

    $anzCSGOAccounts = count($csgoGameaccount['list']);

    $teams = '';
    $log = '';

    $teamLink = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;teamID=';

    $anzTeams = 0;

    $query = mysqli_query(
        $_database,
        "SELECT
                a.teamID AS team_id,
                a.join_date AS join_date,
                a.left_date AS left_date,
                a.active AS isActivePlayer,
                a.kickID AS isKickedBy,
                b.name AS name
            FROM `" . PREFIX . "cups_teams_member` a
            JOIN `" . PREFIX . "cups_teams` b ON a.teamID = b.teamID
            WHERE a.userID = " . $user_id . "
            GROUP BY a.teamID"
    );
    while ($get = mysqli_fetch_array($query)) {

        $logText = '';
        if ($get['isActivePlayer']) {

            $teams .= '<a href="'.$teamLink.$get[getConstNameTeamIdWithUnderscore()].'" target="_blank" class="list-group-item">'.$get['name'].'</a>';
            $logText = getformatdatetime($get['join_date']);

            $anzTeams++;

        } else {

            $leftText = ($get['isKickedBy'] > 0) ?
                $_language->module['team_kicked_since'] : $_language->module['team_left_since'];

            $logText .= str_replace(
                '%days%',
                convert2days($get['left_date']),
                $leftText
            );
            $logText .=  ' - ' . getformatdatetime($get['left_date']);

        }

        $log .= '<a href="'.$teamLink.$get[getConstNameTeamIdWithUnderscore()].'" target="_blank" class="list-group-item">';
        $log .= $get['name'];
        $log .= '<span class="pull-right">'.$logText.'</span>';
        $log .= '</a>';

    }

    $query = mysqli_query(
        $_database,
        "SELECT * FROM `".PREFIX."cups_gameaccounts_profiles`
            WHERE user_id = " . $user_id . " AND deleted = 0
            ORDER BY category ASC, date ASC"
    );
    $anzProfiles = mysqli_num_rows($query);
    if ($anzProfiles > 0) {

        $i = 1;

        $profileList = '';
        while ($get = mysqli_fetch_array($query)) {

            $category = '<a href="" target="_blank"></a>';

            $data_array = array();
            $data_array['$index'] = $i++;
            $data_array['$user_id'] = $user_id;
            $data_array['$profile_id'] = $get['profileID'];
            $data_array['$category'] = $get['category'];
            $data_array['$url'] = $get['url'];
            $data_array['$date'] = getformatdate($get['date']);
            $profileList .= $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_log_profiles_list", $data_array);

        }

    } else {
        $profileList = '<tr><td colspan="4">' . $_language->module['no_profiles'] . '</td></tr>';
    }

    $data_array = array();
    $data_array['$profileList'] = $profileList;
    $data_array['$user_id'] = $user_id;
    $profiles = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_log_profiles", $data_array);

    $active_tab = 'teams';
    if (isset($_GET['profiles'])) {
        $active_tab = 'profiles';
    }

    $data_array = array();
    $data_array['$image_url'] = $image_url;
    $data_array['$games'] = getGamesAsOptionList('csg');
    $data_array['$user_id'] = $user_id;
    $data_array['$username'] = getnickname($user_id);
    $data_array['$gameaccount_list'] = $gameaccount_list;
    $data_array['$csgoGameaccountCheck'] = ($anzCSGOAccounts > 0) ? 'true' : 'false';
    $data_array['$active_tab'] = $active_tab;
    $data_array['$teams'] = $teams;
    $data_array['$log'] = $log;
    $data_array['$profiles'] = $profiles;
    $data_array['$anzTeams'] = $anzTeams;
    $data_array['$anzProfiles'] = $anzProfiles;
    $gameaccount_log = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_log", $data_array);
    echo $gameaccount_log;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
