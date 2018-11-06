<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array()
);

try {
    
    $_language->readModule('gameaccounts', false, true);
    
    if(!$loggedin || !iscupadmin($userID)) {
        throw new \Exception('access_denied');
    }

    $userArray = (isset($_GET['user_id'])) ? 
		explode(',', $_GET['user_id']) : array();
	
    $anzUser = count($userArray);
    if($anzUser < 1) {
        throw new \Exception('unknwon_user_array');
    }

    $steamImagePath = $dir_global . '/images/cup/steam/';
    
    for ($n = 0; $n < $anzUser; $n++) {

        try {
            
            $user_id = (isset($userArray[$n]) && validate_int($userArray[$n])) ?
                $userArray[$n] : 0;

            if($user_id < 1) {
                throw new \Exception('unknown_user_id');
            }

            $csgoGameaccount = array(
                'list'	=> array(),
                'data'	=> array()
            );

            $gameaccount_list = '';
			
            $query = mysqli_query(
                $_database, 
                "SELECT 
                        `gameaccID`,
                        `category`, 
                        `value`, 
                        `active` 
                    FROM `" . PREFIX . "cups_gameaccounts` 
                    WHERE `userID` = " . $user_id . " AND `category` IN ('csg')       
                    ORDER BY `active` DESC, `category` ASC, `date` DESC"
            );
            $anz = mysqli_num_rows($query);
            if($anz > 0) {

                while($ds = mysqli_fetch_array($query)) {

					$gameaccount_id = $ds['gameaccID'];
                    $steam64_id = $ds['value'];

                    $checkIf = mysqli_fetch_array(
                        mysqli_query(
                            $_database, 
                            "SELECT 
									COUNT(*) AS exist 
								FROM `" . PREFIX . "cups_gameaccounts_csgo`
								WHERE `gameaccID` = " . $gameaccount_id
                        )
                    );

                    if(!$checkIf['exist']) {

                        $subquery = mysqli_query(
                            $_database, 
                            "INSERT INTO `".PREFIX."cups_gameaccounts_csgo`
                                (
                                    `gameaccID`, 
                                    `date`, 
                                    `hours`
                                )
                                VALUES
                                (
                                    " . $gameaccount_id . ", 
                                    " . time() . ", 
                                    -1
                                )");

                    }

                    if(!in_array($steam64_id, $csgoGameaccount['list'])) {
                        
                        $csgoGameaccount['data'][] = array(
                            'gameaccID'		=> $gameaccount_id,
                            'active'		=> $ds['active'],
                            'game'		    => $ds['category'],
                            'steam_id'		=> $steam64_id
                        );
                        
                        $csgoGameaccount['list'][] = $steam64_id;
                        
                    }

                }

            }

            $checkVACYears = 1;

            $anzCSGOAccounts = count($csgoGameaccount['list']);
            if($anzCSGOAccounts > 0) {
               
                for($x=0;$x<$anzCSGOAccounts;$x++) {

                    $steam64_id = $csgoGameaccount['data'][$x]['steam_id'];
                    $accountDetails = getCSGOAccountInfo($steam64_id);

                    if(!isset($accountDetails['csgo_stats']['time_played']) || !is_array($accountDetails['csgo_stats']['time_played'])) {
                        throw new \Exception('getCSGOAccountInfo() failed, time_played fehlerhaft');
                    }

                    $steamProfile = $accountDetails['steam_profile'];

                    $steamAvatar = '';
                    if(!empty($steamProfile)) {

                        $avatarFile = $steam64_id . '.' . getImageType($steamProfile['avatarfull']);
                        if(copy($steamProfile['avatarfull'], $steamImagePath . $avatarFile)) {

                            $steam_avatar = $image_url . '/cup/steam/' . $avatarFile;
                            $accountName = getinput($steamProfile['personaname']);
                            $steamAvatar = '<img src="' . $steam_avatar . '" alt="' . $accountName . '" title="' . $accountName . '" style="height: 20px;" />';

                        }

                    }

                    $activeStatus = $csgoGameaccount['data'][$x]['active'];

                    $active = ($activeStatus) ? 
                        '<span class="btn btn-success btn-xs">' . $_language->module['yes'] . '</span>' : 
                        '<span class="btn btn-danger btn-xs">' . $_language->module['no'] . '</span>';

                    $isBanned = '0';

                    $vac_status = '<span class="btn btn-danger btn-xs">BANNED</span>';
                    if(isset($accountDetails['vac_status']['VACBanned'])) {

                        if(empty($accountDetails['vac_status']['VACBanned'])) {
                            $vac_status = '<span class="btn btn-success btn-xs">ok</span>';
                        } elseif($accountDetails['vac_status']['VACBanned'] == 1) {

                            $isActualBan = 'btn-danger';

                            $anzVACBann = $accountDetails['vac_status']['NumberOfVACBans'];
                            $lastBann = $accountDetails['vac_status']['DaysSinceLastBan'];
                            if(($lastBann / ($checkVACYears * 365)) > 1) {
                                $isActualBan = 'btn-success';
                                $lastBann = '> '.((int) ($lastBann / ($checkVACYears * 365))).' Jahre';
                            } else {
                                $lastBann = 'Letzter Bann: vor '.$lastBann.' Tage';
                            }

                            $vac_status = '<span class="btn '.$isActualBan.' btn-xs">'.$anzVACBann.'x BANNED</span>';
                            $vac_status .= ' <span class="btn btn-default btn-xs">'.$lastBann.'</span>';

                            $isBanned = '1';

                        }

                    }

                    $community_status = '<span class="btn btn-danger btn-xs">BANNED</span>';
                    if(isset($accountDetails['vac_status']['CommunityBanned'])) {
                        if(empty($accountDetails['vac_status']['CommunityBanned'])) {
                            $community_status = '<span class="btn btn-success btn-xs">ok</span>';
                        }
                    }

                    $subquery = mysqli_query(
                        $_database, 
                        "UPDATE `".PREFIX."cups_gameaccounts_csgo`
                            SET `date` = " . time() . ", 
                                `hours` = " . $accountDetails['csgo_stats']['time_played']['hours'] . ",
                                `vac_bann` = " . $isBanned . "
                            WHERE `gameaccID` = " . $csgoGameaccount['data'][$x]['gameaccID']
                    );

                    echo '<tr>
                        <td>'.$steamAvatar.'</td>
                        <td><a href="https://steamcommunity.com/profiles/' . $steam64_id . '" class="blue" target="_blank">'.$csgoGameaccount['data'][$x]['steam_id'].'</a></td>
                        <td>'.$active.'</td>
                        <td>'.$vac_status.'</td>
                        <td>'.$community_status.'</td>
                        <td>'.$accountDetails['csgo_stats']['time_played']['hours'].'</td>
                    </tr>';

                }

            } else {
                echo '<tr><td colspan="6">' . $_language->module['no_gameaccount'] . '</td></tr>';
            }
            
        } catch(Exception $e) {
            echo '<tr><td colspan="8" class="alert-danger">Etwas lief schief..</td></tr>';
        }
        
    }
    
} catch(Exception $e) {
    echo '<tr><td colspan="8">' . $e->getMessage() . '</td></tr>';
}
