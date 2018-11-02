<?php

try {

    $_language->readModule('squads', false, true);

    if (!isuseradmin($userID) || (mb_substr(basename($_SERVER[ 'REQUEST_URI' ]), 0, 15) != "admincenter.php")) {
        echo $_language->module[ 'access_denied' ];
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'admincenter.php?site=member_positions';

        try {

            if (isset($_POST['saveAddPosition']) || isset($_POST['saveEditPosition'])) {

                $name = (isset($_POST['name'])) ?
                    getinput($_POST['name']) : '';

                if(empty($name)) {
                    throw new \Exception($_language->module[ 'error_position_name' ]);
                }

                $tag = (isset($_POST['tag'])) ?
                    getinput($_POST['tag']) : '';

                if(empty($tag) || ($tag > 2)) {
                    throw new \Exception($_language->module[ 'error_position_tag' ]);
                }

                $tag = strtolower($tag);

                $game_id = (isset($_POST['game_id']) && validate_int($_POST['game_id'])) ?
                    $_POST['game_id'] : null;

                if ($game_id < 1) {
                    $game_id = null;
                }

                $get = mysqli_fetch_array(
                    mysqli_query(
                        $_database,
                        "SELECT 
                                COUNT(*) AS `anz` 
                            FROM `" . PREFIX . "user_position_static`"
                    )
                );

                $sort = $get['anz'] + 1;

                if (isset($_POST['saveAddPosition'])) {

                    $setGameColumn = (is_null($game_id)) ? 
                        '' : '`game_id`,';

                    $setGameValue = (is_null($game_id)) ? 
                        '' : $game_id . ',';

                    $query = mysqli_query(
                        $_database,
                        "INSERT INTO `" . PREFIX . "user_position_static`
                            (
                                `name`, 
                                `tag`, 
                                " . $setGameColumn . " 
                                `sort`
                            )
                            VALUES
                            (
                                '" . $name . "', 
                                '" . $tag . "', 
                                " . $setGameValue . "
                                " . $sort . "
                            )"
                    );

                    if (!$query) {
                        throw new \Exception($_language->module[ 'query_failed' ]);
                    }

                    $position_id = mysqli_insert_id($_database);

                } else {

                    $position_id = (isset($_POST['position_id']) && validate_int($_POST['position_id'])) ?
                        (int)$_POST['position_id'] : 0;

                    if ($position_id < 1) {
                        throw new \Exception($_language->module[ 'unknown_position' ]);
                    }

                    $setGameValue = (is_null($game_id)) ? 
                        'game_id = NULL' : 'game_id = ' . $game_id;

                    $query = mysqli_query(
                        $_database,
                        "UPDATE `" . PREFIX . "user_position_static` 
                            SET name = '".$name."', 
                                tag = '".$tag."',
                                " . $setGameValue . "
                            WHERE positionID = " . $position_id
                    );

                    if (!$query) {
                        throw new \Exception($_language->module[ 'query_failed' ]);
                    }

                }

                $_SESSION['successArray'][] = $_language->module[ 'query_saved' ];

            } else if (isset($_POST['saveDeletePosition'])) {

                $position_id = (isset($_POST['position_id']) && validate_int($_POST['position_id'])) ?
                    (int)$_POST['position_id'] : 0;

                if ($position_id < 1) {
                    throw new \Exception($_language->module[ 'unknown_position' ]);
                }

                $name = getoutput($_POST['name']);

                $query = mysqli_query(
                    $_database,
                    "DELETE FROM `" . PREFIX . "user_position_static` 
                        WHERE positionID = " . $position_id
                );

                if (!$query) {
                    throw new \Exception($_language->module[ 'query_failed' ]);
                }

                $_SESSION['successArray'][] = $_language->module[ 'query_deleted' ];

            } else if (isset($_POST['sort'])) {

                $errorCount = 0;

                if (!isset($_POST['positionArray']) || is_array($_POST['positionArray'])) {
                    throw new \Exception($_language->module[ 'unknown_action' ]);
                }

                $positionArray = explode(',', $_POST['positionArray']);

                $anzPositions = count($positionArray);
                for($x=0;$x<$anzPositions;$x++) {

                    $position_id = $positionArray[$x];
                    if(isset($_POST[$position_id])) {

                        $query = mysqli_query(
                            $_database,
                            "UPDATE `".PREFIX."user_position_static` 
                                SET sort = " . $_POST[$position_id] . " 
                                WHERE positionID = " . $position_id
                        );

                        if(!$query) {
                            throw new \Exception($_language->module[ 'query_failed' ]);
                        }

                    }

                }

                $_SESSION['successArray'][] = $_language->module[ 'query_saved' ];

            } else {
                throw new \Exception($_language->module[ 'unknown_action' ]);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $games = getGamesAsOptionList('', FALSE, TRUE);

        if ($getAction == 'edit') {

            $position_id = (isset($_GET['id']) && validate_int($_GET['id'])) ? 
                (int)$_GET['id'] : 0;

            if($position_id < 1) {
                throw new \Exception($_language->module[ 'access_denied' ]);
            }

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT positionID, name, tag, game_id FROM `".PREFIX."user_position_static` 
                        WHERE positionID = " . $position_id
                )
            );

            if(!is_null($get['game_id']) && ($get['game_id'] > 0)) {

                $games = str_replace(
                    'value="' . $get['game_id'] . '"',
                    'value="' . $get['game_id'] . '" selected="selected"',
                    $games
                );

            }

            $data_array = array();
            $data_array['$title'] = $_language->module[ 'edit_position' ];
            $data_array['$name'] = getoutput($get['name']);
            $data_array['$tag'] = getoutput($get['tag']);
            $data_array['$games'] = $games;
            $data_array['$position_id'] = $position_id;
            $data_array['$postName'] = 'saveEditPosition';
            $edit_position = $GLOBALS["_template_cup"]->replaceTemplate("member_position_action", $data_array);
            echo $edit_position;

        } else if ($getAction == 'delete') {

            $position_id = (isset($_GET['id']) && validate_int($_GET['id'])) ? 
                (int)$_GET['id'] : 0;

            if($position_id < 1) {
                throw new \Exception($_language->module[ 'access_denied' ]);
            }

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT positionID, name, tag FROM `" . PREFIX . "user_position_static` 
                        WHERE positionID = " . $position_id
                )
            );

            $data_array = array();
            $data_array['$title'] = $_language->module[ 'delete_position' ];
            $data_array['$name'] = getoutput($get['name']);
            $data_array['$tag'] = getoutput($get['tag']);
            $data_array['$games'] = $games;
            $data_array['$position_id'] = $position_id;
            $data_array['$postName'] = 'saveDeletePosition';
            $delete_position = $GLOBALS["_template_cup"]->replaceTemplate("member_position_action", $data_array);
            echo $delete_position;

        } else {

            $positionArray = array();

            $query = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "user_position_static` 
                    ORDER BY `sort` ASC"
            );
            if(mysqli_num_rows($query) > 0) {

                $positionList = '';
                while($ds = mysqli_fetch_array($query)) {

                    $position_id = $ds['positionID'];

                    $positionArray[] = $position_id;

                    if(!is_null($ds['game_id']) && ($ds['game_id'] > 0)) {

                        $gameArray = getGame($ds['game_id']);
                        $game = $gameArray['short'];

                    } else {
                        $game = '';
                    }

                    $data_array = array();
                    $data_array['$position_id'] = $position_id;
                    $data_array['$name'] = $ds['name'];
                    $data_array['$tag'] = $ds['tag'];
                    $data_array['$game'] = $game;
                    $data_array['$sort'] = $ds['sort'];
                    $positionList .= $GLOBALS["_template_cup"]->replaceTemplate("member_position_list", $data_array);

                }

            } else {
                $positionList = '<tr><td colspan="5">' . $_language->module[ 'no_position' ] . '</td></tr>';
            }

            $positionString = (count($positionArray) > 0) ? implode(',', $positionArray) : 0;

            $data_array = array();
            $data_array['$title'] = $_language->module[ 'add_position' ];
            $data_array['$name'] = '';
            $data_array['$tag'] = '';
            $data_array['$games'] = $games;
            $data_array['$position_id'] = 0;
            $data_array['$postName'] = 'saveAddPosition';
            $add_position = $GLOBALS["_template_cup"]->replaceTemplate("member_position_action", $data_array);

            $data_array = array();
            $data_array['$positionList'] = $positionList;
            $data_array['$positionArray'] = $positionString;
            $data_array['$add_position'] = $add_position;
            $position_home = $GLOBALS["_template_cup"]->replaceTemplate("member_position_home", $data_array);
            echo $position_home;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
