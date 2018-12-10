<?php

$_language->readModule('navigation');

include($dir_cup . '/sc_activematches.php');

if (!isset($activeMatches)) {
    $activeMatches = '';
}

$showLogin = FALSE;
include(__DIR__ . '/sc_login.php');

if (!isset($login)) {
    $login = '';
}

$data_array = array();
$data_array['$isHome'] = ($getSite == 'home') ?
    ' class="active"' : '';
$data_array['$isCup'] = ($getSite == 'cup') ?
    ' class="active"' : '';
$data_array['$isTeams'] = ($getSite == 'teams') ?
    ' class="active"' : '';
$data_array['$isAdmins'] = ($getSite == 'admins') ?
    ' class="active"' : '';
$data_array['$isHallOfFame'] = ($getSite == 'hof') ?
    ' class="active"' : '';
$data_array['$activeMatches'] = $activeMatches;
$data_array['$login'] = $login;
$navigation = $GLOBALS["_template_cup"]->replaceTemplate("navigation", $data_array);
echo $navigation;