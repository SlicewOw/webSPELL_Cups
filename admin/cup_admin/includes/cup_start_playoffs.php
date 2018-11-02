<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['login']);
    }

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['unknown_cup_id']);
    }
    
    $parent_url = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=' . $cup_id;
    
    //
    // Cup Array 
    $cupArray = getcup($cup_id);

	$setValueArray = array();
	$setValueArray[] = '`status` = 3';
	
    //
    // Bracket zu gross?
    $anzTeams = $cupArray['teams']['checked_in'];
    if ($anzTeams < 3) {
        $setValueArray[] = '`max_size` = 2';
    } else if ($anzTeams < 5) {
        $setValueArray[] = '`max_size` = 4';
	} else if ($anzTeams < 9) {
        $setValueArray[] = '`max_size` = 8';
	} else if ($anzTeams < 17) {
        $setValueArray[] = '`max_size` = 16';
	} else if ($anzTeams < 33) {
        $setValueArray[] = '`max_size` = 32';
	} else if ($anzTeams < 65) {
        $setValueArray[] = '`max_size` = 64';
	}
	
    $setValues = implode(', ', $setValueArray);
    
    //
    // Cup Status Aktualisieren
    // Status:
    // 1: offen
    // 2: Gruppenphase
    // 3: Playoffs
    // 4: beendet
    $query = mysqli_query(
        $_database, 
        "UPDATE `" . PREFIX . "cups` 
            SET " . $setValues . " 
            WHERE `cupID` = " . $cup_id
    );
    
    if (!$query) {
        throw new \Exception($_language->module['error_update_query_failed']);
    }

    //
    // Bracket erstellen
    $createBracket = __DIR__ . '/cup_start_playoffs_bracket.php';
    if (!file_exists($createBracket)) {
        throw new \Exception($_language->module['unknown_file']);
    }
    
    include($createBracket);

    //
    // Bot Addon
    if ($cupArray['bot']) {
        
        $setPlayer = __DIR__ . '/cup_setplayer.php';
        if (!file_exists($setPlayer)) {
            throw new \Exception($_language->module['unknown_file']);
        }

        include($setPlayer);
        
    }

    $_SESSION['successArray'][] = $_language->module['bracket_created'];
    
	$parent_url .= '&amp;page=bracket';
    
} catch(Exception $e) {
    echo showError($e->getMessage());
}

//header('Location: ' . $parent_url);
