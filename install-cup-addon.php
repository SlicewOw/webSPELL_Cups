<?php

include("_mysql.php");
include("_settings.php");

try {

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "games`
            ADD `short` VARCHAR(50) NULL AFTER `name`,
            ADD `active` INT(1) NOT NULL DEFAULT '1' AFTER `short`;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "user_groups`
            ADD `cup` int(1) NOT NULL DEFAULT 0"
    );

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups` (
            `cupID` int(11) NOT NULL,
            `priority` varchar(50) COLLATE latin1_german1_ci NOT NULL DEFAULT 'normal',
            `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `registration` varchar(20) COLLATE latin1_german1_ci NOT NULL DEFAULT 'open',
            `checkin_date` int(11) NOT NULL,
            `start_date` int(11) NOT NULL,
            `game` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `gameID` int(11) NOT NULL DEFAULT '0',
            `elimination` varchar(20) COLLATE latin1_german1_ci NOT NULL DEFAULT 'single',
            `server` int(1) NOT NULL DEFAULT '0',
            `bot` int(1) NOT NULL DEFAULT '0',
            `mapvote_enable` int(1) NOT NULL DEFAULT '0',
            `mappool` int(11) NOT NULL DEFAULT '0',
            `mode` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `ruleID` int(11) NOT NULL,
            `max_size` int(11) NOT NULL,
            `max_penalty` int(11) NOT NULL DEFAULT '12',
            `groupstage` int(1) NOT NULL DEFAULT '0',
            `status` int(11) NOT NULL DEFAULT '1',
            `hits` int(11) NOT NULL DEFAULT '0',
            `hits_teams` int(11) NOT NULL DEFAULT '0',
            `hits_groups` int(11) NOT NULL DEFAULT '0',
            `hits_bracket` int(11) NOT NULL DEFAULT '0',
            `hits_rules` int(11) NOT NULL DEFAULT '0',
            `description` text COLLATE latin1_german1_ci NOT NULL,
            `saved` int(1) NOT NULL DEFAULT '0',
            `admin_visible` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    if (!$createTableQuery) {
        throw new \Exception('query_failed_cups');
    }

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_admin` (
      `adminID` int(11) NOT NULL,
      `userID` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `rights` int(11) NOT NULL DEFAULT '1'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_awards` (
      `awardID` int(11) NOT NULL,
      `teamID` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `award` int(11) NOT NULL,
      `date` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_awards_category` (
      `awardID` int(11) NOT NULL,
      `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `icon` varchar(30) COLLATE latin1_german1_ci NOT NULL,
      `active_column` varchar(50) COLLATE latin1_german1_ci NOT NULL,
      `platzierung` int(3) DEFAULT NULL,
      `anz_cups` int(11) DEFAULT NULL,
      `anz_matches` int(11) DEFAULT NULL,
      `sort` int(11) NOT NULL DEFAULT '1',
      `description` text COLLATE latin1_german1_ci NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_gameaccounts` (
      `gameaccID` int(11) NOT NULL,
      `userID` int(11) NOT NULL,
      `date` int(11) NOT NULL,
      `category` varchar(3) COLLATE latin1_german1_ci NOT NULL,
      `value` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `smurf` int(1) NOT NULL DEFAULT '0',
      `active` int(1) NOT NULL DEFAULT '0',
      `deleted` int(1) NOT NULL DEFAULT '0',
      `deleted_date` int(11) NOT NULL DEFAULT '0',
      `deleted_seen` int(1) NOT NULL DEFAULT '1'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_gameaccounts_banned` (
      `id` int(11) NOT NULL,
      `game` varchar(3) COLLATE latin1_german1_ci NOT NULL,
      `game_id` int(11) NOT NULL,
      `value` varchar(50) COLLATE latin1_german1_ci NOT NULL,
      `description` text COLLATE latin1_german1_ci NOT NULL,
      `date` int(11) NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_gameaccounts_csgo` (
      `gameaccID` int(11) NOT NULL,
      `validated` int(1) NOT NULL DEFAULT '0',
      `date` int(11) DEFAULT NULL,
      `hours` int(11) DEFAULT NULL,
      `vac_bann` int(1) NOT NULL DEFAULT '0',
      `bann_date` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_gameaccounts_lol` (
      `gameaccID` int(11) NOT NULL,
      `unique_id` int(11) NOT NULL,
      `region` varchar(20) COLLATE latin1_german1_ci NOT NULL DEFAULT 'euw',
      `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `date` int(11) NOT NULL,
      `division` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `rank` int(11) NOT NULL DEFAULT '0',
      `level` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_gameaccounts_mc` (
      `gameaccID` int(11) NOT NULL,
      `unique_id` varchar(50) COLLATE latin1_german1_ci NOT NULL,
      `active` int(1) NOT NULL DEFAULT '0',
      `date` int(11) NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_gameaccounts_profiles` (
      `profileID` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `category` varchar(30) NOT NULL,
      `url` varchar(100) NOT NULL,
      `date` int(11) NOT NULL,
      `deleted` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_gruppen` (
      `matchID` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `gruppeID` int(11) NOT NULL,
      `runde` int(11) NOT NULL,
      `team1` int(11) NOT NULL,
      `ergebnis1` int(11) NOT NULL DEFAULT '0',
      `team2` int(11) NOT NULL,
      `ergebnis2` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_mappool` (
      `mappoolID` int(11) NOT NULL,
      `name` varchar(255) NOT NULL,
      `game` varchar(3) NOT NULL,
      `gameID` int(11) NOT NULL DEFAULT '0',
      `maps` text NOT NULL,
      `deleted` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_matches_playoff` (
      `matchID` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `wb` int(11) NOT NULL DEFAULT '0',
      `runde` int(11) NOT NULL,
      `spiel` int(11) NOT NULL,
      `format` varchar(3) COLLATE latin1_german1_ci NOT NULL DEFAULT 'bo1',
      `date` int(11) NOT NULL DEFAULT '0',
      `mapvote` int(1) NOT NULL DEFAULT '0',
      `team1` int(11) NOT NULL,
      `team1_freilos` int(11) NOT NULL DEFAULT '0',
      `ergebnis1` int(11) NOT NULL DEFAULT '0',
      `team2` int(11) NOT NULL,
      `team2_freilos` int(11) NOT NULL DEFAULT '0',
      `ergebnis2` int(11) NOT NULL DEFAULT '0',
      `active` int(1) NOT NULL DEFAULT '0',
      `comments` int(1) NOT NULL DEFAULT '1',
      `team1_confirmed` int(1) NOT NULL DEFAULT '0',
      `team2_confirmed` int(1) NOT NULL DEFAULT '0',
      `admin_confirmed` int(1) NOT NULL DEFAULT '0',
      `maps` text COLLATE latin1_german1_ci NOT NULL,
      `server` text COLLATE latin1_german1_ci NOT NULL,
      `hits` int(11) NOT NULL DEFAULT '0',
      `bot` int(1) NOT NULL DEFAULT '0',
      `admin` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_matches_playoff_screens` (
      `screenshotID` int(11) NOT NULL,
      `matchID` int(11) NOT NULL,
      `file` varchar(100) NOT NULL,
      `category_id` int(11) NOT NULL,
      `date` int(11) NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_matches_playoff_screens_category` (
      `categoryID` int(11) NOT NULL,
      `game_id` int(11) NOT NULL,
      `name` varchar(100) NOT NULL,
      `deleted` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_penalty` (
      `ppID` int(11) NOT NULL,
      `adminID` int(11) NOT NULL DEFAULT '0',
      `date` int(11) NOT NULL,
      `duration_time` int(11) NOT NULL DEFAULT '0',
      `teamID` int(11) NOT NULL DEFAULT '0',
      `userID` int(11) NOT NULL DEFAULT '0',
      `reasonID` int(11) NOT NULL DEFAULT '0',
      `comment` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `deleted` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_penalty_category` (
      `reasonID` int(11) NOT NULL,
      `name_de` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `name_uk` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `points` int(11) NOT NULL DEFAULT '0',
      `lifetime` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_platzierungen` (
      `pID` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `teamID` int(11) NOT NULL,
      `platzierung` varchar(255) COLLATE latin1_german1_ci NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_policy` (
      `id` int(11) NOT NULL,
      `content` text COLLATE latin1_german1_ci NOT NULL,
      `date` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_preise` (
      `preisID` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `preis` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `platzierung` int(11) NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_rules` (
      `ruleID` int(11) NOT NULL,
      `gameID` int(5) NOT NULL DEFAULT '0',
      `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `text` text COLLATE latin1_german1_ci NOT NULL,
      `date` int(11) NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_screenshots` (
      `screenID` int(11) NOT NULL,
      `match_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `date` int(11) NOT NULL,
      `image` int(11) NOT NULL,
      `is_file` int(1) NOT NULL DEFAULT '1',
      `deleted` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_sponsors` (
      `id` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `sponsorID` int(11) NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_streams` (
      `streamID` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `livID` int(11) NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_supporttickets` (
      `ticketID` int(11) NOT NULL,
      `start_date` int(11) NOT NULL,
      `take_date` int(11) DEFAULT NULL,
      `closed_date` int(11) NOT NULL,
      `closed_by_id` int(11) NOT NULL DEFAULT '0',
      `userID` int(11) NOT NULL,
      `opponent_adminID` int(11) NOT NULL DEFAULT '0',
      `adminID` int(11) NOT NULL DEFAULT '0',
      `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `screenshot` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `categoryID` int(11) NOT NULL DEFAULT '0',
      `cupID` int(11) NOT NULL DEFAULT '0',
      `teamID` int(11) NOT NULL DEFAULT '0',
      `opponentID` int(11) NOT NULL DEFAULT '0',
      `matchID` int(11) NOT NULL DEFAULT '0',
      `text` text COLLATE latin1_german1_ci NOT NULL,
      `status` int(1) NOT NULL DEFAULT '1'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_supporttickets_category` (
      `categoryID` int(11) NOT NULL,
      `name_de` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `name_uk` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `template` varchar(255) COLLATE latin1_german1_ci NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_supporttickets_content` (
      `contentID` int(11) NOT NULL,
      `ticketID` int(11) NOT NULL,
      `date` int(11) NOT NULL,
      `userID` int(11) NOT NULL,
      `text` text COLLATE latin1_german1_ci NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_supporttickets_status` (
      `ticket_id` int(11) NOT NULL,
      `primary_id` int(11) NOT NULL,
      `admin` int(1) NOT NULL DEFAULT '0',
      `ticket_seen_date` int(11) DEFAULT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_team` (
      `userID` int(11) NOT NULL,
      `date` int(11) NOT NULL,
      `position` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `description` text COLLATE latin1_german1_ci NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_teams` (
      `teamID` int(11) NOT NULL,
      `date` int(11) NOT NULL,
      `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `tag` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `userID` int(11) NOT NULL,
      `country` varchar(4) COLLATE latin1_german1_ci NOT NULL DEFAULT 'de',
      `hp` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `logotype` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `password` varchar(255) COLLATE latin1_german1_ci NOT NULL,
      `hits` int(11) NOT NULL DEFAULT '0',
      `deleted` int(1) NOT NULL DEFAULT '0',
      `admin` int(1) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_teams_comments` (
      `teamID` int(11) NOT NULL,
      `date` int(11) NOT NULL DEFAULT '0',
      `userID` int(11) NOT NULL DEFAULT '0',
      `comment` text COLLATE latin1_german1_ci NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_teams_log` (
      `teamID` int(11) NOT NULL,
      `teamName` varchar(100) COLLATE latin1_german1_ci NOT NULL,
      `date` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `parent_id` int(11) DEFAULT NULL,
      `kicked_id` int(11) DEFAULT NULL,
      `action` varchar(255) COLLATE latin1_german1_ci NOT NULL
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_teams_member` (
      `memberID` int(11) NOT NULL,
      `userID` int(11) NOT NULL,
      `teamID` int(11) NOT NULL,
      `position` int(11) NOT NULL,
      `join_date` int(11) NOT NULL,
      `left_date` int(11) NOT NULL,
      `kickID` int(11) NOT NULL DEFAULT '0',
      `active` int(1) NOT NULL DEFAULT '1'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_teams_position` (
      `positionID` int(11) NOT NULL,
      `name` varchar(50) NOT NULL,
      `counter` int(11) DEFAULT NULL,
      `level_id` int(11) DEFAULT NULL,
      `sort` int(11) NOT NULL DEFAULT '1'
    ) ENGINE=MyISAM;");

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_teams_social` (
      `teamID` int(11) NOT NULL,
      `category_id` int(11) NOT NULL,
      `value` varchar(50) COLLATE latin1_german1_ci NOT NULL,
      `date` int(11) DEFAULT NULL
    ) ENGINE=MyISAM;"
    );

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_teilnehmer` (
      `ID` int(11) NOT NULL,
      `cupID` int(11) NOT NULL,
      `teamID` int(11) NOT NULL,
      `checked_in` int(1) NOT NULL DEFAULT '0',
      `date_register` int(11) NOT NULL DEFAULT '0',
      `date_checkin` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=MyISAM;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups`
      ADD PRIMARY KEY (`cupID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_admin`
      ADD PRIMARY KEY (`adminID`),
      ADD UNIQUE KEY `adminID` (`adminID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_awards`
      ADD PRIMARY KEY (`awardID`),
      ADD UNIQUE KEY `awardID` (`awardID`),
      ADD UNIQUE KEY `teamID` (`teamID`,`award`) USING BTREE;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_awards_category`
      ADD PRIMARY KEY (`awardID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts`
      ADD PRIMARY KEY (`gameaccID`),
      ADD UNIQUE KEY `gameaccID` (`gameaccID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts_banned`
      ADD PRIMARY KEY (`id`),
      ADD UNIQUE KEY `value` (`value`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts_csgo`
      ADD UNIQUE KEY `pk_gameacc_csgo` (`gameaccID`) USING BTREE;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts_lol`
      ADD PRIMARY KEY (`gameaccID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts_mc`
      ADD PRIMARY KEY (`gameaccID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts_profiles`
      ADD PRIMARY KEY (`profileID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gruppen`
      ADD PRIMARY KEY (`matchID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_mappool`
      ADD UNIQUE KEY `mappoolID` (`mappoolID`),
      ADD UNIQUE KEY `unique_name` (`name`,`gameID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_matches_playoff`
      ADD PRIMARY KEY (`matchID`),
      ADD UNIQUE KEY `matchID` (`matchID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_matches_playoff_screens`
      ADD PRIMARY KEY (`screenshotID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_matches_playoff_screens_category`
      ADD PRIMARY KEY (`categoryID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_penalty`
      ADD PRIMARY KEY (`ppID`),
      ADD UNIQUE KEY `ppID` (`ppID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_penalty_category`
      ADD PRIMARY KEY (`reasonID`),
      ADD UNIQUE KEY `reasonID` (`reasonID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_platzierungen`
      ADD PRIMARY KEY (`pID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_policy`
      ADD PRIMARY KEY (`id`),
      ADD UNIQUE KEY `id` (`id`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_preise`
      ADD PRIMARY KEY (`preisID`),
      ADD UNIQUE KEY `preisID` (`preisID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_rules`
      ADD PRIMARY KEY (`ruleID`),
      ADD UNIQUE KEY `ruleID` (`ruleID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_screenshots`
      ADD PRIMARY KEY (`screenID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_sponsors`
      ADD PRIMARY KEY (`id`),
      ADD UNIQUE KEY `id` (`id`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_streams`
      ADD UNIQUE KEY `streamID` (`streamID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_supporttickets`
      ADD PRIMARY KEY (`ticketID`),
      ADD UNIQUE KEY `ticketID` (`ticketID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_supporttickets_category`
      ADD PRIMARY KEY (`categoryID`),
      ADD UNIQUE KEY `categoryID` (`categoryID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_supporttickets_content`
      ADD PRIMARY KEY (`contentID`),
      ADD UNIQUE KEY `contentID` (`contentID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_supporttickets_status`
      ADD UNIQUE KEY `ticket_id` (`ticket_id`,`primary_id`,`admin`) USING BTREE;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_team`
      ADD PRIMARY KEY (`userID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teams`
      ADD PRIMARY KEY (`teamID`),
      ADD UNIQUE KEY `tag` (`tag`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teams_comments`
      ADD PRIMARY KEY (`teamID`,`date`) USING BTREE;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teams_member`
      ADD PRIMARY KEY (`memberID`),
      ADD UNIQUE KEY `memberID` (`memberID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teams_position`
      ADD PRIMARY KEY (`positionID`),
      ADD UNIQUE KEY `name` (`name`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teams_social`
      ADD PRIMARY KEY (`teamID`,`category_id`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teilnehmer`
      ADD PRIMARY KEY (`ID`),
      ADD UNIQUE KEY `check_teilnehmer` (`cupID`,`teamID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups`
      MODIFY `cupID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_admin`
      MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_awards`
      MODIFY `awardID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_awards_category`
      MODIFY `awardID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts`
      MODIFY `gameaccID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts_banned`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gameaccounts_profiles`
      MODIFY `profileID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_gruppen`
      MODIFY `matchID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_mappool`
      MODIFY `mappoolID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_matches_playoff`
      MODIFY `matchID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_matches_playoff_screens`
      MODIFY `screenshotID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_matches_playoff_screens_category`
      MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_penalty`
      MODIFY `ppID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_penalty_category`
      MODIFY `reasonID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_platzierungen`
      MODIFY `pID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_policy`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_preise`
      MODIFY `preisID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_rules`
      MODIFY `ruleID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_screenshots`
      MODIFY `screenID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_sponsors`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_streams`
      MODIFY `streamID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_supporttickets`
      MODIFY `ticketID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_supporttickets_category`
      MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_supporttickets_content`
      MODIFY `contentID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teams`
      MODIFY `teamID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teams_member`
      MODIFY `memberID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teams_position`
      MODIFY `positionID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_teilnehmer`
      MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE IF NOT EXISTS `" . PREFIX . "user_position_static` (
            `positionID` int(11) NOT NULL,
              `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
              `tag` varchar(2) COLLATE latin1_german1_ci NOT NULL,
              `game_id` int(11) DEFAULT NULL,
              `sort` int(11) NOT NULL DEFAULT '1'
            ) ENGINE=MyISAM"
    );

    $alterTabelQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "user_position_static`
            ADD PRIMARY KEY (`positionID`),
            ADD UNIQUE KEY `positionID` (`positionID`),
            ADD UNIQUE KEY `TagAndGame` (`tag`,`game_id`),
            ADD KEY `FK_UserPosition_Game` (`game_id`);"
    );

    $alterTabelQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "user_position_static`
            MODIFY `positionID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $insertDataQuery = mysqli_query(
        $_database,
        "INSERT INTO `" . PREFIX . "cups_teams_position`
            (`positionID`, `name`, `counter`, `level_id`, `sort`)
            VALUES
            (1, 'Admin', NULL, NULL, 1),
            (2, 'Coach', NULL, NULL, 2),
            (3, 'Player', NULL, NULL, 3);"
    );

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "user_log` (
            `userID` int(11) NOT NULL,
            `username` varchar(100) COLLATE latin1_german1_ci NOT NULL,
            `date` int(11) NOT NULL,
            `parent_id` int(11) NOT NULL,
            `action` varchar(255) COLLATE latin1_german1_ci NOT NULL
        ) ENGINE=MyISAM;"
    );

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "cups_settings` (
            `cup_id` int(11) NOT NULL,
            `round` int(2) NOT NULL,
            `format` varchar(5) COLLATE utf8_bin NOT NULL
        ) ENGINE=MyISAM;"
    );

    $alterTabelQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "cups_settings`
            ADD UNIQUE KEY `cup_id` (`cup_id`,`round`);"
    );

    echo "Delete this file!";

} catch (Exception $e) {
    echo $e->getMessage();
}