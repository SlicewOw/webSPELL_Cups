<?php

try {

    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \UnexpectedValueException($_language->module['login']);
    }

    $whereClauseArray = array();
    $whereClauseArray[] = '`status` = 4';

    if (!iscupadmin($userID)) {
        $whereClauseArray[] = '`saved` = 1';
        $whereClauseArray[] = '`admin_visible` = 0';
    }

    $whereClause = (validate_array($whereClauseArray, true)) ?
        'WHERE ' . implode(' AND ', $whereClauseArray) : '';

    $ergebnis = mysqli_query(
        $_database,
        "SELECT * FROM `" . PREFIX . "cups`
            " . $whereClause . "
            ORDER BY `status` ASC, `start_date` ASC"
    );

    if (!$ergebnis) {
        throw new \UnexpectedValueException($_language->module['query_select_failed']);
    }

    if (mysqli_num_rows($ergebnis) > 0) {

        $cupList = '';
        while($ds = mysqli_fetch_array($ergebnis)) {

            $cup_id = $ds[getConstNameCupId()];

            $cupArray = getcup($cup_id);

            if ($ds['status'] == 1) {
                $size = $cupArray['teams']['registered'] . ' / ' . $ds['max_size'];
            } else {
                $size = $cupArray['teams']['checked_in'] . ' / ' . $ds['max_size'];
            }

            $data_array = array();
            $data_array['$alert_type'] = ($ds['saved']) ?
                '' : 'class="alert-info"';
            $data_array['$cup_id'] = $cup_id;
            $data_array['$game'] = getGame($ds['game'], 'icon');
            $data_array['$name'] = $ds['name'];
            $data_array['$mode'] = $ds['mode'];
            $data_array['$checkin_start'] = getformatdatetime($ds['checkin_date']);
            $data_array['$cup_start'] = getformatdatetime($ds['start_date']);
            $data_array['$size'] = $size;
            $data_array['$status'] = $_language->module['cup_status_' . $ds['status']];
            $data_array['$url'] = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=' . $cup_id;
            $cupList .= $GLOBALS["_template_cup"]->replaceTemplate("cups_admin_list", $data_array);

        }

    } else {
        $cupList = '<tr><td colspan="7">'.$_language->module['no_cup'].'</td></tr>';
    }

    $data_array = array();
    $data_array['$cupList'] = $cupList;
    $cups_home = $GLOBALS["_template_cup"]->replaceTemplate("cups_admin_home", $data_array);
    echo $cups_home;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
