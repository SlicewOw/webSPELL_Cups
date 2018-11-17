<?php

if(!isset($content)) {
    $content = '';
}

$navi_home = 'btn-info white darkshadow';

$status = $_language->module['cup_status_' . $cupArray['status']];

$info = '<span class="list-group-item">Status: '.$status.'</span>';
$info .= '<span class="list-group-item">'.getgamename($cupArray['game']).'</span>';
$info .= '<span class="list-group-item">'.$_language->module['mode'].': '.$cupArray['mode'].'</span>';
$info .= '<span class="list-group-item">Check-In: '.getformatdatetime($cupArray['checkin']).'</span>';
$info .= '<span class="list-group-item">Start: '.getformatdatetime($cupArray['start']).'</span>';
if ($cupArray['phase'] == 'register' || $cupArray['phase'] == 'admin_register') {
    $info .= '<span class="list-group-item">'.$_language->module['teams_registered'].': '.getcup($cup_id, 'anz_teams').' / '.$cupArray['size'].'</span>';
} else if ($cupArray['phase'] == 'checkin' || $cupArray['phase'] == 'admin_checkin' || $cupArray['phase'] == 'finished') {
    $info .= '<span class="list-group-item">'.$_language->module['teams_checked_in'].': '.getcup($cup_id, 'anz_teams_checkedin').' / '.$cupArray['size'].'</span>';
}
$info .= '<span class="list-group-item">'.$_language->module['max_penalty'].': '.$cupArray['max_pps'].'</span>';

if ($cupArray['mappool'] > 0) {

    $get_maps = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT
                    `maps`
                FROM `" . PREFIX . "cups_mappool`
                WHERE `mappoolID` = " . $cupArray['mappool']
        )
    );

    if(!empty($get_maps['maps'])) {
        $maps = unserialize($get_maps['maps']);
        $anzMaps = count($maps);

        $info .= '<span class="list-group-item">Maps: ';
        for($x=0;$x<$anzMaps;$x++) {
            $info .= $maps[$x];
            if($x < ($anzMaps - 1)) {
                $info .= ', ';
            }
        }
        $info .= '</span>';
    }

}

//
// Cup Admins
$content_admin_add = '';
$admins = mysqli_query(
    $_database, 
    "SELECT userID FROM ".PREFIX."cups_admin 
        WHERE cupID = " . $cup_id
);
$anz_admins2 = mysqli_num_rows($admins);
if($anz_admins2) {
    $i=0;
    while($db = mysqli_fetch_array($admins)) {
        $url = 'index.php?site=profile&amp;id='.$db['userID'];
        $content_admin_add .= '<a href="'.$url.'" target="_blank">'.getnickname($db['userID']).'</a>';
        if($i < ($anz_admins2 - 1)) {
            $content_admin_add .= ', ';
        }
        $i++;
    }
}

if(!empty($content_admin_add)) {
    $info .= '<span class="list-group-item">Admins: '.$content_admin_add.'</span>';
}

//
// Cup Platzierungen
$finish_cup_info = '';
if($cupArray['status'] == 4) {
    include($dir_cup.'cups_details_platzierungen.php');
}

$content .= '<div class="panel panel-default">';
$content .= '<div class="panel-heading">Informationen</div>';
$content .= '<div class="list-group">'.$info.'</div>';
if(!empty($cupArray['description'])) {
    $content .= '<div class="panel-body"><label>'.$_language->module['description'].'</label><br />'.getoutput($cupArray['description']).'</div>';
}
$content .= '</div>';

//
// Cup Format
$content .= '<div class="panel panel-default">';
$content .= '<div class="panel-heading">Cup Format</div>';
$content .= '<div class="list-group">';

for($x=1;$x<($cupArray['anz_runden'] + 1);$x++) {

    if (isset($cupArray['settings']['format'][$x])) {

        if ($cupArray['settings']['format'][$x] == 'bo3') {
            $formatName = 'Best of 3';
        } else if ($cupArray['settings']['format'][$x] == 'bo5') {
            $formatName = 'Best of 5';
        } else if ($cupArray['settings']['format'][$x] == 'bo7') {
            $formatName = 'Best of 7';
        } else {
            $formatName = 'Best of 1';
        }

    } else {
        $formatName = 'Best of 1';
    }

    $content .= '<div class="list-group-item">'.$_language->module['cup_1_round_'.$cupArray['size'].'_'.$x].': '.$formatName.'</div>';

}

$content .= '</div>';
$content .= '</div>';

//
// Preise
$preis = mysqli_query(
    $_database, 
    "SELECT * FROM ".PREFIX."cups_preise 
        WHERE cupID = " . $cup_id . " 
        ORDER BY platzierung ASC"
);
if(mysqli_num_rows($preis)) {
    $preise = '';
    while($db = mysqli_fetch_array($preis)) {
        $preise .= '<div class="list-group-item">Platz #'.$db['platzierung'].': '.$db['preis'].'</div>';
    }
    $content .= '<div class="panel panel-default"><div class="panel-heading">Preise</div><div class="list-group">'.$preise.'</div></div>';
}