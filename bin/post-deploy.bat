@echo off
REM ============================================================================
REM bin\post-deploy.bat
REM Equivalent Windows du script post-deploy.sh, utile pour tester localement.
REM Usage : depuis la racine du projet
REM   bin\post-deploy.bat
REM ============================================================================

set APP_ENV=prod
set APP_DEBUG=0

echo.
echo ==^> Studio Photo : post-deploiement
echo.

if not exist .env.prod.local (
    echo ERREUR : .env.prod.local introuvable.
    echo Copiez .env.prod.local.example en .env.prod.local et remplissez vos vraies valeurs.
    exit /b 1
)

echo ==^> Preparation du cache Symfony
php bin\console cache:clear --env=prod --no-debug
php bin\console cache:warmup --env=prod --no-debug

echo ==^> Migration de la base de donnees
php bin\console doctrine:migrations:migrate --env=prod --no-interaction

echo.
echo ==^> Deploiement termine !
echo.
