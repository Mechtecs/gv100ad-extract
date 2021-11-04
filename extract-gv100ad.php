<?php

/**
 * Erstellt eine komplette Liste aller Allgemeiner Regionalschlüssel aus dem Gemeindeverzeichnis der Destatis.
 * (https://www.destatis.de/DE/ZahlenFakten/LaenderRegionen/Regionales/Gemeindeverzeichnis/Gemeindeverzeichnis.html)
 */

$f = fopen('GV100AD_301121.ASC', 'r');

$fullARS = $kreise = [];
$matches = null;
while ($line = fgets($f)) {
    $lineFormatCorrect = preg_match("/.{10}(\d{2})(\d| )(\d{2}| {2})(\d{3}| {3})(\d{4}| {4})(.{50})(.{50}).{98}/", $line, $matches);
    if ($lineFormatCorrect !== 1) {
        continue;
    }

    $landRegierungKreis = $matches[1] . trim($matches[2]) . trim($matches[3]);
    $amtlicherRegionalSchluessel = $landRegierungKreis . trim($matches[5]) . trim($matches[4]);
    $entry = [
        'ars' => $amtlicherRegionalSchluessel,
        'name' => trim($matches[6]),
        'gemeindeschluessel' => $landRegierungKreis . trim($matches[4]),
        'verbandsschluessel' => trim($matches[5]),
        'landesregierung' => trim($matches[7]),
    ];

    $fullARS[] = $entry;
    if (mb_strlen($amtlicherRegionalSchluessel) === 5) {
        $kreise[] = $entry;
    }
}

fclose($f);

// JSON out
file_put_contents('ars.json', json_encode($fullARS, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR));
file_put_contents('ars.min.json', json_encode($fullARS, JSON_PARTIAL_OUTPUT_ON_ERROR));
file_put_contents('ars-kreise.json', json_encode($kreise, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR));
file_put_contents('ars-kreise.min.json', json_encode($kreise, JSON_PARTIAL_OUTPUT_ON_ERROR));


// SQL out
$inserts = array_map(static function (array $item) {
    return "('{$item['ars']}', '{$item['gemeindeschluessel']}', '{$item['verbandsschluessel']}', '{$item['landesregierung']}', '{$item['name']}')";
}, $fullARS);

$out = "CREATE TABLE IF NOT EXISTS `ars` (`ars` VARCHAR(12) NOT NULL, `ags` VARCHAR(8) NOT NULL, `verband` VARCHAR(4) NULL DEFAULT NULL, `land` VARCHAR(50) NULL DEFAULT NULL, `name` VARCHAR(50) NULL DEFAULT NULL, PRIMARY KEY(`ars`));\n";
$out .= "INSERT INTO `ars` (`ars`, `ags`, `verband`, `land`, `name`) VALUES \n" . implode(",\n", $inserts) . ";\n";

file_put_contents('ars.sql', $out);
