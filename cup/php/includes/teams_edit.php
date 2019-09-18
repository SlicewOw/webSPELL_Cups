<?php

if ($loggedin) {

    if (isset($_POST['submit_team_edit'])) {

        if (isset($_POST[getConstNameTeamIdWithUnderscore()])) {
            $team_id = $_POST[getConstNameTeamIdWithUnderscore()];
        } else if (isset($_POST[getConstNameTeamId()])) {
            $team_id = $_POST[getConstNameTeamId()];
        } else {
            $team_id = 0;
        }

    } else if (isset($_GET['id'])) {
        $team_id = (int)$_GET['id'];
    }

}

try {

    if (!isset($team_id) || !validate_int($team_id)) {
        throw new \UnexpectedValueException($_language->module['not_loggedin']);
    }

    if (!isinteam($userID, $team_id, 'admin')) {
        header('Location: index.php?site=teams&action=show');
    }

    $checkIf = mysqli_fetch_array(
        mysqli_query(
            $_database,
            "SELECT COUNT(*) AS exist FROM `".PREFIX."cups_teams`
                WHERE teamID = " . $team_id
        )
    );

    if ($checkIf['exist'] != 1) {
        throw new \UnexpectedValueException($_language->module['no_team']);
    }

    if (isset($_POST['submitEditTeam'])) {

        $parent_url = 'index.php?site=teams&action=admin&id=' . $team_id;

        systeminc('classes/cup_teams');

        try {

            $team = new \myrisk\cup_team();

            if(isset($_POST['teamname'])) {
                $team->setName($_POST['teamname']);
            }

            if(isset($_POST['teamtag'])) {
                $team->setTag($_POST['teamtag']);
            }

            if(isset($_POST['homepage'])) {
                $team->setHomepage($_POST['homepage']);
            }

            $country = (isset($_POST['country'])) ?
                $_POST['country'] : getCupDefaultLanguage();
            $team->setCountry($country);

            //
            // Team Image
            if(isset($_FILES['logotype'])) {
                $team->uploadLogotype($_FILES['logotype']);
            }

            //
            // Team speichern in DB
            $team->saveTeam($team_id);

            $parent_url .= '&message=edit_ok';

        } catch(Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }

        header('Location: ' . $parent_url);

    } else {

        $ds = mysqli_fetch_array(
            mysqli_query(
                $_database,
                "SELECT
                      `name`,
                      `tag`,
                      `hp`,
                      `logotype`,
                      `country`
                    FROM `" . PREFIX . "cups_teams`
                    WHERE `teamID` = " . $team_id
            )
        );

        $teamname = $ds['name'];
        $teamtag = $ds['tag'];
        $homepage = $ds['hp'];
        $country = $ds['country'];

        $countries = getcountries($country);

        $logotype_max_size = (isset($cup_team_logotype_max_size) && $cup_team_logotype_max_size) ?
            $cup_team_logotype_max_size : 500;

        $image_response = str_replace(
            '%max_pixels%',
            $logotype_max_size,
            $_language->module['image_response']
        );

        $data_array = array();
        $data_array['$title'] = $_language->module['edit'];
        $data_array['$logotype_is_required'] = (isset($cup_team_logotype_is_required) && $cup_team_logotype_is_required) ?
            ' *' : '';
        $data_array['$image_response'] = $image_response;
        $data_array['$error_add'] = '';
        $data_array['$teamname'] = (isset($teamname)) ? $teamname : '';
        $data_array['$teamtag'] = (isset($teamtag)) ? $teamtag : '';
        $data_array['$homepage'] = (isset($homepage)) ? $homepage : '';
        $data_array['$countries'] = $countries;
        $data_array['$pic'] = '<img src="' . getCupTeamImage($team_id, true) . '" alt="" />';
        $data_array['$team_id'] = $team_id;
        $data_array['$postName'] = 'submitEditTeam';
        $teams_add = $GLOBALS["_template_cup"]->replaceTemplate("teams_action", $data_array);
        echo $teams_add;

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}