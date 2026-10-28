#!/usr/bin/env bash
# =============================================================================
# bin/post-deploy.sh
# -----------------------------------------------------------------------------
# Commandes à exécuter sur le serveur après avoir uploadé le dossier de release
# et créé .env.prod.local.
#
# Usage : depuis la racine du projet sur le serveur
#   bash bin/post-deploy.sh
#
# Le script peut être relancé à chaque mise à jour : il n'écrase jamais les
# données existantes (sites, catégories, comptes).
# =============================================================================

set -e

echo ""
echo "==> Studio Photo/Vidéos : post-déploiement"
echo ""

# 1. Vérifier que .env.prod.local existe
if [ ! -f .env.prod.local ]; then
    echo "ERREUR : .env.prod.local introuvable."
    echo "Copiez .env.prod.local.example en .env.prod.local et remplissez vos vraies valeurs."
    exit 1
fi

# 2. Vérifier que APP_SECRET a été changé
if grep -q "A_CHANGER_AVEC_UNE_VRAIE_VALEUR_ALEATOIRE" .env.prod.local; then
    echo "ERREUR : APP_SECRET n'a pas été modifié dans .env.prod.local"
    echo "Générez un secret avec : php -r \"echo bin2hex(random_bytes(32));\""
    exit 1
fi

# 3. Charger l'env de production
export APP_ENV=prod
export APP_DEBUG=0

# 4. Permissions sur les dossiers writables
echo "==> Permissions sur var/ et public/uploads/"
mkdir -p var/cache var/log var/client-galleries
mkdir -p public/uploads/photos public/uploads/articles public/uploads/avatars public/uploads/before-after public/uploads/products
chmod -R 775 var public/uploads 2>/dev/null || true

# 5. Cache
echo "==> Préparation du cache Symfony"
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

# 6. Assets des bundles (CSS/JS de l'admin EasyAdmin)
echo "==> Assets de l'admin"
php bin/console assets:install public --env=prod --no-debug

# 7. Migrations Doctrine
echo "==> Migration de la base de données"
php bin/console doctrine:migrations:migrate --env=prod --no-interaction --allow-no-migration

# 8. Données de base (ne crée que ce qui manque, n'écrase jamais l'existant)
#    Les noms de domaine des deux sites viennent de SITE_PHOTO_DOMAIN et SITE_VIDEO_DOMAIN
#    dans .env.prod.local (ex. photo.mon-studio.com et video.mon-studio.com : un seul nom
#    de domaine suffit, avec deux sous-domaines). Sans eux, valeurs par défaut à corriger dans l'admin.
env_value() {
    grep -E "^$1=" .env.prod.local | tail -n 1 | cut -d= -f2- | tr -d "\"'\r"
}
SITE_PHOTO_DOMAIN="${SITE_PHOTO_DOMAIN:-$(env_value SITE_PHOTO_DOMAIN)}"
SITE_VIDEO_DOMAIN="${SITE_VIDEO_DOMAIN:-$(env_value SITE_VIDEO_DOMAIN)}"

sites_args=(--only-missing --env=prod --no-interaction)
if [ -n "$SITE_PHOTO_DOMAIN" ]; then
    sites_args+=("--photo-domain=$SITE_PHOTO_DOMAIN")
fi
if [ -n "$SITE_VIDEO_DOMAIN" ]; then
    sites_args+=("--video-domain=$SITE_VIDEO_DOMAIN")
fi

echo "==> Données de base (sites Photo/Vidéo, catégories vidéo)"
php bin/console app:install:sites "${sites_args[@]}"
php bin/console app:install:video-categories --only-missing --env=prod --no-interaction

# 9. Compte administrateur : créé seulement s'il n'y en a encore aucun.
#    Sans ADMIN_EMAIL / ADMIN_PASSWORD dans .env.prod.local, la commande vous les demande
#    (mot de passe masqué, 12 caractères minimum).
echo "==> Compte administrateur"
php bin/console app:create-admin --if-none --env=prod

# 10. Lien symbolique public/ si nécessaire (hébergement où DocumentRoot != public/)
# Décommentez si votre hébergeur sert depuis la racine du domaine :
# ln -sf public/* . 2>/dev/null || true

echo ""
echo "==> Déploiement terminé !"
echo ""
echo "À vérifier maintenant :"
echo "  - https://votre-domaine.fr          → le site répond"
echo "  - https://votre-domaine.fr/admin    → connexion avec le compte créé ci-dessus"
echo "  - Admin > Sites : vérifier les noms de domaine des sites Photo et Vidéo (SITE_PHOTO_DOMAIN / SITE_VIDEO_DOMAIN)"
echo "  - HTTPS forcé (décommentez les lignes RewriteRule dans public/.htaccess)"
echo ""
echo "Pensez aussi à retirer ADMIN_PASSWORD de .env.prod.local une fois le compte créé."
echo ""
