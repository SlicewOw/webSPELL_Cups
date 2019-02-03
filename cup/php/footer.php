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

            if (preg_match('/register/', $cupArray['phase'])) {
                $date = date('Y/m/d H:i:s', $cupArray['checkin']);
            } else if (preg_match('/checkin/', $cupArray['phase'])) {
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

if (isset($date) && !empty($date)) {
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