<?php

if (!isset($bracket)) {
	$bracket = '';
}

try {
	
	if (!iscupadmin($userID)) {
		throw new \Exception($_language->module['access_denied']);
	} 
	   
	if (!isset($cup_id)) {
		throw new \Exception($_language->module['unknown_cup']);
	}

	if (!isset($round_id)) {
		if(isset($i)) {
			$round_id = $i;
		} else {
			$round_id = 0;
		}
	}

	if ($round_id < 1) {
		throw new \Exception($_language->module['unknown_round']);
    }

    $ergebnis = mysqli_query(
        $_database, 
        "SELECT * FROM `" . PREFIX . "cups_matches_playoff` 
            WHERE cupID = " . $cup_id . " AND runde = " . $round_id . " 
            ORDER BY matchID ASC, wb DESC"
    );
    while($ds = mysqli_fetch_array($ergebnis)) {

        $matchID = $ds['matchID'];

        $match_url = $cup_url . '/index.php?site=cup&amp;action=match&amp;id=' . $cup_id . '&amp;mID=' . $matchID;

        if ($ds['active'] == 0) {
            $border_class = 'class="list-group-item"';	
        } else if ($ds['team1_confirmed'] == 1 && $ds['team2_confirmed'] == 0 && $ds['admin_confirmed'] == 0) {
            $border_class = 'class="list-group-item alert-warning"';	
        } else if ($ds['team1_confirmed'] == 0 && $ds['team2_confirmed'] == 1 && $ds['admin_confirmed'] == 0) {
            $border_class = 'class="list-group-item alert-warning"';	
        } else if (($ds['team1_confirmed'] == 1 && $ds['team2_confirmed'] == 1) || $ds['admin_confirmed'] == 1) {
            $border_class = 'class="list-group-item alert-success"';	
        } else if ($ds['mapvote'] == 0) {
            $border_class = 'class="list-group-item alert-danger"';	
        } else {
            $border_class = 'class="list-group-item alert-info"';	
        }

        $teamArray = array(
            0 => '',
            1 => ''
        );

        for ($teamIndex = 1; $teamIndex < 3; $teamIndex++) {

            if ($ds['team'.$teamIndex] != 0) { 

                if($cupArray['mode'] == '1on1') {
                    $teamArray[$teamIndex] = getnickname($ds['team'.$teamIndex]); 
                } else {
                    $teamArray[$teamIndex] = getteam($ds['team'.$teamIndex], 'name'); 
                }

            } else if ($ds['team'.$teamIndex.'_freilos'] == 1) { 
                $teamArray[$teamIndex] = $_language->module['cup_freilos']; 
            } else { 
                $teamArray[$teamIndex] = '<span class="italic">unknown</span>'; 
            }

        }

        $serverArray = unserialize($ds['server']);

        $serverInfoArray = array();

        if (isset($serverArray['ip']) && !empty($serverArray['ip'])) {
            $serverInfoArray[] = 'IP: '.$serverArray['ip'];
        }

        if (isset($serverArray['password']) && !empty($serverArray['password'])) {
            $serverInfoArray[] = 'PW: '.$serverArray['password'];
        }

        if (isset($serverArray['rcon']) && !empty($serverArray['rcon'])) {
            $serverInfoArray[] = 'RCON: '.$serverArray['rcon'];
        }

        if (isset($serverArray['gotv']) && !empty($serverArray['gotv'])) {
            $serverInfoArray[] = 'GOTV IP: '.$serverArray['gotv'];
        }

        if (isset($serverArray['gotv_pw']) && !empty($serverArray['gotv_pw'])) {
            $serverInfoArray[] = 'GOTV PW: '.$serverArray['gotv_pw'];
        }

        $server_info = implode(' / ', $serverInfoArray);

        $bracket_color = ($ds['wb']) ? 'btn-success' : 'btn-danger';
        $bracket_text = ($ds['wb']) ? 'WB' : 'LB';

        $info = '';
        $info .= '<span class="pull-right">';

        $anzComments = getCommentAnz($matchID, 'cm');
        $anzComments = ($anzComments != 1) ? 
            $anzComments.' '.$_language->module['comments'] : 
            $anzComments.' '.$_language->module['comment'];
        $info .= '<a target="_blank" href="'.$match_url.'#comments" class="btn btn-default btn-sm">'.$anzComments.'</a> ';	

        if ($ds['team1_freilos'] || $ds['team2_freilos']) {
            $info .= '<span class="btn btn-default btn-sm">Def-Win</span> ';	
        }
        $info .= '<span class="btn '.$bracket_color.' btn-sm white darkshadow uppercase">'.$bracket_text.'</span> ';	
        $info .= ' <input type="button" class="btn btn-info btn-sm white darkshadow" value="Admin Details" onclick="openAdminDetails('.$matchID.');" /> ';	
        $info .= ' <a target="_blank" class="btn btn-info btn-sm white darkshadow" href="'.$match_url.'">Match ansehen</a>';
        $info .= '</span>';
        $info .= '<span class="pull-left black">'.$teamArray[1].' : '.$teamArray[2].'</span><br /><span class="pull-left fontsmall">'.$server_info.'</span>';

        $data_array = array();
        $data_array['$border_class'] = $border_class;
        $data_array['$info'] = $info;
        $bracket .= $GLOBALS["_template_cup"]->replaceTemplate("cup_match_details", $data_array);

        unset($info);

    }

} catch (Exception $e) {
	$bracket .= showError($e->getMessage(), true);
}
