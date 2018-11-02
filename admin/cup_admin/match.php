<?php

$_language->readModule('cups', false, true);

try {
    
    if(!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    if(is_array($_POST) && (count($_POST) > 0)) {
        
        $parent_url = 'admincenter.php?site=cup&mod=match&action=add';
        
        if( isset($_POST['add']) ) {

            $game			= $_POST['game'];
            $mappoolID		= $_POST['mappool'];
            $mode			= $_POST['mode'];
            $bot			= $_POST['bot'];
            $team1			= $_POST['team1'];
            $team2			= $_POST['team2'];

            $maps = array();

            $mapList = getMaps($mappoolID);

            $mapArray['open'] = $mapList;
            $mapArray['banned'] = array(
                'team1' => array(),
                'team2' => array()
            );
            $mapArray['picked'] = array();
            $mapArray['list'] = $mapList;
            $maps = serialize($mapArray);

            $cup_id = 1;

            $insertQuery = mysqli_query(
                $_database,
                "INSERT INTO `".PREFIX."cups_matches_playoff` 
                    (     
                        `cupID`, 
                        `wb`, 
                        `runde`, 
                        `spiel`,      
                        `format`,         
                        `date`,        
                        `team1`,        
                        `team2`, 
                        `active`,        
                        `maps`,        
                        `bot`, 
                        `admin`
                    ) 
                    VALUES 
                    (
                        " . $cup_id . ",  
                        1,     
                        1,     
                        1, 
                        '".$mode."', 
                        '".time()."', 
                        '".$team1."', 
                        '".$team2."',    
                        1, 
                        '".$maps."', 
                        '".$bot."',     
                        1
                    )"
            );

            if(!$insertQuery) {
                throw new \Exception($_language->module['error_insert_query_failed']);
            }
            
            $match_id = mysqli_insert_id($_database);

            $parent_url = 'index.php?site=cup&action=match&id=' . $cup_id . '&mID=' . $match_id;

        }

        header('Location: ' . $parent_url);
        
    } else {
        
        $games = getGamesAsOptionList('csg', TRUE);

        $mappool = '<option value="0">-- / --</option>';
        $pool = mysqli_query(
            $_database, 
            "SELECT mappoolID, name, gameID, maps FROM ".PREFIX."cups_mappool 
                ORDER BY gameID ASC, name ASC"
        );
        while($db = mysqli_fetch_array($pool)) {
            $maps = unserialize($db['maps']);
            $anz = count($maps);		
            $mappool .= '<option value="'.$db['mappoolID'].'">'.$db['name'].' ('.$anz.' Maps / '.getGame($db['gameID'], 'short').')</option>';	
        }

        $teams = '';
        $query = mysqli_query(
            $_database, 
            "SELECT teamID, name FROM ".PREFIX."cups_teams 
                WHERE admin = 1 
                ORDER BY name ASC"
        );
        while($db = mysqli_fetch_array($query)) {
            $teams .= '<option value="'.$db['teamID'].'">'.$db['name'].'</option>';
        }

        $data_array = array();
        $data_array['$error'] 	= '';
        $data_array['$games'] 	= $games;
        $data_array['$mappool'] = $mappool;
        $data_array['$teams'] 	= $teams;
        $match_add = $GLOBALS["_template_cup"]->replaceTemplate("cups_match_add", $data_array);
        echo $match_add;

    }
    
} catch(Exception $e) {
    echo showError($e->getMessage());
}
