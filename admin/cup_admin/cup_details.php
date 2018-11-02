<?php

try {

    $data_array = array();
    $data_array['$overview'] = (empty($getAction)) ?
        'btn-info white darkshadow' : 'btn-default';
    $data_array['$add'] = ($getAction == 'add') ?
        'btn-info white darkshadow' : 'btn-default';
    $cups_controls = $GLOBALS["_template_cup"]->replaceTemplate("cups_controls_admin", $data_array);
    echo $cups_controls;

    $whereClauseArray = array();

    if ($getAction != 'archive') {
        $whereClauseArray[] = '`status` < 4';
    }

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
        throw new \Exception($_language->module['query_select_failed']);
    }

    if (mysqli_num_rows($ergebnis) > 0) {

        $cupList = '';
        while($ds = mysqli_fetch_array($ergebnis)) {

            $cup_id = $ds['cupID'];

            $cupArray = getcup($cup_id);

            if ($ds['status'] == 1) {
                $size = $cupArray['teams']['registered'] . ' / ' . $ds['max_size'];
                $date = getformatdatetime($ds['checkin_date']);
            } else {
                $size = $cupArray['teams']['checked_in'] . ' / ' . $ds['max_size'];
                $date = getformatdatetime($ds['start_date']);
            }

            $data_array = array();
            $data_array['$alert_type'] = ($ds['saved']) ?
                '' : 'class="alert-info"';
            $data_array['$cup_id'] = $cup_id;
            $data_array['$game'] = getGame($ds['game'], 'icon');
            $data_array['$name'] = $ds['name'];
            $data_array['$date'] = $date;
            $data_array['$size'] = $size;
            $data_array['$status'] = $_language->module['cup_status_' . $ds['status']];
            $data_array['$url'] = 'admincenter.php?site=cup&amp;mod=cup&amp;action=cup&amp;id=' . $cup_id;
            $cupList .= $GLOBALS["_template_cup"]->replaceTemplate("cups_list", $data_array);

        }

    } else {
        $cupList = '<tr><td colspan="7">'.$_language->module['no_cup'].'</td></tr>';
    }

    $data_array = array();
    $data_array['$cupList'] = $cupList;
    $cups_home = $GLOBALS["_template_cup"]->replaceTemplate("cups_home", $data_array);
    echo $cups_home;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
