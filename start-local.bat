@echo off
echo ========================================
echo   Demarrage de Beehive Lodge (Local)
echo ========================================
echo.

:: Verifier si PHP est installe
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERREUR: PHP n'est pas installe ou pas dans le PATH
    echo.
    echo Solutions:
    echo 1. Installer XAMPP: https://www.apachefriends.org/
    echo 2. Ou installer PHP: https://www.php.net/downloads
    echo 3. Ou utiliser Python: python -m http.server 8000
    pause
    exit
)

echo PHP detecte - Version:
php --version | findstr /C:"PHP"
echo.

:: Creer le dossier logs s'il n'existe pas
if not exist logs mkdir logs
echo Dossier logs cree/verifie
echo.

:: Demarrer le serveur PHP
echo Demarrage du serveur sur http://localhost:8000
echo.
echo CTRL+C pour arreter le serveur
echo ========================================
echo.

:: Ouvrir le navigateur (optionnel)
timeout /t 2 /nobreak >nul
start http://localhost:8000

:: Demarrer le serveur
php -S localhost:8000
