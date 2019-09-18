<?php

try {

    $_language->readModule('page_stats', false, true);
    $_language->readModule('cups', true, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    $maxEntries = 10;

    $base_cup_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=';
    $base_profile_url = 'admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id=';
    $base_team_url = $hp_url . '/index.php?site=teams&amp;action=details&amp;id=';
    $base_userlog_url = 'admincenter.php?site=cup&amp;mod=gameaccounts&amp;action=log&amp;user_id=';

    $statisticPageArray = array(
        'cups',
        'teams',
        'matches',
        'gameaccounts',
        'support'
    );

    foreach($statisticPageArray as $subpage) {

        $filePath = __DIR__ . '/includes/page_stats_' . $subpage . '.php';
        if (file_exists($filePath)) {
            include($filePath);
        }

    }

    $adminlist = '';

    $data_array = array();
    $data_array['$cups_detailed_stats_list'] = $cups_detailed_stats_list;
    $data_array['$cuphit_list'] = $cuphit_list;
    $data_array['$cupChartHits'] = $cupChartHits;
    $data_array['$cupteams_list'] = $cupteams_list;
    $data_array['$cupteam_list'] = $cupteam_list;
    $data_array['$match_detailed_stats_list'] = $match_detailed_stats_list;
    $data_array['$matchhit_list'] = $matchhit_list;
    $data_array['$matchanz_list_team'] = $matchanz_list_team;
    $data_array['$matchanz_list_player'] = $matchanz_list_player;
    $data_array['$gameacc_act_list'] = $gameacc_act_list;
    $data_array['$gameaccChartRows'] = $gameaccChartRows;
    $data_array['$gameacc_del_list'] = $gameacc_del_list;
    $data_array['$gameacc_csgo_list'] = $gameacc_csgo_list;
    $data_array['$gameacc_acc_min'] = $gameacc_acc_list[0];
    $data_array['$gameacc_acc_max'] = $gameacc_acc_list[1];
    $data_array['$gameaccCSGOValidateRows'] = $gameaccCSGOValidateRows;
    $data_array['$teams_detailed_stats_list'] = $teams_detailed_stats_list;
    $data_array['$teams_list'] = $teams_list;
    $data_array['$team_member_list'] = $team_member_list;
    $data_array['$ticket_adm_list'] = $ticket_adm_list;
    $data_array['$ticket_usr_list'] = $ticket_usr_list;
    $data_array['$ticket_cat_list'] = $ticket_cat_list;
    $data_array['$adminlist'] = $adminlist;
    $stats_home = $GLOBALS["_template_cup"]->replaceTemplate("page_stats_home", $data_array);
    echo $stats_home;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
