# 🚀 Beehive Lodge - Guide de Déploiement

Ce guide explique comment déployer le site web Beehive Lodge avec Docker et configurer les parties backend pour les formulaires de contact et demande de démo.

## 📋 Table des matières

- [Déploiement avec Docker (Frontend)](#déploiement-avec-docker-frontend)
- [Configuration Backend PHP (Contact & Démo)](#configuration-backend-php-contact--démo)
- [Déploiement Production](#déploiement-production)
- [Surveillance et Maintenance](#surveillance-et-maintenance)

---

## 🐳 Déploiement avec Docker (Frontend)

### Prérequis

- Docker installé sur votre système
- Docker Compose (optionnel mais recommandé)
- Port 80 disponible

### 1. Construction de l'image Docker

```bash
# Aller dans le répertoire du projet
cd "C:\Users\makrem\Desktop\Site vetrine\Beehive Lodge"

# Construire l'image Docker
docker build -t beehive-lodge:latest .

# Ou avec un tag spécifique
docker build -t beehive-lodge:1.0 .
```

### 2. Lancement du conteneur

```bash
# Lancement simple
docker run -d \
  --name beehive-lodge \
  -p 80:80 \
  beehive-lodge:latest

# Lancement avec volumes pour les logs
docker run -d \
  --name beehive-lodge \
  -p 80:80 \
  -v $(pwd)/logs:/var/log/nginx \
  beehive-lodge:latest

# Lancement avec configuration personnalisée
docker run -d \
  --name beehive-lodge \
  -p 80:80 \
  -v $(pwd)/nginx-custom.conf:/etc/nginx/nginx.conf:ro \
  -v $(pwd)/logs:/var/log/nginx \
  beehive-lodge:latest
```

### 3. Vérification

```bash
# Vérifier que le conteneur fonctionne
docker ps

# Voir les logs
docker logs beehive-lodge

# Tester l'application
curl http://localhost
curl http://localhost/health
```

### 4. Docker Compose (Recommandé)

Créer un fichier `docker-compose.yml`:

```yaml
version: '3.8'

services:
  beehive-lodge:
    build: .
    container_name: beehive-lodge
    ports:
      - "80:80"
    volumes:
      - ./logs:/var/log/nginx
      - ./data:/usr/share/nginx/html/logs
    environment:
      - TZ=Europe/Paris
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 30s

  # Service PHP-FPM pour les formulaires (optionnel)
  php:
    image: php:8.1-fpm-alpine
    container_name: beehive-lodge-php
    volumes:
      - ./api:/var/www/html/api
      - ./logs:/var/www/html/logs
    environment:
      - TZ=Europe/Paris
    restart: unless-stopped
```

Puis :

```bash
# Lancer avec Docker Compose
docker-compose up -d

# Arrêter
docker-compose down

# Voir les logs
docker-compose logs -f
```

---

## 🔧 Configuration Backend PHP (Contact & Démo)

### Option 1: Serveur PHP séparé

#### Installation PHP

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.1 php8.1-fpm php8.1-curl php8.1-json php8.1-mbstring

# CentOS/RHEL
sudo yum install php php-fpm php-curl php-json php-mbstring

# Windows (XAMPP)
# Télécharger et installer XAMPP depuis https://www.apachefriends.org/
```

#### Configuration des emails

1. **Éditer les fichiers PHP** :

```bash
# Éditer le fichier de configuration contact
nano api/contact.php

# Modifier les paramètres SMTP :
$config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'votre-email@gmail.com',
    'smtp_password' => 'votre-mot-de-passe-app-gmail',
    'from_email' => 'votre-email@gmail.com',
    'from_name' => 'Beehive Lodge',
    'to_email' => 'info@beechivelodge.com',
];
```

2. **Configuration Gmail** :

```bash
# 1. Activer l'authentification à 2 facteurs
# 2. Générer un mot de passe d'application :
#    - Aller sur https://myaccount.google.com/security
#    - Sélectionner "Mots de passe des applications"
#    - Générer un nouveau mot de passe pour "Mail"
#    - Utiliser ce mot de passe dans smtp_password
```

#### Lancement du serveur PHP

```bash
# Serveur de développement PHP
cd "C:\Users\makrem\Desktop\Site vetrine\Beehive Lodge"
php -S localhost:8080

# Avec Nginx + PHP-FPM (Production)
sudo systemctl start php8.1-fpm
sudo systemctl start nginx
```

### Option 2: Docker avec PHP

Créer un `docker-compose-full.yml` :

```yaml
version: '3.8'

services:
  nginx:
    build: .
    container_name: beehive-lodge-nginx
    ports:
      - "80:80"
    volumes:
      - ./api:/usr/share/nginx/html/api
      - ./logs:/var/log/nginx
    depends_on:
      - php
    restart: unless-stopped

  php:
    image: php:8.1-fpm-alpine
    container_name: beehive-lodge-php
    volumes:
      - ./api:/var/www/html
      - ./logs:/var/www/html/logs
    environment:
      - TZ=Europe/Paris
    restart: unless-stopped
    # Installer les extensions nécessaires
    command: >
      sh -c "docker-php-ext-install curl json mbstring && php-fpm"
```

Lancer :

```bash
docker-compose -f docker-compose-full.yml up -d
```

### Option 3: Service d'email tiers (EmailJS)

Pour une solution frontend uniquement :

1. **S'inscrire sur EmailJS** : https://www.emailjs.com/
2. **Modifier `js/contact.js`** :

```javascript
// Décommenter et configurer EmailJS
(function() {
    emailjs.init("VOTRE_PUBLIC_KEY");
})();

// Dans la fonction submitToBackend :
return emailjs.send(
    'VOTRE_SERVICE_ID',
    'VOTRE_TEMPLATE_ID', 
    data,
    'VOTRE_PUBLIC_KEY'
);
```

---

## 🌐 Déploiement Production

### 1. Hébergement Cloud

#### AWS (Amazon Web Services)

```bash
# 1. Créer une instance EC2
# 2. Installer Docker
sudo yum update -y
sudo yum install -y docker
sudo service docker start

# 3. Déployer l'application
git clone YOUR_REPOSITORY
cd beehive-lodge
docker build -t beehive-lodge .
docker run -d -p 80:80 beehive-lodge

# 4. Configurer un Load Balancer (ALB) si nécessaire
```

#### Google Cloud Platform

```bash
# 1. Créer un projet GCP
gcloud projects create beehive-lodge-project

# 2. Activer Cloud Run
gcloud services enable run.googleapis.com

# 3. Déployer sur Cloud Run
gcloud builds submit --tag gcr.io/beehive-lodge-project/beehive-lodge
gcloud run deploy --image gcr.io/beehive-lodge-project/beehive-lodge --platform managed
```

#### DigitalOcean Droplet

```bash
# 1. Créer un Droplet Ubuntu 20.04
# 2. Installer Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# 3. Déployer l'application
git clone YOUR_REPOSITORY
cd beehive-lodge
docker-compose up -d

# 4. Configurer le pare-feu
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### 2. Configuration SSL (HTTPS)

#### Avec Let's Encrypt (Gratuit)

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-nginx

# Obtenir le certificat
sudo certbot --nginx -d beechivelodge.com -d www.beechivelodge.com

# Renouvellement automatique
sudo crontab -e
# Ajouter : 0 12 * * * /usr/bin/certbot renew --quiet
```

#### Docker avec SSL

Modifier le `docker-compose.yml` :

```yaml
version: '3.8'

services:
  nginx:
    build: .
    container_name: beehive-lodge
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./ssl:/etc/ssl/certs
      - ./nginx-ssl.conf:/etc/nginx/nginx.conf
    restart: unless-stopped

  certbot:
    image: certbot/certbot
    container_name: certbot
    volumes:
      - ./ssl:/etc/letsencrypt
    command: certonly --webroot -w /var/www/certbot -d beechivelodge.com
```

### 3. Configuration DNS

```bash
# Enregistrements DNS nécessaires :
# A    beechivelodge.com       → IP_DU_SERVEUR
# A    www.beechivelodge.com   → IP_DU_SERVEUR
# CNAME www                   → beechivelodge.com
```

---

## 📊 Surveillance et Maintenance

### 1. Monitoring

#### Avec Docker

```bash
# Vérifier l'état des conteneurs
docker ps
docker stats

# Logs en temps réel
docker logs -f beehive-lodge

# Health checks
curl http://localhost/health
```

#### Script de monitoring automatique

Créer `monitor.sh` :

```bash
#!/bin/bash
# Script de monitoring pour Beehive Lodge

# Vérifier si le conteneur fonctionne
if ! docker ps | grep beehive-lodge > /dev/null; then
    echo "❌ Conteneur arrêté, redémarrage..."
    docker restart beehive-lodge
fi

# Vérifier la réponse HTTP
if ! curl -f http://localhost/health > /dev/null 2>&1; then
    echo "❌ Site non accessible, redémarrage..."
    docker restart beehive-lodge
fi

# Nettoyer les logs anciens
find ./logs -name "*.log" -mtime +30 -delete

echo "✅ Vérification terminée - $(date)"
```

Programmer dans cron :

```bash
# Exécuter toutes les 5 minutes
crontab -e
# Ajouter : */5 * * * * /path/to/monitor.sh
```

### 2. Sauvegarde

#### Script de sauvegarde

```bash
#!/bin/bash
# Sauvegarde des données Beehive Lodge

BACKUP_DIR="/backups/beehive-lodge"
DATE=$(date +%Y%m%d_%H%M%S)

# Créer le répertoire de sauvegarde
mkdir -p $BACKUP_DIR

# Sauvegarder les fichiers web
tar -czf "$BACKUP_DIR/website_$DATE.tar.gz" /path/to/beehive-lodge

# Sauvegarder les logs de formulaires
cp -r ./logs "$BACKUP_DIR/logs_$DATE"

# Garder seulement les 7 dernières sauvegardes
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "✅ Sauvegarde terminée - $DATE"
```

### 3. Mises à jour

#### Mise à jour de l'application

```bash
# 1. Récupérer les dernières modifications
git pull origin main

# 2. Reconstruire l'image
docker build -t beehive-lodge:latest .

# 3. Redémarrer avec la nouvelle image
docker-compose down
docker-compose up -d

# 4. Vérifier le fonctionnement
curl http://localhost/health
```

#### Mise à jour des dépendances

```bash
# Mettre à jour l'image de base
docker pull nginx:alpine

# Reconstruire
docker build --no-cache -t beehive-lodge:latest .
```

---

## 🔧 Commandes Utiles

### Docker

```bash
# Construire et lancer
docker build -t beehive-lodge . && docker run -d -p 80:80 beehive-lodge

# Voir les logs en temps réel
docker logs -f beehive-lodge

# Entrer dans le conteneur
docker exec -it beehive-lodge sh

# Arrêter et supprimer
docker stop beehive-lodge && docker rm beehive-lodge

# Nettoyer les images inutiles
docker system prune -a
```

### Nginx

```bash
# Recharger la configuration
docker exec beehive-lodge nginx -s reload

# Tester la configuration
docker exec beehive-lodge nginx -t

# Voir les processus Nginx
docker exec beehive-lodge ps aux | grep nginx
```

### PHP (si utilisé)

```bash
# Vérifier la configuration PHP
php --ini

# Tester un script PHP
php -f api/contact.php

# Logs PHP
tail -f /var/log/php8.1-fpm.log
```

---

## 🆘 Dépannage

### Problèmes courants

1. **Port 80 occupé** :
   ```bash
   # Voir qui utilise le port
   sudo netstat -tlnp | grep :80
   # Ou utiliser un autre port
   docker run -d -p 8080:80 beehive-lodge
   ```

2. **Erreur de permissions** :
   ```bash
   sudo chown -R $USER:$USER ./logs
   chmod 755 ./logs
   ```

3. **Formulaires ne fonctionnent pas** :
   ```bash
   # Vérifier les logs PHP
   tail -f ./logs/contact_submissions.log
   # Vérifier la configuration email
   php -m | grep curl
   ```

4. **Site inaccessible** :
   ```bash
   # Vérifier le firewall
   sudo ufw status
   # Vérifier DNS
   nslookup beechivelodge.com
   ```

---

## 📞 Support

- **Email** : support@beechivelodge.com
- **Documentation** : Voir README.md
- **Issues** : Créer un ticket sur le repository

---

**Version** : 1.0  
**Dernière mise à jour** : Novembre 2024
