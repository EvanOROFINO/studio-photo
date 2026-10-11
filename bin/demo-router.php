<?php

/**
 * Routeur du serveur PHP intégré pour partager une démo publique (tunnel Cloudflare, etc.).
 *
 * Usage : php -S 127.0.0.1:8001 -t public bin/demo-router.php
 *
 *  - force le mode production (ni profiler, ni page d'erreur détaillée) ;
 *  - bloque l'admin, la connexion, le profiler et les webhooks : la démo est en lecture seule ;
 *  - signale à Symfony qu'on est en HTTPS derrière un proxy (X-Forwarded-Proto / CF-Visitor) ;
 *  - demande aux moteurs de recherche de ne pas indexer la démo.
 *
 * Ce routeur n'est PAS fait pour la vraie production : utiliser un vrai serveur web.
 */

$root = dirname(__DIR__);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

header('X-Robots-Tag: noindex, nofollow');

// Chemins suspects : traversée de répertoire, octet nul, fichiers cachés (.env, .git…)
if (str_contains($path, "\0") || preg_match('#(^|/)\.#', $path)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Page non disponible dans la démo.\n";

    return true;
}

// Zones réservées au propriétaire : jamais exposées dans la démo publique
foreach (['/admin', '/login', '/logout', '/_profiler', '/_wdt', '/_error', '/webhook'] as $blocked) {
    if ($path === $blocked || str_starts_with($path, $blocked.'/')) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo "Page non disponible dans la démo.\n";

        return true;
    }
}

// Fichiers statiques (css, images…) : servis directement par le serveur intégré
if ($path !== '/' && is_file($root.'/public'.$path) && !str_ends_with($path, '.php')) {
    return false;
}

// Mode production + secret aléatoire persistant (dans var/, ignoré par git)
$secretFile = $root.'/var/demo-secret.txt';
if (!is_file($secretFile)) {
    @file_put_contents($secretFile, bin2hex(random_bytes(32)));
}
$env = [
    'APP_ENV' => 'prod',
    'APP_DEBUG' => '0',
    'APP_SECRET' => trim((string) @file_get_contents($secretFile)),
];
foreach ($env as $key => $value) {
    $_SERVER[$key] = $_ENV[$key] = $value;
    putenv($key.'='.$value);
}

// Derrière le tunnel : requête en HTTPS et nom d'hôte public
$visitor = (string) ($_SERVER['HTTP_CF_VISITOR'] ?? '');
if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' || str_contains($visitor, '"https"')) {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}
if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_HOST'] = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0]);
}

$_SERVER['SCRIPT_FILENAME'] = $root.'/public/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
chdir($root.'/public');

require $root.'/public/index.php';

return true;
