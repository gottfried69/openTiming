-- Vereine mit dem meisten Läufern im Ziel 
SELECT Verein, COUNT(id) anz 
 FROM teilnehmer 
 WHERE lid IN (74, 76) 
  AND Platz <> 0 
  AND Verein <> "" 
 GROUP BY Verein 
 ORDER BY anz DESC 
 LIMIT 0,10;
 

-- Export für Import in openTiming
SELECT 18, '', Startnummer, name, Vorname, Geschlecht, Jahrgang, Verein, Att, `Event` 
 FROM `marktlauf` 
 WHERE veranstaltung = '2016' 
 order by `Event`, Verein;
 
-- Anmeldungen pro Lauf
select count(*), event from marktlauf where veranstaltung = '2017' group by event 

-- Anmeldungen pro Lauf vergeleichen
select count(*), event, veranstaltung from marktlauf where veranstaltung = '2017' group by event
union all
select count(*), event, veranstaltung from marktlauf where veranstaltung = '2016' group by event order by event, veranstaltung;

-- alle zeiten der U10
SELECT * FROM `zeit` WHERE nummer in (SELECT stnr from teilnehmer where (klasse = 'MU10' or klasse = 'WU10') and vid = 19) and vid = 19 order by ID;

-- alle zeiten der U10 um 46 sekunden reduzieren
UPDATE `zeit` set `zeit` = SEC_TO_TIME( TIME_TO_SEC(zeit) - TIME_TO_SEC('00:00:46')) WHERE nummer in (SELECT stnr from teilnehmer where (klasse = 'MU10' or klasse = 'WU10') and vid = 19) and vid = 19;

-- alle zeiten der U10 löschen
delete from zeit where nummer in (SELECT stnr from teilnehmer where (klasse = 'MU10' or klasse = 'WU10') and vid = 19) and vid = 19;

-- mySQL mit datum und zeit rechnen
select SEC_TO_TIME(to_seconds('2017-10-26 08:00:20') - to_seconds('2017-10-25 08:00:00'));

-- Orte aus denen die Läufer sind
SELECT count(o.ort), o.ort, o.lon, o.lat FROM teilnehmer t
 LEFT JOIN verein_ort vo on t.verein = vo.verein
 LEFT JOIN ort o on vo.ort = o.ort
 where t.vid = 19 and o.ort is not null
 group by o.ort;

 -- Vereine ohne Ort
 SELECT t.verein, o.ort, o.lon, o.lat FROM teilnehmer t
 LEFT JOIN verein_ort vo on t.verein = vo.verein
 LEFT JOIN ort o on vo.ort = o.ort
 where t.verein is not null and t.vid not in (12, 11, 9)
 group by t.verein;
 
 -- Orte die noch keine Geodaten hinterlegt haben
 SELECT count(t.id) cnt, vo.ort, o.ort from teilnehmer t
 LEFT JOIN verein_ort vo on t.verein = vo.verein
 LEFT JOIN ort o on vo.ort = o.ort
 group by vo.ort
 order by cnt desc
;