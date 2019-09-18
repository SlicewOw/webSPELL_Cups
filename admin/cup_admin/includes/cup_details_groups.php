<?php

if (!isset($content)) {
    $content = '';
}

try {

    if (!isset($cupArray) || !validate_array($cupArray, true)) {
        throw new \UnexpectedValueException($_language->module['unknown_action']);
    }

    if (($cupArray['status'] > 1) && ($cupArray['groupstage'] == 1)) {

        /**
         * WiP
         */

        $groups = '';

    } else if ($cupArray['groupstage'] == 1) {
        $groups = '<span class="italic">' . $_language->module['cup_not_started'] . '</span>';
    } else {
        $groups = '<span class="italic">' . $_language->module['no_groups'] . '</span>';
    }

    $content .= '<div class="clearfix"><div class="cup_container">'.$groups.'</div></div>';

} catch (Exception $e) {
    $content .= showError($e->getMessage());
}
