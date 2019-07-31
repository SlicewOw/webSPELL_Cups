<?php

try {

    if (!isset($content)) {
        $content = '';
    }

    if (!isset($cupArray) || !validate_array($cupArray, true)) {
        throw new \Exception($_language->module['access_denied']);
    }

    $navi_teams = 'btn-info white darkshadow';

    $teams = '';

    if (isset($cupArray['settings']['challonge']['state']) && ($cupArray['settings']['challonge']['state'] == 1)) {

        $challonge_api = getChallongeApiObject();
        $challonge_id = getChallongeTournamentId($cup_id);

        $participants = $challonge_api->getParticipants($challonge_id);
        $participantArray = $participants->participant;

        $panel_content = '';

        foreach ($participantArray as $participant) {

            $data_array = array();
            $data_array['$url'] = '#';
            $data_array['$name'] = $participant->name;
            $data_array['$team_info'] = $participant->name;
            $panel_content .= $GLOBALS["_template_cup"]->replaceTemplate("teams_list", $data_array);

        }

        $panel_title = ($cupArray['mode'] == '1on1') ? 'player_checked_in' : 'teams_registered';

        $data_array = array();
        $data_array['$panel_type'] = 'panel-default';
        $data_array['$panel_title'] = $_language->module[$panel_title];
        $data_array['$panel_content'] = $panel_content;
        $teams .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

    } else {

        if (getcup($cup_id, 'anz_teams') == 0) {
            $teams = '<div class="panel panel-default"><div class="panel-body">' . $_language->module['no_team'] . '</div></div>';
        } else {

            for ($x = 0; $x < 2; $x++) {

                //
                // 1: Teams (Checked-In)
                // 0: Teams (Registered)
                $isChecked = ($x == 0) ? 1 : 0;

                if ($cupArray['mode'] == '1on1') {
                    $teamsTitle = ($x == 0) ? 'player_checked_in' : 'player_registered';
                } else {
                    $teamsTitle = ($x == 0) ? 'teams_checked_in' : 'teams_registered';
                }

                $team = cup_query(
                    "SELECT
                            `teamID`
                        FROM `" . PREFIX . "cups_teilnehmer`
                        WHERE `cupID` = " . $cup_id . " AND `checked_in` = " . $isChecked,
                    __FILE__
                );

                if (mysqli_num_rows($team) > 0) {

                    $panel_content = '';

                    if ($cupArray['mode'] == '1on1') {

                        while ($db = mysqli_fetch_array($team)) {

                            $user_id = $db['teamID'];

                            $url = 'index.php?site=profile&id=' . $user_id . '#content';

                            $data_array = array();
                            $data_array['$url'] = $url;
                            $data_array['$name'] = getnickname($user_id);
                            $data_array['$team_info'] = getnickname($user_id);
                            $panel_content .= $GLOBALS["_template_cup"]->replaceTemplate("teams_list", $data_array);

                        }

                    } else {

                        while ($db = mysqli_fetch_array($team)) {

                            $team_id = $db['teamID'];

                            $teamArray = getteam($team_id, '');
                            $url = 'index.php?site=teams&amp;action=details&amp;id=' . $team_id;
                            $name = $teamArray['name'];
                            $logo = $teamArray['logotype'];

                            $team_info = '<img src="' . $teamArray['logotype'] . '" class="img-rounded" alt="" width="16" height="16" />';
                            $team_info .= '<span style="margin: 0 0 0 10px">' . $name . '</span>';

                            $data_array = array();
                            $data_array['$url'] = $url;
                            $data_array['$name'] = $name;
                            $data_array['$team_info'] = $team_info;
                            $panel_content .= $GLOBALS["_template_cup"]->replaceTemplate("teams_list", $data_array);

                        }

                    }

                    $data_array = array();
                    $data_array['$panel_type'] = 'panel-default';
                    $data_array['$panel_title'] = $_language->module[$teamsTitle];
                    $data_array['$panel_content'] = $panel_content;
                    $teams .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

                }

            }

        }

    }

    $content .= $teams;

} catch (Exception $e) {
    $content .= showError($e->getMessage());
}
