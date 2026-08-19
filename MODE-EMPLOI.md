# B-Stock — Mode d’emploi

Guide pour reprendre et utiliser le magasin au quotidien.  
Pas de technique : uniquement *quoi cliquer* et *à quoi ça sert*.

---

## En deux mots

B-Stock sert à **tenir un magasin** : stock, achats, ventes, caisse, clients, rapports.

Il y a **deux portes** :

| Porte | Pour qui | Adresse |
|---|---|---|
| **Caisse / magasin** | Gérant, caissier, magasinier | `/#/login` |
| **Back-office** | Propriétaire de B-Stock (vous) | `/platform/login` |

Un magasin ne voit **que ses données**. Le back-office gère les magasins, les tarifs d’abonnement, le logo et le nom de l’appli.

Si l’abonnement du magasin est fini : on peut encore se connecter, mais l’écran est grisé. Il faut aller dans **Abonnement** puis **Reconduire** pour payer.

---

## Comment tout s’enchaîne

```
Fournisseur  →  Achat         →  Stock dans l’entrepôt
                                  ↓
Client       ←  Vente / Caisse ←  Produits
                                  ↓
                               Rapports (ce qui a été vendu, acheté, gagné)
```

1. On crée **produits**, **clients**, **fournisseurs**, **entrepôt**.
2. On **achète** chez le fournisseur → le stock **monte**.
3. On **vend** (caisse ou vente) → le stock **descend**, l’argent rentre.
4. Les **rapports** résument tout ça.

Sans produit + stock, on ne peut pas vendre. Sans client, on peut souvent utiliser le client « passage » déjà prévu.

---

## Passer une vente (le plus important)

Deux façons, même résultat : le stock baisse et la vente est enregistrée.

### A. Caisse rapide (bouton **PDV** en haut)

Idéal en magasin, file de clients.

1. Ouvrir la **caisse** du jour si l’appli le demande (fond de caisse).
2. Choisir l’**entrepôt** (le lieu du stock).
3. Chercher le produit (nom ou code-barres) et indiquer la **quantité**.
4. Vérifier le client (ou laisser le client de passage).
5. Encaisser (espèces, etc.) et valider.
6. Imprimer / donner le ticket si besoin.
7. En fin de journée : **fermer la caisse** (rapport de caisse).

### B. Vente enregistrée (menu **Ventes**)

Idéal pour une facture, un crédit, une vente à revoir plus tard.

1. **Ventes** → nouvelle vente.
2. Client, entrepôt, produits, quantités, prix.
3. Enregistrer, puis encaisser si ce n’est pas déjà fait.

**Retour produit** : menu **Ventes** → retours. Le stock **remonte**, on rembourse ou on crédite le client.

**Devis (Citations)** : même idée qu’une vente, mais **sans toucher au stock**. Quand le client accepte, on transforme le devis en vente.

---

## Les menus du magasin

### Tableau de bord
Vue du jour : ventes, alertes stock, meilleurs produits. On commence souvent ici.

### Abonnement
Offre en cours, jours restants, autres tarifs. Payer / renouveler (GeniusPay). Visible pour tout le monde du magasin.

### Produits
Le catalogue.

- **Produits** : ce que vous vendez (nom, prix, code-barres, stock).
- **Catégories** : ranger (ex. boissons, snacks).
- **Variations** : tailles / parfums d’un même article.
- **Marques** : Coca, local, etc.
- **Unités** : pièce, carton, litre.
- **Code-barres** : imprimer des étiquettes.

### Ajustements
Corriger le stock **sans** achat ni vente (casse, inventaire, oubli). À utiliser avec prudence : ça change le stock tout de suite.

### Citations (devis)
Proposition de prix. Pas de sortie de stock tant que ce n’est pas devenu une vente.

### Achats
Marchandise qui **entre**.

- **Achats** : commande chez le fournisseur → stock **+**.
- **Retours d’achat** : on renvoie au fournisseur → stock **−**.

### Ventes
Ventes enregistrées + **retours client**.

### Transferts
Déplacer du stock **d’un entrepôt à un autre** (boutique ↔ dépôt). Le total ne change pas, l’emplacement si.

### Dépenses
Loyer, essence, petit matériel… ce n’est **pas** un achat de marchandise. Sert au rapport gain / perte. Les **catégories de dépenses** rangent ces sorties.

### Peuples (personnes)

- **Fournisseurs** : qui vous livre.
- **Clients** : qui vous achète (et l’historique).
- **Utilisateurs** : les comptes de l’équipe du magasin.

### Rôles / autorisations
Qui a le droit de faire quoi (ex. caissier = caisse seulement, gérant = tout).  
Le propriétaire B-Stock peut en plus **limiter** certaines fonctions par magasin depuis le back-office.

### Entrepôt
Lieux de stock (boutique, réserve). Chaque mouvement (achat, vente, transfert) est lié à un lieu.

### Signaler (rapports)
Chiffres : ventes, achats, stock, ruptures, meilleurs clients, fournisseurs, profit / perte, caisse. Pour piloter, pas pour encaisser.

### Devises
Franc CFA, etc. En Côte d’Ivoire on reste en général en **XOF**.

### Langages
Français / autres langues de l’écran.

### Modèles (e-mail / SMS)
Textes automatiques (facture, relance). Utile si l’envoi de mails/SMS est branché.

### Réglages (souvent en bas ou via le profil)
Nom du magasin, logo **du magasin**, téléphone, entrepôt et client par défaut, numéro de facture.  
Le logo **de toute l’application B-Stock** se change dans le **back-office**, pas ici.

---

## Back-office (propriétaire)

Connexion séparée. Les clients magasins n’y ont pas accès.

- **Tableau de bord** : combien de magasins, essais, abonnements.
- **Magasins** : créer un commerce, le suspendre, choisir **quelles fonctions** il a le droit d’utiliser.
- **Offres d’abonnement** : Starter, Professional, prix, mensuel / annuel.
- **Logo et nom** : identité B-Stock (logo = aussi l’icône de l’onglet du navigateur).

Un nouveau magasin démarre en général avec un **essai**. Ensuite il paie via **Abonnement** dans son espace.

---

## Routine type d’une journée

1. Ouvrir la caisse.  
2. Vendre au **PDV**.  
3. Si un camion arrive : **Achats**.  
4. Si casse : **Ajustements**.  
5. Le soir : fermer la caisse, jeter un œil au **tableau de bord** / **rapports**.

---

## Si ça bloque

| Situation | Que faire |
|---|---|
| Écran grisé, « abonnement expiré » | Menu **Abonnement** → Reconduire |
| Produit introuvable en caisse | Le créer dans **Produits**, avec du stock (**Achats** ou ajustement) |
| Stock négatif / faux | Vérifier ventes, achats, puis **Ajustements** |
| Un employé ne voit pas un menu | **Rôles** (magasin) ou droits du magasin (back-office) |
| Mauvais logo / nom de l’appli | Back-office → **Logo et nom** |

---

Créé par Alassane Oubda — B-Stock  
Contact : oubdaalassane01@gmail.com · +225 0757613098
