<?php

namespace myrisk;

class stream {

    //
    // Unique ID
    var $stream_id = null;

    //
    // Stream ID
    var $value = null;
    var $value_tmp = null;

    //
    // Settings
    var $cronjob_max = 5;
    var $cronjob = 1;
    var $stream_title = null;
    var $stream_type = 1;
    var $stream_game = null;
    var $stream_online = 0;
    var $stream_date = 0;
    var $user_id = 0;

    //
    // Stream Socials
    var $social_facebook = null;
    var $social_twitter = null;
    var $social_youtube = null;

    //
    // Twitch Stream
    var $isTwitchStream = null;

    //
    // Aktiv?
    var $isActive = 0;

    //
    // Sprache
    var $lang = null;

    public function __construct() {

        global $userID, $_language;

        $_language->readModule('liveshow');

        $this->lang = $_language;

        $this->isTwitchStream = FALSE;

        if (isanyadmin($userID)) {
            $this->isActive = 1;
        }

    }

    public function insertStream($stream_id, $var_value) {

        //
        // Speichere Gameaccount Value
        $this->setValue($var_value);

        //
        // CronJob ID
        $this->setCronJob();

        //
        // DB Eintrag
        $this->saveStream($stream_id);

    }

    public function setID($stream_id) {

        if (!validate_int($stream_id, true)) {
            return;
        }

        $this->stream_id = $stream_id;

    }

    public function setUserID($user_id) {

        if (!validate_int($user_id, true)) {
            return;
        }

        $this->user_id = $user_id;

    }

    public function getID() {
        return $this->stream_id;
    }

    public function getCronJob() {
        return $this->cronjob;
    }

    public function setCronJob() {

        global $_database;

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS `streamCount` FROM `".PREFIX."liveshow`"
            )
        );

        if($get['streamCount'] < $this->cronjob_max) {

            if($get['streamCount'] == 0) {
                $cronjob_id = 1;
            } else {

                for($x=1;$x<($this->cronjob_max + 1);$x++) {

                    $subget = mysqli_fetch_array(
                        mysqli_query(
                            $_database,
                            "SELECT COUNT(*) AS `streamCount` FROM `".PREFIX."liveshow`
                                WHERE cronjobID = " . $x
                        )
                    );

                    if($subget['streamCount'] == 0) {
                        $cronjob_id = $x;
                        $x = 2 * $this->cronjob_max;
                    }

                }

            }

        } else {

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT cronjobID FROM `".PREFIX."liveshow`
                        GROUP BY cronjobID
                        ORDER BY COUNT(*) ASC
                        LIMIT 0, 1"
                )
            );

            $cronjob_id = (isset($get['cronjobID']) && validate_int($get['cronjobID'])) ?
                (int)$get['cronjobID'] : 1;

        }

        $this->cronjob = $cronjob_id;

    }

    public function setValue($var_value) {

        if (is_null($var_value) || empty($var_value)) {
            throw new \UnexpectedValueException($this->lang->module['error_missing_value']);
        }

        $var_value = getinput($var_value);

        //
        // Temporäres Value setzen
        $this->value_tmp = strtolower($var_value);

        if ($this->isTwitchAccount()) {

            $this->convert2twitch();

            $this->value = $this->value_tmp;

        } else {
            throw new \UnexpectedValueException($this->lang->module['error_cannot_convert']);
        }

    }

    public function isTwitchAccount() {

        if (is_null($this->value_tmp) || empty($this->value_tmp)) {
            throw new \UnexpectedValueException($this->lang->module['error_missing_value']);
        }

        if (!validate_url($this->value_tmp)) {
            throw new \UnexpectedValueException($this->lang->module['error_url_expected']);
        }

        if (preg_match('/twitch.tv/i', $this->value_tmp)) {
            $this->isTwitchStream = TRUE;
            return TRUE;
        }

        return FALSE;

    }

    public function convert2twitch() {

        if (is_null($this->value_tmp)) {
            throw new \UnexpectedValueException($this->lang->module['error_missing_value']);
        }

        $searchArray = array(
            'https:\/\/www.twitch.tv\/',
            'https:\/\/go.twitch.tv\/',
            'https:\/\/twitch.tv\/',
            'http:\/\/www.twitch.tv\/',
            'http:\/\/go.twitch.tv\/',
            'http:\/\/twitch.tv\/'
        );

        $anz = count($searchArray);
        if ($anz > 0) {

            for ($x = 0; $x < $anz; $x++) {

                $searchValue = $searchArray[$x];
                if (preg_match('/'.$searchValue.'/i', strtolower($this->value_tmp))) {

                    $searchValue = stripslashes($searchValue);

                    $this->value_tmp = str_replace(
                        $searchValue,
                        '',
                        $this->value_tmp
                    );

                }

            }

        }

        $this->value_tmp = str_replace(
            '/',
            ' ',
            $this->value_tmp
        );

        $valueArray = explode(' ', $this->value_tmp);
        if (is_array($valueArray)) {

            $anz = count($valueArray);

            if ($anz > 1) {
                $this->value_tmp = $valueArray[0];
            }

        }

    }

    public function setSocialNetwork($socialArray = array()) {

        if (!is_array($socialArray)) {
            return;
        }

        $anzSocials = count($socialArray);
        if ($anzSocials == 0) {
            return;
        }

        for ($x = 0; $x < $anzSocials; $x++) {

            $social_value = trim($socialArray[$x]);
            if (!empty($social_value) && validate_url($social_value)) {

                if (preg_match('/facebook/i', $social_value)) {
                    $this->social_facebook = convert2id($social_value, 'facebook');
                } else if (preg_match('/twitter/i', $social_value)) {
                    $this->social_twitter = convert2id($social_value, 'twitter');
                } else if (preg_match('/youtube/i', $social_value)) {
                    $this->social_youtube = convert2id($social_value, 'youtube');
                }

            }

        }

    }

    public function checkStream() {

        //
        // Stream existiert?
        // TRUE     : ja
        // FALSE    : nein

        global $_database;

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS exist FROM `".PREFIX."liveshow`
                    WHERE id = '" . $this->value . "'"
            )
        );

        return ($checkIf['exist'] > 0) ? TRUE : FALSE;

    }

    public function getTwitchData() {

        if(is_null($this->value)) {
            return;
        }

        $this->stream_title = '';
        $this->stream_game = '';

        try {

            $json_url = 'https://api.twitch.tv/kraken/channels/' . $this->value;
            if($json_data = getAPIData($json_url, 'twitch')) {

                $twitchData = json_decode($json_data, TRUE);
                if (!isset($twitchData['status']) || ($twitchData['status'] == '404')) {
                    throw new \UnexpectedValueException($this->lang->module['error_getting_twitch_data_2']);
                }

                if (isset($twitchData['display_name'])) {
                    $this->stream_title = addslashes($twitchData['display_name']);
                }

                if (isset($twitchData['game'])) {
                    $this->stream_game = str_replace(
                        '\'',
                        '',
                        $twitchData['game']
                    );
                }

                $this->stream_online = 0;
                $this->stream_date = time();

            } else {
                throw new \UnexpectedValueException($this->lang->module['error_getting_twitch_data_1']);
            }

        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

    }

    public function saveStream($stream_id = null, $redirect = FALSE) {

        if (!validate_int($stream_id) && !validate_int($this->stream_id)) {

            if ($this->checkStream()) {
                throw new \UnexpectedValueException($this->lang->module['unknown_stream']);
            }

        }

        global $_database, $userID;

        if (!$this->checkStream()) {


            /***********
            Neuer Stream
            ***********/

            // Cronjob ID
            $this->setCronJob();

            // Twitch Stream Details
            if ($this->isTwitchStream) {
                $this->getTwitchData();
            }

            $insertQuery = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "liveshow`
                    (
                        `cronjobID`,
                        `title`,
                        `id`,
                        `userID`,
                        `facebook`,
                        `twitter`,
                        `youtube`,
                        `online`,
                        `game`,
                        `date`,
                        `active`
                    )
                    VALUES
                    (
                        " . $this->cronjob . ",
                        '" . $this->stream_title . "',
                        '" . $this->value . "',
                        " . $this->user_id . ",
                        '" . $this->social_facebook . "',
                        '" . $this->social_twitter . "',
                        '" . $this->social_youtube . "',
                        " . $this->stream_online . ",
                        '" . $this->stream_game . "',
                        '" . $this->stream_date . "',
                        " . $this->isActive . "
                    )"
            );

            if (!$insertQuery) {
                throw new \UnexpectedValueException($this->lang->module['query_failed_insert']);
            }

            $this->stream_id = mysqli_insert_id($_database);

            $_SESSION['successArray'][] = $this->lang->module['query_saved'];

        } else {

            if (!validate_int($this->stream_id, true)) {
                throw new \UnexpectedValueException($this->lang->module['error_unknown_id']);
            }

            // Twitch Stream Details
            if ($this->isTwitchStream) {
                $this->getTwitchData();
            }

            /************
            Update Stream
            ************/
            $updateQuery = mysqli_query(
                $_database,
                "UPDATE " . PREFIX . "liveshow
                    SET	`cronjobID` = " . $this->cronjob . ",
                        `title` = '" . $this->stream_title  . "',
                        `id` = '" . $this->value . "',
                        `facebook` = '".$this->social_facebook."',
                        `twitter` = '".$this->social_twitter."',
                        `youtube` = '".$this->social_youtube."',
                        `online` = " . $this->stream_online . ",
                        `date` = " . $this->stream_date . ",
                        `game` = '" . $this->stream_game . "',
                        `active` = " . $this->isActive . "
                    WHERE livID = " . $this->stream_id
            );

            if (!$updateQuery) {
                throw new \UnexpectedValueException($this->lang->module['query_failed_update']);
            }

            $_SESSION['successArray'][] = $this->lang->module['query_saved'];

        }

        if ($redirect && validate_int($this->stream_id, true)) {

            global $varPage;

            if ($varPage == 'admin') {
                $parent_url = 'index.php?site=streams&cronID=' . $this->cronjob;
            } else {
                $parent_url = 'index.php?site=streams&id=' . $this->stream_id;
            }

            header('Location: ' . $parent_url);

        }

    }

}