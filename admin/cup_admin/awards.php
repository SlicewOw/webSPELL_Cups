<?php

try {

    $_language->readModule('cups', false, true);

    if (!($loggedin && iscupadmin($userID))) {
        throw new \UnexpectedValueException($_language->module['access_denied']);
    }

    if (validate_array($_POST, true)) {

        $columnArray = array(
            'platzierung' => 'platzierung',
            'anz_cups' => 'anz_cups',
            'anz_matches' => 'anz_matches'
        );

        $parent_url = 'admincenter.php?site=cup&mod=awards';

        try {

            if (isset($_POST['settings'])) {

                $parent_url .= '&settings';

                if (isset($_POST['submitAddCategory']) || isset($_POST['submitEditCategory'])) {

                    $name = (isset($_POST['category_name']) && !empty($_POST['category_name'])) ?
                        getinput($_POST['category_name']) : '';

                    $icon = (isset($_POST['category_icon'])) ?
                        $_POST['category_icon'] : 'gold';

                    $active_column = (isset($_POST['active_column'])) ?
                        $_POST['active_column'] : 'platzierung';

                    $description = (isset($_POST['category_description']) && !empty($_POST['category_description'])) ?
                        getinput($_POST['category_description']) : '';

                    $column_value = (isset($_POST['type_' . $active_column]) && is_numeric($_POST['type_' . $active_column])) ?
                        (int)$_POST['type_' . $active_column] : 1;

                    if ($column_value < 1) {
                        $column_value = 1;
                    }

                    if (isset($_POST['submitAddCategory'])) {

                        $query = mysqli_query(
                            $_database,
                            "INSERT INTO `".PREFIX."cups_awards_category`
                                (
                                    `name`,
                                    `icon`,
                                    `active_column`,
                                    `description`,
                                    `" . $active_column . "`
                                )
                                VALUES
                                (
                                    '" . $name . "',
                                    '" . $icon . "',
                                    '" . $active_column . "',
                                    '" . $description . "',
                                    " . $column_value . "
                                )"
                        );

                        if (!$query) {
                            throw new \UnexpectedValueException($_language->module['query_insert_failed']);
                        }

                        $award_id = mysqli_insert_id($_database);

                    } else {

                        $award_id = (isset($_POST['award_id']) && validate_int($_POST['award_id'], true)) ?
                            (int)$_POST['award_id'] : 0;

                        if ($award_id < 1) {
                            throw new \UnexpectedValueException($_language->module['unknown_action']);
                        }

                        unset($columnArray[$active_column]);

                        $updateStatement = implode(' = NULL, ', $columnArray) . ' = NULL';

                        $query = mysqli_query(
                            $_database,
                            "UPDATE `".PREFIX."cups_awards_category`
                                SET `name` = '" . $name . "',
                                    `icon` = '" . $icon . "',
                                    `active_column` = '" . $active_column . "',
                                    `description` = '" . $description . "',
                                    `" . $active_column . "` = " . $column_value .",
                                    " . $updateStatement . "
                                WHERE awardID = " . $award_id
                        );

                        if (!$query) {
                            throw new \UnexpectedValueException($_language->module['query_update_failed']);
                        }

                    }

                    $_SESSION['successArray'][] = $_language->module['cat_query_saved'];

                } else if (isset($_POST['sortCategories'])) {

                    $awardCategoryList = (isset($_POST['categoryList'])) ?
                        $_POST['categoryList'] : '';

                    if (empty($awardCategoryList)) {
                        throw new \UnexpectedValueException($_language->module['unknown_action']);
                    }

                    $awardCategoryArray = explode(',', $awardCategoryList);
                    if(count($awardCategoryArray) < 1) {
                        throw new \UnexpectedValueException($_language->module['unknown_action']);
                    }

                    $x = 0;
                    foreach ($awardCategoryArray as $award_id) {

                        $sortValue = (isset($_POST['sort']) && is_array($_POST['sort']) && isset($_POST['sort'][$x])) ?
                            (int)$_POST['sort'][$x++] : 1;

                        $query = mysqli_query(
                            $_database,
                            "UPDATE `" . PREFIX . "cups_awards_category`
                                SET sort = " . $sortValue . "
                                WHERE awardID = " . $award_id
                        );

                    }

                }

            } else {
                throw new \UnexpectedValueException($_language->module['unknown_action']);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $overviewMenu = (!isset($_GET['settings'])) ?
            'btn-info white darkshadow' : 'btn-default';

        $categoryMenu = (isset($_GET['settings'])) ?
            'btn-info white darkshadow' : 'btn-default';

        $data_array = array();
        $data_array['$overviewMenu'] = $overviewMenu;
        $data_array['$categoryMenu'] = $categoryMenu;
        $cup_awards_menu = $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_menu", $data_array);
        echo $cup_awards_menu;

        if (isset($_GET['settings'])) {

            if($getAction == 'deleteCategory') {

                $award_id = (isset($_GET['award_id']) && is_numeric($_GET['award_id'])) ?
                    (int)$_GET['award_id'] : 0;

                if($award_id > 0) {

                    $checkIf = mysqli_fetch_array(
                        mysqli_query(
                            $_database,
                            "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_awards`
                                WHERE award = " . $award_id
                        )
                    );

                    if($checkIf['exist'] > 0) {

                        $query = mysqli_query(
                            $_database,
                            "DELETE FROM `".PREFIX."cups_awards`
                            WHERE award = " . $award_id
                        );

                        if($query) {
                            $_SESSION['successArray'][] = $_language->module['query_deleted'] . ': ' . $checkIf['exist'];
                        } else {
                            $_SESSION['errorArray'][] = $_language->module['query_failed'];
                        }

                    }

                    $query = mysqli_query(
                        $_database,
                        "DELETE FROM `".PREFIX."cups_awards_category`
                            WHERE awardID = " . $award_id
                    );

                    if($query) {
                        $_SESSION['successArray'][] = $_language->module['cat_query_deleted'];
                    } else {
                        $_SESSION['errorArray'][] = $_language->module['cat_query_failed'];
                    }

                }

                header('Location: admincenter.php?site=cup&mod=awards&settings');

            } else {

                $query = mysqli_query(
                    $_database,
                    "SELECT * FROM `".PREFIX."cups_awards_category`
                    ORDER BY `sort` ASC"
                );

                $categorySortArray = array();

                $anzAwards = mysqli_num_rows($query);
                if($anzAwards > 0) {

                    $sort_base = '';
                    for($x=1;$x<($anzAwards + 1);$x++) {
                        $sort_base .= '<option value="' . $x . '">' . $x . '</option>';
                    }

                    $awardCategoryList = '';
                    while($get = mysqli_fetch_array($query)) {

                        $award_id = $get['awardID'];

                        $categorySortArray[] = $award_id;

                        if(file_exists('../../images/cup/' . $get['icon'] . '_small.png')) {
                            $url = $image_url . '/cup/' . $get['icon'] . '_small.png';
                            $icon = '<img src="' . $url . '" alt="' . $get['icon'] . '" title="' . $get['icon'] . '" id="cat_' . $award_id . '_icon" />';
                        } else {
                            $icon = '';
                        }

                        $active_column = $get['active_column'];

                        $sort = str_replace(
                            'value="' . $get['sort'] . '"',
                            'value="' . $get['sort'] . '" selected="selected"',
                            $sort_base
                        );

                        $data_array = array();
                        $data_array['$award_id'] = $award_id;
                        $data_array['$name'] = $get['name'];
                        $data_array['$description'] = $get['description'];
                        $data_array['$icon'] = $icon;
                        $data_array['$active_column'] = $active_column;
                        $data_array['$value'] = $get[$active_column];
                        $data_array['$sort'] = $sort;
                        $awardCategoryList .= $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_settings_list", $data_array);

                    }

                } else {
                    $awardCategoryList = '<tr><td colspan="8">' . $_language->module['no_category'] . '</td></tr>';
                }

                $categorySortList = (count($categorySortArray) > 0) ?
                    implode(',', $categorySortArray) : '';

                $data_array = array();
                $data_array['$awardCategoryList'] = $awardCategoryList;
                $data_array['$categorySortList'] = $categorySortList;
                $cup_awards = $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_settings", $data_array);
                echo $cup_awards;

            }

        } else {

            $data_array = array();
            $data_array['$image_url'] = $image_url;
            $cup_awards = $GLOBALS["_template_cup"]->replaceTemplate("cup_awards", $data_array);
            echo $cup_awards;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
