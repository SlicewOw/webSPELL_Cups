<?php

/**
 * General
 */
function getImageType($file) {

    if (empty($file)) {
        return FALSE;
    }

    if (is_string($file)) {

        $fileArray = explode('.', $file);
        $anz = count($fileArray);
        $fileType = $fileArray[$anz-1];

    } else if (validate_array($file, true)) {

        if (!isset($file['type'])) {
            return FALSE;
        }

        $image_type = $file['type'];
        if ($image_type == 'image/png') {
            $fileType = 'png';
        } else if ($image_type == 'image/gif') {
            $fileType = 'png';
        } else if ($image_type == 'image/jpg') {
            $fileType = 'jpg';
        } else {
            return FALSE;
        }

    } else {
        return FALSE;
    }

    return $fileType;

}

function deleteImageFromDatabase($table_name, $column_banner_name, $column_active_name, $primary_key_name, $primary_key_id) {

    if (empty($table_name)) {
        return;
    }

    if (empty($column_banner_name)) {
        return;
    }

    if (empty($primary_key_name)) {
        return;
    }

    if (!validate_int($primary_key_id, true)) {
        return;
    }

    $setValueArray = array();
    $setValueArray[] = '`' . $column_banner_name . '` = \'\'';

    if (!empty($column_active_name)) {
        $setValueArray[] = '`' . $column_active_name . '` = 0';
    }

    $setValue = implode(', ', $setValueArray);

    global $_database, $system_user;

    $updateQuery = mysqli_query(
        $_database,
        "UPDATE `" . PREFIX . $table_name . "` 
            SET " . $setValue . "
            WHERE `" . $primary_key_name . "` = " . $primary_key_id
    );

    if (!$updateQuery) {
        return false;
    }

    return true;

}

/**
 * Sponsor
 */
function getSponsorImage($sponsor_id, $returnAsFullImageLink = TRUE, $imageSize = 'tall') {

    $default_image = '';

    if (!validate_int($sponsor_id)) {
        return $default_image;
    }

    global $_database, $image_url;

    $query = mysqli_query(
        $_database,
        "SELECT
                `banner`,
                `banner_small`
            FROM `" . PREFIX . "sponsors`
            WHERE `sponsorID` = " . $sponsor_id
    );

    if (!$query) {
        return $default_image;
    }

    $get = mysqli_fetch_array($query);

    $selectBanner = ($imageSize == 'tall') ?
        'banner' : 'banner_small';

    if (empty($get[ $selectBanner ])) {
        return $default_image;
    }

    $imagePath = '/sponsors/' . $get[ $selectBanner ];
    $filePath = __DIR__ . '/../../images' . $imagePath;

    if (!file_exists($filePath)) {
        deleteImageFromDatabase('sponsors', $selectBanner, 'displayed', 'sponsorID', $sponsor_id);
        return $default_image;
    }

    if ($returnAsFullImageLink) {
        return $image_url . $imagePath;
    } else {
        return $get[ $selectBanner ];
    }

}

/**
 * User
 */
function getUserImage($category, $user_id, $returnCompleteImagePath = FALSE) {

    if (empty($category)) {
        return '';
    }

    $allowedUserImagesArray = array(
        'avatar',
        'userpic'
    );

    if (!in_array($category, $allowedUserImagesArray)) {
        return '';
    }

    global $image_url;

    $base_url = $image_url . $category . 's/';

    $errorPictureByCategory = 'no' . $category . '.gif';

    $unknown_pic = ($returnCompleteImagePath) ?
        $base_url . $errorPictureByCategory : $errorPictureByCategory;

    if (!validate_int($user_id, true)) {
        return $unknown_pic;
    }

    global $_database;

    $selectQuery = mysqli_query(
        $_database,
        "SELECT
                `" . $category . "`
            FROM `" . PREFIX . "user`
            WHERE `userID` = " . (int)$user_id
    );

    if (!$selectQuery) {
        return $unknown_pic;
    }

    $ds = mysqli_fetch_array($selectQuery);

    if (empty($ds[$category])) {
        return $unknown_pic;
    }

    $picture = $ds[$category];

    $absoluteFilePath = __DIR__ . '/../../images/' . $category . 's/' . $picture;
    if (!file_exists($absoluteFilePath)) {

        $updateQuery = mysqli_query(
            $_database,
            "UPDATE `" . PREFIX . "user`
                SET `" . $category . "` = ''
                WHERE `userID` = " . (int)$user_id
        );

        if (!$updateQuery) {
            return $unknown_pic;
        }

        return $unknown_pic;

    }

    return ($returnCompleteImagePath) ?
        $base_url . $picture : $picture;

}

function getuserpic($user_id, $returnCompleteImagePath = FALSE) {
    return getUserImage('userpic', $user_id, $returnCompleteImagePath);
}

function getavatar($user_id, $returnCompleteImagePath = FALSE) {
    return getUserImage('avatar', $user_id, $returnCompleteImagePath);
}

/**
 * Game
 */
function getGameIcon($game_tag, $returnAsFullImageLink = TRUE) {

    $default_image = '';

    if (empty($game_tag)) {
        return $default_image;
    }

    $iconExtensionsAllowed = array(
        'gif',
        'png'
    );

    foreach ($iconExtensionsAllowed as $icon_extension) {

        $imageName = $game_tag . '.' . $icon_extension;
        $imagePath = '/games/' . $imageName;
        $filePath = __DIR__ . '/../../images' . $imagePath;

        if (file_exists($filePath)) {
            break;
        }

    }

    if (!file_exists($filePath)) {
        return $default_image;
    }

    global $image_url;

    if ($returnAsFullImageLink) {
        return $image_url . $imagePath;
    } else {
        return $imageName;
    }

}

/**
 * Cup Images
 */
function getCupTeamImage($team_id, $returnAsFullImageLink = TRUE) {

    global $_database, $image_url;

    $default_image = $image_url . '/cup/teams/team_nologotype.png';

    if (!validate_int($team_id, true)) {
        return $default_image;
    }

    $query = mysqli_query(
        $_database,
        "SELECT
                `logotype`
            FROM `" . PREFIX . "cups_teams`
            WHERE `teamID` = " . $team_id
    );

    if (!$query) {
        return $default_image;
    }

    $get = mysqli_fetch_array($query);

    if (empty($get[ 'logotype' ])) {
        return $default_image;
    }

    $imagePath = '/cup/teams/' . $get[ 'logotype' ];
    $filePath = __DIR__ . '/../../images' . $imagePath;

    if (!file_exists($filePath)) {
        deleteImageFromDatabase('cups_teams', 'logotype', '', 'teamID', $team_id);
        return $default_image;
    }

    if ($returnAsFullImageLink) {
        return $image_url . $imagePath;
    } else {
        return $get[ 'logotype' ];
    }

}

function getCupIcon($cup_id, $returnAsFullImageLink = TRUE) {

    $default_image = '';

    if (!validate_int($cup_id, true)) {
        return $default_image;
    }

    $iconExtensionsAllowed = array(
        'gif',
        'png',
        'jpg'
    );

    foreach ($iconExtensionsAllowed as $icon_extension) {

        $imageName = $cup_id . '.' . $icon_extension;
        $imagePath = '/cup/icons/' . $imageName;
        $filePath = __DIR__ . '/../../images' . $imagePath;

        if (file_exists($filePath)) {
            break;
        }

    }

    if (!file_exists($filePath)) {
        return $default_image;
    }

    global $image_url;

    if ($returnAsFullImageLink) {
        return $image_url . $imagePath;
    } else {
        return $imageName;
    }

}

function getCupBanner($cup_id, $returnAsFullImageLink = TRUE) {

    $default_image = '';

    if (!validate_int($cup_id, true)) {
        return $default_image;
    }

    $iconExtensionsAllowed = array(
        'gif',
        'png',
        'jpg'
    );

    foreach ($iconExtensionsAllowed as $icon_extension) {

        $imageName = $cup_id . '.' . $icon_extension;
        $imagePath = '/cup/banner/' . $imageName;
        $filePath = __DIR__ . '/../../images' . $imagePath;

        if (file_exists($filePath)) {
            break;
        }

    }

    if (!file_exists($filePath)) {
        return $default_image;
    }

    global $image_url;

    if ($returnAsFullImageLink) {
        return $image_url . $imagePath;
    } else {
        return $imageName;
    }

}

/**
 * Country
 */
function getCountryImage($country_short_name, $returnAsFullImageLink = TRUE) {

    $default_image = 'eu';

    if (empty($country_short_name) || (strlen($country_short_name) > 3)) {
        $country_short_name = $default_image;
    }

    $imagePath = '/flags/' . $country_short_name . '.gif';
    $filePath = __DIR__ . '/../../images' . $imagePath;

    if (!file_exists($filePath)) {
        $imagePath = '/flags/' . $default_image . '.gif';
        $filePath = __DIR__ . '/../../images' . $imagePath;
    }

    global $image_url;

    if ($returnAsFullImageLink) {
        return $image_url . $imagePath;
    } else {
        return $country_short_name . '.gif';
    }

}
