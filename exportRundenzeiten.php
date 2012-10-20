<?php

require('fpdf/fpdf.php');
include("function.php");
session_start();

class PDF extends FPDF
{
	function exportRundenzeiten($id) {

		$numBefore = 0;
		$fill = false;
		$linesPerPage = 45;
		$header = $this->getHeader($_SESSION['vID'], $id);

		$rd = getRennenData($id);
		$this->setHeader($header);
		$this->setMyFont();

		if($rd['use_lID'] == 1) {
			$sql_lID = " and z.lID = $id";
		} else { $sql_lID = "";
		}
		
		
		$sql = "SELECT stnr, nachname, vorname, verein from teilnehmer where vID = ".$_SESSION['vID']." and lID = $id and platz <> 0";
		$result = mysql_query($sql);
		if (!$result) {
			die('Invalid query: ' . mysql_error());
		}

		
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if($row['nachname'] != "") { $team = "  |  ".htmlspecialchars_decode(utf8_decode($row['nachname']), ENT_QUOTES).", ".htmlspecialchars_decode(utf8_decode($row['vorname']), ENT_QUOTES)." - ".htmlspecialchars_decode(utf8_decode($row['verein']), ENT_QUOTES); }
			$header = "StNr: ".$row['stnr']." ".$team;
			$this->setGroupHeader($header);
			$r = 1;
			$fill=false;
			$zeitBefore = $rd['startZeit'];

			$sql2 = "select zeit from zeit where zeit > '".$rd['startZeit']."' and vID = ".$_SESSION['vID']." $sql_lID and nummer = '".$row['stnr']."' order by zeit asc";
			$result2 = mysql_query($sql2);
			if (!$result) {
				die('Invalid query: ' . mysql_error());
			}

			$zeitBefore = $rd['startZeit'];
			$r = 1;
			
			while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {

				$rundenzeit = getRealTime($zeitBefore, $row2['zeit']);
					
				$this->Cell(15,5,$r,0,0,'R',$fill);
				$this->Cell(20,5,$row2['zeit'],0,0,'R',$fill);
				$this->Cell(25,5,$rundenzeit,0,0,'R',$fill);
				$this->Ln();
				$fill=!$fill;
				$zeitBefore = $row2['zeit'];
				$r++;
			}
		}
		
	}

	function setGroupHeader($header) {
		$this->Ln(5);
		$this->SetFillColor(220,220,220);
		$this->Cell(120,5,$header,'',0,'L',1);
		$this->Ln();
		
		$this->Cell(15,5,"Runde",'B',0,'R',1);
		$this->Cell(20,5,"Uhrzeit",'B',0,'R',1);
		$this->Cell(25,5,"Rundenzeit",'B',0,'R',1);
		$this->Cell(60,5,"",'B',0,'R',1);
		$this->Ln();
		$this->SetFillColor(224,235,255);
	}

	function setMyFont() {
		$this->SetTextColor(0);
		$this->SetFont('Arial','',10);
		//$this->SetDrawColor(0,0,0);
		//$this->SetLineWidth(.5);
	}

	function getHeader($veranstaltung, $rennen) {
		$sql = "select titel, untertitel, datum from veranstaltung where id = $veranstaltung";
		$result = mysql_query($sql);
		if (!$result) { die('Invalid query: ' . mysql_error()); }

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$header['titel'] 		= htmlspecialchars_decode($row['titel'], ENT_QUOTES);
			$header['untertitel'] 	= htmlspecialchars_decode($row['untertitel'], ENT_QUOTES);
			$header['datum'] 		= htmlspecialchars_decode($row['datum'], ENT_QUOTES);
		}

		$sql = "select titel, untertitel from lauf where id = $rennen";
		$result = mysql_query($sql);
		if (!$result) { die('Invalid query: ' . mysql_error()); }

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$header['lauf'] 		= htmlspecialchars_decode($row['titel'], ENT_QUOTES);
			$header['lauf2'] 		= htmlspecialchars_decode($row['untertitel'], ENT_QUOTES);
		}

		return $header;
	}

	function setHeader($header) {
		$this->SetTextColor(0);
		$this->SetY(1);
		$this->SetFont('Arial','',10);
		$this->Ln(5);
		$this->Cell(160);
		$d = explode("-",$header['datum']);
		$this->Cell(30,10,$d[2].".".$d[1].".".$d[0],0,0,'R');
		$this->Ln(5);

		$this->SetFont('Arial','BI',16);
		$this->Cell(80);
		$this->Cell(30,10,utf8_decode($header['titel']),0,0,'C');
		if($header['untertitel'] != "") {
			$this->SetFont('Arial','BI',12);
			$this->Ln(5);
			$this->Cell(80);
			$this->Cell(30,10,utf8_decode($header['untertitel']),0,0,'C');
		}

		$this->Ln(5);
		$this->SetFont('Arial','BI',10);
		$this->Cell(80);
		$this->Cell(30,10,utf8_decode($header['lauf']),0,0,'C');
		$this->Ln(5);
		$this->Cell(80);
		$this->Cell(30,10,utf8_decode($header['lauf2']),0,0,'C');
		$this->Ln(5);
	}

	function Footer() {
		$this->SetY(-15);
		$this->SetFont('Arial','I',8);
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}

}

$link = connectDB();
//$filename = $_GET['action'].'.pdf';

$pdf=new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('Portrait', 'A4');
$pdf->exportRundenzeiten($_GET['id']);

$pdf->Output();

mysql_close($link);

?>