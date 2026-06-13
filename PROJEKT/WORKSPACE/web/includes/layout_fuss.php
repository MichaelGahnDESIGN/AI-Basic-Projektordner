<?php

declare(strict_types=1);

/**
 * Aufgabe: HTML-Abschluss für alle SMU-Web-Seiten.
 */
function layout_fuss(): void
{
    ?>
</main>

<footer class="seiten-fuss">
    <span>Shape Miner · <a href="https://deluxe.shapeminer.com">Spiel starten</a></span>
    <span>
        v0.0.1
        <button type="button" class="cache-leeren" data-cache-leeren>Cache leeren</button>
    </span>
</footer>

</body>
</html>
<?php
}
