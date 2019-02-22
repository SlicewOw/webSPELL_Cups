<?php

if (!isset($cupArray) || !validate_array($cupArray, true)) {

    $cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id == 0) {
        $cup_id = isset($_GET['cup_id']) ? (int)$_GET['cup_id'] : 0;
    }

    $cupArray = getcup($cupID, 'all');
}

if (($cup_id > 0) && !empty($cupArray)) {

    $match_class = 'cup_bracket_'.$cupArray['size'].'_match';

    if ($cupArray['size'] == 64) {
        $matchVars = array(
            'vars' => array(
                1 => 'matches_1_32',
                2 => 'matches_33_48',
                3 => 'matches_49_56',
                4 => 'matches_57_60',
                5 => 'matches_61_62',
                6 => 'match63'
            ),
            'matches_1_32' => '',
            'matches_33_48' => '',
            'matches_49_56' => '',
            'matches_57_60' => '',
            'matches_61_62' => '',
            'match63' => ''
        );
    } else if ($cupArray['size'] == 32) {
        $matchVars = array(
            'vars' => array(
                1 => 'matches_1_16',
                2 => 'matches_17_24',
                3 => 'matches_25_28',
                4 => 'matches_29_30',
                5 => 'match31'
            ),
            'matches_1_16' => '',
            'matches_17_24' => '',
            'matches_25_28' => '',
            'matches_29_30' => '',
            'match31' => ''
        );
    } else if ($cupArray['size'] == 16) {
        $matchVars = array(
            'vars' => array(
                1 => 'matches_1_8',
                2 => 'matches_9_12',
                3 => 'matches_13_14',
                4 => 'match15',
            ),
            'matches_1_8' => '',
            'matches_9_12' => '',
            'matches_13_14' => '',
            'match15' => ''
        );
    } else if ($cupArray['size'] == 8) {
        $matchVars = array(
            'vars' => array(
                1 => 'matches_1_4',
                2 => 'matches_5_6',
                3 => 'match7',
            ),
            'matches_1_4' => '',
            'matches_5_6' => '',
            'match7' => ''
        );
    } else if ($cupArray['size'] == 4) {
        $matchVars = array(
            'vars' => array(
                1 => 'matches_1_2',
                2 => 'match3',
            ),
            'matches_1_2' => '',
            'match3' => ''
        );
    } else {
        $matchVars = array(
            'vars' => array(
                1 => 'match1',
            ),
            'match1' => ''
        );
    }

    $prefixArray = array(
        0 => 'M32',
        1 => 'M16',
        2 => 'M8',
        3 => 'V',
        4 => 'H',
        5 => 'Finale'
    );

    $matchQuery = mysqli_query(
        $_database,
        "SELECT
                `matchID`
            FROM `" . PREFIX . "cups_matches_playoff`
            WHERE `cupID` = " . $cup_id . "
            ORDER BY `matchID` ASC, `wb` DESC"
    );

    $i=1;
    while ($db = mysqli_fetch_array($matchQuery)) {

        //
        // Match Infos
        $match_id = $db['matchID'];
        $matchArray = getmatch($match_id);

        $url = ($matchArray['active'] == 1) ?
            'index.php?site=cup&amp;action=match&amp;id=' . $cup_id . '&amp;mID=' . $match_id:
            'javascript:void(0);';

        $break = '';
        $prefix_id = '';

        if ($cupArray['size'] == 64) {
            if ($i < 33) {
                if ($i < 32) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_32"></div>';
                }
                $prefix_id = 0;
            } else if ($i < 49) {
                if ($i < 48) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_16"></div>';
                }
                $prefix_id = 1;
            } else if ($i < 57) {
                if ($i < 56) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_8"></div>';
                }
                $prefix_id = 2;
            } else if ($i < 61) {
                if ($i < 60) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_4"></div>';
                }
                $prefix_id = 3;
            } else if ($i < 63) {
                if ($i < 62) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_2"></div>';
                }
                $prefix_id = 4;
            }
        } else if ($cupArray['size'] == 32) {
            if ($i < 17) {
                if ($i < 16) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_16"></div>';
                }
                $prefix_id = 1;
            } else if (($i > 16) && ($i < 25)) {
                if (($i > 16) && ($i < 24)) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_8"></div>';
                }
                $prefix_id = 2;
            } else if (($i > 24) && ($i < 29)) {
                if (($i > 24) && ($i < 28)) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_4"></div>';
                }
                $prefix_id = 3;
            } else if (($i > 28) && ($i < 31)) {
                if (($i > 28) && ($i < 30)) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_2"></div>';
                }
                $prefix_id = 4;
            } else if ($i == 31) {
                $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_8"></div>';
            }
        } else if ($cupArray['size'] == 16) {
            if ($i < 9) {
                if ($i < 8) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_8"></div>';
                }
                $prefix_id = 2;
            } else if ($i < 13) {
                if($i < 12) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_4"></div>';
                }
                $prefix_id = 3;
            } else if ($i < 15) {
                if($i < 14) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_2"></div>';
                }
                $prefix_id = 4;
            }
        } else if ($cupArray['size'] == 8) {
            if ($i < 5) {
                if ($i < 4) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_4"></div>';
                }
                $prefix_id = 3;
            } else if ($i < 7) {
                if (($i > 4) && ($i < 6)) {
                    $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_2"></div>';
                }
                $prefix_id = 4;
            } else if ($i == 7) {
                $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_4"></div>';
            }
        } else if ($cupArray['size'] == 4) {
            if ($i < 2) {
                $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_2"></div>';
                $prefix_id = 4;
            } else if ($i == 3) {
                $break = '<div class="cup_bracket_' . $cupArray['size'] . '_break_sup3"></div>';
            }
        }

        //
        // Spiel ID
        if (empty($prefix_id)) {
            $prefix_id = 5;
            if ($matchArray['bracket'] == 1) {
                //
                // WB Finale
                $game_id = $prefixArray[$prefix_id];
            } else {
                //
                // LB Finale
                $game_id = 'SuP 3';
            }
        } else {
            $game_id = ($prefix_id < 3) ?
                    $prefixArray[$prefix_id].'-'.$matchArray['spiel'] :
                    $prefixArray[$prefix_id].$matchArray['spiel'];
        }

        //
        // Team Name
        for ($x = 1; $x < 3; $x++) {

            $teamValue = 'team' . $x;
            $team_id_column = $teamValue . '_id';

            $spanClass = ($x == 1) ?
                'cup_bracket_match_team_home' :
                'cup_bracket_match_team_oppo right';

            if ($matchArray[$teamValue.'_id'] > 0) {

                if ($cupArray['mode'] == '1on1') {

                    $teamName = getnickname($matchArray[$team_id_column]);

                } else {

                    $team_array = getteam($matchArray[$team_id_column]);
                    if (strlen($team_array['name']) < 15) {
                        $teamName = $team_array['name'];
                    } else {
                        $teamName = $team_array['tag'];
                    }

                }

                $teamInfoArray[$x] = '<span class="' . $spanClass . ' lh_twenty">' . $teamName . '</span>';

            } else if ($matchArray[$teamValue.'_freilos'] == 1) {
                $teamInfoArray[$x] = '<span class="' . $spanClass . ' lh_twenty grey italic">' . $_language->module['cup_freilos'] . '</span>';
            } else {

                $matchCounter = (($matchArray['spiel'] * 2) + $x) - 2;

                $winnerPreviousMatch = ($matchArray['bracket']) ?
                    $_language->module['cup_winner'] :
                    $_language->module['cup_loser'];

                if (($prefix_id - 1) < 3) {
                    $winnerPreviousMatch .= ' ' . $prefixArray[$prefix_id - 1] . '-' . $matchCounter;
                } else {
                    $winnerPreviousMatch .= ' ' . $prefixArray[$prefix_id - 1] . $matchCounter;
                }

                $teamInfoArray[$x] = '<span class="' . $spanClass . ' lh_twenty">' . $winnerPreviousMatch . '</span>';

            }

        }

        $score1_class = '';
        $score2_class = '';

        if ($matchArray['ergebnis1'] < $matchArray['ergebnis2']) {
            $score1_class = ' lose darkshadow';
            $score2_class = ' win darkshadow';
        } else if ($matchArray['ergebnis1'] > $matchArray['ergebnis2']) {
            $score1_class = ' win darkshadow';
            $score2_class = ' lose darkshadow';
        }

        $score1 = '<span class="cup_bracket_match_score_home lh_twenty center ' . $score1_class . '">' . $matchArray['ergebnis1'] . '</span>';
        $score2 = '<span class="cup_bracket_match_score_oppo lh_twenty center ' . $score2_class . '">' . $matchArray['ergebnis2'] . '</span>';

        if (isset($matchVars['vars'][$matchArray['runde']])) {

            $matchRow = $matchVars['vars'][$matchArray['runde']];

            $data_array = array();
            $data_array['$match_class'] = $match_class;
            $data_array['$url'] = $url;
            $data_array['$team1'] = $teamInfoArray[1];
            $data_array['$score1'] = $score1;
            $data_array['$id'] = $game_id;
            $data_array['$team2'] = $teamInfoArray[2];
            $data_array['$score2'] = $score2;
            $data_array['$break'] = $break;
            $matchVars[$matchRow] .= $GLOBALS["_template_cup"]->replaceTemplate("cup_bracket_match", $data_array);

        }

        unset($teamInfoArray);
        $i++;

    }

    $data_array = array();
    for ($x = 1; $x < ($cupArray['anz_runden'] + 1); $x++) {
        $matchRow = $matchVars['vars'][$x];
        $data_array['$' . $matchRow] = $matchVars[$matchRow];
    }
    $bracket = $GLOBALS["_template_cup"]->replaceTemplate("cup_bracket_" . $cupArray['size'], $data_array);

} else {
    $bracket = $_language->module['no_cup'];
}
