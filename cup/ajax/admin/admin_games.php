<?php

$returnArray = array(
	'status' => FALSE,
	'message' => array()
);

try {
    
    $_language->readModule('games', false, true);
    
    if (!ispageadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if (empty($getAction)) {
        throw new \Exception($_language->module['unknown_action']);
    }
    
    $game_id = (isset($_GET['game_id']) && validate_int($_GET['game_id'])) ? 
        (int)$_GET['game_id'] : 0;

    if ($game_id < 1) {
        throw new \Exception($_language->module['unknown_game']);
    }

    if($getAction == 'setActiveMode') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT active FROM `".PREFIX."games`
                    WHERE gameID = " . $game_id
            )
        );

        $new_value = (!empty($get['active']) && ($get['active'] == 1)) ? 0 : 1;

        $query = mysqli_query(
            $_database, 
            "UPDATE `".PREFIX."games`
                SET active = " . $new_value . "
                WHERE gameID = " . $game_id
        );

        if(!$query) {
            throw new \Exception($_language->module['query_update_failed']);
        }

        $returnArray['value'] = $new_value;

    } else if ($getAction == 'getDetails') {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database, 
                "SELECT  
                        COUNT(gameID) AS exist,
                        gameID,
                        name,
                        tag,
                        short
                    FROM `".PREFIX."games`
                    WHERE gameID = " . $game_id
            )
        );

        if ($get['exist'] != 1) {
            throw new \Exception($_language->module['unknown_game']);
        }

        $imageTypeArray = array(
            'jpg',
            'gif',
            'png'
        );
        
        $file = 'games/' . $get['tag'] . '.gif';
        $icon = (file_exists(__DIR__ . '/../../../images/' . $file)) ? 
            '<img src="' . $image_url . '/' . $file . '" alt="" />' : '- / -';

        $image = '';
        foreach ($imageTypeArray as $image_type) {
            
            $file = 'squadicons/' . $get[ 'tag' ] . '.' . $image_type;
            $url = $image_url . '/' . $file;
            
            if (file_exists(__DIR__ . '/../../../images/' . $file)) {
                $image = '<a href="' . $url . '" target="_blank"><img src="' . $url . '" alt="" height="16" /></a>';
                break;
            }

        }

        if(empty($image)) {

            $file = 'squadicons/' . $get[ 'tag' ] . '.png';
            $url = $image_url . '/' . $file;
            $image = (file_exists(__DIR__ . '/../../../images/' . $file)) ? 
                '<a href="' . $url . '" target="_blank"><img src="' . $url . '" alt="" height="16" /></a>' : '- / -';

        }

        $file = 'teams/switch/' . $get['gameID'] . '.png';
        $url = $image_url . '/' . $file;
        $slider_tab = (file_exists(__DIR__ . '/../../../images/' . $file)) ? 
            '<a href="' . $url . '" target="_blank"><img src="' . $url . '" alt="" height="16" /></a>' : '- / -';

        $file = 'teams/switch/' . $get['gameID'] . '-bg-blue.png';
        $url = $image_url . '/' . $file;
        $slider_bg = (file_exists(__DIR__ . '/../../../images/' . $file)) ? 
            '<a href="' . $url . '" target="_blank"><img src="' . $image_url . '/' . $file . '" alt="" height="16" /></a>' : '- / -';

        $returnArray['data'] = array(
            'gameID' => $get['gameID'],
            'name' => $get['name'],
            'tag' => $get['tag'],
            'short' => $get['short'],
            'icon' => $icon,
            'image' => $image,
            'slider' => array(
                'tab' => $slider_tab,
                'bg' => $slider_bg
            )
        );

    } else {
        throw new \Exception($_language->module['unknown_action']);
    }

    $returnArray['status'] = TRUE;
    
} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
