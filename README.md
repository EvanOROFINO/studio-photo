# 📸 Studio Photo

> Plateforme web complète pour photographe professionnel : site vitrine, galerie privée client, réservations en ligne avec acompte Stripe, calendrier de disponibilités, blog SEO, et back-office d'administration.

![CI](https://github.com/EvanOROFINO/studio-photo/actions/workflows/ci.yml/badge.svg)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/Symfony-7.4-000000?logo=symfony&logoColor=white)
![Tests](https://img.shields.io/badge/tests-31%20passing-brightgreen)
![Coverage](https://img.shields.io/badge/license-MIT-blue)

---

## ✨ Aperçu

Studio Photo est un **outil métier complet** pensé pour un photographe indépendant. Au-delà d'un simple site vitrine, l'application couvre tout le cycle de vie d'une mission :

`Prospect → Devis → Réservation en ligne → Acompte CB → Séance → Livraison galerie privée → Avis client`

Une seule plateforme, branchée à Stripe et à un back-office riche pour piloter l'activité au quotidien.

## 🚀 Fonctionnalités

### Site public
- 🏠 **Page d'accueil** hero responsive + photos à la une + catégories + témoignages
- 🖼️ **Galerie** avec filtres par catégorie et tags, pagination, lightbox, mode masonry
- 📅 **Page disponibilités** avec calendrier FullCalendar (jours libres / occupés / bloqués)
- 💼 **Prestations & tarifs** avec bouton "Réserver en ligne"
- 💳 **Réservation en ligne** avec acompte 30% via Stripe Checkout
- 📝 **Blog SEO** rédigé en Markdown, JSON-LD, flux RSS
- 💬 **Témoignages clients** avec avatar + note
- 🎚️ **Avant / Après** slider interactif pour montrer la retouche
- 📧 **Formulaire de contact** avec emails de confirmation HTML
- 🌐 **Multi-langue FR/EN** avec switcher dans la nav
- 🌙 **Mode sombre** persistant
- ⚖️ **Conformité RGPD** (mentions légales, politique de confidentialité, bandeau cookies)

### Espace client
- 🔒 **Galerie privée** par token + mot de passe
- 📥 **Téléchargement** individuel ou ZIP de toutes les photos
- ⏱️ **Expiration automatique** des accès
- 🛡️ Photos stockées **hors document root** + servies via contrôleur

### Back-office admin (EasyAdmin)
- 📊 **Dashboard analytics** avec 8 KPI + 6 graphiques Chart.js (CA, bookings, photos, blog, etc.)
- 🖼️ Gestion photos, catégories, tags, prestations
- 📅 Calendrier de réservations + dates bloquées (congés)
- 📝 Éditeur Markdown pour le blog
- 💬 Modération témoignages et demandes de contact
- 📧 Newsletter (export des abonnés)
- 🔒 Gestion des galeries clients privées
- 👥 Multi-utilisateurs avec rôles

## 🛠️ Stack technique

| Catégorie | Choix |
|---|---|
| Backend | **Symfony 7.4**, PHP 8.2, Doctrine ORM |
| Database | MySQL / MariaDB (SQLite pour les tests) |
| Frontend | Bootstrap 5, Twig, AOS, GLightbox, FullCalendar 6, Chart.js, img-comparison-slider |
| Images | **LiipImagineBundle** (WebP auto, srcset Retina, 7 presets) |
| Paiement | **Stripe Checkout** hosted (mode démo fallback) |
| Markdown | `league/commonmark` (Github-flavored) |
| Upload | **VichUploaderBundle** (4 mappings dont 1 hors `public/` pour sécurité) |
| Admin | **EasyAdmin 5** |
| Tests | **PHPUnit 11** (31 tests, BDD SQLite isolée) |
| CI/CD | **GitHub Actions** (lint Twig/YAML/container + tests + audit) |
| SEO | Sitemap dynamique, robots.txt, Open Graph, JSON-LD BlogPosting |

## 🧪 Tests

```bash
vendor/bin/phpunit --testdox
```

**31 tests, 63 assertions** :
- Smoke tests des 14 routes publiques
- Tests fonctionnels métier (galerie privée, réservation Stripe en mode démo)
- Tests unitaires (`StripeCheckoutService`, `AvailabilityService`)
- Validation des règles d'accès (auth, expiration, dates passées)

## 🚦 Installation locale

```bash
# 1. Cloner et installer
git clone https://github.com/EvanOROFINO/studio-photo.git
cd studio-photo
composer install

# 2. Configurer la base
cp .env .env.local
# Éditer DATABASE_URL dans .env.local

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction

# 3. Lancer
php -S 127.0.0.1:8000 -t public/
```

**Admin par défaut** (fixtures) :
- URL : `/admin`
- Email : `admin@studio-photo.local`
- Mot de passe : `admin`

**Galerie cliente de démo** : récupérer le token avec
```bash
php bin/console doctrine:query:sql "SELECT token FROM client_gallery LIMIT 1"
```
Puis ouvrir `/galerie-client/{token}` avec le mot de passe `demo1234`.

## 🌐 Déploiement production

Le projet inclut tout ce qu'il faut pour partir en production :

```bash
# Sur le poste local : générer une archive prête à uploader
./bin/build-release.ps1
# → produit release/studio-photo-YYYYMMDD-HHmmss.zip

# Sur le serveur (après upload + décompression) :
cp .env.prod.local.example .env.prod.local
# Éditer .env.prod.local avec les vraies valeurs

bash bin/post-deploy.sh
```

Configurations Apache fournies :
- `public/.htaccess` : rewrite Symfony + HSTS-ready + gzip + cache assets
- `.htaccess` racine : fallback si l'hébergeur ne permet pas de pointer le DocumentRoot sur `public/`

Hébergeurs testés : OVH, Infomaniak, o2switch (PHP 8.2 + MySQL 10.6+).

### Activer le vrai Stripe en production

Renseigner dans `.env.prod.local` :
```
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

Sans ces clés, l'application bascule automatiquement en **mode démo** (le checkout simule un paiement réussi). Permet de démontrer le flow complet sans compte Stripe.

### Backup automatique de la base

```bash
# Crontab du serveur
0 3 * * * cd /var/www/studio-photo && php bin/console app:backup:database
```

Génère un `.sql.gz` dans `var/backups/` et conserve 14 jours d'historique.

## 📁 Structure

```
studio-photo/
├── bin/                       # Scripts CLI (build-release, post-deploy)
├── config/
│   ├── packages/              # Configuration des bundles
│   └── services.yaml          # Paramètres app & bindings
├── migrations/                # Migrations Doctrine
├── public/
│   ├── css/                   # Site CSS (variables, mode sombre)
│   ├── favicon.svg            # Logo caméra
│   ├── robots.txt
│   └── uploads/               # Photos, articles, avatars (gitignored)
├── src/
│   ├── Command/               # Commande backup BDD
│   ├── Controller/
│   │   ├── Admin/             # CRUD EasyAdmin
│   │   └── *Controller.php    # Routes publiques
│   ├── Entity/                # 14 entités Doctrine
│   ├── EventListener/         # Locale, etc.
│   ├── Form/                  # Form types (Booking, Contact, Newsletter)
│   ├── Repository/            # Custom queries
│   ├── Service/               # Métier (Stripe, Availability, Mail, Stats)
│   └── Twig/                  # Extension Markdown
├── templates/
│   ├── admin/                 # Dashboard
│   ├── blog/                  # Liste, article, sidebar, feed RSS
│   ├── client_gallery/        # Login, view, unavailable
│   ├── emails/                # Templates HTML transactionnels
│   ├── macros/                # Macros images responsive
│   └── partials/              # Header, footer
├── tests/
│   ├── Functional/            # 5 fichiers de tests fonctionnels
│   └── Unit/                  # Tests services
├── translations/              # messages.fr.yaml, messages.en.yaml
└── var/
    ├── backups/               # Dumps SQL (gitignored)
    └── client-galleries/      # Photos privées (gitignored, hors public/)
```

## 📊 Métriques

- **70+ tâches livrées** sur 4 jours
- **14 entités Doctrine** + 16 routes publiques
- **31 tests** PHPUnit verts
- **6 graphiques** Chart.js dans le dashboard
- **7 presets** d'image LiipImagine (thumbnail/card/full/hero/og/Retina)
- **2 langues** (FR/EN)

## 🛣️ Roadmap

- [x] Site vitrine + RGPD
- [x] Galerie privée client (token + ZIP)
- [x] Réservation Stripe + calendrier
- [x] Blog SEO + RSS
- [x] Tests PHPUnit + CI GitHub Actions
- [x] Optimisation images WebP
- [x] Dashboard analytics
- [x] Mode sombre
- [x] Multi-langue FR/EN
- [x] Backup BDD automatique
- [ ] Galerie vidéo (showreel)
- [ ] Boutique tirages d'art
- [ ] Cache HTTP / CDN Cloudflare

## 📄 Licence

MIT. Projet de portfolio développé par [Evan Orofino](https://github.com/EvanOROFINO) — disponible pour développement / personnalisation sur demande.

---

🤖 Construit avec [Claude Code](https://claude.com/claude-code).
