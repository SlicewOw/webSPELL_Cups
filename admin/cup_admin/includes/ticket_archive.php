<?php

$data_array = array();
$ticket_home_admin = $GLOBALS["_template_cup"]->replaceTemplate("ticket_archive_home", $data_array);
echo $ticket_home_admin;