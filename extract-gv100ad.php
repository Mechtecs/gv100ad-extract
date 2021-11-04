<?php

/**
 * Erstellt eine komplette Liste aller Allgemeiner Regionalschlüssel aus dem Gemeindeverzeichnis der Destatis.
 * (https://www.destatis.de/DE/ZahlenFakten/LaenderRegionen/Regionales/Gemeindeverzeichnis/Gemeindeverzeichnis.html)
 */

$f = fopen('GV100AD_301121.ASC', 'r');


//    120643405410
//    12 Brandenburg
//    0 -
//    64 Landkreis Märkisch Oderland
//    340 Gemeinde Neuhardenberg
//    5410 Amt Neuhardenberg

$list = [];
while ($line = fgets($f)) {
  $ags = substr($line, 10, 8);
  $verband = substr($line, 18, 4);
  $ars = substr($ags, 0, 5) . $verband . substr($ags, 5);

  $matches = null;
  $formatCorrect = preg_match("/.{10}(\d{5})(\d{3})(\d{4})(.{50})(.{50}).{98}/", $line, $matches);
  if ($formatCorrect !== 1) {
      continue;
  }



  $list[] = [
    'ars' => trim($ars),
    'name' => trim(substr($line, 22, 50)),
    'gemeindeschluessel' => trim($ags),
    'verbandsschluessel' => trim($verband),
    'landesregierung' => trim(substr($line, 72, 50)),
  ];
}

fclose($f);

// JSON out
file_put_contents('ars.json', json_encode($list, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR));
file_put_contents('ars.min.json', json_encode($list, JSON_PARTIAL_OUTPUT_ON_ERROR));


// SQL out
$inserts = [];
foreach ($list as $item) {
  $inserts[] = "('{$item['ars']}', '{$item['gemeindeschluessel']}', '{$item['verbandsschluessel']}', '{$item['landesregierung']}', '{$item['name']}')";
}
$out = "CREATE TABLE IF NOT EXISTS `ars` (`ars` VARCHAR(12) NOT NULL, `ags` VARCHAR(8) NOT NULL, `verband` VARCHAR(4) NULL DEFAULT NULL, `land` VARCHAR(50) NULL DEFAULT NULL, `name` VARCHAR(50) NULL DEFAULT NULL, PRIMARY KEY(`ars`));\n";
$out .= "INSERT INTO `ars` (`ars`, `ags`, `verband`, `land`, `name`) VALUES \n" . implode(",\n", $inserts) . ";\n";

file_put_contents('ars.sql', $out);
