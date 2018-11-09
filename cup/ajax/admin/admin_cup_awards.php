<?php

$returnArray = array(
    'status' => FALSE,
    'message' => array(),
    'html' => ''
);

try {
    
    $_language->readModule('cups', false, true);

    if (!$loggedin || !iscupadmin($userID)) {
        throw new \Exception($_language->module['access_denied']);
    }

    if ($getAction == 'home') {

        $query = mysqli_query(
            $_database,
            "SELECT * FROM  `".PREFIX."cups_awards_category`
                ORDER BY sort ASC"
        );

        if (!$query) {
            throw new \Exception($_language->module['query_select_failed']);
        }

        $n = 1;
        while ($get = mysqli_fetch_array($query)) {

            $award_id = $get['awardID'];

            $subget = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS anz FROM  `".PREFIX."cups_awards`
                        WHERE award = '".$award_id."'"
                )
            );

            $anz = $subget['anz'];

            $awardColumn = $get['active_column'];
            $awardValue = $get[$awardColumn];

            $data_array = array();
            $data_array['$n']           = $n++;
            $data_array['$award_id']    = $award_id;
            $data_array['$name']        = $get['name'];
            $data_array['$awardColumn'] = $awardColumn;
            $data_array['$awardValue']  = $awardValue;
            $data_array['$anz']         = $anz;
            $returnArray['html'] .= $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_row", $data_array);

        }

    } else if (isset($_GET['award_id'])) {

        $award_id = (isset($_GET['award_id']) && validate_int($_GET['award_id'], true)) ?
            (int)$_GET['award_id'] : 0;

        if ($award_id < 1) {
            throw new \Exception($_language->module['unknown_award']);
        }

        if (!checkIfContentExists($award_id, 'awardID', 'cups_awards')) {
            throw new \Exception($_language->module['unknown_award']);
        }
        
        if ($getAction == 'details') {

            $get = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT * FROM  `".PREFIX."cups_awards_category`
                        WHERE awardID = " . $award_id
                )
            );

            $subget = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT COUNT(*) AS anz FROM  `".PREFIX."cups_awards`
                        WHERE award = " . $award_id
                )
            );

            $anz = $subget['anz'];

            $awardColumn = $get['active_column'];
            $awardValue = $get[$awardColumn];

            $data_array = array();
            $data_array['$n']           = 1;
            $data_array['$award_id']    = $award_id;
            $data_array['$name']        = $get['name'];
            $data_array['$awardColumn'] = $awardColumn;
            $data_array['$awardValue']  = $awardValue;
            $data_array['$anz']         = $anz;
            $returnArray['html'] .= $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_row", $data_array);

            $query = mysqli_query(
                $_database,
                "SELECT * FROM  `".PREFIX."cups_awards`
                    WHERE award = '".$award_id."'
                    ORDER BY date DESC"
            );

            $anzAwards = mysqli_num_rows($query);
            if($anzAwards > 0) {

                $data_array = array();
                $returnArray['html'] .= $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_details_head", $data_array);

                $n = 1;
                while($get = mysqli_fetch_array($query)) {

                    $award_id = $get['awardID'];

                    $id = 0;
                    if($get['teamID'] > 0) {
                        $id = $get['teamID'];
                        $teamArray = getteam($id);
                        $detail_url = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;teamID='.$id;
                    } else if($get['userID'] > 0) {
                        $id = $get['userID'];
                        $detail_url = '';
                    }

                    if(($id > 0) && isset($teamArray) && is_array($teamArray)) {

                        $data_array = array();
                        $data_array['$n']           = $n++;
                        $data_array['$id']          = $id;
                        $data_array['$award_id']    = $award_id;
                        $data_array['$name']        = $teamArray['name'];
                        $data_array['$date']        = ($get['date'] > 0) ? getformatdatetime($get['date']) : '';
                        $data_array['$detail_url']  = $detail_url;
                        $returnArray['html'] .= $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_details_row", $data_array);

                    }

                }

            }

            $statsArray = getStatistic($awardColumn, ($awardValue - 3), $awardValue, 10, TRUE);

            $anzStats = count($statsArray);
            if(is_array($statsArray) && ($anzStats > 0)) {

                $data_array = array();
                $returnArray['html'] .= $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_stats_head", $data_array);

                for($x=0;$x<$anzStats;$x++) {

                    $id = $statsArray[$x]['id'];
                    $detail_url = 'admincenter.php?site=cup&amp;mod=teams&amp;action=active&amp;teamID='.$id;

                    $data_array = array();
                    $data_array['$n']           = ($x + 1);
                    $data_array['$id']          = $id;
                    $data_array['$award_id']    = 'new_'.$id;
                    $data_array['$name']        = $statsArray[$x]['name'];
                    $data_array['$date']        = $statsArray[$x]['count'];
                    $data_array['$detail_url']  = $detail_url;
                    $returnArray['html'] .= $GLOBALS["_template_cup"]->replaceTemplate("cup_awards_details_row", $data_array);

                }

            }

        } else {
            throw new \Exception($_language->module['unknown_action']);
        }

    } else {
        throw new \Exception($_language->module['unknown_action']);
    }
    
    $returnArray['status'] = TRUE;
    
} catch (Exception $e) {
    $returnArray['message'][] = $e->getMessage();
}

echo json_encode($returnArray);
