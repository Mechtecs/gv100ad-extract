<?php

/**
 * Erstellt eine komplette Liste aller Allgemeiner Regionalschlüssel aus dem Gemeindeverzeichnis der Destatis.
 * (https://www.destatis.de/DE/ZahlenFakten/LaenderRegionen/Regionales/Gemeindeverzeichnis/Gemeindeverzeichnis.html)
 */

$f = fopen('GV100AD_301121.ASC', 'r');

$list = [];
$matches = null;
while ($line = fgets($f)) {
  $formatCorrect = preg_match("/.{10}(\d{5})(\d{3})(\d{4})(.{50})(.{50}).{98}/", $line, $matches);
  if ($formatCorrect !== 1) {
      continue;
  }



  $list[] = [
    'ars' => $matches[1] . $matches[3] . $matches[2],
    'name' => trim($matches[4]),
    'gemeindeschluessel' => $matches[1] . $matches[2],
    'verbandsschluessel' => $matches[3],
    'landesregierung' => trim($matches[5]),
  ];
}

fclose($f);

// JSON out
file_put_contents('ars.json', json_encode($list, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR));
file_put_contents('ars.min.json', json_encode($list, JSON_PARTIAL_OUTPUT_ON_ERROR));


// SQL out
$inserts = array_map(static function (array $item) {
    return "('{$item['ars']}', '{$item['gemeindeschluessel']}', '{$item['verbandsschluessel']}', '{$item['landesregierung']}', '{$item['name']}')";
}, $list);

$out = "CREATE TABLE IF NOT EXISTS `ars` (`ars` VARCHAR(12) NOT NULL, `ags` VARCHAR(8) NOT NULL, `verband` VARCHAR(4) NULL DEFAULT NULL, `land` VARCHAR(50) NULL DEFAULT NULL, `name` VARCHAR(50) NULL DEFAULT NULL, PRIMARY KEY(`ars`));\n";
$out .= "INSERT INTO `ars` (`ars`, `ags`, `verband`, `land`, `name`) VALUES \n" . implode(",\n", $inserts) . ";\n";

file_put_contents('ars.sql', $out);
