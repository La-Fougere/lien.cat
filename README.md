# LIEN.CAT

```
 /\_/\ 
( o.o )  LIEN.CAT
 > ^ <
```

Le raccourcisseur de liens le plus mignon du monde.

## Apercu
LIEN.CAT est un mini service de raccourcissement d'URL avec statistiques de visites et suppression par cle.
Le coeur est un front PHP qui cree un dossier par lien et y depose un `index.php` de redirection.

## Fonctionnalites
- Slug personnalise (ou generation auto en 4 lettres si vide)
- Redirection instantanee + compteur de visites
- Page stats ` /show/<slug> ` protegee par cle
- Suppression d'un lien via la cle unique
- Admin panel pour lister, modifier et supprimer (optionnel)

## Stack
- PHP (PDO)
- MySQL / MariaDB
- HTML / CSS / JS

## Arborescence utile
- `lien/liens/index.php` : page principale (creation + suppression)
- `lien/liens/<slug>/index.php` : redirection + compteur
- `lien/liens/show/<slug>/index.php` : page stats protegee
- `lien/liens/show/test/index.php` : template copie lors de la creation
- `lien/liens/admin/index.php` : admin panel
- `lien/assets/` et `lien/liens/assets/` : styles et images

## Installation rapide
1. Creer une base `liens` et un utilisateur avec droits en lecture/ecriture.
2. Creer la table `liens` (schema minimal ci-dessous).
3. Mettre a jour les identifiants BDD dans:
   - `lien/liens/index.php`
   - `lien/liens/show/test/index.php` (template)
   - `lien/liens/admin/index.php`
4. Donner les droits d'ecriture au serveur web sur:
   - `lien/liens/`
   - `lien/liens/show/`
5. Pointer le DocumentRoot du vhost sur `lien/liens/` pour que `/<slug>` fonctionne.

Schema minimal (exemple):
```
CREATE TABLE liens (
  nom VARCHAR(250) PRIMARY KEY,
  url TEXT NOT NULL,
  created_at INT UNSIGNED NOT NULL,
  visited INT UNSIGNED NOT NULL DEFAULT 0,
  del_password VARCHAR(255) NOT NULL
);
```

## Utilisation
- Creer un lien: entre une URL et un slug optionnel.
- Si le slug est vide, LIEN.CAT genere 4 caracteres au hasard.
- Le site renvoie:
  - le lien court
  - une cle unique (a conserver)
  - un lien stats ` /show/<slug> `
- Supprimer un lien: bouton "Supprimer un lien" + cle unique.
- Voir les stats: aller sur ` /show/<slug> ` et saisir la cle.

## Notes
- Les pages de redirection et de stats sont generees automatiquement dans les dossiers `/<slug>` et `/show/<slug>`.
- Si vous changez les identifiants BDD, pensez a mettre a jour les anciens liens deja crees.
