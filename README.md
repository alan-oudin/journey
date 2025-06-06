Guide pour initialiser et committer un projet Git avec PhpStorm
Prérequis

Assure-toi que Git est installé sur ton ordinateur.
Configure ton identité Git (si ce n'est pas déjà fait) :git config --global user.name "Ton Nom"
git config --global user.email "ton.email@example.com"



Étapes via la ligne de commande (Terminal dans PhpStorm)

Ouvrir le terminal :

Dans PhpStorm, va dans View > Tool Windows > Terminal ou utilise Alt+F12.


Initialiser un dépôt Git :
git init

Cela crée un dépôt Git vide dans le dossier de ton projet.

Ajouter tous les fichiers au suivi :
git add .


Créer le premier commit :
git commit -m "Initial commit"


Associer le dépôt distant :
git remote add origin https://github.com/alan-oudin/journey.git


Pousser vers le dépôt distant :
git push -u origin main

Note : Si la branche principale est master, utilise git push -u origin master.


Étapes via l'interface graphique de PhpStorm

Initialiser un dépôt Git :

Va dans VCS > Enable Version Control Integration.
Sélectionne Git et clique sur OK.


Ajouter les fichiers au commit :

Ouvre la fenêtre de commit avec Ctrl+K ou VCS > Commit.
Coche les fichiers à inclure (ou tous).


Faire un commit :

Dans la fenêtre de commit, saisis un message (ex. : "Initial commit").
Clique sur Commit ou utilise Ctrl+K.


Ajouter le dépôt distant :

Va dans VCS > Git > Remotes.
Clique sur + et ajoute l'URL : https://github.com/alan-oudin/journey.git.


Pousser vers le dépôt distant :

Utilise VCS > Git > Push ou Ctrl+Shift+K.
Sélectionne la branche (main ou master) et clique sur Push.



Vérifications

Vérifie l'état de ton dépôt avec :git status


Si tu rencontres des erreurs d'authentification lors du push, assure-toi que tes identifiants GitHub ou un token sont configurés dans PhpStorm ou utilise une clé SSH.

