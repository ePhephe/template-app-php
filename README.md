Template d'application PHP créé lors des exercices de ma formation chez WEBECOM.

Le template se décompose ainsi :
- dossier " public " qui contient les fichiers du front de l'application : css, javascript, images et polices.
- dossier " assets " avec tout le code scss de base et un fichier main.scss pour écrire le css spécifique à l'application
- dossier " docs " destiné à accueillir la documentation (MCD, schéma d'ergonomie, tableau de liste des éléments MVC)
- dossier " src " qui contient les sources PHP de l'application : controller, modeles, templates et utils
- fichier " index.php " point d'entrée de l'application
- fichier " .htaccess " qui gère la réécriture des URLs

Le framework PHP comporte :
- Les classes dans utils :
    - _controller, classe mère de tous les controllers qui seront créés
    - _template, utilisé par la classe _controller, gère l'affichage des templates (vues)
    - _requete, s'occupe de toutes les interactions avec la base de données
    - _session, gère le système de session
    - _permission, définit et gère les permissions à l'application si nécessaire
    - _model, pour gérer tous les objets métiers
    - _field, classe qui s'occupe des champs des objets métiers
- Nous avons deux classes métiers prédéfinies :
    - utilisateur, qui gère les utilisateurs de l'application et la partie connexion
    - piecejointe, qui s'occupe de la gestion des fichiers

Le framework SCSS :
- Un fichier de reset (reset.scss)
- Un fichier de variables (variables.scss) à modifier avec plusieurs paramètres (couleurs, tailles, etc...)
- Un dossier components avec différents éléments construits :
    - Classes des flexbox
    - Gestion des layouts de page selon les devices
    - Menu Burger préfabriqué
    - Input " switch "
