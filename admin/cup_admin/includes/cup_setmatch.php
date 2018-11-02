<?php

$returnArray = array(
	'status' => FALSE,
	'message' => 'unknown action'
);

try {
    
    //
    // Server verfügbar?
    $value = array(
        'action' => 'setmatch',
        'anz' => 0,
        'maps' => array()
    );
    
    if (!isset($match_id)) {
        throw new \Exception('unknown_match');
    }
    
    if (!isset($cup_id)) {
        throw new \Exception('unknown_cup_id');
    }
    
    if (!isset($valueArray) || !validate_array($valueArray, true)) {
        throw new \Exception('unknown_values');
    }

    //
    // Match Data
    $matchArray = getmatch($match_id);

    if ($cup_id != $matchArray['cup_id']) {
        throw new \Exception('unknown_cup');
    }

    //
    // Cup Data
    $cupArray = getcup($cup_id);

    //
    // Server Password
    $server_password = RandPass($password_length);

    if (!$matchArray['mapvote']) {
        throw new \Exception('error_nothing_to_vote');
    }

    $mapArray = unserialize($matchArray['maps']);

    $anzMaps = count($mapArray['picked']);

    if ($anzMaps < 1) {
        throw new \Exception('error_map_veto');
    }
    
    $value['anz'] = $anzMaps;
    $value['match_id'] = $match_id;

    $botSettings = array(
        'g_LiveConfig'		=> 'live.cfg',
        'g_KnifeRound'		=> 1,
        'g_OvertimeEnabled'	=> 1,
        'g_OvertimeRounds'	=> 10,
        'g_OvertimeMoney'	=> 16000,
        'g_MaxRounds'		=> 30,
        'g_SteamCheck'		=> 1,
        'g_PauseAfterKnife'	=> 0,
        'g_SteamForce'		=> 1,
        'g_RoundScore'		=> 1,
        'g_EseaStats'		=> 0,
        'g_CaptainVote'		=> 1
    );

    for ($x = 0; $x < $anzMaps; $x++) {

        //
        // Map
        $map = '';

        if($matchArray['format'] == 'bo1') {
            
            $map = ($x == 0) ? 
                $mapArray['picked'][0] : '';	
            
        } else if ($matchArray['format'] == 'bo3') {

            if ($x == 0) {
                $map = $mapArray['picked']['team1'][0];
            } else if ($x == 1) {
                $map = $mapArray['picked']['team2'][0];
            } else if ($x == 2) {
                $map = $mapArray['picked'][0];	
            }

        }

        if(!empty($map)) {

            //
            // Team Data
            for ($n = 1; $n < 3; $n++) {

                $varTeamName = addslashes($matchArray['team'.$n]['name']);

                $varTeamName = str_replace(
                    array('\'', '"',  'ä',  'Ä',  'ö',  'Ö',  'ü',  'Ü'), 
                    array('',    '', 'ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue'), 
                    $varTeamName
                );			  

                $teamName[$n] = $varTeamName;

            }

            //
            // Teamsize
            $teamsize = $cupArray['max_mode'];

            if ($x == 0) {
                $map_index = $anzMaps - 1;	
            } else {
                $map_index = $x - 1;	
            }

            $g_Active = ($x == 0) ? 1 : 0;

            //
            // Match in externe DB schreiben
            $value['maps'][$x] = array(
                'matchID' => $match_id,
                'ip' => '',
                'port' => '',
                'map' => $map,
                'teamA' => $teamName[1],
                'teamB' => $teamName[2],
                'password' => $server_password,
                'status' => 1,
                'teamsize' => $teamsize,
                'inserted_from' => 'cup'
            );

            $value['maps'][$x] = array_merge($value['maps'][$x], $botSettings);

        }

    }

    sort($value['maps']);
    $value = json_encode($value);

    //
    // save tmp file
    // name: cup_setmatch_1-121.json
    // 1 	= cupID
    // 121 	= matchID
    // Location: http://[Cup URL]/tmp/[name]
    $datei = fopen($filepath.'/cup_setmatch_'.$cup_id.'-'.$match_id.'.json', 'w');
    if(fwrite($datei, $value, strlen($value))) {
        $status = TRUE;	
    }
    fclose($datei);

    $sleepSeconds = round(1, 10);
    sleep($sleepSeconds);

    $file = 'http://myrisk.info:9100/index.php?q=cup_setmatch_'.$cup_id.'-'.$match_id;

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $file);
    curl_setopt($curl_handle, CURLOPT_PORT, 9100);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 500);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'myRisk_eV_Cup-Bot');
    $contents = curl_exec($curl_handle);

    if (!$contents) {
        throw new \Exception(curl_error($curl_handle));
    }

    curl_close($curl_handle);
    
    $serverArray = json_decode($contents, TRUE);
    if (!isset($serverArray['status']) || ($serverArray['status'] != 'true')) {
        throw new \Exception('error_server_status'));
    }

    $serverIP = $serverArray['messages']['ip'] . ':' . $serverArray['messages']['port'];

    $serverDataArray = array(
        'ip' => $serverIP,
        'password' => $serverArray['messages']['pw'],
        'rcon' => '',
        'gotv' => '',
        'gotv_pw' => ''
    );

    $serverData = serialize($serverDataArray);

    $updateQuery = mysqli_query(
        $_database, 
        "UPDATE `" . PREFIX . "cups_matches_playoff` 
            SET server = '" . $serverData . "' 
            WHERE matchID = " . $match_id
    );

    if (!$updateQuery) {
        throw new \Exception('query_update_failed'));
    }
    
    $returnArray = array(
        'message' => 'server is ready',
        'data' => $serverDataArray
    );
    
    $returnArray['status'] = TRUE;
    
} catch (Exception $e) {
    $returnArray['errorArray'][] = $e->getMessage();
}

echo json_encode($returnArray);
