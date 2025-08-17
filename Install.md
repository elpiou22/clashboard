# Guide de déploiement de l’application

## 1️⃣ Pré-requis
Php > 8.2
- tester avec: `php -v`
- sinon suivre: `PHP_install.md`

Docker Desktop
- tester avec: `docker -v`
- sinon suivre: `Docker_install.md`


Composer
- tester avec `composer -v`
- sinon suivre: `Composer_install.md`
> L’app Symfony tourne en local (PHP), la BDD et le mailer tournent dans Docker.


## 2️⃣ Installation
Dans le dossier script, exécuter le fichier `repo_maj.bat`

## 3️⃣ Configuration des variables d'environnement
Déplace ou met à jour le `.env` à la racine du projet:
```yaml
APP_ENV=dev
APP_SECRET=

# Base de données (valeurs par défaut du docker-compose)
DATABASE_URL="mysql://root:clashboard@localhost:3306/clashboard?serverVersion=11.5.2-MariaDB"
MAILER_DSN="smtp://localhost:60755"
```
> Si le port 3306 est déjà utilisé, mappe 3307:3306 dans le docker-compose.yml et remplace :3306 par :3307 dans DATABASE_URL.


## 4️⃣ Démarrer l’infrastructure Docker (MariaDB + Mailpit)
À l'aide des scripts fournis:
- `.\start-stack.ps1`
Pour arrêter les conteneurs docker:
- `.\stop-stack.ps1`

Endpoints utiles :
- Mailpit UI : http://localhost:8025
- SMTP : localhost:1025
- MariaDB : 127.0.0.1:3306


## 5️⃣Préparation de la base de données
- `php bin/console doctrine:migrations:migrate -n`
- `php bin/console doctrine:fixtures:load -n`

Verifications utiles:
- `php bin/console doctrine:schema:validate`
- `php bin/console doctrine:query:sql "SELECT VERSION()"`


## 6️⃣ Lancer l’application
- `./scripts/starter.bat`

## 7️⃣ Pour accéder aux sites et ouils:
Pour accéder au site:
- `localhost:8000`

Pour accéder à la boite mail de tests en local:
- `localhost:8025`



## 8️⃣ Lancement des tests
#### PhpStan *(Vérification POO + incohérences de code + pas de duplication de code + méthodes cohérentes + typage des variables)*
- `./scripts/verif_poo.bat`
#### PhpUnit *(Tests Unitaires)*
- `./scripts/tests_unitaires.bat`

## ℹ️ Si une nouvelle mise à jour est disponible:
- `./scripts/repo_maj.bat`


## 9️⃣ FAQ:
- **Port 3306 occupé:** utiliser 3307:3306 + mettre :3307 dans DATABASE_URL.






