<?php

function validate_int($value, $checkIfLessOne = true) {

    //
    // Kontrolliere Value, ob es Integer ist

    if (is_null($value) || !is_numeric($value)) {
        return FALSE;
    }

    if (!$checkIfLessOne) {
        return TRUE;
    }

    if ($value < 1) {
        return FALSE;
    }

    return TRUE;

}

function validate_array($array, $checkForEmpty = TRUE) {

    if (is_null($array) || !is_array($array)) {
        return FALSE;
    }

    if ($checkForEmpty) {

        if(count($array) < 1) {
            return FALSE;
        }

    }

    return TRUE;

}
