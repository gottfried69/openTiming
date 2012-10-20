<?php
/*
 * Created on 06.11.2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

function rennen() {

	$xajax = new xajax();
	$xajax->register(XAJAX_FUNCTION, "lockRace");
	$xajax->register(XAJAX_FUNCTION, "clearDiv");
	$xajax->processRequest();
	$xajax->printJavascript();
	
	global $func;	
	$teamAnz = 0;
	
	# insert / edit Veranstaltung
	if (isset($_POST['submit'])) {
		$link = connectDB();
		$zeit = $_POST['hour'].$_POST['min'].$_POST['sec'];
		if($_POST['func'] == "edit") {
			$sql = "update lauf set vID = '".$_SESSION['vID']."', 
							 titel = '".htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8')."', 
							 untertitel = '".htmlspecialchars($_POST['subTitle'], ENT_QUOTES, 'UTF-8')."', 
							 start = ".htmlspecialchars($zeit, ENT_QUOTES, 'UTF-8').", 
							 klasse = '".htmlspecialchars($_POST['klasse'], ENT_QUOTES, 'UTF-8')."', 
							 team_anz = ".htmlspecialchars($_POST['teamAnz'], ENT_QUOTES, 'UTF-8').", 
							 uTemplate = '".htmlspecialchars($_POST['uTemplate'], ENT_QUOTES, 'UTF-8')."', 
							 uDefinition = '".htmlspecialchars($_POST['uDefinition'], ENT_QUOTES, 'UTF-8')."', 
							 rundenrennen = ".htmlspecialchars($_POST['rr'], ENT_QUOTES, 'UTF-8')." , 
							 use_lID = ".htmlspecialchars($_POST['use_lID'], ENT_QUOTES, 'UTF-8').", 
							 teamrennen = ".htmlspecialchars($_POST['teamrennen'], ENT_QUOTES, 'UTF-8').", 
							 rdVorgabe = ".htmlspecialchars($_POST['rdVorgabe'], ENT_QUOTES, 'UTF-8').", 
							 vklasse = ".htmlspecialchars($_POST['vklasse'], ENT_QUOTES, 'UTF-8').", 
							 showLogo = ".htmlspecialchars($_POST['showLogo'], ENT_QUOTES, 'UTF-8').", 
							 mainReaderIp = '".htmlspecialchars($_POST['reader'], ENT_QUOTES, 'UTF-8')."' 
						where ID = ".$_POST['ID'].";";
		} else {
			$sql = "insert into lauf (vID, titel, untertitel, start, 
										klasse, team_anz, uDefinition, uTemplate, 
										rundenrennen, use_lID, teamrennen, rdVorgabe, 
										vklasse, showLogo, mainReaderIp) values 
										( '".$_SESSION['vID']."', 
										'".htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8')."', 
										'".htmlspecialchars($_POST['subTitle'], ENT_QUOTES, 'UTF-8')."', 
										".htmlspecialchars($zeit, ENT_QUOTES, 'UTF-8').", 
										".htmlspecialchars($_POST['klasse'], ENT_QUOTES, 'UTF-8').", 
										".htmlspecialchars($_POST['teamAnz'], ENT_QUOTES, 'UTF-8').", 
										'".htmlspecialchars($_POST['uDefinition'], ENT_QUOTES, 'UTF-8')."', 
										'".htmlspecialchars($_POST['uTemplate'], ENT_QUOTES, 'UTF-8')."', 
										".htmlspecialchars($_POST['rr'], ENT_QUOTES, 'UTF-8').", 
										".htmlspecialchars($_POST['use_lID'], ENT_QUOTES, 'UTF-8').", 
										".htmlspecialchars($_POST['teamrennen'], ENT_QUOTES, 'UTF-8').", 
										".htmlspecialchars($_POST['rdVorgabe'], ENT_QUOTES, 'UTF-8').", 
										".htmlspecialchars($_POST['vklasse'], ENT_QUOTES, 'UTF-8').", 
										".htmlspecialchars($_POST['showLogo'], ENT_QUOTES, 'UTF-8').", 
										'".htmlspecialchars($_POST['reader'], ENT_QUOTES, 'UTF-8')."')";
		}
		//echo $sql;
		$result = mysql_query($sql);
			if (!$result) {
    			die('Invalid query: ' . mysql_error());
			}
		mysql_close($link);
	}
	
	# display Form
	if ((isset($func[1]) && $func[1] == "edit") || (isset($func[1]) && $func[1] == "insert")) {
		$html = showRaceEditForm($func);		
	} else {
		$html = showRennen();
	}

	$html .= "<div id='data_div'></div>";
	return table("Rennen", $html);
	
}

function lockRace($rennen, $lock) {
	$objResponse = new xajaxResponse();

	$link = connectDB();
	$sql = "update lauf set lockRace = $lock where ID = $rennen;";
	//echo $sql;
	$result = mysql_query($sql);
		if (!$result) {
   			die('Invalid query: ' . mysql_error());
		}
	mysql_close($link);

	if($result) {
		if ($lock == 0 ) {
			$html = "<a href=\"#\" onClick=\"xajax_lockRace($rennen, 1); return false;\"><img src=\"img/offen.png\" alt=\"offen\" border=\"0\"></a>";
		} else {	
			$html = "<a href=\"#\" onClick=\"xajax_lockRace($rennen, 0); return false;\"><img src=\"img/geschlossen.png\" alt=\"geschlossen\" border=\"0\"></a>";
		}
	}
	
	$objResponse->assign('lock_div_'.$rennen, 'innerHTML', $html);
	return $objResponse;
}

function showRennen() {
	
		# Display Rennen
		$html = "";
		$link = connectDB();
		$veranstaltung = $_SESSION['vID'];
		$sql = "select * from lauf where vID = $veranstaltung order by start asc, titel;";
		$result = mysql_query($sql);
			if (!$result) {
    			die('Invalid query: ' . mysql_error());
			}

		$html2 = "";
		$i=1;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if($i%2 == 0) { $html2 .= "<tr class=\"even\">\n"; } else { $html2 .= "<tr class=\"odd\">\n"; }

			$html2 .= "<td width=\"30\" align\"left\">".$row['ID']."</td>\n";
			$html2 .= "<td align\"left\">".$row['titel']."</td>\n";
			$html2 .= "<td align\"left\">".$row['untertitel']."</td>\n";
			$html2 .= "<td align\"left\">".$row['start']."</td>\n";
			$html2 .= "<td align\"center\">" .
					"<a href=\"".$_SERVER["REQUEST_URI"].".edit&ID=".$row['ID']."\">edit</a>" .
					"&nbsp;&nbsp;";

			if($row['lockRace'] == 0) { 
				$lock = 1; 
				$img = "img/offen.png";
				$alt = "offen";
			} else { 
				$lock = 0; 
				$img = "img/geschlossen.png";
				$alt = "geschlossen";
			}
			
			$html2 .=  "|&nbsp;&nbsp;<span id=\"lock_div_".$row['ID']."\"><a href=\"#\" onClick=\"xajax_lockRace(".$row['ID'].", $lock); return false;\"><img src=\"$img\" alt=\"$alt\" border=\"0\"></a></span>" .
					"&nbsp;&nbsp;";
			
			$html2 .= "</td>\n";
			$html2 .= "</tr>\n";
			$i++;
		}
	
		$columns = array('ID', 'Titel', 'Bemerkung', 'Start', 'Aktion');
		$html .= tableList($columns, $html2, "common meetings");
		
		mysql_close($link);
		
		$html .="<br><div class=\"vboxitem\" >\n";
		$html .="	<div class=\"navigation-buttons\" >\n";
		$html .="		<input type=\"submit\" value=\"neues Rennen\" class=\"button\" ONCLICK=\"window.location.href='".$_SERVER["REQUEST_URI"].".insert'\">\n";
		$html .="	</div>\n";
		$html .="</div>\n";	

		return $html;
}

function showRaceEditForm($func) {
		
		global $config;
		$ID = "";
		$titel = ""; $teamAnz = 0; $untertitel = "";
		$dat[0] = ""; $dat[1] = ""; $dat[2] = "";
		$kl = 0; $vkl = 0; $use_lID = 0; $tr = 0; $sl = 1; $rr = 0;
		$rdVorgabe = 0; $readerIp = "0.0.0.0"; $uTemplate = ""; $uDefinition = "";
		
		$link = connectDB();
		
		if($func[1] == "edit") {
			$sql = "select * from lauf where ID = ".$_GET['ID'];
			$result = mysql_query($sql);
				if (!$result) {
	    			die('Invalid query: ' . mysql_error());
				}
	
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$titel = $row['titel'];
				$untertitel = $row['untertitel'];
				$zeit = $row['start'];
				$ID = $row['ID'];
				$kl = $row['klasse'];
				$vkl = $row['vklasse'];
				$teamAnz = $row['team_anz'];
				$uTemplate = $row['uTemplate'];
				$uDefinition = $row['uDefinition'];
				$rr = $row['rundenrennen'];
				$tr = $row['teamrennen'];
				$sl = $row['showLogo'];
				$use_lID = $row['use_lID'];
				$rdVorgabe = $row['rdVorgabe'];
				$readerIp = $row['mainReaderIp'];
			}
			$dat = explode(':', $zeit);
						
		}
		
		$sql = "select * from klasse order by name";
		$result = mysql_query($sql);
		if (!$result) { die('Invalid query: ' . mysql_error());}
		$kID = 0;
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$kArray[$kID]['ID'] = $row['ID'];
			$kArray[$kID]['name'] = $row['name'];
			$kID++;
		}				
		
		mysql_close($link);
		
		
		$html  ="<form name=\"editVeranstaltungen\" method=\"POST\" action=\"?func=rennen\">\n";
		$html .="<input name=\"func\" type=\"hidden\" value=\"$func[1]\">\n";
		$html .="<input name=\"ID\" type=\"hidden\" value=\"$ID\">\n";
		$html .="<div class=\"vboxitem\" >\n";
		$html .="	<span class=\"description\" >\n";
		$html .="		Hier k&ouml;nnen Sie die Rennen eingeben. Felder mit einem * sind Pflicht.\n";
		$html .="	</span>\n";
		$html .="</div>\n";
	    #$html .="    <p class=\"vboxspacer\">&nbsp;</p>\n";
		$html .="<div class=\"vboxitem\" >\n";
		$html .="	<table class=\"grey-bg\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" >\n";
		$html .="		<tr class=\"top-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Titel*:\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\" >\n";
		$html .="				<input type=\"text\" name=\"title\" maxlength=\"200\" size=\"50\" value=\"$titel\">\n";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\" ></td>\n";
		$html .="		</tr>\n";
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Bemerkung*:\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\" >\n";
		$html .="				<input type=\"text\" name=\"subTitle\" maxlength=\"200\" size=\"50\" value=\"$untertitel\">\n";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\" ></td>\n";
		$html .="		</tr>\n";

		$html .="		<tr class=\"middle-row\" >\n";
		$html .="   		<td class=\"leftcolumn\" nowrap >Start*:\n";
		$html .="   		</td>\n";
		$html .="		<td class=\"rightcolumn\" >\n";
		$html .="		      	<select name=\"hour\">\n";
		$html .="		      			<option value=\"00\">--</option>\n";
										$i=0;
										while($i <= 23) {
										if($i < 10) { $x="0"; } else { $x=""; }
										if($x.$i == $dat[0]) { $s="selected"; } else { $s=""; }
		$html .="						<option value=\"$x$i\" $s>$x$i</option>\n";
										$i++;
										}
		$html .="		      	</select>\n";
	
		$html .="		      	<select name=\"min\">\n";
		$html .="		      			<option value=\"00\">--</option>\n";
										$i=0;
										while($i <= 59) {
										if($i < 10) { $x="0"; } else { $x=""; }
										if($x.$i == $dat[1]) { $s="selected"; } else { $s=""; }
		$html .="						<option value=\"$x$i\" $s>$x$i</option>\n";
										$i++;
										}
		$html .="		      	</select>\n";

		$html .="		      	<select name=\"sec\">\n";
		$html .="		      			<option value=\"00\">--</option>\n";
										$i=0;
										while($i <= 59) {
										if($i < 10) { $x="0"; } else { $x=""; }
										if($x.$i == $dat[2]) { $s="selected"; } else { $s=""; }
		$html .="						<option value=\"$x$i\" $s>$x$i</option>\n";
										$i++;
										}
		$html .="		      	</select>\n";
		
		$html .="		      	Uhr\n";
		$html .="    		</td>\n";
		$html .="			<td class=\"errorcolumn\" ></td>\n";
		$html .="		</tr>\n";
		
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Klasseneinteilung* :\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\" >\n";
		$html .="		      	<select name=\"klasse\">\n";
		$html .="		      			<option value=\"0\">--</option>\n";

					foreach($kArray as $k) {
						if($kl == $k['ID']) { $s="selected"; } else { $s=""; }
		$html .="						<option	value=\"".$k['ID']."\" $s>".$k['name']."</option>\n";
					}
						
		$html .="		      	</select>\n";
		$html .="    		</td>\n";
		$html .="			<td class=\"errorcolumn\" ></td>\n";
		$html .="		</tr>\n";
		
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Klasseneinteilung (Mannschaft) :\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\" >\n";		
		$html .="		      	<select name=\"vklasse\">\n";
		$html .="		      			<option value=\"0\">--</option>\n";

					foreach($kArray as $k) {
						if($vkl == $k['ID']) { $s="selected"; } else { $s=""; }
		$html .="						<option	value=\"".$k['ID']."\" $s>".$k['name']."</option>\n";
					}
		
		$html .="		      	</select>\n";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\" ></td>\n";
		$html .="		</tr>\n";
		
		
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Teammitglieder:\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\" >\n";
		$html .="				<input type=\"text\" name=\"teamAnz\" maxlength=\"5\" size=\"5\" value=\"$teamAnz\">\n";
		$html .="			&nbsp;";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";

		if ($use_lID == 0) { $lID0 = "checked=\"checked\""; } else { $lID0 = ""; }
		if ($use_lID == 1) { $lID1 = "checked=\"checked\""; } else { $lID1 = ""; }
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >Lauf ID bei der Auswertung <br>berücksichtigen</td>\n";
		$html .="			<td class=\"rightcolumn\">
								<input type=\"radio\" $lID0 value=\"0\" name=\"use_lID\"> Nein
								<input type=\"radio\" $lID1 value=\"1\" name=\"use_lID\"> Ja
							</td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";
		
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"line-buttom\">&nbsp</td>\n";
		$html .="			<td class=\"line-buttom\">&nbsp</td>\n";
		$html .="			<td class=\"line-buttom\">&nbsp</td>\n";
		$html .="		</tr>\n";

		if ($tr == 0) { $t0 = "checked=\"checked\""; } else { $t0 = ""; }
		if ($tr == 1) { $t1 = "checked=\"checked\""; } else { $t1 = ""; }
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >Teamrennen:</td>\n";
		$html .="			<td class=\"rightcolumn\">
								<input type=\"radio\" $t0 value=\"0\" name=\"teamrennen\"> Nein
								<input type=\"radio\" $t1 value=\"1\" name=\"teamrennen\"> Ja
								&nbsp;(relevant für die Darstellung der Ergebnisliste)
							</td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";

		if ($sl == 0) { $sl0 = "checked=\"checked\""; }  else { $sl0 = ""; }
		if ($sl == 1) { $sl1 = "checked=\"checked\""; }  else { $sl1 = ""; }
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >openTiming Logo anzeigen:</td>\n";
		$html .="			<td class=\"rightcolumn\">
								<input type=\"radio\" $sl0 value=\"0\" name=\"showLogo\"> Nein
								<input type=\"radio\" $sl1 value=\"1\" name=\"showLogo\"> Ja
							</td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";


		
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"line-buttom\">&nbsp</td>\n";
		$html .="			<td class=\"line-buttom\">&nbsp</td>\n";
		$html .="			<td class=\"line-buttom\">&nbsp</td>\n";
		$html .="		</tr>\n";
		
		if ($rr == 0) { $r0 = "checked=\"checked\""; }  else { $r0 = ""; }
		if ($rr == 1) { $r1 = "checked=\"checked\""; }  else { $r1 = ""; }
		if ($rr == 2) { $r2 = "checked=\"checked\""; }  else { $r2 = ""; }
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Rundenrennen:\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\"></td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";
		
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >&nbsp;</td>\n";
		$html .="			<td class=\"rightcolumn\"><input type=\"radio\" $r0 value=\"0\" name=\"rr\"> kein Rundenrennen</td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";
		
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >&nbsp;</td>\n";
		$html .="			<td class=\"rightcolumn\">\n";
		$html .="               <input type=\"radio\" $r1  value=\"1\" name=\"rr\"> Rundenrennen mit Zeitvorgabe\n";
		$html .="        </td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";

		if($rdVorgabe == "") { $rdVorgabe = 1;}
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >&nbsp;</td>\n";
		$html .="			<td class=\"rightcolumn\"><input type=\"radio\" $r2 value=\"2\" name=\"rr\"> Rundenrennen mit ";
		$html .="				<input type=\"text\" name=\"rdVorgabe\" value=\"$rdVorgabe\" size=\"2\"> Runden Vorgabe";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";

		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >Reader IP für Rundenzeiten:</td>\n";
		$html .="			<td class=\"rightcolumn\"><input value=\"$readerIp\" type=\"text\" name=\"reader\">";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\"></td>\n";
		$html .="		</tr>\n";
						

		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"line-top\">&nbsp</td>\n";
		$html .="			<td class=\"line-top\">&nbsp</td>\n";
		$html .="			<td class=\"line-top\">&nbsp</td>\n";
		$html .="		</tr>\n";
		
		
		$html .="		</tr>\n";
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Urkundenvorlage:\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\" >\n";
		$html .="				<input type=\"text\" name=\"uTemplate\" maxlength=\"200\" size=\"50\" value=\"$uTemplate\">\n";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\" ></td>\n";
		$html .="		</tr>\n";

		$html .="		</tr>\n";
		$html .="		<tr class=\"middle-row\" >\n";
		$html .="			<td class=\"leftcolumn\" nowrap >\n";
		$html .="				Urkundendefinition:\n";
		$html .="			</td>\n";
		$html .="			<td class=\"rightcolumn\" >\n";
		$html .="				<input type=\"text\" name=\"uDefinition\" maxlength=\"200\" size=\"50\" value=\"$uDefinition\">\n";
		$html .="			</td>\n";
		$html .="			<td class=\"errorcolumn\" ></td>\n";
		$html .="		</tr>\n";
		
		
		$html .="	</table>\n";
		$html .="</div>\n";
#		$html .="<p class=\"vboxspacer\">&nbsp;</p>\n";
#		$html .="<div class=\"vboxitem\" ></div>\n";
#		$html .="<p class=\"vboxspacer\">&nbsp;</p>\n";
		$html .="<div class=\"vboxitem\" >\n";
		$html .="	<div class=\"navigation-buttons\" >\n";
		$html .="		<input type=\"button\" name=\"cancel\" value=\"<< Zur&uuml;ck\" class=\"button\" onclick=\"window.location.href='".$_SERVER["SCRIPT_NAME"]."?func=rennen'\">\n";
		$html .="		&nbsp;&nbsp;\n";
		$html .="		<input name=\"submit\" type=\"submit\" value=\"Speichern\" class=\"button\">\n";
		$html .="	</div>\n";
		$html .="</div>\n";
		$html .="</form>\n";

		return $html;
}

?>