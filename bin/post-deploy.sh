#!/usr/bin/env bash
# =============================================================================
# bin/post-deploy.sh
# -----------------------------------------------------------------------------
# Commandes à exécuter sur le serveur après avoir uploadé le dossier de release
# et créé .env.prod.local.
#
# Usage : depuis la racine du projet sur le serveur
#   bash bin/post-deploy.sh
# =============================================================================

set -e

echo ""
echo "==> Studio Photo : post-déploiement"
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
mkdir -p public/uploads/photos public/uploads/articles
chmod -R 775 var public/uploads 2>/dev/null || true

# 5. Cache warmup
echo "==> Préparation du cache Symfony"
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

# 6. Migrations Doctrine
echo "==> Migration de la base de données"
php bin/console doctrine:migrations:migrate --env=prod --no-interaction

# 7. Créer un compte admin si la table user est vide
ADMIN_COUNT=$(php bin/console doctrine:query:sql "SELECT COUNT(*) AS n FROM \`user\`" --env=prod 2>&1 | grep -oP '\d+' | tail -1)
if [ "$ADMIN_COUNT" = "0" ]; then
    echo "==> Aucun admin trouvé. Création d'un compte par défaut..."
    php bin/console security:hash-password >/dev/null 2>&1 || true
    echo ""
    echo "IMPORTANT : créez votre admin manuellement en suivant ces étapes :"
    echo "  1. Générez un hash de mot de passe :"
    echo "     php bin/console security:hash-password --env=prod"
    echo "  2. Insérez en BDD :"
    echo "     INSERT INTO \`user\` (email, roles, password, full_name) VALUES"
    echo "     ('votre.email@exemple.fr', '[\"ROLE_ADMIN\"]', 'HASH_OBTENU', 'Votre Nom');"
    echo ""
fi

# 8. Lien symbolique public/ si nécessaire (hébergement où DocumentRoot != public/)
# Décommentez si votre hébergeur sert depuis la racine du domaine :
# ln -sf public/* . 2>/dev/null || true

echo ""
echo "==> Déploiement terminé !"
echo ""
echo "Pensez à vérifier :"
echo "  - https://votre-domaine.fr → le site répond"
echo "  - https://votre-domaine.fr/admin → page de login admin accessible"
echo "  - HTTPS forcé (décommentez les lignes RewriteRule dans public/.htaccess)"
echo ""
