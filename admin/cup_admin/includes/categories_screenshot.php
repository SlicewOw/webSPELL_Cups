<?php

try {

    $category_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($getAction == 'show') {

        if (isset($_POST['submitAddScreenshotCategory'])) {

            $name = (isset($_POST['name'])) ?
                getinput($_POST['name']) : '';

            $game_id = (isset($_POST['game_id']) && validate_int($_POST['game_id'], true)) ?
                (int)$_POST['game_id'] : 0;

            if (isset($_POST['submitAddScreenshotCategory'])) {

                $insertQuery = mysqli_query(
                    $_database,
                    "INSERT INTO `" . PREFIX . "cups_matches_playoff_screens_category`
                        (
                            `name`,
                            `game_id`
                        )
                        VALUES
                        (
                            '" . $name . "',
                            " . $game_id . "
                        )"
                );

                if (!$insertQuery) {
                    throw new \Exception($_language->module['query_insert_failed']);
                }

                $category_id = mysqli_insert_id($_database);

            }

            header('Location: admincenter.php?site=cup&mod=categories&action=show&category=screenshot');

        } else {

            $info = mysqli_query(
                $_database,
                "SELECT
                        `categoryID`,
                        `name`,
                        `game_id`
                    FROM `" . PREFIX . "cups_matches_playoff_screens_category`
                    WHERE `deleted` = 0
                    ORDER BY `name` ASC"
            );

            if (!$info) {
                throw new \Exception($_language->module['query_select_failed']);
            }

            if (mysqli_num_rows($info) > 0) {

                $content = '';

                while ($db = mysqli_fetch_array($info)) {

                    $data_array = array();
                    $data_array['$category_id'] = $db['categoryID'];
                    $data_array['$name'] = $db['name'];
                    $data_array['$game'] = getGame($db['game_id'], 'name');
                    $content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_screenshot_list", $data_array);

                }

                $data_array = array();
                $data_array['$content'] = $content;
                $temps = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_screenshot_home", $data_array);
                echo $temps;

            }

            $data_array = array();
            $data_array['$title'] = $_language->module[ 'add_category_screenshot' ];
            $data_array['$cat_id'] = 0;
            $data_array['$name'] = '';
            $data_array['$games'] = getGamesAsOptionList('csg', false, false);
            $data_array['$postName'] = 'submitAddScreenshotCategory';
            $screenshot_add = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_screenshot_action", $data_array);
            echo $screenshot_add;

        }

    } else if ($getAction == 'edit') {

        if ($category_id < 1) {
            throw new \Exception('unknown_id');
        }

        if (isset($_POST['submitEditScreenshotCategory'])) {

            if ($category_id < 1) {
                throw new \Exception($_language->module['unknown_category']);
            }

            $name = (isset($_POST['name'])) ?
                getinput($_POST['name']) : '';

            $game_id = (isset($_POST['game_id']) && validate_int($_POST['game_id'], true)) ?
                (int)$_POST['game_id'] : 0;

            $updateQuery = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "cups_matches_playoff_screens_category`
                    SET `name` = '" . $name . "',
                        `game_id` = " . $game_id . "
                    WHERE `categoryID` = " . $category_id
            );

            if (!$updateQuery) {
                throw new \Exception($_language->module['query_update_failed']);
            }

            header('Location: admincenter.php?site=cup&mod=categories&action=show&category=screenshot');

        } else {

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            cmpsc.`categoryID`,
                            cmpsc.`name`,
                            cmpsc.`game_id`,
                            g.`tag`
                        FROM `" . PREFIX . "cups_matches_playoff_screens_category` cmpsc
                        LEFT JOIN `" . PREFIX . "games` g ON cmpsc.`game_id` = g.`gameID`
                        WHERE `deleted` = 0 AND `categoryID` = " . $category_id
                )
            );

            $data_array = array();
            $data_array['$title'] = $_language->module[ 'edit_category_screenshot' ];
            $data_array['$cat_id'] = $category_id;
            $data_array['$name'] = $ds['name'];
            $data_array['$games'] = getGamesAsOptionList($ds['tag'], false, false);
            $data_array['$postName'] = 'submitEditScreenshotCategory';
            $screenshot_edit = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_screenshot_action", $data_array);
            echo $screenshot_edit;

        }

    } else if ($getAction == 'delete') {

        if ($category_id < 1) {
            throw new \Exception('unknown_id');
        }

        if (isset($_POST['submitDeleteScreenshotCategory'])) {

            $updateQuery = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "cups_matches_playoff_screens_category`
                    SET `deleted` = 1
                    WHERE `categoryID` = " . $category_id
            );

            header('Location: admincenter.php?site=cup&mod=categories&action=show&category=screenshot');

        } else {

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            cmpsc.`categoryID`,
                            cmpsc.`name`,
                            cmpsc.`game_id`,
                            g.`name` AS `game`
                        FROM `" . PREFIX . "cups_matches_playoff_screens_category` cmpsc
                        LEFT JOIN `" . PREFIX . "games` g ON cmpsc.`game_id` = g.`gameID`
                        WHERE `deleted` = 0 AND `categoryID` = " . $category_id
                )
            );

            $data_array = array();
            $data_array['$cat_id'] = $category_id;
            $data_array['$name'] = $ds['name'];
            $data_array['$game'] = $ds['game'];
            $screenshot_delete = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_screenshot_delete", $data_array);
            echo $screenshot_delete;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
