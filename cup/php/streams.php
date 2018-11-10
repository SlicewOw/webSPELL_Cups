<?php

try {

    $_language->readModule('liveshow');

    $new = '';

    if (validate_array($_POST, true)) {

        $parent_url = 'index.php?site=streams';

        try {

            if (isset($_POST['submit_edit'])) {

                $stream_id = (isset($_POST['stream_id']) && validate_int($_POST['stream_id'])) ?
                    (int)$_POST['stream_id'] : 0;

                if ($stream_id < 1) {
                    throw new \Exception($_language->module['error_unknown_id']);
                }

                if (!checkIfContentExists($stream_id, 'livID', 'liveshow')) {
                    throw new \Exception($_language->module['error_unknown_id']);
                }

                $id = strtolower(getinput($_POST['twitch_id']));
                $id = convert2id($id, 'twitch');

                $fb = (isset($_POST['facebook']) && validate_url($_POST['facebook'])) ?
                    convert2id($_POST['facebook'], 'facebook') : '';

                $tw = (isset($_POST['twitter']) && validate_url($_POST['twitter'])) ?
                    convert2id($_POST['twitter'], 'twitter') : '';

                $yt = (isset($_POST['youtube']) && validate_url($_POST['youtube'])) ?
                    convert2id($_POST['youtube'], 'youtube') : '';

                $active = (isanyadmin($userID)) ? 1 : 0;

                $ergebnis = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "liveshow`
                        SET `id` = '" . $id . "',
                            `facebook` = '" . $fb . "',
                            `twitter` = '" . $tw . "',
                            `youtube` = '" . $yt . "',
                            `active` = '" . $active . "'
                        WHERE `livID` = " . $stream_id
                );

                if (!$ergebnis) {
                    throw new \Exception($_language->module['query_failed_update']);
                }

                $parent_url .= '&id=' . $stream_id;

            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $stream_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
            (int)$_GET['id'] : 0;

        if ($getAction == 'edit') {

            if (!istvadmin($userID)) {
                throw new \Exception($_language->module['access_denied']);
            }

            if ($stream_id < 1) {
                throw new \Exception($_language->module['error_unknown_id']);
            }

            if (!checkIfContentExists($stream_id, 'livID', 'liveshow')) {
                throw new \Exception($_language->module['error_unknown_id']);
            }

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT * FROM `" . PREFIX . "liveshow`
                        WHERE `livID` = " . $stream_id
                )
            );

            $data_array = array();
            $data_array['$stream_id'] = $stream_id;
            $data_array['$twitch_id'] = 'https://twitch.tv/' . $ds['id'];
            $data_array['$facebook'] = (!empty($ds['facebook'])) ?
                'https://facebook.com/'.$ds['facebook'] : '';
            $data_array['$twitter'] = (!empty($ds['twitter'])) ?
                'https://twitter.com/'.$ds['twitter'] : '';
            $data_array['$youtube'] = (!empty($ds['youtube'])) ?
                'https://youtube.com/'.$ds['youtube'] : '';
            $liveshow_edit = $GLOBALS["_template_cup"]->replaceTemplate("liveshow_edit", $data_array);
            echo $liveshow_edit;

        } else if ($getAction == 'delete') {

            if (!istvadmin($userID)) {
                throw new \Exception($_language->module['access_denied']);
            }

            if ($stream_id < 1) {
                throw new \Exception($_language->module['error_unknown_id']);
            }

            if (!checkIfContentExists($stream_id, 'livID', 'liveshow')) {
                throw new \Exception($_language->module['error_unknown_id']);
            }

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT `userID` FROM `" . PREFIX . "liveshow`
                        WHERE livID = " . $stream_id
                )
            );

            if ($userID != $get['userID']) {
                throw new \Exception($_language->module['access_denied']);
            }

            $deleteQuery = mysqli_query(
                $_database,
                "DELETE FROM `" . PREFIX . "liveshow`
                    WHERE `livID` = " . $stream_id
            );

            if (!$deleteQuery) {
                throw new \Exception($_language->module['error_query_delete']);
            }

            $text = 'Stream #'.$stream_id.' gel&ouml;scht';
            $_SESSION['errorArray'][] = $text;

            header('Location: index.php?site=streams');

        } else if ($stream_id > 0) {

            if (!checkIfContentExists($stream_id, 'livID', 'liveshow')) {
                throw new \Exception($_language->module['error_unknown_id']);
            }

            $liveshow_show = '';

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                        a.userID AS userID,
                        a.title AS title,
                        a.id AS id,
                        a.game AS game,
                        a.facebook AS facebook,
                        a.twitter AS twitter,
                        a.youtube AS youtube,
                        a.active AS active,
                        b.video AS video_embed,
                        b.chat AS chat_embed,
                        g.`short`,
                        g.`tag`
                    FROM `" . PREFIX . "liveshow` a
                    JOIN `" . PREFIX . "liveshow_type` b ON a.`type` = b.`typeID`
                    LEFT JOIN `" . PREFIX . "games` g ON a.`game` = g.`name`
                    WHERE a.livID = " . $stream_id
                )
            );

            $plattform_id = $ds['id'];

            $whereClause = 'active = 1 AND online = 1';
            $game = addslashes($ds['game']);

            $base_url = 'index.php?site=streams&amp;action=';

            if ($loggedin && (($userID == $ds['userID'] && $ds['active'] != 2) || isanyadmin($userID))) {
                $adm = '<a class="btn btn-info btn-sm white darkshadow" href="' . $base_url . 'edit&amp;id=' . $stream_id . '">' . $_language->module['edit_liveshow'] . '</a>';
                $adm .= ' <a class="btn btn-info btn-sm white darkshadow" href="' . $base_url . 'delete&amp;id=' . $stream_id . '">' . $_language->module['delete'] . '</a>';
            } else {
                $adm = '';
            }

            $name = '';

            if (!empty($ds['short'])) {

                $gameShort = $ds['short'];
                $gameTag = $ds['tag'];

                $game_cat = (!empty($gameShort)) ?
                    '<a href="index.php?site=streams&amp;cat=' . $gameTag . '">' . $gameShort . '</a>' : $ds['game'];

                if ($game_cat > 0) {
                    $name .= '<li>' . $game_cat . '</li>';
                }

            }

            $title = (!empty($ds['title'])) ? $ds['title'] : $ds['id'];

            $name .= '<li>'.$title.'</li>';

            $code = '';
            if (!empty($ds['video_embed'])) {
                $code = str_replace(
                    '%channel_id%',
                    $plattform_id,
                    $ds['video_embed']
                );
            }

            $chat = '';
            if (!empty($ds['chat_embed'])) {
                $chat = str_replace(
                    '%channel_id%',
                    $plattform_id,
                    $ds['chat_embed']
                );
            }

            $data_array = array();
            $data_array['$streamID'] = $stream_id;
            $data_array['$adm'] = $adm;
            $data_array['$name'] = $name;
            $data_array['$title'] = $ds['title'];
            $data_array['$code'] = $code;
            $data_array['$chat'] = $chat;
            $liveshow_show = $GLOBALS["_template_cup"]->replaceTemplate("liveshow_show", $data_array);

            //
            // Update Hits
            setHits('liveshow', 'livID', $stream_id, false);

            $liveshow_content = '';

            $data_array = array();
            $data_array['$name'] = $name;
            $data_array['$liveshow_show'] = $liveshow_show;
            $data_array['$new'] = $new;
            $liveshow = $GLOBALS["_template_cup"]->replaceTemplate("liveshow_details", $data_array);
            echo $liveshow;

            $details = '';

            $socialMediaArray = array(
                'facebook',
                'twitter',
                'youtube'
            );

            $details_content = '';
            foreach ($socialMediaArray as $social_media) {

                if (!empty($ds[$social_media])) {
                    $details_content .= '<a href="https://' . $social_media . '.com/'.$ds[$social_media].'" target="_blank" class="list-group-item clearfix">';
                    $details_content .= '<span class="social-icon-small">' . getSocialIcon($social_media, 'small', 'blue') . '</span><span class="pull-right">'.$ds[$social_media].'</span>';
                    $details_content .= '</a>';
                }

            }

            if (!empty($details_content)) {

                $data_array = array();
                $data_array['$game_title'] = 'Social Media - ' . $ds['title'];
                $data_array['$game'] = $details_content;
                $data_array['$game_anz'] = '';
                $details .= $GLOBALS["_template_cup"]->replaceTemplate("liveshow_details_content", $data_array);

            }

            $details_content = '';

            $baseWhereClauseArray = array();
            $baseWhereClauseArray[] = '`livID` != ' . $stream_id;
            $baseWhereClauseArray[] = '`active` = 1';
            $baseWhereClauseArray[] = '`online` = 1';

            $orderArray = array();
            $orderArray[] = '`tv` DESC';
            $orderArray[] = '`prioritization` DESC';
            $orderArray[] = '`viewer` DESC';

            $orderByStatement = implode(', ', $orderArray);

            if (isset($gameShort)) {

                $whereClauseArray = $baseWhereClauseArray;
                $whereClauseArray[] = '`game` = \'' . $game . '\'';

                $whereClause = implode(' AND ', $whereClauseArray);

                $query = mysqli_query(
                    $_database,
                    "SELECT * FROM `" . PREFIX . "liveshow`
                        WHERE " . $whereClause . "
                        ORDER BY " . $orderByStatement
                );

                if (!$query) {
                    throw new \Exception($_language->module['query_select_failed']);
                }

                while ($db = mysqli_fetch_array($query)) {
                    $details_content .= '<a href="index.php?site=streams&amp;id='.$db['livID'].'" class="list-group-item">';
                    $details_content .= $db['title'];
                    $details_content .= '<span class="pull-right">'.$db['viewer'].'</span>';
                    $details_content .= '</a>';
                }

                $anz = mysqli_num_rows($query) - 1;
                if ($anz < 0) {
                    $details_content = '<div class="list-group-item">' . $_language->module['no_online'] . '</div>';
                    $anzahl = '';
                } else if ($anz == 0) {
                    $anzahl = '';
                } else {
                    $anzahl = ($anz == 1) ?
                        '1 ' . $_language->module['other'] :
                        $anz . ' ' . $_language->module['others'];
                    $anzahl = '<a class="list-group-item" href="index.php?site=streams&amp;cat=' . $gameTag . '&amp;online=true">' . $anzahl . '</a>';
                }

                $data_array = array();
                $data_array['$game_title']= $gameShort.' Streams';
                $data_array['$game'] = $details_content;
                $data_array['$game_anz'] = $anzahl;
                $details .= $GLOBALS["_template_cup"]->replaceTemplate("liveshow_details_content", $data_array);

            }

            $whereClauseArray = $baseWhereClauseArray;
            $whereClauseArray[] = '`game` != \'' . $game . '\'';

            $whereClause = implode(' AND ', $whereClauseArray);

            $details_content = '';
            $query = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "liveshow`
                    WHERE " . $whereClause . "
                    ORDER BY " . $orderByStatement
            );

            if (!$query) {
                throw new \Exception($_language->module['query_select_failed']);
            }

            $anzOtherStreams = mysqli_num_rows($query);
            if ($anzOtherStreams > 0) {

                $maxStreamCounter = 5;

                while ($db = mysqli_fetch_array($query)) {

                    $details_content .= '<a href="index.php?site=streams&amp;id='.$db['livID'].'" class="list-group-item">';
                    $details_content .= $db['title'];
                    $details_content .= '<span class="pull-right">'.$db['viewer'].'</span>';
                    $details_content .= '</a>';

                    $maxStreamCounter--;

                    if ($maxStreamCounter == 0) {
                        break;
                    }

                }

                $otherStreamCounter = ($anzOtherStreams -$maxStreamCounter);

                $anzahl = ($otherStreamCounter == 1) ?
                    '1 ' . $_language->module['other'] :
                    $otherStreamCounter . ' ' . $_language->module['others'];
                $anzahl = '<a class="list-group-item" href="index.php?site=streams&amp;action=all">' . $anzahl . '</a>';

            } else {
                $details_content = '<div class="list-group-item grey italic">' . $_language->module['no_online'] . '</div>';
                $anzahl = '';
            }

            $data_array = array();
            $data_array['$game_title'] = 'Streams';
            $data_array['$game'] = $details_content;
            $data_array['$game_anz'] = $anzahl;
            $details .= $GLOBALS["_template_cup"]->replaceTemplate("liveshow_details_content", $data_array);

            $data_array = array();
            $data_array['$details']	= $details;
            $liveshow_footer = $GLOBALS["_template_cup"]->replaceTemplate("liveshow_details_footer", $data_array);

            $parentID = $stream_id;
            $type = "st";
            $referer = 'index.php?site=streams&amp;id=' . $stream_id;
            $comments_allowed = 1;
            include(__DIR__ . '/../../comments.php');

            echo $liveshow_footer;

        } else if ($getAction == 'all') {

            $list = '';

            $orderArray = array();
            $orderArray[] = '`online` DESC';
            $orderArray[] = '`tv` DESC';
            $orderArray[] = '`prioritization` DESC';
            $orderArray[] = '`viewer` DESC';
            $orderArray[] = '`title` DESC';

            $orderByStatement = implode(', ', $orderArray);

            $selectQuery = mysqli_query(
                $_database,
                "SELECT * FROM `".PREFIX."liveshow`
                    WHERE `active` = 1
                    ORDER BY " . $orderByStatement
            );

            if (!$selectQuery) {
                throw new \Exception($_language->module['query_select_failed']);
            }

            while ($da = mysqli_fetch_array($selectQuery)) {

                if ($da['online'] == 1) {
                    $online = ' <span class="pull-right">'.$da['game'].' // '.$da['viewer'].' '.$_language->module['viewers'].'</span>';
                } else {
                    $online = '';
                }

                $list .= '<a class="list-group-item" href="index.php?site=streams&amp;id='.$da['livID'].'">'.$da['title'].$online.'</a>';

            }

            $data_array = array();
            $data_array['$list'] = $list;
            $liveshow = $GLOBALS["_template_cup"]->replaceTemplate("liveshow_all", $data_array);
            echo $liveshow;

        } else if (isset($_GET['cat'])) {

            $cat = getinput($_GET['cat']);

            $name = '';

            $game = '';
            if($cat == 'csg') {
                $game = 'Counter-Strike: Global Offensive';
            } elseif($cat == 'f16') {
                $game = 'FIFA16';
            } elseif($cat == 'lol' || $cat == 'LoL') {
                $game = 'League of Legends';
            }

            if(!empty($game)) {

                $name = '<li class="active">' . $game . '</li>';

                $ergebnis = mysqli_query(
                    $_database,
                    "SELECT
                            a.livID AS livID,
                            a.title AS title,
                            a.type AS type,
                            a.viewer AS viewer,
                            b.plattform AS typeName
                        FROM `".PREFIX."liveshow` a
                        JOIN `".PREFIX."liveshow_type` b ON a.type = b.typeID
                        WHERE active = '1' AND online = '1' AND game = '".$game."' 
                        ORDER BY `tv` DESC, `prioritization` DESC, `viewer` DESC"
                );
                if(mysqli_num_rows($ergebnis)) {

                    $streamlist = '';
                    while($ds = mysqli_fetch_array($ergebnis)) {

                        $streamlist .= '<a href="index.php?site=streams&amp;id='.$ds['livID'].'" class="list-group-item">';
                        $streamlist .= '<img src="' . getStreamTypeIcon($ds['type'], true) . '" alt="'.$ds['typeName'].'" title="'.$ds['typeName'].'" />';
                        $streamlist .= '<span class="box-padding-left">' . $ds['title'] . '</span>';
                        $streamlist .= '<span class="pull-right">'.$ds['viewer'].' '.$_language->module['viewers'].'</span>';
                        $streamlist .= '<span class="clear"></span>';
                        $streamlist .= '</a>';

                    }

                } else {
                    $streamlist = '<span class="list-group-item">'.$_language->module['no_online'].'</span>';
                }

                $data_array = array();
                $data_array['$name'] = $name;
                $data_array['$game'] = $game;
                $data_array['$streamlist'] = $streamlist;
                $data_array['$new'] = $new;
                $liveshow_cat = $GLOBALS["_template_cup"]->replaceTemplate("liveshow_cat", $data_array);
                echo $liveshow_cat;

            }

        } else {

            include(__DIR__ . '/topstream.php');

            $name = '<li class="active">Home</li>';

            $getGames = mysqli_query(
                $_database,
                "SELECT
                        `game`,
                        COUNT(*) AS `anz`
                    FROM `" . PREFIX . "liveshow`
                    WHERE `active` = 1 AND `online` = 1
                    GROUP BY `game`
                    ORDER BY COUNT(*) DESC
                    LIMIT 0, 3"
            );

            if ($getGames) {

                $gameArray = array();

                $topStreams = array(
                    1 => array(
                        'game' => array(
                            'name' => '',
                            'query' => '',
                            'short' => ''
                        ),
                        'list' => ''
                    ),
                    2 => array(
                        'game' => array(
                            'name' => '',
                            'query' => '',
                            'short' => ''
                        ),
                        'list' => ''
                    ),
                    3 => array(
                        'game' => array(
                            'name' => '',
                            'query' => '',
                            'short' => ''
                        ),
                        'list' => ''
                    )
                );

                $n = 1;
                while ($getTopStream=mysqli_fetch_array($getGames)) {

                    $anzStreams = $getTopStream['anz'];
                    if ($anzStreams > 0) {

                        $game = $getTopStream['game'];
                        $queryGame = addslashes($game);

                        $topStreams[$n]['game']['name'] = $game;
                        $topStreams[$n]['game']['query'] = $queryGame;

                        $gameArray[] = $queryGame;

                        $gameShort = getgameshort($game);
                        if (!empty($gameShort)) {
                            $gameTag = getgametag($game);
                            $topStreams[$n]['game']['short'] = $gameShort;
                        } else {
                            $gameTag = '';
                            $topStreams[$n]['game']['short'] = $game;
                        }

                        if(!empty($game)) {

                            $whereClauseArray = array();
                            $whereClauseArray[] = '`active` = 1';
                            $whereClauseArray[] = '`online` = 1';
                            $whereClauseArray[] = '`game` = \'' . $queryGame . '\'';

                            $whereClause = implode(' AND ', $whereClauseArray);

                            $selectQuery = mysqli_query(
                                $_database,
                                "SELECT
                                        COUNT(*) AS `anz`
                                    FROM `" . PREFIX . "liveshow`
                                    WHERE " . $whereClause
                            );

                            if (!$selectQuery) {
                                throw new \Exception($_language->module['query_select_failed']);
                            }

                            $get = mysqli_fetch_array($selectQuery);

                            $streamCount = $get['anz'];
                            if ($streamCount > 5) {
                                $streamCount = $streamCount - 5;
                            }

                            $topStreams[$n]['total'] = $streamCount;

                            $ergebnis = mysqli_query(
                                $_database,
                                "SELECT * FROM `" . PREFIX . "liveshow`
                                    WHERE " . $whereClause . "
                                    ORDER BY `tv` DESC, `prioritization` DESC, `viewer` DESC
                                    LIMIT 0, 5"
                            );

                            if (!$ergebnis) {
                                throw new \Exception($_language->module['query_select_failed']);
                            }

                            while ($ds=mysqli_fetch_array($ergebnis)) {

                                $stream_title = $ds['title'].' &nbsp; // &nbsp; '.$ds['game'].' &nbsp; // &nbsp; '.$ds['viewer'];
                                $stream_info = $ds['title'].'<span class="pull-right">'.$ds['viewer'].' '.$_language->module['viewers'].'</span>';

                                $stream_url = 'index.php?site=streams&amp;id='.$ds['livID'];
                                $topStreams[$n]['list'] .= '<a class="list-group-item" href="'.$stream_url.'" title="'.$stream_title.'">'.$stream_info.'</a>';

                            }

                            $categoryLink = (!empty($gameTag)) ? '&amp;cat='.$gameTag : '';

                            $anz = $get['anz'] - 5;
                            if ($anz == 1) {
                                $topStreams[$n]['list'] .= '<a href="index.php?site=streams'.$categoryLink.'" class="list-group-item">1 '.$_language->module['other'].'</a>';
                            } else if ($anz > 0) {
                                $topStreams[$n]['list'] .= '<a href="index.php?site=streams'.$categoryLink.'" class="list-group-item">'.$anz.' '.$_language->module['others'].'</a>';
                            }

                        }

                    } else {
                        $topStreams[$n]['list'] = '<span class="list-group-item grey italic">'.$_language->module['no_online'].'</span>';
                    }

                    $n++;

                }

                $restgame = '';

                if (validate_array($gameArray, true)) {

                    $whereClauseArray = array();
                    $whereClauseArray[] = '`active` = 1';
                    $whereClauseArray[] = '`online` = 1';
                    $whereClauseArray[] = '`game` NOT IN (\'' . implode('\', \'', $gameArray) . '\')';

                    $whereClause = implode(' AND ', $whereClauseArray);

                    $ergebnis = mysqli_query(
                        $_database,
                        "SELECT * FROM `".PREFIX."liveshow`
                            WHERE " . $whereClause . "
                            ORDER BY `tv` DESC, `prioritization` DESC, `viewer` DESC"
                    );

                    if ($ergebnis) {

                        $anz = mysqli_num_rows( $ergebnis );
                        if ($anz > 0) {

                            while ($ds=mysqli_fetch_array($ergebnis)) {

                                $streamTitleArray = array();
                                $streamTitleArray[] = $ds['title'];
                                $streamTitleArray[] = $ds['game'];
                                $streamTitleArray[] = $ds['viewer'];

                                $stream_title = implode(' &nbsp; // &nbsp; ', $streamTitleArray);

                                $stream_info = $ds['title'] . '<span class="pull-right">' . $ds['game'] . ' / ' . $ds['viewer'] . ' ' . $_language->module['viewers'] . '</span>';

                                $restgame .= '<a class="list-group-item black" href="index.php?site=streams&amp;id=' . $ds['livID'] . '#content" title="' . $stream_title . '">' . $stream_info . '</a>';

                            }

                        }

                        $restgame .= '<a class="list-group-item black" href="index.php?site=streams&amp;action=all#content">&raquo; ' . $_language->module['all_streams'] . '</a>';

                    }

                }

                $data_array = array();
                $data_array['$name'] = $name;
                $data_array['$liveshow_show'] = $liveshow_show;
                $data_array['$restgame'] = $restgame;
                $data_array['$new'] = $new;

                $game_categories = '';
                for ($x = 1; $x < 4; $x++) {

                    if (isset($topStreams[$x]['list']) && isset($topStreams[$x]['game']['short']) && !empty($topStreams[$x]['game']['short'])) {

                        $subdata_array = array();
                        $subdata_array['$game_short'] = $topStreams[$x]['game']['short'];
                        $subdata_array['$game'] = $topStreams[$x]['list'];
                        $game_categories .= $GLOBALS["_template_cup"]->replaceTemplate("liveshow_game_category", $subdata_array);

                    }

                }

                $data_array['$game_categories'] = $game_categories;
                $liveshow = $GLOBALS["_template_cup"]->replaceTemplate("liveshow", $data_array);
                echo $liveshow;

            }

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
