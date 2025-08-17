:: repo_maj.bat
@echo off
echo "Mise à jour du projet"
cd ..
git pull origin preprod
:: git pull origin prod

echo "Installation des dépendances"
CALL composer install --optimize-autoloader
:: composer install --no-dev --optimize-autoloader

echo "Audit de sécurité Composer"
CALL composer audit
IF %ERRORLEVEL% NEQ 0 (
    echo "[SECURITE] Vulnérabilités détectées dans les dépendances !"
    echo "Veuillez mettre à jour les packages concernés avant déploiement."
    exit /b 1
)

echo "Migration de la base"
php bin/console doctrine:migrations:migrate
php bin/console cache:clear --env=prod

echo "Nettoyage du cache"
php bin/console cache:clear --env=prod

cd scripts

