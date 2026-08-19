# Déployer B-Stock sur InfinityFree (bstock.site.je)

InfinityFree n’a **pas de terminal**. On n’exécute pas `php artisan` sur le serveur. On envoie les fichiers + une copie de la base MySQL.

> Cet hébergement gratuit est limité (taille d’upload, pas de SSH, parfois blocage des appels vers GeniusPay). Si ça coince trop, un hébergeur payant type LWS / o2switch / Hostinger sera plus simple.

---

## 1. Préparer InfinityFree

1. **Avancé** → version **PHP 8.1 ou 8.2** (pas 7.x).
2. **Bases de données MySQL** → créer une base.
   Notez exactement :
   - hôte (souvent `sqlXXX.infinityfree.com`, **pas** `localhost`)
   - nom de la base
   - utilisateur
   - mot de passe MySQL
3. **Compte d’hébergement** → **FTP** : hôte `ftpupload.net`, identifiant du type `if0_42694966`, mot de passe FTP.

Le site s’affiche depuis le dossier **`htdocs`**.

---

## 2. Envoyer les fichiers

**Ne pas** envoyer le dossier `node_modules`. **Oui** envoyer `vendor` (obligatoire).

Avec **FileZilla** (recommandé, le gestionnaire web refuse souvent les gros fichiers) :

- Serveur : `ftpupload.net`
- Dossier distant : `/htdocs`
- Envoyez **tout le projet** Laravel dedans (`app`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, `storage`, `vendor`, `artisan`, `composer.json`, `.htaccess`, `.env`…).

Puis **supprimez** dans `htdocs` les fichiers d’exemple InfinityFree (`index2.html` et le fichier « Les fichiers de votre site… »).

Le fichier `.htaccess` à la racine de `htdocs` redirige déjà vers `public/` : l’adresse du site reste `https://bstock.site.je` (pas `/public`).

---

## 3. Fichier `.env` sur le serveur

Copiez votre `.env` local, puis **changez** :

```
APP_NAME="B-Stock"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bstock.site.je

DB_CONNECTION=mysql
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=le_nom_donne_par_infinityfree
DB_USERNAME=if0_42694966
DB_PASSWORD=le_mot_de_passe_mysql

SANCTUM_STATEFUL_DOMAINS=bstock.site.je,www.bstock.site.je
CORS_ALLOWED_ORIGINS=https://bstock.site.je,https://www.bstock.site.je
```

Gardez `APP_KEY`, les clés GeniusPay, etc.

Droits d’écriture (gestionnaire de fichiers) sur :
- `storage`
- `bootstrap/cache`

---

## 4. Copier la base de données

Sur votre PC (XAMPP) :

```
C:\xampp\mysql\bin\mysqldump.exe -u root pos > C:\Users\djama\Desktop\bstock.sql
```

Sur InfinityFree : **phpMyAdmin** de la base créée → **Importer** → `bstock.sql`.

---

## 5. Vérifier le site

1. Attendre le DNS (jusqu’à 72 h la première fois, souvent moins).
2. Ouvrir `https://bstock.site.je/#/login`
3. Magasin : `admin@bstock.ci` / `123456` (changez le mot de passe ensuite)
4. Back-office : `https://bstock.site.je/platform/login`  
   `platform@bstock.ci` / `123456`

GeniusPay : webhook  
`https://bstock.site.je/api/geniuspay/webhook`

---

## Si page blanche ou erreur 500

- PHP en 8.1+
- `.env` bien dans `htdocs` (pas seulement dans `public`)
- `vendor` bien uploadé
- `storage/logs` : lire `laravel.log`
- base importée et `DB_HOST` = hôte InfinityFree, pas `127.0.0.1`
