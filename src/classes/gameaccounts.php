<?php

namespace myrisk;

class gameaccount {

    //
    // Unique ID
    var $gameaccount_id = null;

    //
    // Gameaccount Game Details
    var $game_id = null;
    var $game_tag = null;

    //
    // Gameaccount Value
    var $value = null;
    var $value_tmp = null;

    //
    // Status
    var $isActive = 0;

    //
    // CS, DotA, etc.
    var $isSteamAccount = false;
    var $isCheckedSteamAccount = false;
    var $steamCommunityId = null;

    //
    // League of Legends
    var $isRiotAccount = false;

    //
    // Minecraft
    var $isMojangAccount = false;

    // Sprache
    var $lang = null;

    public function __construct() {

        global $_language;

        $_language->readModule('gameaccounts');

        $this->lang = $_language;

    }

    public function insertGameaccount($gameaccount_id, $game_id, $var_value) {

        //
        // Speichere Game Details
        $this->setGame($game_id);

        //
        // Speichere Gameaccount Value
        $this->setValue($var_value);

        //
        // DB Eintrag
        $this->saveGameaccount($gameaccount_id, FALSE);

    }

    public function setGameaccountID($gameaccount_id) {

        if (is_null($gameaccount_id)|| !is_numeric($gameaccount_id) || ($gameaccount_id < 1)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_id_type']);
        }

        $this->gameaccount_id = $gameaccount_id;

    }

    public function setGame($game_id = null) {

        if (is_null($game_id) || empty($game_id)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_game'] . ' (1)');
        }

        if (!is_numeric($game_id)) {

            $gameIdLength = strlen($game_id);
            if ($gameIdLength < 2 || $gameIdLength > 3) {
                throw new \UnexpectedValueException($this->lang->module['error_gameaccount_game_tag']);
            }

            $game_tag = $game_id;

            $game_id = getGame($game_tag, 'id');

        } else if (!validate_int($game_id)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_game'] . ' (2)');
        }

        if ($game_id < 1) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_game'] . ' (3)');
        }

        global $_database;

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS exist FROM `".PREFIX."games`
                    WHERE gameID = " . $game_id
            )
        );

        //
        // Game ID exisitert?
        if ($checkIf['exist'] != 1) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_game']);
        }

        //
        // Setze Game ID
        $this->game_id = $game_id;

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT tag FROM `".PREFIX."games`
                    WHERE gameID = " . $game_id
            )
        );

        //
        // Setze Game Tag
        $this->game_tag = strtolower($get['tag']);

        //
        // Setze Gameaccount Game Flags
        if ($this->game_tag == 'mc') {
            $this->isMojangAccount = TRUE;
        } else if ($this->game_tag == 'lol') {
            $this->isRiotAccount = TRUE;
        }

    }

    public function setValue($var_value) {

        if (is_null($var_value) || empty($var_value)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_value']);
        }

        //
        // Lösche Leerzeichen
        $var_value = getinput($var_value);

        //
        // Temporäres Value setzen
        $this->value_tmp = $var_value;

        if ($this->isSteamAccount()) {

            //
            // Kontrolle, ob korrekter Steam Gameaccount
            if (!$this->checkSteamAccount()) {
                throw new \UnexpectedValueException($this->lang->module['error_steam_value_url']);
            }

            $this->value = $this->value_tmp;

        } else {
            $this->value = $this->value_tmp;
        }

    }

    public function getValue() {
        return $this->value;
    }

    public function isBannedValue() {

        //
        // TRUE 	: ist gesperrt
        // FALSE	: ist nicht gesperrt

        if(is_null($this->value)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_value']);
        }

        if(is_null($this->game_tag)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_game_tag']);
        }

        global $_database;

        $value = $this->value;

        if($this->isSteamAccount) {

            if(!$this->isCheckedSteamAccount) {
                return TRUE;
            }

            if(strlen($this->value) != 17) {

                //
                // Steam Community ID (SteamID64)
                $value = SteamID2CommunityID($this->value);
                if(strlen($value) != 17) {
                    throw new \UnexpectedValueException($this->lang->module['wrong_steam64_id']);
                }

            }

        }

        $whereClauseArray = array();

        $whereClauseArray[] = 'game = "' . $this->game_tag . '"';
        $whereClauseArray[] = 'value = "' . $value . '"';

        $whereClause = implode(' AND ', $whereClauseArray);

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `exist`
                    FROM `" . PREFIX . "cups_gameaccounts_banned`
                    WHERE " . $whereClause
            )
        );

        if ($checkIf['exist'] != 0) {

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT
                            `description`
                        FROM `" . PREFIX . "cups_gameaccounts_banned`
                        WHERE " . $whereClause
                )
            );

            $exceptionMessage = str_replace(
                '%description%',
                $get['description'],
                $this->lang->module['error_gameaccount_banned']
            );

            throw new \UnexpectedValueException(getinput($exceptionMessage));

        }

    }

    public function setSteamIDUnique() {

        if (!$this->isCheckedSteamAccount) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_value']);
        }

        if (!validate_int($this->gameaccount_id)) {
            throw new \UnexpectedValueException($this->lang->module['unknown_gameaccount']);
        }

        global $_database;

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS `exist` FROM `".PREFIX."cups_gameaccounts_csgo`
                    WHERE `gameaccID` = " . $this->gameaccount_id
            )
        );

        if ($checkIf['exist'] != 1) {

            $query = mysqli_query(
                $_database,
                "INSERT INTO `".PREFIX."cups_gameaccounts_csgo`
                    (
                        `gameaccID`,
                        `date`,
                        `hours`
                    )
                    VALUES
                    (
                        " . $this->gameaccount_id . ",
                        " . time() . ",
                        -1
                    )"
            );

        }

        global $userID;

        //
        // Update Gameaccount mit Steam Daten
        updateCSGOGameaccount($userID);

    }

    public function isSteamAccount() {

        if (is_null($this->game_tag)) {
            return FALSE;
        }

        if (is_null($this->value_tmp)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_value']);
        }

        $steamAccountArray = array(
            'cs',
            'csg',
            'css'
        );

        if (in_array($this->game_tag, $steamAccountArray)) {

            //
            // Value in Großschreibung formatieren
            // steam_ -> STEAM_
            $this->value_tmp = strtolower($this->value_tmp);

            $this->isSteamAccount = TRUE;
            return TRUE;

        }

        return FALSE;

    }

    public function checkSteamAccount() {

        if (is_null($this->value_tmp)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_value']);
        }

        if (!$this->isSteamAccount) {
            return FALSE;
        }

        //
        // Steam Community Link
        if (!validate_url($this->value_tmp)) {

            if(strlen($this->value_tmp) == 17) {
                $steam64_id = $this->value_tmp;
            } else {
                throw new \UnexpectedValueException($this->lang->module['error_steam_value_url'] . ' (1)');
            }

        }

        if (!isset($steam64_id)) {

            if(preg_match('/\/profiles\//i', $this->value_tmp)) {
                $getTypeOfURL = 'profiles';
            } else if(preg_match('/\/id\//i', $this->value_tmp)) {
                $getTypeOfURL = 'id';
            }

            $getTypeOfURLArray = array(
                'profiles',
                'id'
            );

            if(!isset($getTypeOfURL) || !in_array($getTypeOfURL, $getTypeOfURLArray)) {
                throw new \UnexpectedValueException($this->lang->module['error_steam_value_url'] . ' (2)');
            }

            $valueArray = explode('/', $this->value_tmp);
            $getIndex = count($valueArray) - 1;

            if(!empty($valueArray[$getIndex])) {
                $value = $valueArray[$getIndex];
            } else {
                $value = $valueArray[$getIndex - 1];
            }

            if ($getTypeOfURL == 'profiles' && (strlen($value) == 17)) {

                $steam64_id = $value;
                $steam64IdIsSet = TRUE;

            } else if ($getTypeOfURL == 'id') {

                $final_community_url = 'https://steamcommunity.com/id/' . $value . '/?xml=1';
                if($result = @file_get_contents($final_community_url)) {

                    $begin = strpos($result, '7656');
                    $steam64_id = substr($result, $begin, 17);
                    $steam64IdIsSet = TRUE;

                } else {
                    throw new \UnexpectedValueException($this->lang->module['error_failed_steamrequest']);
                }

            }

            if (!isset($steam64IdIsSet)) {
                return FALSE;
            }

            if (!isset($steam64_id)) {
                return FALSE;
            }

        }

        $this->value_tmp = $steam64_id;

        $this->isCheckedSteamAccount = TRUE;

        return TRUE;

    }

    public function setMojangUnique() {

        if(is_null($this->value)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_value']);
        }

        if(is_null($this->gameaccount_id)) {
            throw new \UnexpectedValueException($this->lang->module['unknown_gameaccount']);
        }

        $curl_url = 'https://api.mojang.com/users/profiles/minecraft/' . strtolower($this->value);

        $accountData = getAPIData($curl_url);
        $accountData = json_decode($accountData, true);

        if (isset($accountData['id']) && !empty($accountData['id'])) {

            $unique_id = $accountData['id'];

            $unique_id_new = '';
            $unique_id_new .= substr($unique_id,0,8);
            $unique_id_new .= '-'.substr($unique_id,8,4);
            $unique_id_new .= '-'.substr($unique_id,12,4);
            $unique_id_new .= '-'.substr($unique_id,16,4);
            $unique_id_new .= '-'.substr($unique_id,20,12);

            if(!empty($unique_id)) {

                global $_database;

                $query = mysqli_query(
                    $_database,
                    "INSERT INTO `" . PREFIX . "cups_gameaccounts_mc`
                        (
                            `gameaccID`,
                            `unique_id`,
                            `active`,
                            `date`
                        )
                        VALUES
                        (
                            '".$this->gameaccount_id."',
                            '".$unique_id_new."',
                            0,
                            ".time()."
                        )"
                );

            }

        }

    }

    public function setRiotUnique() {
        /**
         * To Do
         */
    }

    public function checkGameaccount() {

        global $_database, $userID;

        if (is_null($this->game_tag)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_game_tag']);
        }

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_gameaccounts`
                    WHERE userID = ".$userID." AND category = '".$this->game_tag."' AND deleted = 0"
            )
        );

        if($checkIf['exist'] == 1) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_existing_user']);
        }

        if(is_null($this->value)) {
            throw new \UnexpectedValueException('unknown value');
        }

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `exist`
                    FROM `" . PREFIX . "cups_gameaccounts`
                    WHERE category = '".$this->game_tag."' AND value = '".$this->value."' AND deleted = 0"
            )
        );

        if($checkIf['exist'] == 1) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_existing']);
        }

        return TRUE;

    }

    public function isGameaccount($table = 'cups_gameaccounts') {

        //
        // TRUE 	: Gameaccount existiert
        // FALSE	: Gameaccount existiert nicht

        global $_database;

        if (is_null($this->gameaccount_id)) {
            throw new \UnexpectedValueException($this->lang->module['unknown_gameaccount']);
        }

        $checkIf = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        COUNT(*) AS `exist`
                    FROM `" . PREFIX . $table . "`
                    WHERE `gameaccID` = ".$this->gameaccount_id
            )
        );

        return ($checkIf['exist'] > 0) ? TRUE : FALSE;

    }

    public function deleteGameaccount() {

        if (!$this->isGameaccount('cups_gameaccounts')) {
            throw new \UnexpectedValueException($this->lang->module['unknown_gameaccount']);
        }

        global $_database, $userID;

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                        `userID`
                    FROM `" . PREFIX . "cups_gameaccounts`
                    WHERE `gameaccID` = " . $this->gameaccount_id
            )
        );

        //
        // Wenn User sich unterscheiden, Flag auf 0 setzen
        // -> Löschung durch Admin sichtbar
        $deletedSeen = ($userID != $get['userID']) ? 0 : 1;

        //
        // Spieler: Aktive Strafpunkte?
        $selectUserPenaltiesQuery = mysqli_query(
            $_database,
            "SELECT
                    COUNT(*) AS `user_penalties`
                FROM `" . PREFIX . "cups_penalty`
                WHERE `userID` = " . $userID . " AND `duration_time` > " . time()
        );

        if (!$selectUserPenaltiesQuery) {
            throw new \UnexpectedValueException($this->lang->module['error_delete_penalty_user']);
        }

        $get = mysqli_fetch_array($selectUserPenaltiesQuery);
        if ($get['user_penalties'] > 0) {
            throw new \UnexpectedValueException($this->lang->module['error_delete_penalty_user']);
        }

        $selectCupTeamsQuery = mysqli_query(
            $_database,
            "SELECT
                    `teamID`
                FROM `" . PREFIX . "cups_teams_member`
                WHERE `userID` = " . $userID . " AND `active` = 1"
        );

        if (!$selectCupTeamsQuery) {
            throw new \UnexpectedValueException($this->lang->module['error_delete_penalty_user']);
        }

        $teamArray = array();
        while($get = mysqli_fetch_array($selectCupTeamsQuery)) {
            $teamArray[] = $get[getConstNameTeamId()];
        }

        $whereClauseArray = array();
        $whereClauseArray[] = '`duration_time` > ' . time();

        if (count($teamArray) > 0) {
            $whereClauseArray[] = 'teamID IN (' . implode(', ', $teamArray) . ')';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectTeamPenaltiesQuery = mysqli_query(
            $_database,
            "SELECT
                    COUNT(*) AS `team_penalties`
                FROM `" . PREFIX . "cups_penalty`
                WHERE " . $whereClause
        );

        if (!$selectTeamPenaltiesQuery) {
            throw new \UnexpectedValueException($this->lang->module['error_delete_penalty_team']);
        }

        $get = mysqli_fetch_array($selectTeamPenaltiesQuery);
        if ($get['team_penalties'] > 0) {
            throw new \UnexpectedValueException($this->lang->module['error_delete_penalty_team']);
        }

        $whereClauseArray = array();
        $whereClauseArray[] = '(ct.`teamID` = ' . $userID . ' AND c.`mode` = \'1on1\')';

        if (count($teamArray) > 0) {
            $whereClauseArray[] = '(ct.`teamID` IN (' . implode(', ', $teamArray) . ') AND c.`mode` != \'1on1\')';
        }

        $whereClause = implode(' OR ', $whereClauseArray);
        $whereClause .= ' AND c.`status` < 4';

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    COUNT(*) AS `active_cups`
                FROM `" . PREFIX . "cups_teilnehmer` ct
                JOIN `" . PREFIX . "cups` c ON ct.`cupID` = c.`cupID`
                WHERE " . $whereClause
        );

        if (!$selectQuery) {
            throw new \UnexpectedValueException($this->lang->module['error_active_cups']);
        }

        $get = mysqli_fetch_array($selectQuery);
        if ($get['active_cups'] > 0) {
            throw new \UnexpectedValueException($this->lang->module['error_active_cups']);
        }

        /**
         * Delete gameaccount
         */

        $updateValueArray = array();
        $updateValueArray[] = '`active` = 0';
        $updateValueArray[] = '`deleted` = 1';
        $updateValueArray[] = '`deleted_date` = ' . time();
        $updateValueArray[] = '`deleted_seen` = ' . $deletedSeen;

        $updateValues = implode(', ', $updateValueArray);

        $updateQuery = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . "cups_gameaccounts`
                SET " .$updateValues . "
                WHERE `gameaccID` = " . $this->gameaccount_id
        );

        if (!$updateQuery) {
            throw new \UnexpectedValueException($this->lang->module['query_failed_delete']);
        }

        $this->gameaccount_id = null;

    }

    private function getActivateStateByGame() {

        global $userID;

        if (isanyadmin($userID)) {
            $this->isActive = 1;
            return;
        }

        if (is_null($this->game_tag)) {
            return;
        }

        global $_database;

        $getGame = mysqli_query(
            $_database,
            "SELECT
                    `cup_auto_active`
                FROM `" . PREFIX . "games`
                WHERE `tag` = '" . $this->game_tag . "'"
        );

        if (!$getGame) {
            return;
        }

        $get = mysqli_fetch_array($getGame);

        if (empty($get['cup_auto_active'])) {
            return;
        }

        if ($get['cup_auto_active'] != 1) {
            return;
        }

        $this->isActive = 1;

    }

    public function saveGameaccount($gameaccount_id = null, $redirect = TRUE) {

        if (is_null($this->game_id) || !validate_int($this->game_id)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_game']);
        }

        if (is_null($this->value)) {
            throw new \UnexpectedValueException($this->lang->module['error_gameaccount_value']);
        }

        if (!is_null($gameaccount_id)) {

            if (!validate_int($gameaccount_id)) {
                throw new \UnexpectedValueException($this->lang->module['error_gameaccount_id_type']);
            }

            //
            // Setze Gameaccount ID
            $this->gameaccount_id = $gameaccount_id;

            //
            // Lösche alten Gameaccount
            $this->deleteGameaccount();

        }

        if ($this->checkGameaccount()) {

            //
            // Kontrolle, ob Value gebanned
            $this->isBannedValue();

            global $_database, $userID;

            //
            // Gameaccount automatisch aktiv?
            $this->getActivateStateByGame();

            //
            // Neuer Gameaccount
            $query = mysqli_query(
                $_database,
                "INSERT INTO `" . PREFIX . "cups_gameaccounts`
                    (
                        `userID`,
                        `date`,
                        `category`,
                        `value`,
                        `active`
                    )
                    VALUES
                    (
                        " . $userID . ",
                        " . time() . ",
                        '" . $this->game_tag . "',
                        '" . $this->value . "',
                        " . $this->isActive . "
                    )"
            );

            if (!$query) {
                throw new \UnexpectedValueException($this->lang->module['query_failed_insert']);
            }

            //
            // Gameaccount ID
            $this->gameaccount_id = mysqli_insert_id($_database);

            if($this->isSteamAccount) {

                //
                // Speichere SteamID 64
                $this->setSteamIDUnique();

            } else if($this->isMojangAccount) {

                //
                // Speichere Mojang ID
                $this->setMojangUnique();

            } else if($this->isRiotAccount) {

                //
                // Speichere Riot ID
                $this->setRiotUnique();

            }

        }

        if ($redirect) {

            $varPage = (mb_substr(basename($_SERVER[ 'REQUEST_URI' ]), 0, 15) != "admincenter.php") ?
                'admin' : 'cup';

            if ($varPage == 'admin') {
                $parent_url = 'admincenter.php?site=cup&mod=gameaccounts';
            } else {
                $parent_url = 'index.php?site=gameaccount';
            }

            header('Location: '.$parent_url);

        }

    }

}