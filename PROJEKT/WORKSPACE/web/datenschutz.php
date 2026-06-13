<?php

declare(strict_types=1);

/**
 * Aufgabe: Datenschutzerklärung (öffentlich, ohne Login).
 *
 * HINWEIS: Strukturierter ENTWURF. Die verbindlichen Rechtstexte muss der
 * Betreiber juristisch prüfen/ergänzen, bevor sie produktiv gelten.
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';
require_once __DIR__ . '/includes/layout_kopf.php';
require_once __DIR__ . '/includes/layout_fuss.php';

smu_sitzung_starten();
layout_kopf('Datenschutz', false);
?>
<div class="auth-karte" style="max-width: 720px; text-align: left;">
    <div class="auth-logo" style="margin-bottom: 8px;">
        <strong>Datenschutzerklärung</strong>
        <span>Shape Miner Account</span>
    </div>

    <div class="hinweis hinweis-warnung" style="margin-bottom: 20px;">
        Entwurf — rechtlich noch zu prüfen und zu vervollständigen.
    </div>

    <div class="rechtstext">
        <h2>1. Verantwortlicher</h2>
        <p>Verantwortlich für die Datenverarbeitung ist der Betreiber von
        Shape&nbsp;Miner. Kontakt: <a href="mailto:support@shapeminer.com">support@shapeminer.com</a>.</p>

        <h2>2. Welche Daten wir verarbeiten</h2>
        <ul>
            <li>Kontodaten: Vorname, Nachname, E-Mail-Adresse, Benutzername (verschlüsselt gespeichert)</li>
            <li>Anmeldedaten: Passwort (ausschließlich als Argon2id-Hash, nie im Klartext)</li>
            <li>Optionale Zwei-Faktor-Authentifizierung (verschlüsseltes TOTP-Secret)</li>
            <li>Einwilligungen (AGB/Datenschutz) mit Zeitstempel und gehashter IP</li>
            <li>Sicherheits-Metadaten zum Schutz vor Missbrauch (z. B. Fehlversuchszähler)</li>
        </ul>

        <h2>3. Zwecke und Rechtsgrundlage</h2>
        <p>Die Verarbeitung dient der Bereitstellung des spielübergreifenden
        Shape-Miner-Kontos (Art.&nbsp;6 Abs.&nbsp;1 lit.&nbsp;b DSGVO – Vertragserfüllung)
        sowie der Sicherheit des Dienstes (lit.&nbsp;f – berechtigtes Interesse).</p>

        <h2>4. Verschlüsselung &amp; Sicherheit</h2>
        <p>Personenbezogene Daten werden mit AES-256-GCM verschlüsselt gespeichert.
        Passwörter werden mit Argon2id gehasht. Die Übertragung erfolgt ausschließlich
        über HTTPS.</p>

        <h2>5. Speicherdauer</h2>
        <p>Kontodaten werden bis zur Löschung des Kontos gespeichert. Bei Löschung
        werden alle zugehörigen Daten unwiderruflich entfernt.</p>

        <h2>6. Ihre Rechte</h2>
        <p>Sie haben das Recht auf Auskunft, Berichtigung, Löschung, Einschränkung,
        Datenübertragbarkeit und Widerspruch. Die Löschung Ihres Kontos können Sie
        jederzeit selbst unter „Account löschen" vornehmen.</p>

        <h2>7. Kontakt</h2>
        <p>Bei Fragen zum Datenschutz:
        <a href="mailto:support@shapeminer.com">support@shapeminer.com</a></p>
    </div>

    <div class="auth-fuss" style="margin-top: 24px;">
        <a href="/registrieren">Zurück zur Registrierung</a>
    </div>
</div>
<?php layout_fuss(); ?>
