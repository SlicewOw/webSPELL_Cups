<?php

try {

    $_language->readModule('cups');

    $cup_id = (isset($_GET['cup_id']) && validate_int($_GET['cup_id'])) ? 
        (int)$_GET['cup_id'] : 0;

    if ($cup_id < 1) {
        throw new \Exception($_language->module['no_cup']);
    }

    //
    // Cup Array
    $cupArray = getcup($cup_id, 'all');

    checkCupDetails($cupArray, $cup_id);

    $time_now = time();
    $content = '';

    $getPage = (isset($_GET['page'])) ?
        getinput($_GET['page']) : 'home';

    if (empty($getPage)) {
        $getPage = 'home';
    }

    //
    // Cup Details Content
    $contentFile = __DIR__ . '/../php/includes/cup_details_' . $getPage . '.php';
    if (file_exists($contentFile)) {
        include($contentFile);
    }

    //
    // Cup Sponsoren
    $sponsors = mysqli_query(
        $_database,
        "SELECT
                `sponsorID`
            FROM `" . PREFIX . "cups_sponsors`
            WHERE `cupID` = " . $cup_id
    );
    if (mysqli_num_rows($sponsors) > 0) {

        $content_sponsors = '';
        while ($db = mysqli_fetch_array($sponsors)) {

            $sponsorArray = getsponsor($db['sponsorID']);

            $linkAttributeArray = array();
            $linkAttributeArray[] = 'href="' . $sponsorArray['url'] . '"';
            $linkAttributeArray[] = 'target="_blank"';
            $linkAttributeArray[] = 'title="' . $sponsorArray['name'] . '"';
            $linkAttributeArray[] = 'class="pull-left"';

            $sponsor_banner_url = getSponsorImage($db['sponsorID'], true, 'white');

            $content_sponsors .= '<a ' . implode(' ', $linkAttributeArray) . '><img src="' . $sponsor_banner_url . '" alt="' . $sponsorArray['name'] . '" /></a>';

        }

        $data_array = array();
        $data_array['$panel_type'] = 'panel-default';
        $data_array['$panel_title'] = 'Sponsoren';
        $data_array['$panel_content'] = $content_sponsors;
        $content .= $GLOBALS["_template_cup"]->replaceTemplate("panel_body", $data_array);

    }

    //
    // Cup Streams
    $selectSponsorsQuery = mysqli_query(
        $_database,
        "SELECT
                `livID`
            FROM `" . PREFIX . "cups_streams`
            WHERE `cupID` = " . $cup_id
    );
    if ($selectSponsorsQuery && (mysqli_num_rows($sponsors) > 0)) {

        $content_streams = '';
        while ($db = mysqli_fetch_array($sponsors)) {

            $streamArray = get_streaminfo($db['livID'], '');
            if (validate_array($streamArray, true)) {

                $stream_url = $hp_url . '/index.php?site=streams&amp;action=show&amp;livID='.$db['livID'];

                $stream_info = $streamArray['title'];
                if (get_streaminfo($db['livID'], 'online')) {
                    $stream_info .= '<span class="pull-right">';
                    if(!empty($streamArray['game'])) {
                        $stream_info .= $streamArray['game'].' / ';
                    }
                    $stream_info .= $streamArray['viewer'].' '.$_language->module['stream_viewer'].'</span>';
                } else {
                    $stream_info .= '<span class="pull-right grey italic">offline</span>';
                }

                $content_streams .= '<a href="'.$stream_url.'" target="_blank" title="'.$streamArray['title'].'" class="list-group-item">'.$stream_info.'</a>';

            }

        }

        if (!empty($content_streams)) {

            $data_array = array();
            $data_array['$panel_type'] = 'panel-default';
            $data_array['$panel_title'] = 'Streams';
            $data_array['$panel_content'] = $content_streams;
            $content .= $GLOBALS["_template_cup"]->replaceTemplate("panel_list_group", $data_array);

        }

    }

    // Admin Anmeldung
    $link = getCupStatusContainer($cupArray);
    $cup_footer = (!empty($link)) ?
        '<div class="list-group">' . $link . '</div>' : '';

    $column = ($getPage == 'home') ?
        'hits' : 'hits_' . $getPage;

    $query = mysqli_query(
        $_database,
        "UPDATE `" . PREFIX . "cups`
            SET ".$column." = ".$column." + 1
            WHERE cupID = " . $cup_id
    );

    echo $content;

} catch (Exception $e) {
    echo showError($e->getMessage());
}
