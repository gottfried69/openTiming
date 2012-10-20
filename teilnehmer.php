<?php
/*
 * Created on 06.11.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
function teilnehmer() {
	global $func;
	$errmsg 	= "";
	$zeit 		= '00:00:00';
	$editError 	= 0;
	$f = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	
	# insert / edit Veranstaltung
	if (isset($_POST['submit']) || $func[1] == 'delete') {
		$f = tInsertUpdate();
		$editError = $f[13];
	}

	# display Form
	if ($func[1] == "edit" || $func[1] == "insert" || $editError == 1) {
		$f = tDisplayEditForm($f);
	} else {
		$f = tDisplayList($f);
	}
	
	$html = $f[14];
	$html .= "<div id='data_div'></div>";
	return table("Teilnehmer", $html);
}

function tInsertUpdate( ) {
	global $func;

	$link = connectDB();
	$_SESSION['rID'] = $_POST['rID'];

	$f[0] 		= $_SESSION['vID'];
	$f[1] 		= $_POST['rID'];
	$f[2]		= $_POST['tID'];
	$f[3]	 	= htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
	$f[4] 		= htmlspecialchars($_POST['vorname'], ENT_QUOTES, 'UTF-8');
	$f[5]		= htmlspecialchars($_POST['geschlecht'], ENT_QUOTES, 'UTF-8');
	$f[6] 		= htmlspecialchars($_POST['ort'], ENT_QUOTES, 'UTF-8');
	$f[7] 		= htmlspecialchars($_POST['jg'], ENT_QUOTES, 'UTF-8');
	$f[8] 		= htmlspecialchars($_POST['stnr'], ENT_QUOTES, 'UTF-8');
	$f[9] 		= htmlspecialchars($_POST['verein'], ENT_QUOTES, 'UTF-8');
	$f[10]		= htmlspecialchars($_POST['zeit'], ENT_QUOTES, 'UTF-8');
	if (isset($_POST['disq'])) { $f[11] = 1; } else { $f[11] = 0; }
	if (isset($_POST['useManTime'])) { $f[16] = 1; } else { $f[16] = 0; }
	$f[12]		= "";
	$f[13]		= 0;
	$f[15]		= htmlspecialchars($_POST['klasse'], ENT_QUOTES, 'UTF-8');
	$f[17]		= "";
	$f[18]		= htmlspecialchars($_POST['manRunden'], ENT_QUOTES, 'UTF-8');
	$f[19]		= htmlspecialchars($_POST['vklasse'], ENT_QUOTES, 'UTF-8');
	$f[20]		= htmlspecialchars($_POST['att'], ENT_QUOTES, 'UTF-8');
	
	if($_POST['func'] == "edit") {
		$sql = "update teilnehmer set " .
				"vID = $f[0], lID = $f[1], nachname = '$f[3]', vorname = '$f[4]', " .
				"geschlecht = '$f[5]', ort = '$f[6]', jahrgang = $f[7], stnr = $f[8], " .
				"verein = '$f[9]', manzeit = '$f[10]', zeit = '$f[10]', useManTime = $f[16] ,disq = $f[11], klasse = '$f[15]', man_runden = $f[18], vklasse = '$f[19]' , att = '$f[20]'" .
				"where ID = $f[2]";
	} elseif ($func[0] == 'teilnehmer' && $func[1] == 'delete') {
		$sql = "update teilnehmer set del = 1 where ID = ".$_GET['ID'];
	} else {
		$sql = "insert into teilnehmer " .
				"(vID, lID, nachname, vorname, jahrgang, geschlecht, ort, stnr, verein, zeit, manzeit, useManTime, disq, klasse, man_runden, vklasse, att) " .
				"values ( $f[0], $f[1], '$f[3]', '$f[4]', $f[7], '$f[5]', '$f[6]', '$f[8]', '$f[9]', '$f[10]','$f[10]', $f[16], $f[11], '$f[15]', $f[18], '$f[19]', '$f[20]')";			
	}
	//echo $sql;
	$result = mysql_query($sql);
	if (!$result) {
		$f[12] = mysql_error();
		//echo $f[12];
		// zeigt das Formular bei einem uptate Error
		if (!isset($func[1])) { $f[13] = 1; }
	}
	mysql_close($link);

	if ($func[0] == 'teilnehmer' && $func[1] == 'delete') {
		$script = 'index.php?func=teilnehmer';
		header('Location: '.$script);
		die;
	}

	if (isset($_POST['nextUrl']) && ($_POST['nextUrl'] != '') && ($f[13] == 0)) {
		$script = $_POST['nextUrl'];
		header('Location: '.$script);
		die;
	}

	return $f;
}

function tDisplayEditForm($f) {
	global $func;
	$html = "";
	
	$xajax = new xajax();
	$xajax->register(XAJAX_FUNCTION,"getKlasse");
	$xajax->processRequest();
	$xajax->printJavascript();

	// wenn vorher kein Fehler war werden leere Felder angezeigt
	if ($f[12] == "") {
		$f[3] 	= '';
		$f[4] 	= '';
		$f[5]	= '';
		$f[6] 	= '';
		$f[7] 	= '';
		$f[8] 	= '';
		$f[9] 	= '';
		$f[10]	= '00:00:00';
		$f[11]	= '0';
		$f[20] = '';
	}

	if($func[1] == "edit") {
		$link = connectDB();
		$sql = "select * from teilnehmer where ID = ".$_GET['ID'];
		$result = mysql_query($sql);
		if (!$result) {
			die('Invalid query: ' . mysql_error());
		}

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$f[8]	= $row['stnr'];
			$f[1]	= $row['lID'];
			$f[3] 	= $row['nachname'];
			$f[4] 	= $row['vorname'];
			$f[9] 	= $row['verein'];
			$f[7] 	= $row['jahrgang'];
			$f[15]	= $row['klasse'];
			$f[16]  = $row['useManTime'];
			$f[5]	= $row['geschlecht'];
			$f[6]	= $row['ort'];
			$f[10]	= $row['zeit'];
			$f[11] 	= $row['disq'];
			$f[2]	= $row['ID'];
			$f[17]  = $row['aut_runden'];
			$f[18]  = $row['man_runden'];
			$f[19]	= $row['vklasse'];
			$f[20]	= $row['att'];
			
		}
		$rInfo = getRennenData($_SESSION['vID'], $f[1]);
		mysql_close($link);
	}

	$nextUrl = '';
	if(isset($_POST['nextUrl'])) { $nextUrl = $_POST['nextUrl']; }
	if(isset($_GET['nextUrl'])) { $nextUrl = base64_decode($_GET['nextUrl']); }

	if ($func[1] == 'insert') {
		$html .="<form name=\"Formular\" method=\"POST\" action=\"?func=teilnehmer.insert\">\n";
	} else {
		$html .="<form name=\"Formular\" method=\"POST\" action=\"?func=teilnehmer\">\n";
		if ($f[13] == 1) { $func[1] = "edit"; }
	}
	$html .="<input name=\"func\" type=\"hidden\" value=\"$func[1]\">\n";
	$html .="<input name=\"tID\" type=\"hidden\" value=\"$f[2]\">\n";
	$html .="<input name=\"nextUrl\" type=\"hidden\" value=\"$nextUrl\">\n";
	$html .="<div class=\"vboxitem\" >\n";
	if ($f[12] != "") {
		$html .="	<div class=\"errorbox\" >\n";
		$html .="		$f[12]\n";
		$html .="	</div>\n";
	}
	$html .="	<div class=\"description\" >\n";
	$html .="		<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
	$html .="			<tr>\n";
	$html .="				<td>\n";
	$html .="					Hier k&ouml;nnen Sie die Teilnehmerdaten eingeben. Felder mit einem * sind Pflicht.\n";
	$html .="				</td>\n";
	$html .="				<td>\n";
	$html .="					<span class=\"description\" style=\"float: right;\">\n";
	$html .="						<a href=\"#\" id=\"calculator\" onClick=\"return false;\">show Calculator</a>\n";
	$html .="					</span>\n";
	$html .="				</td>\n";
	$html .="			</tr>\n";
	$html .="		</table>\n";
	$html .="	</div>\n";
	
	$html .="</div>\n";
	#$html .="    <p class=\"vboxspacer\">&nbsp;</p>\n";
	$html .="<div class=\"vboxitem\" >\n";
	$html .="	<table class=\"grey-bg\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" >\n";
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				StartNr*:\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<input type=\"text\" name=\"stnr\" id=\"stnr\" maxlength=\"4\" size=\"4\" value=\"$f[8]\">\n";
	$html .="			</td>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	$html .="		<tr class=\"top-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Name, Vorname*:\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<input type=\"text\" name=\"name\" maxlength=\"200\" size=\"20\" value=\"$f[3]\">\n";
	$html .="				<input type=\"text\" name=\"vorname\" maxlength=\"200\" size=\"20\" value=\"$f[4]\">\n";
	$html .="			</td>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Jahrgang*:\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<input type=\"text\" id=\"jg\" name=\"jg\" maxlength=\"4\" size=\"4\" value=\"$f[7]\" onChange=\"xajax_getKlasse(document.getElementById('jg').value, document.getElementById('geschlecht').value, document.getElementById('rID').value, 1); return false;\">\n";
	$html .="				&nbsp;&nbsp;\n";
	$html .="				<input type=\"text\" readonly id=\"klasse\" name=\"klasse\" maxlength=\"5\" size=\"5\" value=\"$f[15]\">\n";
	$html .="				&nbsp;&nbsp;\n";
	$html .="				<input type=\"text\" readonly id=\"vklasse\" name=\"vklasse\" maxlength=\"5\" size=\"5\" value=\"$f[19]\">\n";
	$html .="			</td>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Geschlecht*:\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<select id=\"geschlecht\" name=\"geschlecht\" onChange=\"xajax_getKlasse(document.getElementById('jg').value, document.getElementById('geschlecht').value, document.getElementById('rID').value, 1); return false;\">\n";
	$m = ""; $w = ""; $x= "";
	if($f[5] == "X") { $x="selected"; }
	elseif ($f[5] == "W") { $w="selected"; }
	else { $m="selected"; }
	$html .="					<option value=\"M\" $m>M</option>\n";
	$html .="					<option value=\"W\" $w>W</option>\n";
	$html .="					<option value=\"X\" $x>X</option>\n";

	$html .="				</select>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Verein:\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<input type=\"text\" id=\"verein\" name=\"verein\" maxlength=\"200\" size=\"43\" value=\"$f[9]\">\n";
	$html .="			</td>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Ort:\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<input type=\"text\" name=\"ort\" maxlength=\"200\" size=\"43\" value=\"$f[6]\">\n";
	$html .="			</td>\n";
	$html .="			<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Zeit:\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<input type=\"text\" name=\"zeit\" maxlength=\"8\" size=\"8\" value=\"$f[10]\">&nbsp;HH:MM:SS\n";
	if ($f[16] == 1 ) { $c1 = "checked"; } else { $c1 = ""; }
	$html .="				 <input type=\"checkbox\" name=\"useManTime\" $c1>&nbsp;&nbsp;manuell eingegebene Zeit nutzen\n";
	$html .="			</td>\n";
	$html .="				<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";

	$html .="		<tr class=\"middle-row\">\n";
	$html .="			<td id=\"rr1\" style=\"display:none\" class=\"leftcolumn\" nowrap >\n";
	$html .="				Runden:\n";
	$html .="			</td>\n";
	$html .="			<td id=\"rr2\" style=\"display:none\" class=\"rightcolumn\" nowrap>\n";
	if($f[17] == '') { $f[17] = 0; }
	$html .="				<input type=\"text\" readonly name=\"autRunden\" id=\"autRunden\" maxlength=\"8\" size=\"8\" value=\"$f[17]\"> (Zeittabelle)\n";
	$html .="				&nbsp;&nbsp;&nbsp;\n";
	if($f[18] == '') { $f[18] = 0; }
	$html .="				<input type=\"text\" name=\"manRunden\" id=\"manRunden\" size=\"8\" onkeyup=\"updateSumRunden(document.getElementById('manRunden').value, document.getElementById('autRunden').value); return false;\" value=\"$f[18]\"> (manuell erfasst)\n";
	$html .="			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id=\"sumRunden\"><span>\n";
	$html .="			</td>\n";
	$html .="				<td id=\"rr3\" style=\"display:none\" class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";

	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Attribut:\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<input type=\"text\" name=\"att\" maxlength=\"2\" size=\"3\" value=\"$f[20]\">&nbsp;\n";
	$html .="			</td>\n";
	$html .="				<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Rennen:\n";
	$html .="			</td>\n";

	// Lauf auswählen
	$link = connectDB();
	$sql = "select * from lauf where vID = ".$_SESSION['vID']." order by start";
	$result2 = mysql_query($sql);
	if (!$result2) { die('Invalid query: ' . mysql_error()); }

	$html .="			<td class=\"rightcolumn\" >\n";
	$html .="				<select id=\"rID\" name=\"rID\" onChange=\"xajax_getKlasse(document.getElementById('jg').value, document.getElementById('geschlecht').value, document.getElementById('rID').value, 1); return false;\">\n";
	$html .="					<option value=\"X\">bitte wählen</option>\n";

	while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
		$rID 		= $row2['ID'];
		$kID 		= $row2['klasse'];
		$titel 		= $row2['titel'];
		$utitel 		= $row2['untertitel'];

		if($func[1] == "edit") {
			if($rID == $f[1]) { $s="selected"; } else { $s=""; }
		} else {
			if($rID == $_SESSION['rID']) { $s="selected"; } else { $s=""; }
		}

		$html .="					<option value=\"$rID\" $s>$titel - $utitel</option>\n";
	}
	mysql_close($link);
	// ende - Lauf auswählen

	$html .="				</select>\n";
	$html .="			</td>\n";
	$html .="				<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	$html .="		<tr class=\"middle-row\" >\n";
	$html .="			<td class=\"leftcolumn\" nowrap >\n";
	$html .="				Disqualifiziert :\n";
	$html .="			</td>\n";
	$html .="			<td class=\"rightcolumn\" >\n";
	if ($f[11] == 1 ) { $c2 = "checked"; } else { $c2 = ""; }
	$html .="				<input type=\"checkbox\" name=\"disq\" $c2>\n";
	$html .="			</td>\n";
	$html .="				<td class=\"errorcolumn\" ></td>\n";
	$html .="		</tr>\n";
	$html .="	</table>\n";
	$html .="</div>\n";
	#		$html .="<p class=\"vboxspacer\">&nbsp;</p>\n";
	#		$html .="<div class=\"vboxitem\" ></div>\n";
	#		$html .="<p class=\"vboxspacer\">&nbsp;</p>\n";
	$html .="<div class=\"vboxitem\" >\n";
	$html .="	<div class=\"navigation-buttons\" >\n";
	$html .="		<input name=\"submit\" type=\"submit\" value=\"Speichern\" class=\"button\">\n";
	$html .="		&nbsp;&nbsp;\n";
	$html .="		<input type=\"button\" name=\"cancel\" value=\"<< Zur&uuml;ck\" class=\"button\" ONCLICK=\"window.location.href='".$_SERVER["SCRIPT_NAME"]."?func=teilnehmer'\">\n";
	if($func[1] == 'edit') {
		$html .="		&nbsp;&nbsp;\n";
		$html .="		<input type=\"button\" name=\"delete\" value=\"löschen\" class=\"button\" ONCLICK=\"window.location.href='".$_SERVER["SCRIPT_NAME"]."?func=teilnehmer.delete&ID=".$_GET['ID']."'\">\n";
	}
	$html .="	</div>\n";
	$html .="</div>\n";
	$html .="</form>\n";

	$f[14] = $html;
	return $f;
}

function tDisplayList ($f)  {
	# Display Veranstaltungen
	$html = "";
	if ($f[12] != "") {
		$html .="	<div class=\"errorbox\" >\n";
		$html .="		$errmsg\n";
		$html .="	</div>\n";
	}

	$link = connectDB();
	$sql = "SELECT t.*, l.titel FROM `teilnehmer` as t INNER JOIN lauf as l ON t.lID = l.ID where t.vID = '".$_SESSION['vID']."' and del=0 order by nachname asc;";
	$result = mysql_query($sql);
	if (!$result) {
		die('Invalid query: ' . mysql_error());
	}

	$html2 = "";
	$i=1;
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		if($i%2 == 0) { $html2 .= "<tr class=\"even\">\n"; } else { $html2 .= "<tr class=\"odd\">\n"; }
		$html2 .= "<td align\"left\">".$row['stnr']."</td>\n";
		$html2 .= "<td align\"left\"><a href=\"".$_SERVER["SCRIPT_NAME"]."?func=teilnehmer.edit&ID=".$row['ID']."\">".$row['nachname'].", ".$row['vorname']."</a></td>\n";
		$html2 .= "<td align\"left\">".$row['verein']."</td>\n";
		$html2 .= "<td align\"left\">".$row['jahrgang']."</td>\n";
		$html2 .= "<td align\"left\">".$row['geschlecht']."</td>\n";
		$html2 .= "<td align\"left\">".$row['klasse']."</td>\n";
		$html2 .= "<td align\"left\">".$row['titel']."</td>\n";
		$html2 .= "<td align\"left\">".$row['zeit']."</td>\n";
		$html2 .= "<td align\"left\">".$row['platz']."</td>\n";
		$html2 .= "<td align\"left\">".$row['akplatz']."</td>\n";

		$html2 .= "</tr>\n";
		$i++;
	}

	$columns = array('Stnr', 'Name', 'Verein', 'JG', 'G', 'Klasse', 'Rennen', 'Zeit', 'Platz', 'AK Platz');
	$html .= tableList($columns, $html2, "common");

	mysql_close($link);
	$f[14] = $html;
	return $f;
}

function getKlasse($jg, $sex, $rennen, $ajax) {

	if($ajax == 1) { $link = connectDB(); }

	$jahr = substr($_SESSION['vDatum'], 0, 4);
	$alter = $jahr - $jg;

	$k[0] = getKlasseData($alter, $sex, $rennen, 0);
	$k[1] = getKlasseData($alter, $sex, $rennen, 1);
	
	if($ajax == 1) { mysql_close($link); }

	if($ajax == 1) {
		// Instantiate the xajaxResponse object
		$objResponse = new xajaxResponse();
	  
		// add a command to the response to assign the innerHTML attribute of
		// the element with id="SomeElementId" to whatever the new content is

		$objResponse->assign("klasse","value", $k[0]);
		$objResponse->assign("vklasse","value", $k[1]);
		//$objResponse->assign("test","innerHTML", $newContent);

		//return the  xajaxResponse object
		return $objResponse;
	} else {
		return $k;
	}
}

function getKlasseData($alter, $sex, $rennen, $mannschaft) {

	$k = "";
	if($mannschaft == 0) { $klasse = 'klasse'; } else { $klasse = 'vklasse'; }
	
	$sql = "SELECT l.*, kd.* FROM `lauf` as l " .
		"INNER JOIN klasse_data as kd ON kd.kID = l.$klasse " .
		"where kd.altervon <= $alter " .
		"and kd.alterbis >= $alter " .
		"and kd.geschlecht = '$sex' " .
		"and l.ID = $rennen";

	//echo $sql;
			
	$result = mysql_query($sql);
	if (!$result) {
		die('Invalid query: ' . mysql_error());
	}

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$k = $row['name'];
	}
	
	return $k;
}

?>
