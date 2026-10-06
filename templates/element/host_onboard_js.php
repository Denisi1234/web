<?php
/**
 * Shared wizard JS: cover-photo upload + Mapbox location picker + room
 * categories + amenities split (host onboarding + lodge edit).
 * Served as ONE inline script assembled from small part files (each under
 * the 300-line cap) so all parts share a single scope exactly like before —
 * zero behavior drift. Edit the part files, both pages follow.
 */
$__obParts = ['host-onboard-upload.js', 'host-onboard-map-search.js', 'host-onboard-map-util.js', 'host-onboard-map-main.js', 'host-onboard-rooms.js'];
$__obJs = '';
foreach ($__obParts as $__obPart) {
    $__obFile = WWW_ROOT . 'assets' . DS . 'js' . DS . $__obPart;
    if (is_readable($__obFile)) {
        $__obJs .= "\n" . (string)file_get_contents($__obFile);
    }
}
echo $this->Html->scriptBlock($__obJs);
