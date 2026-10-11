#!/bin/bash
# =============================================================================
# docker-entrypoint.sh
# -----------------------------------------------------------------------------
# Exécuté au démarrage du conteneur, avant Apache :
#   1. attend la base de données (60 s max, sinon le démarrage échoue)
#   2. applique les migrations (un échec arrête le démarrage : jamais de site
#      en ligne sur un schéma de base incomplet)
#   3. installe les données de base SANS rien écraser (sites, catégories vidéo)
#   4. crée le compte administrateur au premier démarrage si ADMIN_EMAIL et
#      ADMIN_PASSWORD sont définis (sans effet dès qu'un compte existe)
#   5. prépare le cache
#
# Aucune donnée de démonstration n'est chargée en production.
#
# Variables optionnelles :
#   ADMIN_EMAIL, ADMIN_PASSWORD   premier compte admin (12 caractères minimum)
#   SITE_PHOTO_DOMAIN             domaine du site Photo  (ex. mon-studio.fr)
#   SITE_VIDEO_DOMAIN             domaine du site Vidéo  (ex. mon-studio-video.fr)
# =============================================================================

set -e

cd /var/www/html

if [ -n "$DATABASE_URL" ]; then
    echo "[entrypoint] En attente de la base de données…"
    ready=0
    for i in $(seq 1 30); do
        if php bin/console doctrine:query:sql "SELECT 1" --env=prod >/dev/null 2>&1; then
            ready=1
            break
        fi
        sleep 2
    done
    if [ "$ready" != "1" ]; then
        echo "[entrypoint] ERREUR : base de données injoignable après 60 s."
        exit 1
    fi
    echo "[entrypoint] Base de données prête."

    echo "[entrypoint] Migrations…"
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod --allow-no-migration

    echo "[entrypoint] Données de base (sans écraser l'existant)…"
    sites_args=(--only-missing --env=prod --no-interaction)
    if [ -n "$SITE_PHOTO_DOMAIN" ]; then
        sites_args+=("--photo-domain=$SITE_PHOTO_DOMAIN")
    fi
    if [ -n "$SITE_VIDEO_DOMAIN" ]; then
        sites_args+=("--video-domain=$SITE_VIDEO_DOMAIN")
    fi
    php bin/console app:install:sites "${sites_args[@]}"
    php bin/console app:install:video-categories --only-missing --env=prod --no-interaction

    if [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
        php bin/console app:create-admin --if-none --env=prod --no-interaction
    else
        echo "[entrypoint] ADMIN_EMAIL / ADMIN_PASSWORD non définis : aucun compte admin créé."
    fi
else
    echo "[entrypoint] DATABASE_URL non défini : migrations et données de base ignorées."
fi

# Préparation du cache
echo "[entrypoint] Préparation du cache…"
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

# Les dossiers d'envoi doivent appartenir à Apache (volumes montés en root)
chown -R www-data:www-data var public/uploads public/media 2>/dev/null || true

# Passage la main à Apache
echo "[entrypoint] Démarrage d'Apache sur le port ${PORT:-8080}…"
exec "$@"
