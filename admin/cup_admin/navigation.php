<?php

try {
    
    if (iscupadmin($userID)) {

        $data_array = array();
        $cups_navigation = $GLOBALS["_template_cup"]->replaceTemplate("navigation_adminpanel", $data_array);
        echo $cups_navigation;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
