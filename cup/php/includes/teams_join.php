<?php

try {

    if (!$loggedin) {
        throw new \UnexpectedValueException($_language->module['not_loggedin'] . '<br />&raquo; <a href="index.php?site=login">Login</a>');
    }

    $team_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if(empty($team_id) || ($team_id < 1)) {
        throw new \UnexpectedValueException($_language->module['no_team']);
    }

    $teamPassword = (isset($_GET['pw'])) ?
        getinput($_GET['pw']) : '';

    if(empty($teamPassword)) {
        throw new \UnexpectedValueException($_language->module['no_access']);
    }

    $info = mysqli_query(
        $_database,
        "SELECT
                `teamID`,
                `userID`,
                `password`
            FROM `" . PREFIX . "cups_teams`
            WHERE teamID = " . $team_id . " AND password = '" . $teamPassword . "' AND deleted = 0"
    );

    if (!$info) {
        throw new \UnexpectedValueException($_language->module['query_select_failed']);
    }

    if(mysqli_num_rows($info) != 1) {
        throw new \UnexpectedValueException($_language->module['add_error6']);
    }

    $ds = mysqli_fetch_array($info);

    $ismember = mysqli_num_rows(
        mysqli_query(
            $_database,
            "SELECT memberID FROM ".PREFIX."cups_teams_member
                WHERE userID = ".$userID." AND teamID = ".$ds[getConstNameTeamId()]." AND active = 1"
        )
    );

    if($userID == $ds['userID']) {
        // User ist Admin
        throw new \UnexpectedValueException($_language->module['team_stillmember']);
    }

    if($teamPassword != $ds['password']) {
        // Falsches Passwort
        throw new \UnexpectedValueException($_language->module['wrong_pw']);
    }

    if($ismember > 0) {
        // User ist bereits Mitglied
        throw new \UnexpectedValueException($_language->module['add_error5']);
    }

    $joinTeam = TRUE;
    $message = '';

    //
    // Team in einem aktiven Cup?
    $cupDataArray = array();
    $query = mysqli_query(
        $_database,
        "SELECT a.cupID FROM `".PREFIX."cups` a
            JOIN `".PREFIX."cups_teilnehmer` b ON a.cupID = b.cupID
            WHERE a.status < 4 AND b.teamID = '".$team_id."'
            ORDER BY cupID ASC"
    );
    while($get = mysqli_fetch_array($query)) {
        $cupDataArray[] = $get[getConstNameCupId()];
    }

    //
    // Aktiver Cup? Wenn ja, dann kontrolliere ob Gameaccount vorhanden
    $anzCups = count($cupDataArray);
    if($anzCups > 0) {

        $get = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT COUNT(*) AS anz FROM `".PREFIX."cups_teilnehmer`
                    WHERE cupID IN (".implode(', ', $cupDataArray).") AND teamID = '".$team_id."'"
            )
        );

    }

    //
    // Gameaccount eingetragen?
    if($get['anz'] > 0) {

        $anzCups = count($cupDataArray);
        for($x=0; $x<$anzCups; $x++) {

            $cup_id = $cupDataArray[$x];
            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT game FROM `".PREFIX."cups`
                        WHERE cupID = '".$cup_id."'"
                )
            );

            $gameaccount = gameaccount($userID, 'get', $get['game']);

            if(empty($gameaccount)) {
                $message = $_language->module['team_join_missing_gameacc'];
                $joinTeam = FALSE;
                break;
            }

        }

    }

    if(!$joinTeam) {
        throw new \UnexpectedValueException($message);
    }

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
                '".$userID."',
                '".$team_id."',
                3,
                '".time()."'
            )"
    );

    if(!$query) {
        throw new \UnexpectedValueException($_language->module['query_insert_failed']);
    }

    $teamname = getteam($team_id, 'name');

    setCupTeamLog($team_id, $teamname, 'player_joined');

    setPlayerLog($userID, $team_id, 'cup_team_join');

    $_SESSION['successArray'][] = $_language->module['team_join_ok'];

    header('Location: index.php?site=teams&action=details&id='.$team_id);

} catch (Exception $e) {
    echo showError($e->getMessage());
}
