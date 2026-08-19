# Déploiement Render — B-Stock (Laravel 10.25)

Render **n’offre pas de MySQL gratuit**. Le service web gratuit dort après ~15 min d’inactivité. Le disque du conteneur est **éphémère** (logos / photos produits perdus au redémarrage, sauf S3/R2).

## GitHub

Repo `Bstoc`, branche `main`. Pousser Dockerfile, docker/, render.yaml. **Ne jamais** committer `.env`.

## MySQL (externe)

Options réalistes :

| Solution | 0 € durable ? | MySQL réel ? |
|---|---|---|
| Render MySQL | Non (payant / inexistant en free) | — |
| Render PostgreSQL free | Oui (quota) | **Non** — à n’utiliser que si vous acceptez de changer de moteur |
| InfinityFree `sql108…` | Oui | Souvent **inaccessible depuis Render** (pas d’IP distantes) |
| TiDB Cloud Serverless | Quota gratuit, compatible protocole MySQL | Oui, le plus raisonnable à 0 € |
| db4free / FreeSQL | Oui | Instable, à éviter en vrai magasin |

Créer une base vide, puis **soit** importer `bstock.sql`, **soit** `php artisan migrate --force` (déjà au démarrage du conteneur) **sans** `db:seed` automatique.

## Render — Web Service

- New → Web Service → repo `Bstoc` → branche `main`
- Runtime : **Docker**
- Instance : **Free**
- Health check : `/`

Variables (Dashboard → Environment) :

```
APP_NAME=B-Stock
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...          # php artisan key:generate --show  (en local)
APP_URL=https://VOTRE-SERVICE.onrender.com
LOG_CHANNEL=stderr
LOG_LEVEL=error
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
CACHE_DRIVER=file
SESSION_DRIVER=cookie
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
MEDIA_DISK=public
QUEUE_CONVERSIONS_BY_DEFAULT=false
BROADCAST_DRIVER=log
MAIL_MAILER=log
MAIL_FROM_ADDRESS=oubdaalassane01@gmail.com
MAIL_FROM_NAME=B-Stock
SANCTUM_TTL=120
SANCTUM_STATEFUL_DOMAINS=VOTRE-SERVICE.onrender.com
CORS_ALLOWED_ORIGINS=https://VOTRE-SERVICE.onrender.com
UPGRADE_MODE=false
POS_PUBLIC_REGISTER=false
GENIUSPAY_BASE_URL=https://geniuspay.ci/api/v1/merchant
GENIUSPAY_API_KEY=
GENIUSPAY_API_SECRET=
GENIUSPAY_WEBHOOK_SECRET=
```

Webhook GeniusPay : `https://VOTRE-SERVICE.onrender.com/api/geniuspay/webhook`

Après le 1er déploiement, recopier l’URL réelle dans `APP_URL`, `SANCTUM_STATEFUL_DOMAINS`, `CORS_ALLOWED_ORIGINS`.

## Après deploy

Comptes (si base importée / seedée une fois à la main) :

- Magasin : `/#/login` — `admin@bstock.ci`
- Plateforme : `/platform/login` — `platform@bstock.ci`

Ne pas lancer `migrate:fresh` ni `db:seed` en production une fois les données en place.
