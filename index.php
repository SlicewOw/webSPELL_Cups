<?php

include("_mysql.php");
include("_settings.php");
include("_functions.php");

include("_plugin.php"); #Plugin-Manager

$getSite = (isset($_GET['site'])) ?
    getinput($_GET['site']) : 'home';

$getAction = (isset($_GET['action'])) ?
    getinput($_GET['action']) : '';

$dir_main = __DIR__ . '/';
$dir_cup = __DIR__ . '/cup/php/';

$image_url = './images/';
$admin_url = 'admin/admincenter.php';
$cup_url = $hp_url;

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

    <link href="./css/flipclock.css" rel="stylesheet">
    <link href="./css/font.css" rel="stylesheet">
    <link href="./css/layout.css" rel="stylesheet">
    <link href="./css/pages.css" rel="stylesheet">
    <?php
    foreach ($components['js'] as $component) {
        echo '<script src="./' . $component . '"></script>';
    }
    ?>
    <script src="./js/bbcode.js"></script>
    <script src="./js/pnotify.custom.min.js"></script>
    <script src="./js/jquery.countdown.js"></script>
    <script src="./js/flipclock.min.js"></script>
    <script src="./js/cup_functions.js"></script>

    <title><?php echo PAGETITLE; ?></title>

</head>

<body>
<?php

if (iscupadmin($userID) && $loggedin && ($getSite == 'cup_admin') && ($getPage == 'bracket')) {
    include($dir_cup . 'admin/cup_matches_admin.php');
}

include($dir_cup . '/navigation.php');

if (($getSite == 'cup') && ($getAction == 'details') && isset($_GET['id'])) {

    $cup_id = (validate_int($_GET['id'], true)) ?
        (int)$_GET['id'] : 0;

    if ($cup_id > 0) {
        $cupArray = getcup($cup_id);
    }

}

?>
<div id="page">
    <div id="header">
        <h1>webSPELL Cup Add-On by SlicewOw</h1>
    </div>
    <div class="linie_1"></div>
    <div id="wrapper">
        <div id="content_wrapper">
            <div id="content_top">
                <div id="content_top_where" class="eleven lh_fourty href_underline">
<?php include($dir_cup . '/where.php'); ?>
                </div>
                <div id="content_top_login" class="eleven lh_fourty right href_underline">
<?php include($dir_cup . '/content_login.php'); ?>
                </div>
                <div class="clear"></div>
            </div>
            <div id="content">
<?php include(__DIR__ . '/content.php'); ?>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>
<div class="linie_2"></div>
<div id="footer_navigation_body">
    <div id="footer_navigation">
        <?php include('./sc_sponsors.php'); ?>
    </div>
</div>
<div class="linie_3"></div>
<div id="copyright" class="white ten lh_fourty darkshadow center">
    <?php include($dir_cup . '/copyright.php'); ?>
</div>
<?php
$date = '';
try {

    if ($getSite == 'cup') {

        $cup_id = getParentIdByValue('id', true);

        if ($cup_id > 0) {

            $where_clause = (iscupadmin($userID)) ?
                ' AND admin_visible = \'0\'' : '';

            if (!isset($cupArray) || !validate_array($cupArray, true)) {
                $cupArray = getcup($cup_id, 'all');
            }

            $cupstatus = $cupArray['status'];
            $time_now = time();

            if (($cupArray['phase'] == 'admin_register') || ($cupArray['phase'] == 'register')) {
                $date = date('Y/m/d H:i:s', $cupArray['checkin']);
            } else if (($cupArray['phase'] == 'admin_checkin') || ($cupArray['phase'] == 'checkin')) {
                $date = date('Y/m/d H:i:s', $cupArray['start']);
            }

        }

    } else if (empty($getSite) || ($getSite == 'home')) {

        $timeNow = time();

        $whereClauseArray = array();
        $whereClauseArray[] = '`start_date` >= ' . $timeNow;

        if (!iscupadmin($userID)) {
            $whereClauseArray[] = '`admin_visible` = 0';
        }

        $whereClause = implode(' AND ', $whereClauseArray);

        $selectQuery = mysqli_query(
            $_database,
            "SELECT
                    `cupID`,
                    `checkin_date`,
                    `start_date`
                FROM `" . PREFIX . "cups`
                WHERE " . $whereClause . "
                ORDER BY `start_date` ASC
                LIMIT 0, 1"
        );

        if (!$selectQuery) {
            throw new \Exception('query_select_failed');
        }

        if (mysqli_num_rows($selectQuery) > 0) {

            $ds = mysqli_fetch_array($selectQuery);

            if ($timeNow <= $ds['checkin_date']) {
                $date = date('Y/m/d H:i:s', $ds['checkin_date']);
            } else {
                $date = date('Y/m/d H:i:s', $ds['start_date']);
            }

        }

    }

} catch (Exception $e) {}
?>
<script src="./cup/js/cup_details_menu.js"></script>
<script src="./cup/js/support_menu.js"></script>
<?php
if (!empty($date)) {
?>
<script type="text/javascript">
$("#cup_details_countdown").countdown("<?php echo $date; ?>", function (event) {

    var format = '%H:%M:%S';

    if (event.offset.days > 0) {
        if (event.offset.days > 1) {
            format = '%-d <?php echo 'Tage'; ?> ' + format;
        } else {
            format = '%-d <?php echo 'Tag'; ?> ' + format;
        }
    }

    if (event.offset.weeks > 0) {
        if (event.offset.weeks > 1) {
            format = '%-w <?php echo 'Wochen'; ?> ' + format;
        } else {
            format = '%-w <?php echo 'Woche'; ?> ' + format;
        }
    }

    $(this).html(event.strftime(format));

});
</script>
<?php
}
?>
</body>
</html>
