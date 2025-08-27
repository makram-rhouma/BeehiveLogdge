#!/bin/bash

echo "========================================"
echo "  Démarrage de Beehive Lodge (Local)"
echo "========================================"
echo

# Vérifier si PHP est installé
if ! command -v php &> /dev/null; then
    echo "ERREUR: PHP n'est pas installé"
    echo
    echo "Solutions:"
    echo "1. Ubuntu/Debian: sudo apt install php"
    echo "2. macOS: brew install php"
    echo "3. CentOS/RHEL: sudo yum install php"
    echo "4. Ou utiliser Python: python3 -m http.server 8000"
    exit 1
fi

echo "PHP détecté - Version:"
php --version | head -n1
echo

# Créer le dossier logs s'il n'existe pas
mkdir -p logs
echo "Dossier logs créé/vérifié"
echo

# Démarrer le serveur PHP
echo "Démarrage du serveur sur http://localhost:8000"
echo
echo "CTRL+C pour arrêter le serveur"
echo "========================================"
echo

# Ouvrir le navigateur (optionnel)
if command -v xdg-open &> /dev/null; then
    sleep 2 && xdg-open http://localhost:8000 &
elif command -v open &> /dev/null; then
    sleep 2 && open http://localhost:8000 &
fi

# Démarrer le serveur
php -S localhost:8000
