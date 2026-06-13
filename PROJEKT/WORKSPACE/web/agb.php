<?php

declare(strict_types=1);

/**
 * Aufgabe: AGB-Seite. Inhalt kommt zentral aus dem Editor (rechtstexte),
 * sodass Änderungen im Editor nach dem Speichern auch hier erscheinen.
 */

require_once __DIR__ . '/includes/rechtstext_seite.php';
smu_rechtstext_seite('agb', 'Allgemeine Geschäftsbedingungen');
