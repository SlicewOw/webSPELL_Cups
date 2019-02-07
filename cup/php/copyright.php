<?php

$_language->readModule('navigation');

$copyright_txt = $_language->module['copyright'];

$copyright_txt = str_replace(
    array('%year_now%', '%clanname%'),
    array(date('Y'), $myclanname),
    $copyright_txt
);

$copyrightLinkArray = array();
$copyrightLinkArray[] = '<a href="index.php?site=contact">' . $_language->module['contact'] . '</a>';
$copyrightLinkArray[] = '<a href="index.php?site=policy">' . $_language->module['policy'] . '</a>';
$copyrightLinkArray[] = '<a href="index.php?site=imprint">' . $_language->module['imprint'] . '</a>';
$copyrightLinkArray[] = '<a href="index.php?site=datenschutz">' . $_language->module['data_protection'] . '</a>';

$copyright_txt .= ' - ' . implode(' - ', $copyrightLinkArray);

$data_array = array();
$data_array['$copyright_txt'] = $copyright_txt;
$copyright = $GLOBALS["_template_cup"]->replaceTemplate("copyright", $data_array);
echo $copyright;
