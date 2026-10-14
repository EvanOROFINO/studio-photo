<?php

/**
 * Routeur du serveur PHP intégré pour le DÉVELOPPEMENT LOCAL.
 *
 * Usage : php -S 127.0.0.1:8000 -t public bin/dev-router.php
 *
 * Sans routeur, le serveur intégré répond 404 à toute adresse qui finit par une extension
 * (.jpg, .webp…) quand le fichier n'existe pas encore, sans jamais appeler Symfony : les
 * miniatures générées à la demande (/media/cache/resolve/…/photo.jpg) ne fonctionneraient donc pas.
 *
 * Les fichiers qui existent sont servis tels quels ; tout le reste passe par le contrôleur frontal.
 * Apache/Nginx font déjà cela en production (réécriture d'URL).
 */

$root = dirname(__DIR__);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

// Fichier existant (css, images, miniatures déjà générées…) : le serveur intégré le sert directement
if ($path !== '/' && !str_contains($path, '..') && is_file($root.'/public'.$path) && !str_ends_with($path, '.php')) {
    return false;
}

$_SERVER['SCRIPT_FILENAME'] = $root.'/public/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
chdir($root.'/public');

require $root.'/public/index.php';

return true;
