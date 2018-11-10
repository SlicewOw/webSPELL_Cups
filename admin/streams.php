<?php

try {

    $_language->readModule('streams', false, true);

    if (!ispageadmin($userID) || (mb_substr(basename($_SERVER[ 'REQUEST_URI' ]), 0, 15) != "admincenter.php")) {
        throw new \Exception($_language->module['no_access']);
    }

    $max_cronjobs = 5;

    if (validate_array($_POST, true)) {

        //
        // Stream Klasse
        systeminc('classes/streams');

        $parent_url = 'admincenter.php?site=streams';

        try {

            if (isset($_POST['submitAddStream']) || isset($_POST['submitEditStream'])) {

                $stream = new \myrisk\stream();

                if (isset($_POST['twitch_url'])) {
                    $id = strtolower($_POST['twitch_url']);
                } else {
                    $id = null;
                }

                $stream->setValue($id);

                if (isset($_POST['socials'])) {

                    if (validate_array($_POST['socials'], true)) {
                       $stream->setSocialNetwork($_POST['socials']);
                    }

                }

                if (isset($_POST['submitEditStream'])) {

                    $liveshow_id = (isset($_POST['stream_id']) && validate_int($_POST['stream_id'], true)) ?
                        (int)$_POST['stream_id'] : 0;

                    if ($liveshow_id < 1) {
                        throw new \Exception($_language->module['unknown_stream']);
                    }

                    $stream->setID($liveshow_id);

                }

                $stream->saveStream();

                if ($stream->getCronJob() > 0) {
                    $parent_url .= '&cronID=' . $stream->getCronJob();
                }

            } else {
                throw new \Exception($_language->module['unknown_stream']);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        if ($getAction == "add") {

            $data_array = array();
            $data_array['$liv_id'] = 0;
            $data_array['$twitch_id'] = '';
            $data_array['$facebook'] = '';
            $data_array['$twitter'] = '';
            $data_array['$youtube'] = '';
            $data_array['$selected'] = '';
            $data_array['$stream_id'] = 0;
            $data_array['$postName'] = 'submitAddStream';
            $stream_add = $GLOBALS["_template_cup"]->replaceTemplate("stream_action", $data_array);
            echo $stream_add;

        } else if ($getAction == "edit") {

            $stream_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ? 
                (int)$_GET['id'] : 0;

            if ($stream_id < 1) {
                throw new \Exception($_language->module['unknown_stream']);
            }

            $ergebnis = mysqli_query(
                $_database,
                "SELECT * FROM `" . PREFIX . "liveshow`
                    WHERE `livID` = " . $stream_id
            );

            if (!$ergebnis) {
                throw new \Exception($_language->module['query_failed']);
            }

            if (mysqli_num_rows($ergebnis) != 1) {
                throw new \Exception($_language->module['unknown_stream']);
            }

            $ds = mysqli_fetch_array($ergebnis);

            $data_array = array();
            $data_array['$twitch_id'] = 'https://twitch.tv/'.$ds['id'];
            $data_array['$facebook'] = (!empty($ds['facebook'])) ?
                'https://facebook.com/'.$ds['facebook'] : '';
            $data_array['$twitter'] = (!empty($ds['twitter'])) ?
                'https://twitter.com/'.$ds['twitter'] : '';
            $data_array['$youtube'] = (!empty($ds['youtube'])) ?
                'https://youtube.com/'.$ds['youtube'] : '';
            $data_array['$stream_id'] = $stream_id;
            $data_array['$postName'] = 'submitEditStream';
            $stream_edit = $GLOBALS["_template_cup"]->replaceTemplate("stream_action", $data_array);
            echo $stream_edit;

        } else if ($getAction == "del") {

            $stream_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ? 
                (int)$_GET['id'] : 0;

            if ($stream_id < 1) {
                throw new \Exception($_language->module['unknown_stream']);
            }

            $parent_url = 'admincenter.php?site=streams';

            $db = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT id, cronjobID, title FROM ".PREFIX."liveshow 
                        WHERE livID = " . $stream_id
                )
            );

            $deleteQuery = mysqli_query(
                $_database,
                "DELETE FROM ".PREFIX."liveshow
                    WHERE livID = ".$stream_id
            );

            if (!$deleteQuery) {
                throw new \Exception($_language->module['cannot_delete']);
            }

            $title = (!empty($db['title'])) ?
                $db['title'] : $db['id'];

            $_SESSION['successArray'][]  = $_language->module['deleted'];

            $parent_url .= '&cronID=' . $db['cronjobID'];

            header('Location: ' . $parent_url);

        } else if ($getAction == "active") {

            $parent_url = 'admincenter.php?site=streams';

            $stream_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ? 
                (int)$_GET['id'] : 0;

            if ($stream_id < 1) {
                throw new \Exception($_language->module['unknown_stream']);
            }

            $db = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            COUNT(*) AS is_existing,
                            id,
                            cronjobID,
                            active
                        FROM `" . PREFIX . "liveshow`
                        WHERE livID = ".$stream_id
                )
            );

            if ($db['is_existing'] != 1) {
                throw new \Exception($_language->module['unknown_stream']);
            }

            if (!$db['active']) {

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "liveshow`
                        SET `active` = 1
                        WHERE `livID` = " . $stream_id
                );

                if (!$updateQuery) {
                    throw new \Exception($_language->module['query_failed']);
                }

                $_SESSION['successArray'][]  = $_language->module['activated'];

            } else {
                $_SESSION['errorArray'][]  = $_language->module['still_activated'];
            }

            $parent_url .= '&cronID=' . $db['cronjobID'];

            header('Location: ' . $parent_url);

        } else {

            $cron_url = 'admincenter.php?site=streams&amp;cronID=';

            $buttons = '';
            for ($x = 1; $x < ($max_cronjobs + 1); $x++) {
                $buttons .= '<a href="' . $cron_url . $x . '" class="btn btn-default btn-sm bold">CronID '.$x.'</a>';
            }

            $cronID = (isset($_GET['cronID']) && validate_int($_GET['cronID'], true)) ?
                (int)$_GET['cronID'] : 1;

            $buttons = str_replace(
                'cronID=' . $cronID . '" class="btn',
                'cronID=' . $cronID . '" class="active btn',
                $buttons
            );

            $whereClause = 'a.`cronjobID` = ' . $cronID;

            $info = mysqli_query(
                $_database,
                "SELECT
                        a.`livID` AS `livID`,
                        a.`title` AS `title`,
                        a.`id` AS `id`,
                        a.`facebook` AS `facebook`,
                        a.`twitter` AS `twitter`,
                        a.`youtube` AS `youtube`,
                        a.`game` AS `game`,
                        a.`date` AS `date`,
                        a.`active` AS `isActive`,
                        a.`prioritization`,
                        b.`plattform` AS `plattform`
                    FROM `" . PREFIX . "liveshow` a
                    JOIN `" . PREFIX . "liveshow_type` b ON a.`type` = b.`typeID`
                    WHERE " . $whereClause . "
                    ORDER BY `prioritization` DESC, `id` ASC"
            );

            if (!$info) {
                throw new \Exception($_language->module['query_failed']);
            }

            $anzStreams = mysqli_num_rows($info);
            if ($anzStreams > 0) {

                $content = '';

                $socialTypeArray = array(
                    'facebook',
                    'twitter',
                    'youtube'
                );

                while ($ds = mysqli_fetch_array($info)) {

                    $socialArray = array();

                    foreach($socialTypeArray as $social) {

                        if (!empty($ds[$social])) {
                            $social_url = 'https://' . $social . '.com/' . $ds[$social];
                            $socialArray[] = '<a href="' . $social_url . '" class="indent" target="_blank">' . getSocialIcon($social, 'small', 'blue') . '</a>';
                        }

                    }

                    $socials = implode(' &nbsp; ', $socialArray);

                    $game = ($ds['game']) ? $ds['game'] :
                        '<span style="font-style: italic; color: #ff0000;">inaktiv</span>';

                    $date = '<span class="grey italic">- / -</span>';
                    if ($ds['date'] > 0) {

                        $inactive = mktime(0, 0, 0, date('m'), date('d')-7, date('Y'));

                        $datetime = getformatdatetime($ds['date']);

                        $date = ($inactive > $ds['date']) ?
                            '<span class="red italic">' . $datetime . '</span>' : $datetime;

                    }

                    $activate_url = 'admincenter.php?site=streams&amp;action=active&amp;id=' . $ds['livID'];
                    $activate_button = (!$ds['isActive']) ?
                        '<a href="' . $activate_url . '" class="btn btn-danger btn-xs white darkshadow">' . $_language->module['activate'] . '</a>' : '';

                    $data_array = array();
                    $data_array['$liv_id'] = $ds['livID'];
                    $data_array['$title'] = $ds['title'];
                    $data_array['$id'] = $ds['id'];
                    $data_array['$plattform'] = $ds['plattform'];

                    $data_array['$game'] = ($ds['game']) ?
                        $ds['game'] : '<span class="red italic">inaktiv</span>';

                    $data_array['$date'] = $date;
                    $data_array['$socials'] = $socials;
                    $data_array['$prio'] = $ds['prioritization'];
                    $data_array['$activate_button'] = $activate_button;
                    $content .= $GLOBALS["_template_cup"]->replaceTemplate("streams_list", $data_array);

                }

            } else {
                $content = '<tr><td colspan="8">' . showError($_language->module['no_stream'], true) . '</td></tr>';
            }

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            `hits`,
                            `date`
                        FROM `" . PREFIX . "liveshow_cronjobs`
                        WHERE id = " . $cronID
                )
            );

            $cron_info = '[Cronjob Info] Hits: '.$get['hits'].' / Update: '.getformatdatetime($get['date']);

            $data_array = array();
            $data_array['$buttons'] = $buttons;
            $data_array['$content'] = $content;
            $data_array['$anzStreams'] = $anzStreams;
            $data_array['$cron_info'] = $cron_info;
            $streams_home = $GLOBALS["_template_cup"]->replaceTemplate("streams_home", $data_array);
            echo $streams_home;

        }

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}
