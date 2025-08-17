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
- `./scripts/starter.bat`

## 6️⃣ Lancement des tests
#### PhpStan *(Vérification POO + incohérences de code + pas de duplication de code + méthodes cohérentes + typage des variables)*
- `./scripts/verif_poo.bat`
#### PhpUnit *(Tests Unitaires)*
- `./scripts/tests_unitaires.bat`

## ℹ️ Si une nouvelle mise à jour est disponible:
- `./scripts/repo_maj.bat`







