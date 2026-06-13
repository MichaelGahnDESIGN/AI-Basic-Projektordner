/*
 * Shape Miner — endlos zoomender Sternen-Hintergrund (Warp-Effekt).
 * Leichtgewichtig, ohne Abhängigkeiten. Legt ein fixiertes Canvas HINTER
 * den Seiteninhalt und fliegt sanft durch ein Sternenfeld.
 *
 * Respektiert prefers-reduced-motion (dann statisches Feld, keine Bewegung).
 */
(function () {
  'use strict';
  if (document.getElementById('sternenfeld')) return;

  var c = document.createElement('canvas');
  c.id = 'sternenfeld';
  c.setAttribute('aria-hidden', 'true');
  c.style.cssText =
    'position:fixed;inset:0;width:100%;height:100%;z-index:-1;display:block;' +
    'background:radial-gradient(120% 120% at 50% 40%,#0A1430 0%,#05060E 70%);pointer-events:none';
  document.body.appendChild(c);

  var ctx = c.getContext('2d');
  var w, h, cx, cy, dpr;
  var sterne = [];
  var ANZAHL = 0;
  var reduziert =
    window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function mass() {
    dpr = Math.min(window.devicePixelRatio || 1, 2);
    w = c.clientWidth;
    h = c.clientHeight;
    c.width = Math.floor(w * dpr);
    c.height = Math.floor(h * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    cx = w / 2;
    cy = h / 2;
    // Dichte an Fläche koppeln (Performance + gleichmäßige Optik).
    ANZAHL = Math.min(420, Math.max(140, Math.round((w * h) / 4200)));
    bauen();
  }

  function neu(s) {
    s.x = (Math.random() - 0.5) * w;
    s.y = (Math.random() - 0.5) * h;
    s.z = Math.random() * w;
    s.pz = s.z;
    return s;
  }

  function bauen() {
    sterne = [];
    for (var i = 0; i < ANZAHL; i++) sterne.push(neu({}));
  }

  var tempo = reduziert ? 0 : 0.0034;

  function zeichne() {
    // Stärkeres Nachleuchten = kurze, ruhige Schweife (kein hektischer Warp).
    ctx.fillStyle = 'rgba(5,6,14,0.30)';
    ctx.fillRect(0, 0, w, h);

    for (var i = 0; i < sterne.length; i++) {
      var s = sterne[i];
      s.pz = s.z;
      s.z -= w * tempo;
      if (s.z < 1) {
        neu(s);
        s.z = w;
        s.pz = s.z;
        continue;
      }
      var k = 128 / s.z;
      var x = cx + s.x * k;
      var y = cy + s.y * k;
      if (x < 0 || x > w || y < 0 || y > h) continue;

      var pk = 128 / s.pz;
      var px = cx + s.x * pk;
      var py = cy + s.y * pk;

      var naehe = 1 - s.z / w; // 0 fern … 1 nah
      var r = naehe * 2.2 + 0.3;
      var alpha = Math.min(1, naehe * 1.1 + 0.15);

      // Cyan-getönte Sterne; nahe Sterne ziehen einen kurzen Schweif.
      ctx.strokeStyle = 'rgba(150,228,255,' + alpha + ')';
      ctx.lineWidth = r;
      ctx.beginPath();
      ctx.moveTo(px, py);
      ctx.lineTo(x, y);
      ctx.stroke();

      ctx.fillStyle = 'rgba(220,247,255,' + alpha + ')';
      ctx.beginPath();
      ctx.arc(x, y, r * 0.6, 0, 6.283);
      ctx.fill();
    }

    if (!reduziert) requestAnimationFrame(zeichne);
  }

  window.addEventListener('resize', mass);
  mass();
  zeichne();
  if (reduziert) zeichne(); // einmal statisch zeichnen
})();

/* ---- Cache leeren (CSP-konform, ohne Inline-Handler) --------------------- */
/* Service-Worker abmelden + Cache-Storage löschen + Hard-Reload mit Cache-Bust.*/
window.smdCacheLeeren = function () {
    var aufgaben = [];
    try {
        if ('serviceWorker' in navigator) {
            aufgaben.push(navigator.serviceWorker.getRegistrations()
                .then(function (rs) { return Promise.all((rs || []).map(function (r) { return r.unregister(); })); })
                .catch(function () {}));
        }
        if (window.caches && caches.keys) {
            aufgaben.push(caches.keys()
                .then(function (ks) { return Promise.all((ks || []).map(function (k) { return caches.delete(k); })); })
                .catch(function () {}));
        }
    } catch (e) {}
    return Promise.all(aufgaben).then(function () {
        location.replace(location.pathname + '?cb=' + Date.now());
    });
};
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-cache-leeren]').forEach(function (knopf) {
        knopf.addEventListener('click', window.smdCacheLeeren);
    });
});
