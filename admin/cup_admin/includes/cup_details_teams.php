<?php

try {

    if (!isset($content)) {
        $content = '';
    }

    if (!isset($cupArray) || !validate_array($cupArray, true)) {
        throw new \Exception($_language->module['unknown_action']);
    }

    $teams = '';

    $teamQuery = cup_query(
        "SELECT
                `teamID`,
                `checked_in`,
                `date_register`,
                `date_checkin`
            FROM `".PREFIX."cups_teilnehmer`
            WHERE `cupID` = " . $cup_id . "
            ORDER BY `checked_in` DESC, `date_checkin` ASC, `date_register` ASC",
        __FILE__
    );

    if (mysqli_num_rows($teamQuery) > 0) {

        $profile_url = $hp_url . '/index.php?site=profile&id=';

        $n = 1;
        while ($db = mysqli_fetch_array($teamQuery)) {

            if ($db['checked_in'] == 1) {
                $status = '<span class="btn btn-success btn-xs">' . $_language->module['checkin_ok'] . '</span> ';
            } else {
                $status = '<span class="btn btn-default btn-xs">' . $_language->module['register_ok'] . '</span>';
            }

            //
            // max_mode = 1, wenn Cup Modus 1on1
            if ($cupArray['max_mode'] == 1) {

                $user_id = $db['teamID'];
                $team_name = getnickname($user_id);

                $admin_info = '';
                $cup_counter = '';
                $match_counter = '';

                $detail_url = $profile_url . $db['teamID'];
                $delete_team_from_cup = 'deleteTeam_' . $cup_id . '_' . $user_id;

            } else {

                $team_id = $db['teamID'];

                $teamArray = getteam($team_id);
                $team_name = $teamArray['name'];

                $admin_info = '<a href="' . $profile_url . $teamArray['admin_id'] . '#content" class="blue" target="_blank">' . getnickname($teamArray['admin_id']) . '</a>';
                $cup_counter = getteam($team_id, 'anz_cups');
                $match_counter = getteam($team_id, 'anz_matches');

                $detail_url = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;teamID=' . $team_id;
                $delete_team_from_cup = 'deleteTeam_' . $cup_id . '_' . $team_id;

            }

            $data_array = array();
            $data_array['$n'] = $n++;
            $data_array['$team_name'] = $team_name;
            $data_array['$admin_info'] = $admin_info;
            $data_array['$status'] = $status;
            $data_array['$date_register'] = ($db['date_register'] > 0) ?
                getformatdatetime($db['date_register']) : '';
            $data_array['$date_checkin'] = ($db['date_checkin'] > 0) ?
                getformatdatetime($db['date_checkin']) : '';
            $data_array['$cup_counter'] = $cup_counter;
            $data_array['$match_counter'] = $match_counter;
            $data_array['$detail_url'] = $detail_url;
            $data_array['$delete_team_from_cup'] = $delete_team_from_cup;
            $teams .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_team_list", $data_array);

        }

    } else {

        $no_participants = ($cupArray['max_mode'] == 1) ?
            $_language->module['no_player'] : $_language->module['no_team'];

        $teams = '<tr><td colspan="9">' . $no_participants . '</td></tr>';

    }

    $data_array = array();
    $data_array['$cupID'] = $cup_id;
    $data_array['$teams'] = $teams;
    $data_array['$add_participant_action'] = ($cupArray['max_mode'] == 1) ?
        'playeradd' : 'teamadd';
    $data_array['$add_participant_text'] = ($cupArray['max_mode'] == 1) ?
        $_language->module['add_player'] : $_language->module['add_team2'];
    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_details_teams", $data_array);

} catch (Exception $e) {
    $content .= showError($e->getMessage());
}
