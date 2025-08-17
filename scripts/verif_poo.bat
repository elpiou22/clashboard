:: verif_poo.bat
@echo off
cd ..
php vendor/bin/phpstan analyse src tests --level=max
cd scripts


