# Guide de déploiement de l’application

## 1️⃣ Pré-requis
- Php > 8.2
- Composer
- Docker

## 2️⃣ Installation
- `composer install --no-dev --optimize-autoloader`
@todo

## 3️⃣ Configuration base de données / variable d'environnement
@todo


## 4️⃣ Préparation de la base de données
- `php bin/console doctrine:database:create`
- `php bin/console doctrine:migrations:migrate`
- `php bin/console doctrine:fixtures:load`

## 5️⃣ Lancement de l'application
#### Sous Windows:
- `./starter.bat`
#### Sous Linux / MacOs:
- `cd public`
- `php -S localhost:8000`

## 6️⃣ Lancement des tests
#### PhpStan *(Vérification POO + incohérences de code + pas de duplication de code + méthodes cohérentes + typage des variables)*
- `php vendor/bin/phpstan analyse src tests --level=max`
#### PhpUnit *(Tests Unitaires)*
- `php bin/phpunit`

## ℹ️ Si une nouvelle mise à jour est disponible:
- `git pull origin main`
- `composer install --no-dev --optimize-autoloader`
- `php bin/console doctrine:migrations:migrate`
- `php bin/console cache:clear --env=prod`







