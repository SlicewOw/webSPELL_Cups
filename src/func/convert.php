<?php

function convert2id($id, $cat) {
    $id = stripslashes($id);
    if ($cat == 'youtube') {
        $search_array = array(
            'https://www.youtube.com/watch?v=',
            'https://www.youtube.de/watch?v=',
            'https://www.youtube.com/',
            'https://www.youtube.de/',
            'http://youtube.com/',
            'https://youtube.com/',
            'http://youtube.de/',
            'https://youtube.de/',
            'http://youtu.be/',
            'https://youtu.be/',
            'watch?v='
        );
    } else if ($cat == 'youtube_live') {
        $search_array = array(
            'http://gaming.youtube.com/user/',
            'https://gaming.youtube.com/user/',
            'http://gaming.youtube.com/watch?v=',
            'https://gaming.youtube.com/watch?v=',
            'http://gaming.youtube.de/watch?v=',
            'https://gaming.youtube.de/watch?v='
        );
    } else if ($cat == 'facebook') {
        $search_array = array(
            'http://www.facebook.com/',
            'https://www.facebook.com/',
            'http://www.facebook.de/',
            'https://www.facebook.de/',
            'http://facebook.com/',
            'https://facebook.com/',
            'http://facebook.de/',
            'https://facebook.de/',
            'http://fb.com/',
            'https://fb.com/',
            'http://fb.de/',
            'https://fb.de/',
            'facebook.com/',
            'facebook.de/',
            'fb.com/',
            'fb.de/'
        );
    } else if ($cat == 'twitter') {
        $search_array = array(
            'http://twitter.com/',
            'https://twitter.com/',
            'http://www.twitter.com/',
            'https://www.twitter.com/',
            'http://twitter.de/',
            'https://twitter.de/',
            'http://www.twitter.de/',
            'https://www.twitter.de/',
            'twitter.com/',
            'twitter.de/',
        );
    } else if ($cat == 'twitch') {
        $search_array = array(
            'http://twitch.com/',
            'http://twitch.de/',
            'https://twitch.com/',
            'https://twitch.de/',
            'http://twitch.tv/',
            'https://twitch.tv/',
            'http://go.twitch.tv/',
            'http://www.twitch.com/',
            'http://www.twitch.de/',
            'https://www.twitch.com/',
            'https://www.twitch.de/',
            'http://www.twitch.tv/',
            'https://www.twitch.tv/',
            'https://go.twitch.tv/'
        );
    } else {
        $search_array = array('');
    }
    $returnValue = str_replace($search_array, '', $id);
    return $returnValue;
}

function convert2filename($name, $setDateAsPrefix = false, $setTimeAsPrefix = false) {

    $searchArray = array( ' ',  'ä',  'ö',  'ü',  'ß', '?', '!', ',', '.', '#', '%', '&', '\'', '+', '<', '>', '`', '\´');
    $replaceArray = array('_', 'ae', 'oe', 'ue', 'ss',  '',  '',  '',  '',  '',  '',  '',   '',  '',  '',  '',  '',   '');

    $returnValue = str_replace(
        $searchArray,
        $replaceArray,
        $name
    );

    if ($setTimeAsPrefix) {
        $returnValue = date('H-i-s') . '_' . $returnValue;
    }

    if ($setDateAsPrefix) {
        $returnValue = date('Y-m-d') . '_' . $returnValue;
    }

    return $returnValue;

}

function convert2int($text) {
    $text = str_replace(',', '.', $text);

    $textArray = explode('.', $text);
    $anz = count($textArray);
    if ($anz == 2) {
        $returnValue = '';
        for ($x = 0; $x < $anz; $x++) {
            if ($x == 0) {
                $returnValue .= $textArray[$x];
            } else {
                $returnValue .= '.';
                $returnValue .= (strlen($textArray[$x]) > 1) ?
                    $textArray[$x] : $textArray[$x].'0';
            }
        }
        $text = $returnValue;
    } else if ($anz == 1) {
        $text .= '.00';
    }

    return $text;
}

function convert2days($time) {

    if (!is_numeric($time) || ($time == 0)) {
        return -1;
    }

    $timeNow = time();

    $timeDiff = $timeNow - $time;

    return (int) ($timeDiff / 60 / 60 / 24);

}