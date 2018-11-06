<?php

try {

    $_language->readModule('cups', false, true);

    if (!iscupadmin($userID) || (mb_substr(basename($_SERVER['REQUEST_URI']), 0, 15) != "admincenter.php")) {
        throw new \Exception($_language->module[ 'access_denied' ]);
    }

    $category = (isset($_GET['category'])) ?
        getinput($_GET['category'], true) : '';

    if (empty($category)) {

        $basis_url = 'admincenter.php?site=cup&amp;mod=categories';

        $data_array = array();
        $data_array['$basis_url'] = $basis_url;
        $categories_home = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_home", $data_array);
        echo $categories_home;

    } else {

        $includePath = __DIR__ . '/includes/categories_' . $category . '.php';
        if (file_exists($includePath)) {
            include($includePath);
        } else {
            throw new \Exception($_language->module['unknown_action']);
        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
