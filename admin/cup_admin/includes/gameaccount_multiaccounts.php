<?php

try {

    $_language->readModule('gameaccounts', false, true);

    if(!iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    $base_url = 'admincenter.php?site=cup&mod=gameaccounts&action=log&user_id=';

    $content = '';

    $query = mysqli_query(
        $_database,
        "SELECT
                COUNT(*) AS anz,
                value
            FROM `".PREFIX."cups_gameaccounts`
            GROUP BY value
            HAVING COUNT(*) > 1
            ORDER BY COUNT(*) DESC"
    );

    while ($get = mysqli_fetch_array($query)) {

        $value = $get['value'];

        $subquery = mysqli_query(
            $_database,
            "SELECT
                a.userID AS user_id,
                a.smurf AS isSmurf,
                a.active AS isActive,
                a.date AS date,
                a.deleted AS isDeleted,
                a.deleted_date AS deleted_date,
                b.nickname AS nickname,
                c.short AS game
            FROM `" . PREFIX . "cups_gameaccounts` a
            JOIN `" . PREFIX . "user` b ON a.userID = b.userID
            JOIN `" . PREFIX. "games` c ON a.category = c.tag
            WHERE a.value = '" . $value . "'"
        );

        $subContent = '';
        $activeName = '';
        $alertLevel = FALSE;

        while($subget = mysqli_fetch_array($subquery)) {

            if(empty($activeName)) {
                $activeName = $subget['nickname'];
            }

            $subContent .= '<tr>';
            $subContent .= '<td>'.$subget['user_id'].'</td>';
            $subContent .= '<td>'.$subget['nickname'].'</td>';
            $subContent .= '<td>'.getformatdatetime($subget['date']).'</td>';
            $subContent .= '<td>'.$subget['game'].'</td>';

            $subContent .= ($subget['isActive']) ?
                '<td><button class="btn btn-success btn-xs">'.$_language->module['yes'].'</button></td>' :
                '<td><button class="btn btn-danger btn-xs">'.$_language->module['no'].'</button></td>';

            $subContent .= ($subget['isDeleted']) ?
                '<td><button class="btn btn-danger btn-xs">'.$_language->module['yes'].'</button></td>' :
                '<td><button class="btn btn-success btn-xs">'.$_language->module['no'].'</button></td>';

            $subContent .= '<td>';
            $subContent .= '<a class="btn btn-default btn-xs" href="'.$base_url.$subget['user_id'].'">User-Log</a>';
            $subContent .= '</td>';

            $subContent .= '</tr>';

            if ($activeName != $subget['nickname']) {
                $alertLevel = TRUE;
            }

        }

        if ($alertLevel) {

            $data_array = array();
            $data_array['$title'] = $value.' ('.$get['anz'].'x)';
            $data_array['$content'] = $subContent;
            $content .= $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_multiaccount_list", $data_array);

        }


    }

    $data_array = array();
    $data_array['$content'] = $content;
    $gameacc_log = $GLOBALS["_template_cup"]->replaceTemplate("gameaccount_multiaccount", $data_array);
    echo $gameacc_log;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
