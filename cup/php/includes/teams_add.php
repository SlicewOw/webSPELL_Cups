<?php

try {

    if(!$loggedin) {
        throw new \Exception($_language->module['not_loggedin']);
    }

    if (validate_array($_POST, true)) {

        $parent_url = 'index.php?site=teams';

        if(isset($_POST['submit_team_add'])) {

            systeminc('classes/cup_teams');

            $team = new \myrisk\cup_team();

            try {

                if (isset($_POST['teamname'])) {

                    $teamname = getinput($_POST['teamname']);

                    $team->setName($teamname);

                    $_SESSION['cup']['team']['name'] = $teamname;

                }

                if (isset($_POST['teamtag'])) {

                    $teamtag = getinput($_POST['teamtag']);

                    $team->setTag($teamtag);

                    $_SESSION['cup']['team']['tag'] = $teamtag;

                }

                if (isset($_POST['homepage'])) {

                    $homepage = $_POST['homepage'];

                    $team->setHomepage($homepage);

                    $_SESSION['cup']['team']['hp'] = $homepage;

                }

                if (isset($_POST['country'])) {

                    $country = $_POST['country'];

                    $_SESSION['cup']['team']['country'] = $country;

                } else {
                    $country = 'de';
                }
                $team->setCountry($country);

                //
                // Team Image
                if (isset($_FILES['logotype'])) {
                    $team->uploadLogotype($_FILES['logotype']);
                }

                //
                // Team speichern in DB
                $team->saveTeam();

                setPlayerLog($userID, $team->getTeamId(), 'cup_team_created');

                unset($_SESSION['cup']);

                $parent_url .= '&action=admin&id=' . $team->getTeamId();

            } catch(Exception $e) {

                $_SESSION['cup']['team']['error'] = showError($e->getMessage());

                if (!is_null($team->getLogotype()) && !empty($team->getLogotype())) {
                    @unlink(__DIR__ . '/../../images/cup/teams/' . $team->getLogotype());
                }

                $parent_url .= '&action=add';

            }

        }

        header('Location: ' . $parent_url);

    } else {

        if (!isset($countries)) {

            $setCountry = (isset($_SESSION['cup']['team']['country'])) ?
                $_SESSION['cup']['team']['country'] : 'de';

            $countries = getcountries($setCountry);

        }

        $data_array = array();

        $data_array['$error_add'] = (isset($_SESSION['cup']['team']['error'])) ?
            $_SESSION['cup']['team']['error'] : '';

        $data_array['$teamname'] = (isset($_SESSION['cup']['team']['name'])) ?
            $_SESSION['cup']['team']['name'] : '';

        $data_array['$teamtag'] = (isset($_SESSION['cup']['team']['tag'])) ?
            $_SESSION['cup']['team']['tag'] : '';

        $data_array['$homepage'] = (isset($_SESSION['cup']['team']['hp'])) ?
            $_SESSION['cup']['team']['hp'] : '';

        $data_array['$countries'] = $countries;
        $data_array['$pic'] = '';
        $data_array['$team_id'] = 0;
        $data_array['$postName'] = 'submit_team_add';
        $teams_add = $GLOBALS["_template_cup"]->replaceTemplate("teams_add", $data_array);
        echo $teams_add;

        unset($_SESSION['cup']);

    }


} catch (Exception $e) {
    echo showError($e->getMessage());
}
