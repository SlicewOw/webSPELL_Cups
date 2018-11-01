<?php

try {

    $_language->readModule('games', false, true);

    if (!ispageadmin($userID) || (mb_substr(basename($_SERVER[ 'REQUEST_URI' ]), 0, 15) != "admincenter.php")) {
        throw new \Exception($_language->module[ 'access_denied' ]);
    }

    $filepath = "../../images/games/";

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=games';

        try {

            if (isset($_POST[ 'save' ]) || isset($_POST[ "saveedit" ])) {

                $status = FALSE;

                $name 	= getinput($_POST[ "name" ]);
                $short 	= getinput($_POST[ "short" ]);
                $tag 	= getinput($_POST[ "tag" ]);

                if (!checkforempty(array('name', 'short', 'tag'))) {
                    throw new \Exception($_language->module['fill_correctly']);
                }

                if(isset($_POST[ 'save' ])) {

                    $saveQuery = mysqli_query(
                        $_database,
                        "INSERT INTO " . PREFIX . "games (
                            name,
                            short,
                            tag
                        ) VALUES (
                            '" . $name . "',
                            '" . $short . "',
                            '" . $tag ."'
                        )"
                    );

                    if (!$saveQuery) {
                        throw new \Exception($_language->module['query_insert_failed']);
                    }

                    $game_id = mysqli_insert_id($_database);

                } else {

                    $game_id = (int)$_POST[ "game_id" ];

                    if (!validate_int($game_id, true)) {
                        throw new \Exception($_language->module['unknown_game']);
                    }

                    $saveQuery = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "games`
                            SET	`name` = '" . $name . "',
                                `tag` = '" . $tag ."',
                                `short` = '" . $short . "'
                            WHERE `gameID` = " . $game_id
                    );

                    if (!$saveQuery) {
                        throw new \Exception($_language->module['query_update_failed']);
                    }

                }

                $_SESSION['successArray'][] = $_language->module['query_saved'];

                $errors = array();

                $_language->readModule('formvalidation', true);

                if (isset($_FILES[ "icon" ])) {

                    $upload = new \webspell\HttpUpload('icon');
                    if ($upload->hasFile()) {
                        
                        if ($upload->hasError() === false) {
                            
                            $mime_types = array('image/gif');

                            if ($upload->supportedMimeType($mime_types)) {
                                
                                $imageInformation = getimagesize($upload->getTempFile());

                                if (is_array($imageInformation)) {
                                    
                                    $file = $tag . ".gif";

                                    if ($upload->saveAs($filepath . $file, true)) {
                                        @chmod($filepath . $file, $new_chmod);
                                    }
                                    
                                } else {
                                    $errors[] = $_language->module['broken_image'];
                                }
                                
                            } else {
                                $errors[] = $_language->module['unsupported_image_type'];
                            }
                            
                        } else {
                            $errors[] = $upload->translateError();
                        }
                    }

                }

                if (isset($_FILES[ "game_image" ])) {

                    $icon = $_FILES[ "game_image" ];
                    $upload = new \webspell\HttpUpload('game_image');
                    if ($upload->hasFile()) {
                        
                        if ($upload->hasError() === false) {

                            $imageInformation = getimagesize($upload->getTempFile());

                            if (is_array($imageInformation)) {

                                $image_ext = getImageType($icon);
                                
                                if (empty($image_ext)) {
                                    throw new \Exception('empty_image_extension');
                                }
                                
                                $file = $tag . "." . $image_ext;

                                $filepath = "../../images/squadicons/";
                                if ($upload->saveAs($filepath . $file, true)) {
                                    @chmod($filepath . $file, $new_chmod);
                                }

                            } else {
                                $errors[] = $_language->module['broken_image'];
                            }

                        } else {
                            $errors[] = $upload->translateError();
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
                throw new \Exception($_language->module['unknown_game']);
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

            $saveQuery = mysqli_query(
                $_database,
                "DELETE FROM " . PREFIX . "games 
                    WHERE `gameID` = " . $game_id
            );
            
            if (file_exists($filepath.$ds['tag'].".gif")) {
                unlink($filepath.$ds['tag'].".gif");
            }

            if (!$saveQuery) {
                setLog('Games: games_query_delete_failed', __FILE__, 0, false);
                throw new \Exception($_language->module['query_delete_failed']);
            }

            header("Location: admincenter.php?site=games");

        } else if ($getAction == "edit") {

            if ($game_id < 1) {
                throw new \Exception($_language->module['unknown_game']);
            }

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT * FROM " . PREFIX . "games
                        WHERE gameID='" . $game_id . "'"
                )
            );

            $pic = '';
            if (file_exists($dir_global.'images/games/' . $ds[ 'tag' ] . '.gif')) {
                $pic = '<img src="' . $image_url . '/games/' . $ds[ 'tag' ] . '.gif" alt="">';
            }

            $data_array = array();
            $data_array['$name'] = getinput($ds['name']);
            $data_array['$short'] = getinput($ds['short']);
            $data_array['$tag'] = getinput($ds['tag']);
            $data_array['$icon'] = $pic;
            $data_array['$game_id'] = $game_id;
            $data_array['$postName'] = 'saveedit';
            $game_edit = $GLOBALS["_template"]->replaceTemplate("game_add", $data_array);
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
                    
                    $file = 'games/' . $ds['tag'] . '.gif';
                    $pic = (file_exists('../../images/'.$file)) ? 
                        '<img src="'.$image_url.'/'.$file.'" alt="" />' : '';

                    $active_class = ($ds['active']) ? 
                        'btn btn-success btn-xs' : 'btn btn-danger btn-xs';

                    $active_txt = ($ds['active']) ? 
                        $_language->module[ 'active' ] : $_language->module[ 'inactive' ];

                    $edit_url = 'admincenter.php?site=games&amp;action=edit&amp;id=' . $game_id;
                    $delete_url	= 'admincenter.php?site=games&amp;delete=true&amp;id=' . $game_id;

                    $data_array = array();
                    $data_array['$gameID'] = $game_id;
                    $data_array['$pic'] = $pic;
                    $data_array['$name'] = $ds[ 'name' ];
                    $data_array['$short'] = $ds[ 'short' ];
                    $data_array['$tag'] = $ds[ 'tag' ];
                    $data_array['$active_class'] = $active_class;
                    $data_array['$active_txt'] = $active_txt;
                    $data_array['$edit_url'] = $edit_url;
                    $data_array['$delete_url'] = $delete_url;

                    if ($ds['active']) {
                        $active_games .= $GLOBALS["_template"]->replaceTemplate("games_list", $data_array);
                    } else {
                        $inactive_games .= $GLOBALS["_template"]->replaceTemplate("games_list", $data_array);
                    }

                }

            } else {
                $active_games = '<tr><td colspan="5">' . $_language->module[ 'no_entries' ] . '</td></tr>';
                $inactive_games = '<tr><td colspan="5">' . $_language->module[ 'no_entries' ] . '</td></tr>';
            }

            $data_array = array();
            $data_array['$name'] 		= '';
            $data_array['$short'] 		= '';
            $data_array['$tag'] 		= '';
            $data_array['$game_id'] 	= 0;
            $data_array['$icon'] 		= '';
            $data_array['$image'] 		= '';
            $data_array['$ts_tab'] 		= '';
            $data_array['$ts_bg'] 		= '';
            $data_array['$postName'] 	= 'save';
            $add_game = $GLOBALS["_template"]->replaceTemplate("game_add", $data_array);

            $data_array = array();
            $data_array['$active_games'] = $active_games;
            $data_array['$inactive_games'] = $inactive_games;
            $data_array['$add_game'] = $add_game;
            $games_home = $GLOBALS["_template"]->replaceTemplate("games_home", $data_array);
            echo $games_home;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
