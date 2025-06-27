@echo off
REM Script pour mettre à jour les dépendances Composer (PHP) et npm (Node.js)

cd /d %~dp0

echo =====================================
echo   Mise à jour des dépendances PHP
=====================================
IF EXIST composer.json (
    IF EXIST composer.lock (
        echo Suppression de composer.lock pour forcer la mise à jour...
        del composer.lock
    )
    composer install
    composer update
) ELSE (
    echo Aucun fichier composer.json trouvé, saut de la mise à jour PHP.
)

echo.
echo =====================================
echo   Mise à jour des dépendances Node.js
=====================================
IF EXIST package.json (
    IF EXIST package-lock.json (
        echo Suppression de package-lock.json pour forcer la mise à jour...
        del package-lock.json
    )
    IF EXIST node_modules (
        echo Suppression du dossier node_modules...
        rmdir /s /q node_modules
    )
    npm install
    npm update
) ELSE (
    echo Aucun fichier package.json trouvé, saut de la mise à jour Node.js.
)

echo.
echo Mise à jour terminée !
pause

