<?php
/*
##########################################################################
#                                                                        #
#           Version 4       /                        /   /               #
#          -----------__---/__---__------__----__---/---/-               #
#           | /| /  /___) /   ) (_ `   /   ) /___) /   /                 #
#          _|/_|/__(___ _(___/_(__)___/___/_(___ _/___/___               #
#                       Free Content / Management System                 #
#                                   /                                    #
#                                                                        #
#                                                                        #
#   Copyright 2005-2015 by webspell.org                                  #
#                                                                        #
#   visit webSPELL.org, webspell.info to get webSPELL for free           #
#   - Script runs under the GNU GENERAL PUBLIC LICENSE                   #
#   - It's NOT allowed to remove this copyright-tag                      #
#   -- http://www.fsf.org/licensing/licenses/gpl.html                    #
#                                                                        #
#   Code based on WebSPELL Clanpackage (Michael Gruber - webspell.at),   #
#   Far Development by Development Team - webspell.org                   #
#                                                                        #
#   visit webspell.org                                                   #
#                                                                        #
##########################################################################
*/

function getanzcwcomments($cwID)
{
    return mysqli_num_rows(
        safe_query(
            "SELECT commentID FROM `" . PREFIX . "comments` WHERE `parentID` = " .(int)$cwID . " AND `type` = 'cw'"
        )
    );
}

function getsquads()
{
    $squads = "";
    $ergebnis = safe_query("SELECT * FROM `" . PREFIX . "squads`");
    while ($ds = mysqli_fetch_array($ergebnis)) {
        $squads .= '<option value="' . $ds[ 'squadID' ] . '">' . $ds[ 'name' ] . '</option>';
    }
    return $squads;
}

function getgamesquads()
{
    $squads = '';
    $ergebnis = safe_query("SELECT * FROM `" . PREFIX . "squads` WHERE `gamesquad` = 1");
    while ($ds = mysqli_fetch_array($ergebnis)) {
        $squads .= '<option value="' . $ds[ 'squadID' ] . '">' . $ds[ 'name' ] . '</option>';
    }
    return $squads;
}

function getsquadname($squadID)
{
    $ds = mysqli_fetch_array(
        safe_query(
            "SELECT `name` FROM `" . PREFIX . "squads` WHERE `squadID` = " . (int)$squadID
        )
    );
    return $ds[ 'name' ];
}

function issquadmember($userID, $squadID)
{
    return (
        mysqli_num_rows(
            safe_query(
                "SELECT
                    `sqmID`
                FROM
                    `" . PREFIX . "squads_members`
                WHERE
                    `userID` = " . (int)$userID . " AND
                    `squadID` = " . (int)$squadID
            )
        ) > 0);
}

function isgamesquad($squadID)
{
    return (
        mysqli_num_rows(
            safe_query(
                "SELECT
                    `squadID`
                FROM
                    `" . PREFIX . "squads`
                WHERE
                    `squadID` = " . (int)$squadID . " AND
                    gamesquad = 1"
            )
        ) > 0);
}

function getgamename($tag)
{
    $ds = mysqli_fetch_array(safe_query("SELECT `name` FROM `" . PREFIX . "games` WHERE `tag` = '$tag'"));
    return $ds[ 'name' ];
}

function is_gametag($tag)
{
    return (mysqli_num_rows(safe_query("SELECT `name` FROM `" . PREFIX . "games` WHERE `tag` = '$tag'")) > 0);
}

function is_gamefilexist($filepath, $tag) {
	if (file_exists($filepath.$tag.'.png')) {
    	$extension = $tag.'.png';
    }
    elseif (file_exists($filepath.$tag.'.jpg')){
	    $extension = $tag.'.jpg';
    }
    else {
	    $extension = $tag.'.gif';
    }
    return $extension;
}

function getGamesAsOptionList($selected = 'csg', $optionValueTag = TRUE, $addDefaultOption = FALSE) {

    global $_database;

    $whereClauseArray = array();
    $whereClauseArray[] = '`active` = 1';
    if (!empty($selected)) {
        $whereClauseArray[] = 'tag = \''.$selected.'\'';
    }

    $whereClause = implode(' OR ', $whereClauseArray);

    $query = mysqli_query(
        $_database,
        "SELECT
                `gameID`,
                `tag`,
                `name`
            FROM `" . PREFIX . "games`
            WHERE " . $whereClause . "
            ORDER BY `name` ASC"
    );

    $list = '';

    if ($addDefaultOption) {
        $list .= '<option value="0">-- / --</option>';
    }

    while ($ds = mysqli_fetch_array($query)) {

        $value = ($optionValueTag) ? 
            $ds['tag'] : $ds['gameID'];

        $list .= '<option value="' . $value . '">' . $ds['name'] . '</option>';

    }

    if (!$optionValueTag && !is_numeric($selected)) {
        $selected = getGame($selected, 'id');
    }

    if (!$optionValueTag) {

        if ($selected > 0) {

            $list = str_replace(
                'value="' . $selected . '"',
                'value="' . $selected . '" selected="selected"',
                $list
            );

        }

    } else if (!empty($selected)) {

        $list = str_replace(
            'value="' . $selected . '"',
            'value="' . $selected . '" selected="selected"',
            $list
        );

    }

    return $list;

}

function getgameshort($name) {

    if (empty($name)) {
        return '';
    }

    global $_database;

    $gameName = addslashes($name);

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                `short`
            FROM `" . PREFIX . "games`
            WHERE `name` = '" . $gameName . "'"
    );

    if (!$selectQuery) {
        return '';
    }

    $ds = mysqli_fetch_array($selectQuery);

    return $ds['short'];

}

function getgametag($name) {

    if (empty($name)) {
        return '';
    }

    global $_database;

    $gameName = addslashes($name);

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                `tag`
            FROM `" . PREFIX . "games`
            WHERE `name` = '" . $gameName . "'"
    );

    if ($selectQuery) {
        return '';
    }

    $ds = mysqli_fetch_array($query);

    return (!empty($ds['tag'])) ?
        $ds['tag'] : '';

}
