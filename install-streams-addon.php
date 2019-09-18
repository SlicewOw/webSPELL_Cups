<?php

include("_mysql.php");
include("_settings.php");

try {

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "liveshow` (
            `livID` int(11) NOT NULL,
            `cronjobID` int(11) NOT NULL,
            `title` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
            `id` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
            `stream_id` varchar(100) COLLATE latin1_german1_ci NOT NULL,
            `type` int(11) NOT NULL DEFAULT '1',
            `userID` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '',
            `facebook` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `twitter` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `youtube` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `active` varchar(1) COLLATE latin1_german1_ci NOT NULL DEFAULT '0',
            `hits` int(11) NOT NULL DEFAULT '0',
            `online` int(11) NOT NULL DEFAULT '0',
            `viewer` int(11) NOT NULL DEFAULT '0',
            `game` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '0',
            `date` int(11) NOT NULL,
            `preview` varchar(255) COLLATE latin1_german1_ci NOT NULL DEFAULT '0',
            `comments` int(1) NOT NULL DEFAULT '1',
            `prioritization` int(5) NOT NULL DEFAULT '1'
        ) ENGINE=MyISAM;"
    );

    if (!$createTableQuery) {
        throw new \UnexpectedValueException('query_failed_liveshow');
    }

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "liveshow_cronjobs` (
            `cronID` int(11) NOT NULL,
            `id` int(11) NOT NULL DEFAULT '0',
            `hits` int(11) NOT NULL DEFAULT '0',
            `date` int(11) NOT NULL DEFAULT '0'
        ) ENGINE=MyISAM;"
    );

    $createTableQuery = mysqli_query(
        $_database,
        "CREATE TABLE `" . PREFIX . "liveshow_type` (
            `typeID` int(11) NOT NULL,
            `plattform` varchar(255) COLLATE latin1_german1_ci NOT NULL,
            `video` text COLLATE latin1_german1_ci NOT NULL,
            `chat` text COLLATE latin1_german1_ci NOT NULL,
            `icon_small` varchar(60) COLLATE latin1_german1_ci NOT NULL
        ) ENGINE=MyISAM;"
    );

    $insertQuery = mysqli_query(
        $_database,
        "INSERT INTO `" . PREFIX . "liveshow_type`
        (`typeID`, `plattform`, `video`, `chat`, `icon_small`)
        VALUES
        (1, 'Twitch', '<iframe src=\"http://player.twitch.tv/?channel=%channel_id%\" class=\"stream_embed\" allowfullscreen scrolling=\"no\"></iframe>', '<iframe src=\"http://www.twitch.tv/%channel_id%/chat\" class=\"chat_embed\" scrolling=\"no\"></iframe>', '1_short.png')"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "liveshow`
            ADD PRIMARY KEY (`livID`),
            ADD UNIQUE KEY `id` (`id`,`type`) USING BTREE;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "liveshow_cronjobs`
            ADD PRIMARY KEY (`cronID`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "liveshow_type`
            ADD PRIMARY KEY (`typeID`),
            ADD UNIQUE KEY `plattform` (`plattform`);"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "liveshow`
            MODIFY `livID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "liveshow_cronjobs`
            MODIFY `cronID` int(11) NOT NULL AUTO_INCREMENT;"
    );

    $alterTableQuery = mysqli_query(
        $_database,
        "ALTER TABLE `" . PREFIX . "liveshow_type`
            MODIFY `typeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;"
    );

    $insertQuery = mysqli_query(
        $_database,
        "INSERT INTO `" . PREFIX . "comments_settings`
            (`ident`, `modul`, `id`, `parent`)
            VALUES
            ('st', 'liveshow', 'livID', 'comments');"
    );

    echo "Delete this file!";

} catch (Exception $e) {
    echo $e->getMessage();
}