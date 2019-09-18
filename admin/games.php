<?php

try {

    $_language->readModule('games', false, true);

    if (!ispageadmin($userID) || (mb_substr(basename($_SERVER[ 'REQUEST_URI' ]), 0, 15) != "admincenter.php")) {
        throw new \UnexpectedValueException($_language->module[ 'access_denied' ]);
    }

    $filepath = "../images/games/";

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=games';

        try {

            if (isset($_POST[ 'save' ]) || isset($_POST[ "saveedit" ])) {

                $status = FALSE;

                $name = getinput($_POST[ "name" ]);
                $short = getinput($_POST[ "short" ]);
                $tag = getinput($_POST[ "tag" ]);
                $tag_old = getinput($_POST[ "tag_old" ]);

                if (empty($tag) || preg_match('/:/', $tag)) {
                    throw new \UnexpectedValueException($_language->module['error_game_tag']);
                }

                $cup_auto_active = (isset($_POST[ "cup_auto_active" ]) && ($_POST[ "cup_auto_active" ] == '1')) ?
                    1 : 0;

                if (!checkforempty(array('name', 'short', 'tag'))) {
                    throw new \UnexpectedValueException($_language->module['fill_correctly']);
                }

                if (isset($_POST[ 'save' ])) {

                    $saveQuery = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "games`
                            (
                                `name`,
                                `short`,
                                `tag`,
                                `cup_auto_active`
                            ) VALUES (
                                '" . $name . "',
                                '" . $short . "',
                                '" . $tag ."',
                                " . $cup_auto_active . "
                            )"
                    );

                    if (!$saveQuery) {
                        throw new \UnexpectedValueException($_language->module['query_insert_failed']);
                    }

                    $game_id = mysqli_insert_id($_database);

                } else {

                    $game_id = (int)$_POST[ "game_id" ];

                    if (!validate_int($game_id, true)) {
                        throw new \UnexpectedValueException($_language->module['unknown_game']);
                    }

                    $saveQuery = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "games`
                            SET `name` = '" . $name . "',
                                `tag` = '" . $tag ."',
                                `short` = '" . $short . "',
                                `cup_auto_active` = " . $cup_auto_active . "
                            WHERE `gameID` = " . $game_id
                    );

                    if (!$saveQuery) {
                        throw new \UnexpectedValueException($_language->module['query_update_failed']);
                    }

                    $updateCupsQuery = cup_query(
                        "UPDATE `" . PREFIX . "cups`
                            SET `game` = '" . $tag . "'
                            WHERE `category` = '" . $tag_old . "'",
                        __FILE__
                    );

                    $updateGameaccountsQuery = cup_query(
                        "UPDATE `" . PREFIX . "cups_gameaccounts`
                            SET `category` = '" . $tag . "'
                            WHERE `category` = '" . $tag_old . "'",
                        __FILE__
                    );

                }

                $_SESSION['successArray'][] = $_language->module['query_saved'];

                $_language->readModule('formvalidation', true);

                if (isset($_FILES[ "icon" ])) {

                    $upload = new \webspell\HttpUpload('icon');
                    if ($upload->hasFile()) {

                        $errors = array();

                        try {

                            if ($upload->hasError() !== false) {
                                throw new \UnexpectedValueException($upload->translateError());
                            }

                            $mime_types = array(
                                'image/jpg',
                                'image/gif',
                                'image/png'
                            );

                            if (!$upload->supportedMimeType($mime_types)) {
                                throw new \UnexpectedValueException($_language->module['unsupported_image_type']);
                            }

                            $imageInformation = getimagesize($upload->getTempFile());

                            if (!is_array($imageInformation)) {
                                throw new \UnexpectedValueException($_language->module['broken_image']);
                            }

                            $filename = $tag . '.' . $upload->getExtension();

                            $deleteExistingIconsIfExisting = array(
                                'jpg',
                                'gif',
                                'png'
                            );

                            foreach ($deleteExistingIconsIfExisting as $image_extension) {

                                $file_to_be_deleted = $filepath . $tag_old . '.' . $image_extension;
                                if (file_exists($file_to_be_deleted)) {
                                    unlink($file_to_be_deleted);
                                }

                                $file_to_be_deleted = $filepath . $tag . '.' . $image_extension;
                                if (file_exists($file_to_be_deleted)) {
                                    unlink($file_to_be_deleted);
                                }

                            }

                            if (!$upload->saveAs($filepath . $filename, true)) {
                                throw new \UnexpectedValueException($_language->module['broken_image']);
                            }

                            @chmod($filepath . $filename, 644);

                        } catch (Exception $e) {
                            $errors[] = $e->getMessage();
                        }

                        if (validate_array($errors, true)) {
                            $_SESSION['errorArray'] = $errors;
                        }

                    }

                }

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header("Location: " . $parent_url);

    } else {

        $game_id = (isset($_GET[ 'id' ]) && validate_int($_GET[ 'id' ], true)) ?
            (int)$_GET[ 'id' ] : 0;

        if (isset($_GET[ "delete" ])) {

            if ($game_id < 1) {
                throw new \UnexpectedValueException($_language->module['unknown_game']);
            }

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            `name`,
                            `tag`
                        FROM `" . PREFIX . "games`
                        WHERE `gameID` = " . $game_id
                )
            );

            $game_tag = $ds['tag'];

            $deleteQuery = mysqli_query(
                $_database,
                "DELETE FROM `" . PREFIX . "games`
                    WHERE `gameID` = " . $game_id
            );

            if (file_exists($filepath . $game_tag . ".gif")) {
                unlink($filepath . $game_tag . ".gif");
            }

            if (!$deleteQuery) {
                throw new \UnexpectedValueException($_language->module['query_delete_failed'] . ' (`games`)');
            }

            $deleteQuery = mysqli_query(
                $_database,
                "DELETE FROM `" . PREFIX . "cups_gameaccounts`
                    WHERE `category` = '" . $game_tag . "'"
            );

            if (!$deleteQuery) {
                throw new \UnexpectedValueException($_language->module['query_delete_failed'] . ' (`cups_gameaccounts`)');
            }

            header("Location: admincenter.php?site=games");

        } else if ($getAction == "edit") {

            if ($game_id < 1) {
                throw new \UnexpectedValueException($_language->module['unknown_game']);
            }

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT * FROM `" . PREFIX . "games`
                        WHERE gameID = " . $game_id
                )
            );

            $pic = '<img src="' . getGameIcon($ds[ 'tag' ], true) . '.gif" alt="" />';

            $data_array = array();
            $data_array['$name'] = getinput($ds['name']);
            $data_array['$short'] = getinput($ds['short']);
            $data_array['$tag'] = getinput($ds['tag']);
            $data_array['$cup_auto_active'] = $ds['cup_auto_active'];
            $data_array['$icon'] = $pic;
            $data_array['$game_id'] = $game_id;
            $data_array['$postName'] = 'saveedit';
            $game_edit = $GLOBALS["_template_cup"]->replaceTemplate("game_action", $data_array);
            echo $game_edit;

        } else {

            $ergebnis = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "games`
                    ORDER BY `active` DESC, `name` ASC"
            );

            $anz = mysqli_num_rows($ergebnis);

            if ($anz > 0) {

                $active_games = '';
                $inactive_games = '';

                while ($ds = mysqli_fetch_array($ergebnis)) {

                    $game_id = $ds[ 'gameID' ];
                    $game_tag = $ds['tag'];

                    $active_class = ($ds['active']) ?
                        'btn btn-success btn-xs' : 'btn btn-danger btn-xs';

                    $active_txt = ($ds['active']) ?
                        $_language->module[ 'active' ] : $_language->module[ 'inactive' ];

                    $auto_active_class = ($ds['cup_auto_active']) ?
                        'btn btn-success btn-xs' : 'btn btn-danger btn-xs';

                    $auto_active_txt = ($ds['cup_auto_active']) ?
                        $_language->module[ 'yes' ] : $_language->module[ 'no' ];

                    $edit_url = 'admincenter.php?site=games&amp;action=edit&amp;id=' . $game_id;
                    $delete_url	= 'admincenter.php?site=games&amp;delete=true&amp;id=' . $game_id;

                    $data_array = array();
                    $data_array['$gameID'] = $game_id;
                    $data_array['$pic'] = getGameIcon($game_tag, true);
                    $data_array['$name'] = $ds[ 'name' ];
                    $data_array['$short'] = $ds[ 'short' ];
                    $data_array['$tag'] = $game_tag;
                    $data_array['$active_class'] = $active_class;
                    $data_array['$active_txt'] = $active_txt;
                    $data_array['$auto_active_class'] = $auto_active_class;
                    $data_array['$auto_active_txt'] = $auto_active_txt;
                    $data_array['$edit_url'] = $edit_url;
                    $data_array['$delete_url'] = $delete_url;

                    if ($ds['active']) {
                        $active_games .= $GLOBALS["_template_cup"]->replaceTemplate("games_list", $data_array);
                    } else {
                        $inactive_games .= $GLOBALS["_template_cup"]->replaceTemplate("games_list", $data_array);
                    }

                }

            }

            $no_entries = '<tr><td colspan="8">' . $_language->module[ 'no_entries' ] . '</td></tr>';

            if (empty($active_games)) {
                $active_games = $no_entries;
            }

            if (empty($inactive_games)) {
                $inactive_games = $no_entries;
            }

            $data_array = array();
            $data_array['$name'] = '';
            $data_array['$short'] = '';
            $data_array['$tag'] = '';
            $data_array['$cup_auto_active'] = '';
            $data_array['$game_id'] = 0;
            $data_array['$icon'] = '';
            $data_array['$postName'] = 'save';
            $add_game = $GLOBALS["_template_cup"]->replaceTemplate("game_action", $data_array);

            $data_array = array();
            $data_array['$active_games'] = $active_games;
            $data_array['$inactive_games'] = $inactive_games;
            $data_array['$add_game'] = $add_game;
            $games_home = $GLOBALS["_template_cup"]->replaceTemplate("games_home", $data_array);
            echo $games_home;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
