<?php

include("_mysql.php");
include("_settings.php");
include("_functions.php");

include("_plugin.php"); #Plugin-Manager

$getSite = (isset($_GET['site'])) ?
    getinput($_GET['site']) : 'home';

$getAction = (isset($_GET['action'])) ?
    getinput($_GET['action']) : '';

$dir_cup = __DIR__ . '/cup/php/';

$image_url = './images/';
$admin_url = 'admin/admincenter.php';

global $loggedin;
if (!$userID) {
    $loggedin = FALSE;
} else {
    $loggedin = TRUE;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="index, follow, noarchive" />
    <meta name="description" content="webSPELL Cup Addon - scripted by SlicewOw myRisk Gaming e.V." />
    <meta name="author" content="myRisk Gaming e.V." />
    <meta name="keywords" content="myrisk, myrisk gaming, myrisktv, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, esl, esea, 4pl, matcharena, nga-l, webspell, 4.2.3a, support, gfx, freetemplates, cms, download, config, configmaker, de, com, cze" />

    <?php
    foreach ($components['css'] as $component) {
        echo '<link href="./' . $component . '" rel="stylesheet">';
    }
    ?>
    <link href="./css/page.css" rel="stylesheet">
    <link href="./css/scrolling-nav.css" rel="stylesheet">
    <link href="./css/styles.css.php" rel="styleSheet" type="text/css">
    <link href="./css/button.css.php" rel="styleSheet" type="text/css">
    <link href="./_stylesheet.css" rel="stylesheet">
    <link href="./cup/dist/css/styles.min.css" rel="stylesheet">

    <?php
    foreach ($components['js'] as $component) {
        echo '<script src="./' . $component . '"></script>';
    }
    ?>
    <script src="./js/bbcode.js"></script>
    <script src="./cup/dist/js/scripts.min.js"></script>

    <title><?php echo PAGETITLE; ?></title>

</head>

<body>
<?php include($dir_cup . '/header.php'); ?>
<div id="page">
    <div id="header">
        <h1>webSPELL Cup Add-On by SlicewOw</h1>
    </div>
    <div class="linie_1"></div>
    <div id="wrapper">
        <div id="content_wrapper">
            <div id="content_top">
                <div class="row">
                    <div class="col-sm-7 eleven lh_fourty href_underline">
<?php include($dir_cup . '/where.php'); ?>
                    </div>
                    <div class="col-sm-5 eleven lh_fourty right href_underline">
<?php include($dir_cup . '/content_login.php'); ?>
                    </div>
                </div>
            </div>
            <div class="clearfix">
                <div id="content">
<?php include(__DIR__ . '/content.php'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="linie_2"></div>
<div id="footer_navigation_body">
    <div id="footer_navigation">
        <?php include('./sc_sponsors.php'); ?>
    </div>
</div>
<?php include($dir_cup . '/copyright.php'); ?>
<script src="./cup/js/cup_details_menu.js"></script>
<script src="./cup/js/support_menu.js"></script>
<?php include($dir_cup . '/footer.php'); ?>
</body>
</html>
