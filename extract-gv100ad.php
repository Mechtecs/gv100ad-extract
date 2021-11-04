<?php

/**
 * Erstellt eine komplette Liste aller Allgemeiner Regionalschlüssel aus dem Gemeindeverzeichnis der Destatis.
 * (https://www.destatis.de/DE/ZahlenFakten/LaenderRegionen/Regionales/Gemeindeverzeichnis/Gemeindeverzeichnis.html)
 */

$f = fopen('GV100AD_300921.txt', 'r');

$list = [];
while ($line = utf8_encode(fgets($f))) {
  $ags = substr($line, 10, 8);
  $verband = substr($line, 18, 4);
  $ars = substr($ags, 0, 5) . $verband . substr($ags, 5);

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
