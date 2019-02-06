<?php

try {

    include('_mysql.php');
    include('_settings.php');
    include('_functions.php');

    $showAjax = FALSE;

    $getSite = (isset($_GET['site'])) ?
        getinput($_GET['site']) : 'home';

    $getAction = (isset($_GET['action'])) ?
        getinput($_GET['action']) : '';

    global $image_url;
    $image_url = $hp_url . '/images/';

    $fileLocation = '';

    $getAccess = TRUE;

    if (preg_match('/admin_/', $getSite)) {

        $fileLocation .= 'admin/';

        //
        // no admin = no information
        if (!isanyadmin($userID)) {
            $getAccess = FALSE;
        }

    }

    if (!$getAccess) {
        throw new \Exception("access_denied");
    }

    $fileLocation .= $getSite;

    $fileSource = __DIR__ . '/cup/ajax/' . $fileLocation . '.php';
    if (isset($getSite) && file_exists($fileSource)) {

        $showAjax = TRUE;

        include($fileSource);

    } else {

        //
        // Globale Variable wird gekilled
        // -> neue Initialisierung
        $showAjax = FALSE;

    }

} catch (Exception $e) {
    echo $e->getMessage();
}

if (!isset($showAjax)) {
    $showAjax = FALSE;
}

if (!$showAjax) {

    $dataArray = array(
        'code' => 404,
        'status' => FALSE,
        'message' => array('Error 404')
    );

    echo json_encode($dataArray);

}