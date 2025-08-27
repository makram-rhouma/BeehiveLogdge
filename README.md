# Beehive Lodge - Website Vitrine

Un site web professionnel et luxueux pour Beehive Lodge, service d'hébergement de prestige.

## 🚀 Fonctionnalités

### Pages principales
- **Accueil** (`index.html`) - Hero slider, services clés, CTA
- **À propos** (`about.html`) - Histoire, mission, équipe, timeline
- **Fonctionnalités** (`features.html`) - Services détaillés, tarifs, grille interactive
- **Blog** (`blog.html`) - Articles avec filtres par catégorie
- **Contact** (`contact.html`) - Formulaire de contact, carte Google Maps, FAQ
- **Demande de Démo** (`demo.html`) - Formulaire de réservation avec calendrier

### Caractéristiques techniques
- **Design responsive** - Adapté mobile, tablette, desktop
- **Animations fluides** - AOS animations, hover effects
- **Thème doré premium** - Couleur principale #D4A017
- **Bootstrap 5.3** - Framework CSS moderne
- **JavaScript vanilla** - Interactions et validations
- **Backend PHP** - Gestion des formulaires

## 🎨 Design

### Couleurs
- **Or principal**: `#D4A017`
- **Or foncé**: `#B8860B` 
- **Texte sombre**: `#2C3E50`
- **Texte clair**: `#6C757D`
- **Fond clair**: `#F8F9FA`

### Typographie
- **Police principale**: Poppins (Google Fonts)
- **Poids**: 300, 400, 500, 600, 700

### Effets visuels
- Ombres douces et graduées
- Glassmorphism subtil
- Animations de révélation au scroll
- Hover effects avec transitions fluides

## 📁 Structure du projet

```
Beehive Lodge/
├── index.html              # Page d'accueil
├── about.html              # À propos
├── features.html           # Fonctionnalités/Services
├── blog.html               # Blog
├── contact.html            # Contact
├── demo.html               # Demande de démo
├── css/
│   └── style.css          # Styles personnalisés
├── js/
│   ├── main.js           # JavaScript principal
│   └── contact.js        # Formulaire de contact
├── api/
│   ├── contact.php       # Handler formulaire contact
│   └── demo.php          # Handler demande démo
├── images/               # Images du site
├── assets/              # Ressources additionnelles
└── logs/               # Logs des soumissions
```

## ⚙️ Installation et Configuration

### 1. Installation basique (Frontend uniquement)
```bash
# Cloner ou télécharger les fichiers
# Ouvrir index.html dans un navigateur
```

### 2. Installation avec serveur local
```bash
# Avec Python
python -m http.server 8000

# Avec Node.js
npx serve .

# Avec PHP
php -S localhost:8000
```

### 3. Configuration backend (PHP)

#### Prérequis
- PHP 7.4+ avec extensions mail
- Serveur web (Apache, Nginx)
- Configuration SMTP (Gmail recommandé)

#### Configuration email
1. Éditer `api/contact.php` et `api/demo.php`
2. Remplacer les paramètres de configuration:

```php
$config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'votre-email@gmail.com',
    'smtp_password' => 'votre-mot-de-passe-app',
    'from_email' => 'votre-email@gmail.com',
    'from_name' => 'Beehive Lodge',
    'to_email' => 'contact@votredomaine.com',
];
```

#### Sécurité Gmail
1. Activer l'authentification à 2 facteurs
2. Générer un mot de passe d'application
3. Utiliser ce mot de passe dans la configuration

## 🌐 Déploiement

### Hébergement statique (sans backend)
- **Netlify**: Drag & drop des fichiers
- **Vercel**: Connexion GitHub
- **GitHub Pages**: Push vers repository

### Hébergement avec PHP
- **cPanel/WHM**: Upload via FTP
- **VPS**: Configuration Apache/Nginx + PHP
- **Services cloud**: AWS, Google Cloud, Azure

### Configuration DNS
1. Pointer le domaine vers l'hébergement
2. Configurer SSL/TLS (Let's Encrypt)
3. Redirection www vers non-www

## 🔧 Personnalisation

### Modifier les couleurs
Éditer le fichier `css/style.css`:
```css
:root {
    --primary-gold: #D4A017;    /* Votre couleur principale */
    --primary-dark: #B8860B;    /* Version plus foncée */
    --text-dark: #2C3E50;       /* Texte principal */
    --text-light: #6C757D;      /* Texte secondaire */
}
```

### Changer les images
1. Remplacer les URLs Unsplash par vos images
2. Optimiser les images (WebP recommandé)
3. Ajouter les attributs `alt` appropriés

### Modifier le contenu
1. Éditer directement les fichiers HTML
2. Adapter les textes à votre établissement
3. Mettre à jour les informations de contact

## 📱 Fonctionnalités mobiles

- **Design responsive** sur tous les écrans
- **Menu mobile** avec hamburger
- **Bouton WhatsApp flottant** 
- **Formulaires tactiles** optimisés
- **Chargement rapide** des images

## 🔍 SEO et Performance

### Optimisations incluses
- **Meta tags** descriptifs
- **Structure HTML** sémantique
- **Images optimisées** avec lazy loading
- **Minification CSS/JS**
- **Schema markup** pour le SEO local

### Améliorations recommandées
- Configurer Google Analytics
- Ajouter Search Console
- Optimiser Core Web Vitals
- Configurer sitemap.xml

## 🛠️ Maintenance

### Sauvegardes régulières
- Files du site web
- Base de données (si utilisée)
- Logs des formulaires

### Mises à jour
- Bootstrap et dépendances
- Contenu et images
- Certificats SSL

### Monitoring
- Temps de réponse du site
- Fonctionnement des formulaires
- Erreurs 404 et autres

## 📞 Support

Pour toute question technique ou personnalisation:

- **Email**: support@beechivelodge.com
- **Documentation**: [Lien vers documentation]
- **Issues GitHub**: [Si applicable]

## 📄 Licence

Ce projet est créé pour Beehive Lodge. Tous droits réservés.

## 🙏 Crédits

- **Framework**: Bootstrap 5.3
- **Animations**: AOS (Animate On Scroll)
- **Icônes**: Font Awesome 6.4
- **Polices**: Google Fonts (Poppins)
- **Images**: Unsplash (à remplacer par vos photos)

---

**Version**: 1.0  
**Dernière mise à jour**: Novembre 2024  
**Développé avec**: ❤️ pour Beehive Lodge
