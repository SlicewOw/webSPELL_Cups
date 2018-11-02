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

    $fileLocation = '';

    $getAccess = TRUE;

    if (preg_match('/admin_/', $getSite)) {

        $fileLocation .= 'admin/';

        //
        // Zeige keine Information, sofern kein Admin
        if (!isanyadmin($userID)) {

            $teamManagementSiteArray = array(
                'admin_clanwar_details'
            );

            if (!in_array($getSite, $teamManagementSiteArray)) {
                $getAccess = FALSE;
            } else {

                $teamAdminArray = isteamadmin($userID, 0);

                if (!isset($teamAdminArray['checkValue']) || !$teamAdminArray['checkValue']) {
                    $getAccess = FALSE;
                }

            }

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