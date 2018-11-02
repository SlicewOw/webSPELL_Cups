<?php

$_language->readModule('index');
$_language->readModule('navigation', true, false);

$copyright = $_language->module['copyright'];

$copyright = str_replace(
    '%year_now%',
    date('Y'),
    $copyright
);

$copyrightLinkArray = array();
$copyrightLinkArray[] = '<a href="index.php?site=contact">' . $_language->module['contact'] . '</a>';
$copyrightLinkArray[] = '<a href="index.php?site=policy">' . $_language->module['policy'] . '</a>';
$copyrightLinkArray[] = '<a href="index.php?site=imprint">' . $_language->module['imprint'] . '</a>';
$copyrightLinkArray[] = '<a href="index.php?site=datenschutz">' . $_language->module['data_protection'] . '</a>';

$copyright .= ' - ' . implode(' - ', $copyrightLinkArray);

echo $copyright;
