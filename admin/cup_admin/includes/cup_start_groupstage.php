<?php

$cupID = (int)$_GET['id'];
$db = mysql_fetch_array(safe_query("SELECT * FROM ".PREFIX."cups WHERE cupID = '".$cupID."'"));

if( $db['max_size'] == 10 )			$gruppen_anz = '2';
elseif( $db['max_size'] == 20 )		$gruppen_anz = '4';
elseif( $db['max_size'] == 30 )		$gruppen_anz = '6';
	
for( $i=0; $i<$gruppen_anz; $i++ ) {

	if( $i == 0 )		$b = 'A';
	elseif( $i == 1 )	$b = 'B';
	elseif( $i == 2 )	$b = 'C';
	elseif( $i == 3 )	$b = 'D';
	elseif( $i == 4 )	$b = 'E';
	else				$b = 'F';
	
	$teams = '';
	$info = safe_query("SELECT * FROM ".PREFIX."cups_teilnehmer WHERE cupID = '".$_GET['id']."'");
	while( $da = mysql_fetch_array($info) ) {
	
		$teams .= '<option value="'.$da['teamID'].'">'.$da['name'].'</option>';
	
	}

	$teams .= '<option value="0" selected="selected">kein Team</option>';

	echo '<div class="cup_container">
	<div class="cup_container_1">Gruppe '.$b.'</div>
	<div class="cup_container_2">
		<select name="g'.$b.'t1">'.$teams.'</select>
		<select name="g'.$b.'t2">'.$teams.'</select>
		<select name="g'.$b.'t3">'.$teams.'</select>
		<select name="g'.$b.'t4">'.$teams.'</select>
		<select name="g'.$b.'t5">'.$teams.'</select>
	</div>
</div>';

}

echo '<div class="cup_main"><input name="gruppen" type="hidden" value="'.$gruppen_anz.'" /><input name="cupID" type="hidden" value="'.$_GET['id'].'" /><input type="submit" name="cup_start" value="Gruppen speichern und Cup starten" /></div></form>';

}
elseif( isset($_POST['cup_start']) ) {

safe_query("UPDATE ".PREFIX."cups SET status = '2' WHERE cupID = '".$_POST['cupID']."'");

for( $i=0; $i<$_POST['gruppen']; $i++ ) {
		
	if( $i == 0 ) {
	
		$team1 = $_POST['gAt1'];
		$team2 = $_POST['gAt2'];
		$team3 = $_POST['gAt3'];
		$team4 = $_POST['gAt4'];
		$team5 = $_POST['gAt5'];
	
	}
	elseif( $i == 1 ) {
	
		$team1 = $_POST['gBt1'];
		$team2 = $_POST['gBt2'];
		$team3 = $_POST['gBt3'];
		$team4 = $_POST['gBt4'];
		$team5 = $_POST['gBt5'];
	
	}
	elseif( $i == 2 ) {
	
		$team1 = $_POST['gCt1'];
		$team2 = $_POST['gCt2'];
		$team3 = $_POST['gCt3'];
		$team4 = $_POST['gCt4'];
		$team5 = $_POST['gCt5'];
	
	}
	elseif( $i == 3 ) {
	
		$team1 = $_POST['gDt1'];
		$team2 = $_POST['gDt2'];
		$team3 = $_POST['gDt3'];
		$team4 = $_POST['gDt4'];
		$team5 = $_POST['gDt5'];
	
	}
	elseif( $i == 4 ) {
	
		$team1 = $_POST['gEt1'];
		$team2 = $_POST['gEt2'];
		$team3 = $_POST['gEt3'];
		$team4 = $_POST['gEt4'];
		$team5 = $_POST['gEt5'];
	
	}
	else {
	
		$team1 = $_POST['gFt1'];
		$team2 = $_POST['gFt2'];
		$team3 = $_POST['gFt3'];
		$team4 = $_POST['gFt4'];
		$team5 = $_POST['gFt5'];
	
	}

	$id = $i+1;

	safe_query("INSERT INTO ".PREFIX."cups_gruppen (  gruppeID,                 cupID,        team1,        team2,        team3,        team4,        team5 ) 
									  VALUES ( '".$id."', '".$_POST['cupID']."', '".$team1."', '".$team2."', '".$team3."', '".$team4."', '".$team5."' )");
	
	// Match Termine 1								  
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '1', '".$team1."',       '0', '".$team2."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '1', '".$team3."',       '0', '".$team4."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '1', '".$team5."',       '0',          '0',       '0' )");									  
		
	// Match Termine 2								  
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '2', '".$team1."',       '0', '".$team5."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '2', '".$team2."',       '0', '".$team3."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '2', '".$team4."',       '0',          '0',       '0' )");									  
		
	// Match Termine 3								  
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '3', '".$team1."',       '0', '".$team4."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '3', '".$team2."',       '0', '".$team5."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '3', '".$team3."',       '0',          '0',       '0' )");									  
		
	// Match Termine 4								  
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '4', '".$team1."',       '0', '".$team3."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '4', '".$team4."',       '0', '".$team5."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '4', '".$team2."',       '0',          '0',       '0' )");									  
		
	// Match Termine 5								  
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '5', '".$team2."',       '0', '".$team4."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '5', '".$team3."',       '0', '".$team5."',       '0' )");
	safe_query("INSERT INTO ".PREFIX."cups_matches_gruppen (                 cupID,  gruppeID, runde,        team1, ergebnis1,        team2, ergebnis2 ) 
											  VALUES ( '".$_POST['cupID']."', '".$id."',   '5', '".$team1."',       '0',          '0',       '0' )");		
		
}
		
echo '<meta http-equiv="refresh" content="1; URL=admincenter.php?site=cup&action=cup&amp;id='.$_POST['cupID'].'">Die Teams wurden den Gruppen zugeordnet, der Cup wurde gestartet.<br />Du wirst automatisch weitergeleitet..';

?>