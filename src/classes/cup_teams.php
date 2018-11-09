<?php

namespace myrisk;

class cup_team {

    //
    // Activity Feed Kategorie
    var $cat_id = null;

    //
    // Language
    var $lang = null;

    //
    // Team Details
    var $team_id = null;
    var $team_name = null;
    var $team_tag = null;
    var $team_hp = null;
    var $team_admin = null;
    var $team_country = null;
    var $team_logotype = null;

    //
    // Logotype Pfad
    var $logotype_path = null;
    var $logotype_max_size = null;
    var $logotype_type = null;

    //
    // Restrictions
    var $team_tag_max_length = null;

    //
    // Status
    var $is_new_team = FALSE;

    public function __construct() {

        global $_language, $dir_global;

        $this->lang = $_language;

        $this->lang->readModule('teams');

        //
        // Default Country
        $this->team_country = 'de';

        //
        // Team-Logotype Bildpfad
        $this->logotype_path = '../../images/cup/teams/';

        //
        // Max. Größe
        // 500 -> 500x500
        $this->logotype_max_size = 500;

        //
        // Max. Länge des Team Tags
        $this->team_tag_max_length = 16;

    }

    public function setName($name) {

        if(empty($name)) {
            throw new \Exception($this->lang->module['wrong_parameter_name']);
        }

        $name = trim($name);

        $this->team_name = $name;

    }

    public function setTag($tag) {

        if(empty($tag)) {
            throw new \Exception($this->lang->module['wrong_parameter_tag']);
        }

        $tag = trim($tag);

        $this->team_tag = $tag;

    }

    public function setHomepage($homepage) {

        if(validate_url($homepage)) {
            $this->team_hp = $homepage;
        }

    }

    public function setCountry($country = 'de') {

        if(empty($country)) {
            $country = 'de';
        }

        $country = trim($country);

        $this->team_country = $country;

    }

    public function uploadLogotype($image) {

        if (is_null($this->team_tag) || (empty($this->team_tag))) {
            return FALSE;
        }

        if (!is_array($image)) {
            throw new \Exception($this->lang->module['wrong_parameter_icon'] . ' (uploadLogotype)');
        }

        if (is_null($this->logotype_path)) {
            throw new \Exception($this->lang->module['wrong_logotype_path']);
        }

        $upload = new \webspell\HttpUpload('logotype');
        if (!$upload->hasFile()) {
            return FALSE;
        }

        if ($upload->hasError() !== false) {
            throw new \Exception($upload->translateError());
        }

        $mime_types = array('image/jpeg', 'image/png', 'image/gif');

        if (!$upload->supportedMimeType($mime_types)) {
            throw new \Exception($this->lang->module['unsupported_image_type']);
        }

        $imageInformation = getimagesize($upload->getTempFile());

        if (!is_array($imageInformation)) {
            throw new \Exception($this->lang->module['broken_image']);
        }

        if (!imagemaxsize($upload->getTempFile(), $this->logotype_max_size, $this->logotype_max_size)) {
            throw new \Exception($this->lang->module['wrong_parameter_icon_size']);
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

        if(!is_null($team_id)) {

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

        if(is_null($this->team_id) || ($this->team_id < 1)) {
            throw new \Exception($this->lang->module['wrong_parameter_id']);
        }

        global $_database;

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                    WHERE `teamID` = ".$this->team_id
            )
        );

        if($checkIf['exist'] == 1) {
            return true;
        } else {
            return false;
        }

    }

    public function isTeamExisting() {

        //
        // true     : Team existiert
        // false    : Team existiert nicht

        if(is_null($this->team_name)) {
            throw new \Exception($this->lang->module['wrong_parameter_name']);
        }

        if(is_null($this->team_tag)) {
            throw new \Exception($this->lang->module['wrong_parameter_tag']);
        }

        global $_database;

        if(is_null($this->team_id)) {

            $checkIf = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                        WHERE `name` = '".$this->team_name."' AND `deleted` = 0"
                )
            );

            if($checkIf['exist'] > 0) {
                throw new \Exception($this->lang->module['wrong_team_name_in_use']);
            }

            $checkIf = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                        WHERE `tag` = '".$this->team_tag."' AND `deleted` = 0"
                )
            );

            if($checkIf['exist'] > 0) {
                throw new \Exception($this->lang->module['wrong_team_tag_in_use']);
            }

        } else {

            $checkIf = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                    WHERE `teamID` != ".$this->team_id." AND `deleted` = 0 AND `name` = '".$this->team_name."'"
                )
            );

            if($checkIf['exist'] > 0) {
                throw new \Exception($this->lang->module['wrong_team_name_in_use']);
            }

            $checkIf = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                    WHERE `teamID` != ".$this->team_id." AND `deleted` = 0 AND `tag` = '".$this->team_tag."'"
                )
            );

            if($checkIf['exist'] > 0) {
                throw new \Exception($this->lang->module['wrong_team_tag_in_use']);
            }

        }

    }

    public function insertTeam() {

        if(is_null($this->team_name)) {
            throw new \Exception($this->lang->module['wrong_parameter_name']);
        }

        if(is_null($this->team_tag)) {
            throw new \Exception($this->lang->module['wrong_parameter_tag']);
        }

        if(is_null($this->team_logotype)) {
            throw new \Exception($this->lang->module['wrong_parameter_icon'] . ' (insertTeam, null)');
        }

        if(empty($this->team_logotype)) {
            throw new \Exception($this->lang->module['wrong_parameter_icon'] . ' (insertTeam, empty)');
        }

        //
        // Prüfe ob Name und Tag noch verfügbar sind
        $this->isTeamExisting();

        global $_database, $userID;

        $saveQuery = mysqli_query(
            $_database,
            "INSERT INTO ".PREFIX."cups_teams 
                (
                    `date`, 
                    `name`, 
                    `tag`, 
                    `userID`, 
                    `hp`, 
                    `logotype`,
                    `password`, 
                    `country`
                ) 
                VALUES 
                ( 
                    ".time().", 
                    '".$this->team_name."', 
                    '".$this->team_tag."', 
                    ".$userID.", 
                    '".$this->team_hp."', 
                    '".$this->team_logotype."', 
                    '".RandPass(20)."', 
                    '".$this->team_country."'
                )"
        );

        if(!$saveQuery) {
            throw new \Exception($this->lang->module['wrong_query_insert']);
        }

        $this->team_id = mysqli_insert_id($_database);

        setCupTeamLog($this->team_id, $this->team_name, 'team_created');

        $query = mysqli_query(
            $_database,
            "INSERT INTO ".PREFIX."cups_teams_member 
                (
                    `userID`, 
                    `teamID`, 
                    `position`, 
                    `join_date`
                ) 
                VALUES 
                (
                    ".$userID.", 
                    ".$this->team_id.", 
                    1, 
                    ".time()."
                )"
        );

        if (!$query) {
            throw new \Exception($this->lang->module['wrong_query_insert']);
        }

        if (!is_null($this->logotype_type)) {

            $fileName = convert2filename($this->team_id, true, true) . $this->logotype_type;

            if (rename($this->logotype_path . $this->team_logotype, $this->logotype_path . $fileName)) {

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE `" . PREFIX . "cups_teams`
                        SET logotype = '" . $fileName . "'
                        WHERE teamID = " . $this->team_id
                );

            } else {
                $_SESSION['errorArray'][] = 'copy failed';
            }

        }

    }

    public function updateTeam() {

        if (is_null($this->team_id)) {
            throw new \Exception($this->lang->module['wrong_parameter_id']);
        }

        if (!$this->isTeam()) {
            throw new \Exception($this->lang->module['team_not_found']);
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

        if (!is_null($this->team_country)) {
            $updateArray[] = '`country` = \'' . $this->team_country . '\'';
        }

        if (!is_null($this->team_hp)) {
            $updateArray[] = '`hp` = \'' . $this->team_hp . '\'';
        }

        if (!is_null($this->team_logotype)) {
            $updateArray[] = '`logotype` = \'' . $this->team_logotype . '\'';
        }

        $anzUpdateValues = count($updateArray);
        if($anzUpdateValues > 0) {

            $updateString = implode(', ', $updateArray);

            global $_database, $userID;

            $query = mysqli_query(
                $_database,
                "UPDATE `" . PREFIX . "cups_teams` 
                    SET " . $updateString . "
                    WHERE `teamID` = " . $this->team_id
            );

            if(!$query) {
                throw new \Exception($this->lang->module['wrong_query_update']);
            }

            setCupTeamLog($this->team_id, $this->team_name, 'team_changed');

        }

    }

    public function redirect() {

        $parent_url = 'index.php?site=teams&action=admin&teamID=' . $this->team_id;

        if(!$this->is_new_team) {
            $parent_url .= '&message=edit_ok';
        }

        header('Location: ' . $parent_url);

    }

}
