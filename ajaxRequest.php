<?php

session_start();
include "function.php";
$link = connectDB();
$_GET = filterParameters($_GET);
$_POST = filterParameters($_POST);

if(isset($_GET['func'])) {
	if($_GET['func'] == 'selectVeranstaltung')		{ selectVeranstaltung($_GET['id']); }
	if($_GET['func'] == 'lockRace')           		{ lockRace($_GET['lid']); }
	if($_GET['func'] == 'addKlasse')           		{ addKlasse($_GET['id']); }
	if($_GET['func'] == 'deleteKlasse')           	{ deleteKlasse($_GET['id']); }
	if($_GET['func'] == 'deleteFullKlasse')			{ deleteFullKlasse(); }
	if($_GET['func'] == 'getVerein')				{ getVerein(); }
	if($_GET['func'] == 'getKlasse')				{ getKlasse($_GET['jg'], $_GET['sex'], $_GET['lid'], 1); }
	if($_GET['func'] == 'getLastRaceUpdate')		{ getLastRaceUpdate(); }
	if($_GET['func'] == 'showStartliste')			{ showStartliste(); }
	if($_GET['func'] == 'showErgebnisse')			{ showErgebnisse(); }
	if($_GET['func'] == 'showErgebnisseM')			{ showErgebnisseM(); }
	if($_GET['func'] == 'setNumOfResults')			{ setNumOfResults(); }
	if($_GET['func'] == 'doAuswertung')				{ doAuswertung(); }
	if($_GET['func'] == 'clearRaceId')				{ clearRaceId(); }
	if($_GET['func'] == 'showEinlaufListe')			{ showEinlaufListe(); }
	if($_GET['func'] == 'saveManZielzeit')			{ saveManZielzeit(); }
	if($_GET['func'] == 'showZielAnalyse')			{ showZielAnalyse(); }
	if($_GET['func'] == 'deleteManReaderTime')		{ deleteManReaderTime(); }
	if($_GET['func'] == 'specialReporting')         { specialReporting(); }
	if($_GET['func'] == 'chat')         			{ getChatMessage( $_GET['id']); }
	if($_GET['func'] == 'getHelpMessage')         	{ getHelpMessage( $_GET['id']); }
}


if(isset($_POST['form'])) {
	if($_POST['form'] == 'saveVeranstaltung')		{ saveVeranstaltung(); }
	if($_POST['form'] == 'saveRennen')				{ saveRennen(); }
	if($_POST['form'] == 'saveKlasse')				{ saveKlasse(); }
	if($_POST['form'] == 'saveTeilnehmer')			{ saveTeilnehmer(); }
	if($_POST['form'] == 'uploadTeilnehmer')		{ tImport(); }
	if($_POST['form'] == 'uploadZeit')				{ zImport(); }
	if($_POST['form'] == 'saveManReaderTime')		{ saveManReaderTime(); }
	if($_POST['form'] == 'getReaderData')			{ getReaderData(); }
	if($_POST['form'] == 'setConfig')				{ setConfig(); }
	if($_POST['form'] == 'showReaderResults')		{ showReaderResults(); }
	if($_POST['form'] == 'loadReaderResults')		{ loadReaderResults(); }	
	if($_POST['form'] == 'addChatMessage')		    { addChatMessage(); }
}


//phpinfo(32);


$link->close();
exit;

function getVerein() {
	//$link = connectDB();
	$term = htmlspecialchars($_GET['term'], ENT_QUOTES, 'UTF-8');
	$term = trim(strip_tags($_GET['term']));
	$a_json = array();
	
	$sql = "select distinct verein from teilnehmer where verein LIKE '$term%'";
	$result = dbRequest($sql, 'SELECT');
	
	if($result[1] > 0) {
		$i = 0;
		foreach ($result[0] as $row) {
			$verein = stripslashes($row['verein']);
			$a_json[$i] = $verein;
			$i++;
		}
	
		echo json_encode($a_json);
		flush();
	}
	//$link->close();
}

function getLastRaceUpdate() {
	$sql = "select aktualisierung from lauf where id = ".$_GET['id'];
	$result = dbRequest($sql, 'SELECT');
	
	if($result[1] > 0) {
		foreach ($result[0] as $row) {
			$a = stripslashes($row['aktualisierung']);
		}
	}
	echo $a;
}

function specialReporting() {
    include "specialReporting/s".$_SESSION['vSpecial'].".php";
    $call = "s".$_SESSION['vSpecial'];
    $call();
}

function setNumOfResults() {
	$_SESSION['anzUrkunden-'.$_GET['id']] = $_GET['num'];
	echo "ok";
}

function clearRaceId() {
	$_SESSION['rID'] = 0;
	echo "ok";
}
?>