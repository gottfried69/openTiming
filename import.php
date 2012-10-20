<?php
/*
 * Created on 06.11.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

function import() {
	global $func;
	$html = '';

	if($func[1] == 'teilnehmer') {
		$html = tImport();
	} elseif ($func[1] == 'zeit') {
		$html = zImport();
	}
	return $html;
}

function tImport() {
	if(isset($_POST['submit'])) {

		$link = connectDB();
		$filename = uploadFile();
		$lines = parseFile($filename);
		//		echo "<pre>";
		//		print_r($lines);
		//		echo "</pre>";
		$html = tUpdateDB($lines);
		mysql_close($link);

	} else {
		$html = uploadForm();
	}
	return table("Teilnehmerliste importieren", $html);
}

function zImport() {
	if(isset($_POST['submit'])) {

		$link = connectDB();
		$filename = uploadFile();
		$lines = parseFile($filename);
		//		echo "<pre>";
		//		print_r($lines);
		//		echo "</pre>";
		$html = zUpdateDB($lines);
		mysql_close($link);

	} else {
		$html = uploadForm();
	}
	return table("Zeit importieren", $html);
}

function uploadFile() {
	$uploaddir = 'upload/';
	$uploadfile = $uploaddir.basename($_FILES['userfile']['name']);

	if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		echo "uploadError"; die;
	}
	return $uploadfile;
}

function parseFile($file) {
	$row = 0;
	$handle = fopen ("$file","r");
	while ( ($data = fgetcsv ($handle, 10000, ";", "\"")) !== FALSE ) {
		$num = count($data);
		for ($c=0; $c < $num; $c++) {
			if ($row != 0) {					// erste Zeile enthält die Ueberschriften
				$lines[$row][$c] = htmlspecialchars($data[$c], ENT_QUOTES, 'UTF-8');
			}
		}
		$row++;
	}
	fclose ($handle);
	return $lines;
}

function tUpdateDB($lines) {

	$i = 1;
	$didIt = 0;
	$errMsg = "";
	
	foreach($lines as $line) {
		
		# wenn kein Nachname vorhanden, dann wird nicht importiert
		if ($line[3] != "") {
			if(!isset($line[8])) { $line[8] = ""; }	
		
			$sql = "select ID from teilnehmer where vID = $line[0] and lID = $line[1] and stnr = $line[2] and del = 0";
			$res = mysql_query($sql);
	
			if (!$res) { die('Invalid query: ' . mysql_error()); }
			if ($res) {
				$line[5] = strtoupper($line[5]);
				$go = 0;
				$num = mysql_num_rows($res);
				if($num != 0) {
					while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
						$tID = $row['ID'];
					}
				}
	
				$klasse = getKlasse($line[6], $line[5], $line[1], 0);
					
				if(isset($_POST['update']) == 1 && $_POST['update'] == 1 && $num != 0) {
					$sql1 = "update teilnehmer set " .
					"vID = $line[0], lID = $line[1], stnr = $line[2], nachname = '".$line[3]."', vorname = '".$line[4]."', " .
					"geschlecht = '$line[5]', jahrgang = $line[6], verein = '".$line[7]."', att = '$line[8]', klasse = '$klasse[0]', vklasse = '$klasse[1]' " .
					"where ID = $tID";
					$go = 1;
				}
				if($num == 0) {
					$sql1 = "insert into teilnehmer " .
					"(vID, lID, stnr, nachname, vorname, geschlecht, jahrgang, verein, att, klasse, vklasse) " .
					"values ( $line[0], $line[1], $line[2], '".$line[3]."', '".$line[4]."', '$line[5]', '$line[6]', '".$line[7]."', '".$line[8]."', '$klasse[0]', '$klasse[1]')";			
					$go = 1;
				}
					
				/*
				 if($_POST['update'] == 1 && $num != 0) {
					$sql1 = "update teilnehmer set " .
					"vID = $line[0], lID = $line[1], stnr = $line[2], nachname = '".utf8_encode($line[3])."', vorname = '".utf8_encode($line[4])."', " .
					"geschlecht = '$line[5]', jahrgang = $line[6], verein = '".utf8_encode($line[7])."', klasse = '$klasse' " .
					"where ID = $tID";
					$go = 1;
					}
					if($num == 0) {
					$sql1 = "insert into teilnehmer " .
					"(vID, lID, stnr, nachname, vorname, geschlecht, jahrgang, verein, klasse) " .
					"values ( $line[0], $line[1], $line[2], '".utf8_encode($line[3])."', '".utf8_encode($line[4])."', '$line[5]', '$line[6]', '".utf8_encode($line[7])."', '$klasse')";
					$go = 1;
					}
					*/
				if($go == 1) {
					$link = connectDB();
					$result1 = mysql_query($sql1);
					if (!$result1) { die('Invalid query: ' . mysql_error()); }
					if (!$result1) {
						$errMsg .= "Fehler in Zeile $i - Fehlermeldung: " . mysql_error() . "<br>\n";
					} else {
						$didIt++;
					}
				}
	
			} else {
				echo mysql_error();
				$errMsg .= "Fehler in Zeile $i<br>\n";
			}
			$i++;
		}
	}

	$errMsg .= "$didIt Datensätze erfolgreich eingefügt / aktualisiert<br>\n";
	return $errMsg;
}

function zUpdateDB($lines) {
	$i = 1;
	$didIt = 0;

	foreach($lines as $line) {
		
		$errMsg = "";
		$sql = "select ID from teilnehmer where vID = $line[0] and lID = $line[1] and stnr = $line[2] and del = 0";
		//echo $sql;
		$result = mysql_query($sql);
		if ($result) {
			$num = mysql_num_rows($result);
			if($num != 0) {
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					$tID = $row['ID'];
				}
			}

			if($num != 0) {
				$sql1 = "update teilnehmer set " .
				"manzeit = '$line[3]', zeit = '$line[3]', usemantime = 1 " .
				"where ID = $tID";

				//echo $sql1."<br>";
				$res1 = mysql_query($sql1);
				if (!$res1) {
					$errMsg .= "Fehler in Zeile $i - Fehlermeldung: " . mysql_error() . "<br>\n";
				} else {
					$didIt++;
				}
			}

		} else {
			$errMsg .= "Fehler in Zeile $i<br>\n";
		}
		$i++;
	}

	$errMsg .= "$didIt Datensätze erfolgreich eingefügt / aktualisiert<br>\n";
	return $errMsg;
}

function uploadForm() {
	global $func;
	$html ="<form enctype=\"multipart/form-data\" name=\"Formular\" method=\"POST\" action=\"?func=import.$func[1]\">\n";
	$html .="<div class=\"vboxitem\" >\n";
	$html .="	<span class=\"description\" >\n";
	$html .="		Bitte wählen Sie die Datei.\n";
	$html .="	</span>\n";
	$html .="</div>\n";
	$html .="<div class=\"vboxitem\" >\n";
	$html .="	<table class=\"grey-bg\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" >\n";
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				&nbsp;\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				&nbsp;\n";
	$html .="			</td>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";


	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	if ($func[1] == "teilnehmer") {
		$html .="				Teilnehmerdatei:\n";
	} else {
		$html .="				Zeitdatei:\n";
	}
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<input type=\"file\" name=\"userfile\" size=\"40\" value=\"\">\n";
	$html .="			</td>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";

	if ($func[1] == "teilnehmer") {
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Update existing:\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\" >\n";
		$html .="				<input type=\"checkbox\" name=\"update\" value=\"1\">&nbsp;wenn diese Option nicht aktiviert ist, werden nur Datensätze angelegt, die noch nicht vorhanden sind.\n";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\" ></td>\n";
		$html .="		</tr>\n";
	}

	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				&nbsp;\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				&nbsp;\n";
	$html .="			</td>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";

	$html .="	</table>\n";
	$html .="</div>\n";

	$html .="<div class=\"vboxitem\" >\n";
	$html .="	<div class=\"navigation-buttons\" >\n";
	$html .="		<input name=\"submit\" type=\"submit\" value=\"Upload\" class=\"button\">\n";
	$html .="		&nbsp;&nbsp;\n";
	$html .="		<input type=\"button\" name=\"cancel\" value=\"<< Zur&uuml;ck\" class=\"button\" ONCLICK=\"window.location.href='".$_SERVER["SCRIPT_NAME"]."?func=teilnehmer'\">\n";
	$html .="	</div>\n";
	$html .="</div>\n";
	$html .="</form>\n";

	$html .="<div class=\"vboxitem\" >\n";
	$html .="<p>&nbsp;</p>\n";
	$html .="<p><b>Dateiformat:</b></p>\n";
	if ($func[1] == "teilnehmer") {
		$html .="<p>Die erste Zeile enthält die Spaltenüberschriften</p>\n";
		$html .="<p>Veranstaltung;Rennen;Startnumer;Nachname;Vorname;Geschlecht;Jahrgang;Verein;Attribut</p>\n";
	} else {
		$html .="<p>Die erste Zeile enthält die Spaltenüberschriften</p>\n";
		$html .="<p>Veranstaltung;Rennen;Startnumer;Zeit (HH:MM:SS)</p>\n";
	}
	$html .="</div>\n";


	return $html;
}

?>
