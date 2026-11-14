<?php

declare(strict_types=1);

/*
 * Échoue si la couverture des fichiers du domaine et de la couche application est inférieure au seuil.
 * Usage : php bin/check-coverage.php var/coverage/clover.xml [seuil=100] [regex=#/(Domain|Application)/#]
 */

$file = $argv[1] ?? 'var/coverage/clover.xml';
$threshold = (float) ($argv[2] ?? 100);
$pattern = $argv[3] ?? '#/(Domain|Application)/#';

$xml = @simplexml_load_file($file);
if (false === $xml) {
    fwrite(\STDERR, "Rapport clover introuvable : {$file}\n");
    exit(1);
}

$total = 0;
$covered = 0;
$uncovered = [];
foreach ($xml->xpath('//file') ?: [] as $node) {
    $name = (string) $node['name'];
    if (1 !== preg_match($pattern, $name)) {
        continue;
    }
    $metrics = $node->metrics;
    $elements = (int) $metrics['elements'];
    $coveredElements = (int) $metrics['coveredelements'];
    $total += $elements;
    $covered += $coveredElements;
    if ($coveredElements < $elements) {
        $uncovered[] = \sprintf('  %s (%d/%d)', $name, $coveredElements, $elements);
    }
}

if (0 === $total) {
    fwrite(\STDERR, "Aucun fichier ne correspond à « {$pattern} ».\n");
    exit(1);
}

$ratio = 100 * $covered / $total;
printf("Couverture %s : %.2f %% (seuil %.2f %%)\n", $pattern, $ratio, $threshold);
if ($ratio < $threshold) {
    fwrite(\STDERR, "Fichiers non couverts :\n".implode("\n", $uncovered)."\n");
    exit(1);
}
