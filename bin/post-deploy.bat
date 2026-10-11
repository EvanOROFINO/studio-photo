@echo off
REM ============================================================================
REM bin\post-deploy.bat
REM Equivalent Windows du script post-deploy.sh, utile pour tester localement.
REM Usage : depuis la racine du projet
REM   bin\post-deploy.bat
REM Peut etre relance a chaque mise a jour : n'ecrase jamais les donnees.
REM ============================================================================

set APP_ENV=prod
set APP_DEBUG=0

echo.
echo ==^> Studio Photo/Videos : post-deploiement
echo.

if not exist .env.prod.local (
    echo ERREUR : .env.prod.local introuvable.
    echo Copiez .env.prod.local.example en .env.prod.local et remplissez vos vraies valeurs.
    exit /b 1
)

echo ==^> Preparation du cache Symfony
php bin\console cache:clear --env=prod --no-debug
php bin\console cache:warmup --env=prod --no-debug

echo ==^> Assets de l'admin
php bin\console assets:install public --env=prod --no-debug

echo ==^> Migration de la base de donnees
php bin\console doctrine:migrations:migrate --env=prod --no-interaction --allow-no-migration

echo ==^> Donnees de base (ne cree que ce qui manque)
php bin\console app:install:sites --only-missing --env=prod --no-interaction
php bin\console app:install:video-categories --only-missing --env=prod --no-interaction

echo ==^> Compte administrateur (cree seulement s'il n'y en a aucun)
php bin\console app:create-admin --if-none --env=prod

echo.
echo ==^> Deploiement termine !
echo.
