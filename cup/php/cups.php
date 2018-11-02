<?php

$redirect_url = 'index.php?site=cup';

$cup_id = (isset($_GET['id']) && validate_int($_GET['id'], true)) ?
	(int)$_GET['id'] : 0;

if ($cup_id > 0) {
	$redirect_url .= '&action=details&id=' . $cup_id;
} else {
	
	$cup_id = (isset($_GET['cupID']) && validate_int($_GET['cupID'], true)) ?
		(int)$_GET['cupID'] : 0;

	if ($cup_id > 0) {
		$redirect_url .= '&action=details&id=' . $cup_id;
	}
	
}

header('Location: ' . $redirect_url);
