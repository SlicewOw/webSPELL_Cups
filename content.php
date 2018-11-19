<?php

$errorNotifyPath = __DIR__ . '/error_notify.php';
if (file_exists($errorNotifyPath)) {
    include($errorNotifyPath);
}

if (!file_exists(__DIR__ . '/cup/settings.php')) {
    echo showError('Please create the settings files within folder "cup/". Just copy the sample file and remove the suffix ".sample".');
}

$invalide = array('\\', '/', '/\/', ':', '.');
$getSite = str_replace($invalide, ' ', $getSite);

$_language->readModule('plugin');
$plugin = new plugin_manager();
$plugin->set_debug(DEBUG);
if (!empty($getSite) AND $plugin->is_plugin($getSite)>0) {
    $data = $plugin->plugin_data($getSite);
    $plugin_path = $data['path'];
    $check = $plugin->plugin_check($data, $getSite);
    if ($check['status']==1) {
        include($check['data']);
    } else {
        echo $check['data'];
    }
} else {

    $site_php = $getSite . ".php";
    if (file_exists($dir_cup . $site_php)) {
        include($dir_cup . $site_php);
    } else if (file_exists($site_php)) {
        include($site_php);
    } else {
        include("error.php");
    }

}
