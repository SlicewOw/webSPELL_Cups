<?php

if (isset($_SESSION['errorArray']) && is_array($_SESSION['errorArray'])) {

    $errorArray = $_SESSION['errorArray'];
    if (validate_array($errorArray, true)) {

        foreach ($errorArray as $message) {
            echo '<input type="hidden" name="errorMessage[]" class="errorNotification" value="' . $message . '" />';
        }

    }

    unset($_SESSION['errorArray']);

}

if (isset($_SESSION['successArray']) && is_array($_SESSION['successArray'])) {

    $successArray = $_SESSION['successArray'];
    if (validate_array($successArray, true)) {

        foreach ($successArray as $message) {
            echo '<input type="hidden" name="successMessage[]" class="successNotification" value="' . $message . '" />';
        }

    }

    unset($_SESSION['successArray']);

}
