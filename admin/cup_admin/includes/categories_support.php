<?php

if($getAction == 'show') {

	if(isset($_POST['submit_support'])) {
			
		if(isset($_POST['name_de']) && isset($_POST['name_uk'])) {
			
			$name_de = getinput($_POST['name_de']);
			$name_uk = getinput($_POST['name_uk']);
				
			$insertQuery = mysqli_query(
			    $_database,
                "INSERT INTO ".PREFIX."cups_supporttickets_category 
                    (
                        `name_de`, 
                        `name_uk`
                    ) 
                    VALUES 
                    (
                        '".$name_de."', 
                        '".$name_uk."'
                    )"
            );

            if($insertQuery) {
                $categoryID = mysqli_insert_id($_database);
            }

		}

		header('Location: admincenter.php?site=cup&mod=categories&action=show&category=support');
			
	} else {
		
		$info = mysqli_query(
			$_database,
			"SELECT categoryID, name_de, name_uk FROM `".PREFIX."cups_supporttickets_category` 
				ORDER BY name_de ASC"
		);
		if(mysqli_num_rows($info) > 0) {

            $content = '';

			while($db = mysqli_fetch_array($info)) {

                $data_array = array();
                $data_array['$category_id'] = $db['categoryID'];
                $data_array['$name_de']     = $db['name_de'];
                $data_array['$name_uk']     = $db['name_uk'];
                $content .= $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_support_list", $data_array);

			}

            $data_array = array();
            $data_array['$content'] = $content;
            $temps = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_support_home", $data_array);
            echo $temps;

		}

        $data_array = array();
        $data_array['$title'] = $_language->module[ 'add_category_support' ];
        $data_array['$cat_id'] 	= 0;
        $data_array['$name_de'] = '';
        $data_array['$name_uk'] = '';
        $support_add = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_support_action", $data_array);
        echo $support_add;

	}
	
} else if($getAction == 'edit') {

	if(isset($_GET['id'])) {

        $category_id = (is_numeric($_GET['id'])) ? (int)$_GET['id'] : 0;

		if(isset($_POST['submit_support'])) {
		
			if(isset($_POST['name_de']) && isset($_POST['name_uk'])) {

                $name_de = getinput($_POST['name_de']);
                $name_uk = getinput($_POST['name_uk']);

                $updateQuery = mysqli_query(
                    $_database,
                    "UPDATE ".PREFIX."cups_supporttickets_category 
                        SET	name_de = '".$name_de."',
							name_uk = '".$name_uk."' 
						WHERE categoryID = " . $category_id
                );

			}

            header('Location: admincenter.php?site=cup&mod=categories&action=show&category=support');

		} else {

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT categoryID, name_de, name_uk FROM `".PREFIX."cups_supporttickets_category` 
                        WHERE categoryID = " . $category_id
                )
            );

            $data_array = array();
            $data_array['$title'] = $_language->module[ 'edit_category_support' ];
            $data_array['$cat_id'] 	= $category_id;
            $data_array['$name_de'] = $ds['name_de'];
            $data_array['$name_uk'] = $ds['name_uk'];
            $support_edit = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_support_action", $data_array);
            echo $support_edit;

        }

	} else {
        header('Location: admincenter.php?site=cup&mod=categories&action=show&category=support');
    }
	
} else if($getAction == 'delete') {

	if(isset($_GET['id'])) {

        $category_id = (is_numeric($_GET['id'])) ? (int)$_GET['id'] : 0;

		if(isset($_POST['submit_support'])) {

            $deleteQuery = mysqli_query(
                $_database,
                "DELETE FROM ".PREFIX."cups_supporttickets_category 
                    WHERE categoryID = " . $category_id
            );

            header('Location: admincenter.php?site=cup&mod=categories&action=show&category=support');

		} else {

            $ds = mysqli_fetch_array(
                mysqli_query(
                    $_database,
                    "SELECT categoryID, name_de, name_uk FROM `".PREFIX."cups_supporttickets_category` 
                        WHERE categoryID = " . $category_id
                )
            );

            $data_array = array();
            $data_array['$cat_id'] 	= $category_id;
            $data_array['$name_de'] = $ds['name_de'];
            $data_array['$name_uk'] = $ds['name_uk'];
            $support_edit = $GLOBALS["_template_cup"]->replaceTemplate("cup_categories_support_delete", $data_array);
            echo $support_edit;

        }

	}
	
}