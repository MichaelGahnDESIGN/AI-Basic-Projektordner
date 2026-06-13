// Erzeugt den TOTP-QR-Code LOKAL im Browser.
// Das Secret (in der otpauth-URL) verlässt damit niemals den Server an Dritte
// (früher: api.qrserver.com). Liest die URL aus dem data-Attribut.
(function () {
  'use strict';
  var el = document.getElementById('totp-qr');
  if (!el || typeof QRCode === 'undefined') {
    return;
  }
  var uri = el.getAttribute('data-otpauth');
  if (!uri) {
    return;
  }
  // davidshimjs/qrcodejs rendert in das Element (Canvas + data:-Bild-Fallback).
  new QRCode(el, {
    text: uri,
    width: 176,
    height: 176,
    colorDark: '#05060E',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
  });
})();
