<?php

try {

    // Cup System written by SlicewOw - myRisk
    // Copyright (c) by SlicewOw
    $_language->readModule('cups_home');

    if (validate_array($_POST, true)) {

        if (isset($_POST['confirmPolicyButton'])) {

            $cup_id = (int)$_POST['cupCupID'];
            $team_id = (int)$_POST['cupTeamID'];

            $query = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "cups_teilnehmer`
                    (
                        `cupID`,
                        `teamID`
                    )
                    VALUES
                    (
                        " . $cup_id . ",
                        " . $team_id . "
                    )"
            );

        } else if (isset($_POST['submitRegisterLogoff'])) {

            $cup_id = (int)$_POST['cupCupID'];
            $team_id = (int)$_POST['team_id'];

            $query = mysqli_query(
                $_database,
                "DELETE FROM `" . PREFIX . "cups_teilnehmer`
                    WHERE `cupID` = " . $cup_id . " AND `teamID` = " . $team_id
            );

        }

        header('Location: index.php');

    } else {

        $cupAdminAccess = (iscupadmin($userID)) ?
            true : false;

        $upcomingCup = '';
        $upcomingCupList = '';

        $whereClauseArray = array();
        $whereClauseArray[] = '`status` < 4';
        $whereClauseArray[] = '`saved` = 1';

        if (!$cupAdminAccess) {
            $whereClauseArray[] = '`admin_visible` = 0';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $query = mysqli_query(
            $_database,
            "SELECT 
                    `cupID`, 
                    `admin_visible` 
                FROM `" . PREFIX . "cups`
                WHERE " . $whereClause . "
                ORDER BY `start_date` ASC
                LIMIT 0, 10"
        );

        if (!$query) {
            throw new \Exception($_language->module['query_select_failed']);
        }

        while ($getCup = mysqli_fetch_array($query)) {

            $cup_id = $getCup['cupID'];

            $cupArray = getcup($cup_id);
            $getGame = getGame($cupArray['game']);

            $detailList = '';

            $detailList .= '<div class="list-group-item">';
            $detailList .= '<img src="' . $image_url . '/games/'.$cupArray['game'].'.gif" alt="'.$getGame['name'].'" class="box-margin-right" /> ';
            $detailList .= $getGame['name'];
            $detailList .= '</div>';

            $timeLeft = 0;
            $timeNow = time();

            if (preg_match('/register/', $cupArray['phase'])) {
                $timeLeft = $cupArray['checkin'] - $timeNow;
            } else if (preg_match('/checkin/', $cupArray['phase']) || $cupArray['phase'] == 'finished') {
                $timeLeft = $cupArray['start'] - $timeNow;
            } else if (($cupArray['start'] - $timeNow) > 0) {
                $timeLeft = $cupArray['start'] - $timeNow;
            }

            $detailList .= '<div class="list-group-item">' . $_language->module['teams_registered'] . ': ' . $cupArray['teams']['registered'] . ' / '.$cupArray['size'].'</div>';
            $detailList .= '<div class="list-group-item">' . $_language->module['teams_checked_in'] . ': ' . $cupArray['teams']['checked_in'] . ' / '.$cupArray['size'].'</div>';

            $listClass = empty($upcomingCup) ? ' alert-info' : '';

            if(empty($upcomingCup)) {

                $detailList .= '<div class="list-group-item">Check-In: '.getformatdatetime($cupArray['checkin']).'</div>';
                $detailList .= '<div class="list-group-item">Start: '.getformatdatetime($cupArray['start']).'</div>';

                $detailList .= '<div class="list-group-item">';
                $detailList .= '<a class="btn btn-info btn-sm white darkshadow" href="index.php?site=cup&amp;action=details&amp;id='.$cup_id.'">';
                $detailList .= $_language->module['goto_cup'];
                $detailList .= '</a>';
                $detailList .= '</div>';

                $data_array = array();
                $data_array['$timeLeft'] = $timeLeft;
                $data_array['$status'] = str_replace(
                    '%cup_id%',
                    $cup_id,
                    $_language->module['status_'.$cupArray['phase']]
                );
                $data_array['$cupName'] = $cupArray['name'];
                $data_array['$detailList'] = $detailList;
                $upcomingCup = $GLOBALS["_template_cup"]->replaceTemplate("home_upcomingcup", $data_array);

            }

            $upcomingCupList .= '<a href="javascript:changeCup('.$cup_id.');" id="cupList'.$cup_id.'" class="cupList list-group-item'.$listClass.'" title="'.$cupArray['name'].'">';
            $upcomingCupList .= '<img src="' . $image_url . '/games/'.$cupArray['game'].'.gif" alt="'.$getGame['name'].'" class="box-margin-right" /> ';
            $upcomingCupList .= $cupArray['name'];
            $upcomingCupList .= '</a>';

        }

        $pastCups = '';

        $teamLink = 'index.php?site=teams&amp;action=details&amp;id=';
        $userLink = 'index.php?site=profile&amp;id=';

        $whereClauseArray = array();
        $whereClauseArray[] = '`status` = 4';
        $whereClauseArray[] = '`saved` = 1';

        if (!$cupAdminAccess) {
            $whereClauseArray[] = '`admin_visible` = 0';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $query = mysqli_query(
            $_database, 
            "SELECT 
                    `cupID` AS `cup_id`,
                    `name` AS `cup_name`,
                    `game` AS `game_tag`,
                    `mode` AS `mode`
                FROM `" . PREFIX . "cups`
                WHERE " . $whereClause . "
                ORDER BY `start_date` DESC, `cupID` ASC
                LIMIT 0, 5"
        );

        if (!$query) {
            throw new \Exception($_language->module['query_select_failed']);
        }

        while ($get = mysqli_fetch_array($query)) {

            $cup_id = $get['cup_id'];

            $teamArray = array(
                array(
                    'name'		=> '',
                    'tag'		=> '',
                    'logotype'	=> '',
                    'id'		=> 0,
                    'visible'	=> 'display: none;'
                ),
                array(
                    'name'		=> '',
                    'tag'		=> '',
                    'logotype'	=> '',
                    'id'		=> 0,
                    'visible'	=> 'display: none;'
                ),
                array(
                    'name'		=> '',
                    'tag'		=> '',
                    'logotype'	=> '',
                    'id'		=> 0,
                    'visible'	=> 'display: none;'
                )
            );

            if ($get['mode'] == '1on1') {
                $subquery = mysqli_query(
                    $_database,
                    "SELECT 
                            a.teamID AS team_id,
                            a.platzierung AS platzierung,
                            b.nickname AS team_name,
                            b.nickname AS team_tag
                        FROM `".PREFIX."cups_platzierungen` a
                        JOIN `".PREFIX."user` b ON a.teamID = b.userID
                        WHERE a.cupID = '".$cup_id."' AND (a.platzierung = '1' OR a.platzierung = '2' OR a.platzierung = '3')
                        ORDER BY a.platzierung ASC"
                );
            } else {
                $subquery = mysqli_query(
                    $_database,
                    "SELECT
                            a.teamID AS team_id,
                            a.platzierung AS platzierung,
                            b.name AS team_name,
                            b.tag AS team_tag
                        FROM `" . PREFIX . "cups_platzierungen` a
                        JOIN `" . PREFIX . "cups_teams` b ON a.teamID = b.teamID
                        WHERE a.cupID = '".$cup_id."' AND (a.platzierung = '1' OR a.platzierung = '2' OR a.platzierung = '3')
                        ORDER BY a.platzierung ASC"
                );
            }
            while ($subget = mysqli_fetch_array($subquery)) {

                if(strlen($subget['team_name']) > 14) {
                    $name = $subget['team_tag'];
                } else {
                    $name = $subget['team_name'];
                }

                $teamArray[$subget['platzierung'] - 1]['name'] = $name;
                $teamArray[$subget['platzierung'] - 1]['id'] = $subget['team_id'];
                $teamArray[$subget['platzierung'] - 1]['tag'] = $subget['team_tag'];
                $teamArray[$subget['platzierung'] - 1]['visible'] = '';

                if ($get['mode'] == '1on1') {
                    $description = ' alt="'.$subget['team_name'].'" title="'.$subget['team_name'].'"';
                    $logotype = getuserpic($subget['team_id'], true);
                } else {
                    $description = ' alt="'.$subget['team_name'].'" title="'.$subget['team_name'].'"';
                    $logotype = getCupTeamImage($subget['team_id'], true);
                }

                $teamArray[$subget['platzierung'] - 1]['logotype'] = '<img src="' . $logotype . '"' . $description . ' style="width: 50px; height: 50px; margin: 0 auto; display: block; border-radius: 50px;" />';

            }

            $data_array = array();
            $data_array['$image_url'] = $image_url;
            $data_array['$cupLink'] = 'index.php?site=cup&amp;action=details&amp;id='.$cup_id;
            $data_array['$cupName'] = $get['cup_name'];
            $data_array['$isVisible1'] = $teamArray[0]['visible'];
            $data_array['$teamLink1'] = ($get['mode'] == '1on1') ? 
                $userLink.$teamArray[0]['id'] : $teamLink.$teamArray[0]['id'];
            $data_array['$teamLogotype1'] = $teamArray[0]['logotype'];
            $data_array['$teamName1'] = $teamArray[0]['name'];
            $data_array['$isVisible2'] = $teamArray[1]['visible'];
            $data_array['$teamLink2'] = ($get['mode'] == '1on1') ? 
                $userLink.$teamArray[1]['id'] : $teamLink.$teamArray[1]['id'];
            $data_array['$teamLogotype2'] = $teamArray[1]['logotype'];
            $data_array['$teamName2'] = $teamArray[1]['name'];
            $data_array['$isVisible3'] = $teamArray[2]['visible'];
            $data_array['$teamLink3'] = ($get['mode'] == '1on1') ? 
                $userLink.$teamArray[2]['id'] : $teamLink.$teamArray[2]['id'];
            $data_array['$teamLogotype3'] = $teamArray[2]['logotype'];
            $data_array['$teamName3'] = $teamArray[2]['name'];
            $pastCups .= $GLOBALS["_template_cup"]->replaceTemplate("home_pastcups", $data_array);

        }

        $data_array = array();
        $data_array['$image_url'] = $image_url;
        $data_array['$upcomingCup'] = $upcomingCup;
        $data_array['$upcomingCupList'] = $upcomingCupList;
        $data_array['$pastCups'] = $pastCups;
        $home = $GLOBALS["_template_cup"]->replaceTemplate("home", $data_array);
        echo $home;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
