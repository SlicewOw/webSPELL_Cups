<?php

try {

    $filepath = '../images/cup/banner/';

    if (validate_array($_POST, true)) {
           
        $_language->readModule('formvalidation', true);
        
        $parent_url = 'admincenter.php?site=cup&mod=cup&action=cup&id=' . $cup_id . '&page=images';
        
        try {
            
            if (isset($_POST['submitCupImages'])) {
                
                $allowedCupImagesArray = array(
                    'cup_icon',
                    'cup_banner'
                );
                
                $cupSetImagesArray = array();

                $errors = array();

                foreach ($allowedCupImagesArray as $image_key) {
                    
                    if (isset($_FILES[$image_key])) {

                        $upload = new \webspell\HttpUpload($image_key);
                        if ($upload->hasFile()) {

                            try {

                                if ($upload->hasError() !== false) {
                                    throw new \Exception($upload->translateError());
                                }

                                $mime_types = array(
                                    'image/jpg',
                                    'image/gif',
                                    'image/png'
                                );

                                if (!$upload->supportedMimeType($mime_types)) {
                                    throw new \Exception($_language->module['unsupported_image_type']);
                                }

                                $imageInformation = getimagesize($upload->getTempFile());

                                if (!is_array($imageInformation)) {
                                    throw new \Exception($_language->module['broken_image']);
                                }

                                $filename = $cup_id . '_' . $image_key . '.' . $upload->getExtension();

                                if (!$upload->saveAs($filepath . $filename, true)) {
                                    throw new \Exception($_language->module['upload_failed']);
                                }

                                $cupSetImagesArray[] = '`' . $image_key . '` = \'' . $filename . '\'';

                                @chmod($filepath . $filename, 644);

                            } catch (Exception $e) {
                                $errors[] = $e->getMessage() . ' (' . $image_key . ')';
                            }

                        }
                        
                    }
                    
                }

                if (validate_array($errors, true)) {
                    $_SESSION['errorArray'] = $errors;
                }
                
                if (validate_array($cupSetImagesArray, true)) {
                    
                    $updateQuery = cup_query(
                        "UPDATE `" . PREFIX . "cups`
                            SET " . implode(', ', $cupSetImagesArray) . "
                            WHERE `cupID` = " . $cup_id,
                        __FILE__
                    );
                    
                }
                
            } else if (isset($_POST['deleteCupImage_icon']) || isset($_POST['deleteCupImage_banner'])) {
                
                $image_key = (isset($_POST['deleteCupImage_icon'])) ?
                    'cup_icon' : 'cup_banner';
                
                $image = getCupImage($cup_id, $image_key, false);

                $imagePath = __DIR__ . '/../../../images/cup/banner/' . $image;
                if (!empty($image) && file_exists($imagePath)) {

                    if (unlink($imagePath)) {
                        $_SESSION['successArray'][] = $_language->module['delete_ok'];
                    } else {
                        $_SESSION['errorArray'][] = $_language->module['delete_failed'];
                    }

                }
                
                $updateQuery = cup_query(
                    "UPDATE `" . PREFIX . "cups`
                        SET `" . $image_key . "` = NULL
                        WHERE `cupID` = " . $cup_id,
                    __FILE__
                );
                
            } else {
                throw new \Exception($_languages->module['unknown_action']);
            }
            
        } catch (Exception $e) {
            $_SESSION['errorArray'][] = $e->getMessage();
        }
        
        header('Location: ' . $parent_url);
        
    } else {

        if (!isset($cupArray) || !validate_array($cupArray, true)) {
            $cupArray = getcup($cup_id);
        }
        
        $cupImageArray = array(
            'icon',
            'banner'
        );
        
        $imageArray = array();

        foreach ($cupImageArray as $image_key) {
        
            $image_value = (!empty($cupArray['images'][$image_key])) ?
                '<img src="' . $cupArray['images'][$image_key] . '" alt="" class="img-responsive" />' : '';
        
            if (!empty($image_value)) {
                
                $deleteButtonAttributeArray = array();
                $deleteButtonAttributeArray[] = 'type="submit"';
                $deleteButtonAttributeArray[] = 'name="deleteCupImage_' . $image_key . '"';
                $deleteButtonAttributeArray[] = 'class="btn btn-danger btn-xs white darkshadow"';
                
                $image_value .= '<br /><button ' . implode(' ', $deleteButtonAttributeArray) . '>' . $_language->module['delete'] . '</button>';
            }

            $imageArray[$image_key] = $image_value;

        }

        $data_array = array();
        $data_array['$cup_icon'] = $imageArray['icon'];
        $data_array['$cup_banner'] = $imageArray['banner'];
        $content = $GLOBALS["_template_cup"]->replaceTemplate("cups_details_images_admin", $data_array);

    }

} catch (Exception $e) {
    echo showError($e->getMessage());
}