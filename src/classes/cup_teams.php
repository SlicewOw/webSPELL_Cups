<?php

namespace myrisk;

class cup_team {

    //
    // Language
    var $lang = null;

    //
    // Team Details
    var $team_id = null;
    var $team_name = null;
    var $team_tag = null;
    var $team_hp = '';
    var $team_admin = null;
    var $team_country = null;
    var $team_logotype = null;

    //
    // Admin team only
    var $admin_team = 0;

    //
    // Logotype Pfad
    var $logotype_path = null;
    var $logotype_max_size = null;
    var $logotype_type = null;

    //
    // Settings
    var $team_tag_max_length = null;
    var $cup_team_logotype_is_required = null;

    //
    // Status
    var $is_new_team = FALSE;

    public function __construct() {

        global $_language;

        $this->lang = $_language;

        $this->lang->readModule('teams');

        //
        // Default Country
        $this->team_country = getCupDefaultLanguage();

        //
        // Team-Logotype Bildpfad
        $this->logotype_path = __DIR__ . '/../../images/cup/teams/';

        //
        // Set cup team settings
        $this->loadCupSettings();

    }

    private function loadCupSettings() {

        //
        // teams need to upload logotype
        $this->cup_team_logotype_is_required = TRUE;

        //
        // Max. site of logotype
        // 500 -> 500x500 pixel
        $this->logotype_max_size = 500;

        //
        // Mmax count of chars of team tag
        $this->team_tag_max_length = 16;

        $settingsFile = __DIR__ . '/../../cup/settings.php';
        if (file_exists($settingsFile)) {

            include($settingsFile);

            //
            // teams need to upload logotype
            if (isset($cup_team_logotype_is_required)) {
                $this->cup_team_logotype_is_required = $cup_team_logotype_is_required;
            }

            //
            // Max. site of logotype
            // 500 -> 500x500 pixel
            if (isset($cup_team_logotype_max_size)) {
                $this->logotype_max_size = $cup_team_logotype_max_size;
            }

            //
            // Mmax count of chars of team tag
            if (isset($cup_team_tag_max_length)) {
                $this->team_tag_max_length = $cup_team_tag_max_length;
            }

        }

    }

    public function setId($team_id) {

        if (!validate_int($team_id, true)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_id']);
        }

        $this->team_id = (int)$team_id;

    }

    public function setName($name) {

        if (empty($name)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_name']);
        }

        $name = trim($name);

        $this->team_name = $name;

    }

    public function setTag($tag) {

        if (empty($tag)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_tag']);
        }

        $tag = trim($tag);

        $this->team_tag = $tag;

    }

    public function setHomepage($homepage) {

        if (validate_url($homepage)) {
            $this->team_hp = $homepage;
        }

    }

    public function setCountry($country = '') {

        if (empty($country)) {
            $country = getCupDefaultLanguage();
        }

        $country = trim($country);

        $this->team_country = $country;

    }

    public function setAdminId($user_id) {

        if (empty($user_id) || !validate_int($user_id, true)) {
            global $userID;
            $this->team_admin = $userID;
        }

        $this->team_admin = $user_id;

    }

    public function setAdminTeamOnly($is_admin_team_only = TRUE) {

        if (!is_bool($is_admin_team_only)) {
            return;
        }

        $this->admin_team = $is_admin_team_only;

    }

    public function uploadLogotype($image) {

        if (is_null($this->team_tag) || (empty($this->team_tag))) {
            return FALSE;
        }

        if (!is_array($image)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_icon'] . ' (uploadLogotype)');
        }

        if (is_null($this->logotype_path)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_logotype_path']);
        }

        $upload = new \webspell\HttpUpload('logotype');
        if (!$upload->hasFile()) {
            return FALSE;
        }

        if ($upload->hasError() !== false) {
            throw new \UnexpectedValueException($upload->translateError());
        }

        $mime_types = array('image/jpeg', 'image/png', 'image/gif');

        if (!$upload->supportedMimeType($mime_types)) {
            throw new \UnexpectedValueException($this->lang->module['unsupported_image_type']);
        }

        $imageInformation = getimagesize($upload->getTempFile());

        if (!is_array($imageInformation)) {
            throw new \UnexpectedValueException($this->lang->module['broken_image']);
        }

        if (!imagemaxsize($upload->getTempFile(), $this->logotype_max_size, $this->logotype_max_size)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_icon_size']);
        }

        switch ($imageInformation[2]) {
            case 1:
                $endung = '.gif';
                break;
            case 3:
                $endung = '.png';
                break;
            default:
                $endung = '.jpg';
                break;
        }

        if (!is_null($this->team_id) && validate_int($this->team_id)) {
            $this->team_logotype = convert2filename($this->team_id, true, true) . $endung;
        } else {
            $this->team_logotype = convert2filename($this->team_tag, true, true) . $endung;
        }

        if ($upload->saveAs($this->logotype_path . $this->team_logotype, true)) {
            @chmod($this->logotype_path . $this->team_logotype, 0777);
        } else {
            $this->team_logotype = null;
        }

    }

    public function getLogotype() {
        return $this->team_logotype;
    }

    public function getTeamId() {
        return $this->team_id;
    }

    public function saveTeam($team_id = null) {

        if (!is_null($team_id)) {

            $this->is_new_team = FALSE;

            $this->team_id = $team_id;

            //
            // Team aktualisieren
            $this->updateTeam();

        } else {

            $this->is_new_team = TRUE;

            //
            // Neues Team
            $this->insertTeam();

        }

    }

    public function isTeam() {

        //
        // true     : Team existiert
        // false    : Team existiert nicht

        if (is_null($this->team_id) || ($this->team_id < 1)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_id']);
        }

        $selectQuery = cup_query(
            "SELECT
                    COUNT(*) AS `exist`
                FROM `" . PREFIX . "cups_teams`
                WHERE `teamID` = " . $this->team_id,
            __FILE__
        );

        $checkIf = mysqli_fetch_array($selectQuery);

        if ($checkIf['exist'] == 1) {
            return true;
        } else {
            return false;
        }

    }

    public function isTeamExisting() {

        //
        // true     : Team existiert
        // false    : Team existiert nicht

        if (is_null($this->team_name)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_name']);
        }

        if(is_null($this->team_tag)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_tag']);
        }

        global $_database;

        if (is_null($this->team_id)) {

            $checkIf = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                        WHERE `name` = '".$this->team_name."' AND `deleted` = 0"
                )
            );

            if ($checkIf['exist'] > 0) {
                throw new \UnexpectedValueException($this->lang->module['wrong_team_name_in_use']);
            }

            $checkIf = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                        WHERE `tag` = '".$this->team_tag."' AND `deleted` = 0"
                )
            );

            if ($checkIf['exist'] > 0) {
                throw new \UnexpectedValueException($this->lang->module['wrong_team_tag_in_use']);
            }

        } else {

            $checkIf = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                    WHERE `teamID` != ".$this->team_id." AND `deleted` = 0 AND `name` = '".$this->team_name."'"
                )
            );

            if ($checkIf['exist'] > 0) {
                throw new \UnexpectedValueException($this->lang->module['wrong_team_name_in_use']);
            }

            $checkIf = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                    WHERE `teamID` != ".$this->team_id." AND `deleted` = 0 AND `tag` = '".$this->team_tag."'"
                )
            );

            if ($checkIf['exist'] > 0) {
                throw new \UnexpectedValueException($this->lang->module['wrong_team_tag_in_use']);
            }

        }

    }

    public function insertTeam() {

        if (is_null($this->team_name)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_name']);
        }

        if (is_null($this->team_tag)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_tag']);
        }

        if ($this->cup_team_logotype_is_required) {

            if (empty($this->team_logotype)) {
                throw new \UnexpectedValueException($this->lang->module['wrong_parameter_icon']);
            }

        } else if (is_null($this->team_logotype)) {
            $this->team_logotype = '';
        }

        if (is_null($this->team_admin)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_admin_id']);
        }

        //
        // Prüfe ob Name und Tag noch verfügbar sind
        $this->isTeamExisting();

        global $_database;

        $saveQuery = cup_query(
            "INSERT INTO `" . PREFIX . "cups_teams`
                (
                    `date`,
                    `name`,
                    `tag`,
                    `userID`,
                    `hp`,
                    `logotype`,
                    `password`,
                    `country`,
                    `admin`
                )
                VALUES
                (
                    " . time() . ",
                    '" . $this->team_name . "',
                    '" . $this->team_tag . "',
                    " . $this->team_admin . ",
                    '" . $this->team_hp . "',
                    '" . $this->team_logotype . "',
                    '" . RandPass(20) . "',
                    '" . $this->team_country . "',
                    " . $this->admin_team . "
                )",
            __FILE__
        );

        $this->team_id = mysqli_insert_id($_database);

        setCupTeamLog($this->team_id, $this->team_name, 'team_created');

        $query = cup_query(
            "INSERT INTO `" . PREFIX . "cups_teams_member`
                (
                    `userID`,
                    `teamID`,
                    `position`,
                    `join_date`
                )
                VALUES
                (
                    " . $this->team_admin . ",
                    " . $this->team_id . ",
                    1,
                    " . time() . "
                )",
            __FILE__
        );

        if (!is_null($this->logotype_type) && !empty($this->team_logotype)) {

            $fileName = convert2filename($this->team_id, true, true) . $this->logotype_type;

            if (rename($this->logotype_path . $this->team_logotype, $this->logotype_path . $fileName)) {

                $updateQuery = cup_query(
                    "UPDATE `" . PREFIX . "cups_teams`
                        SET `logotype` = '" . $fileName . "'
                        WHERE `teamID` = " . $this->team_id,
                    __FILE__
                );

            } else {
                $_SESSION['errorArray'][] = 'copy failed';
            }

        }

    }

    public function updateTeam() {

        if (is_null($this->team_id)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_id']);
        }

        if (!$this->isTeam()) {
            throw new \UnexpectedValueException($this->lang->module['team_not_found']);
        }

        //
        // Prüfe ob Name und Tag noch verfügbar sind
        $this->isTeamExisting();

        $updateArray = array();

        $updateArray[] = '`password` = \'' . RandPass(20) . '\'';

        if (!is_null($this->team_name)) {
            $updateArray[] = '`name` = \'' . $this->team_name . '\'';
        }

        if (!is_null($this->team_tag)) {
            $updateArray[] = '`tag` = \'' . $this->team_tag . '\'';
        }

        if (!is_null($this->team_admin) && validate_int($this->team_admin, true)) {
            $updateArray[] = '`userID` = ' . $this->team_admin;
        }

        if (!is_null($this->team_country)) {
            $updateArray[] = '`country` = \'' . $this->team_country . '\'';
        }

        if (!is_null($this->team_hp) && !empty($this->team_hp)) {
            $updateArray[] = '`hp` = \'' . $this->team_hp . '\'';
        }

        if (!is_null($this->team_logotype)) {
            $updateArray[] = '`logotype` = \'' . $this->team_logotype . '\'';
        }

        if (!is_null($this->admin_team)) {
            $updateArray[] = '`admin` = ' . $this->admin_team;
        }

        $anzUpdateValues = count($updateArray);
        if ($anzUpdateValues > 0) {

            $updateString = implode(', ', $updateArray);

            cup_query(
                "UPDATE `" . PREFIX . "cups_teams`
                    SET " . $updateString . "
                    WHERE `teamID` = " . $this->team_id,
                __FILE__
            );

            setCupTeamLog($this->team_id, $this->team_name, 'team_changed');

        }

    }

    public function deleteTeam() {

        if (is_null($this->team_id)) {
            throw new \UnexpectedValueException($this->lang->module['wrong_parameter_id']);
        }

        cup_query(
            "UPDATE `" . PREFIX . "cups_teams`
                SET `deleted` = 1
                WHERE `teamID` = " . $this->team_id,
            __FILE__
        );

        cup_query(
            "UPDATE `" . PREFIX . "cups_teams_member`
                SET `left_date` = " . time() . ",
                    `active` = 0
                WHERE `teamID` = " . $this->team_id,
            __FILE__
        );

        $team_name = getteam($this->team_id, 'name');

        // Team Log
        setCupTeamLog($this->team_id, $team_name, 'team_deleted');

        // Player log
        global $userID;
        setPlayerLog($userID, $this->team_id, 'cup_team_deleted');

    }

    public function redirect() {

        $parent_url = 'index.php?site=teams&action=admin&teamID=' . $this->team_id;

        if (!$this->is_new_team) {
            $parent_url .= '&message=edit_ok';
        }

        header('Location: ' . $parent_url);

    }

}
