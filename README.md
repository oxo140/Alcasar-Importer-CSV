<div align="center">

# Alcasar-Importer-CSV

</div>

🧰 Simplifiez l'importation d'un fichier CSV pour le système ALCASAR 

🏗️ Ces modules PHP permettent d'importer des utilisateurs dans la base de données à partir d'un fichier CSV. 

🛠️ Instructions d'installation 

🔧 Placez les fichiers `index.php`, `upload.php`, `download.php`, `backup.php`, `sauvegarde.php`,`login.php`, `utilisateursinactifs.php` et `doublon.php` dans le répertoire de votre serveur web.

🔧 Exemple : `/var/www/html/csv/index.php`

🔧 Creer le repertoire `apache` (mkdir apache) et lui donner les droits (chmod 777 apache)

🔧 Accédez à http://localhost/csv (ou à l'URL correspondant à votre configuration).

```diff
- ⚠️ IL EST IMPERATIF DE MODIFIER LES VARIABLES DE MOTS DE PASSE DANS LES FICHIERS SUIVANTS : sauvegarde.php, doublon.php, upload.php, login.php, utilisateursinactifs.php
```

✔️ Le bouton "Importer CSV" permet de lire le fichier CSV pour une importation dans la base de données d'ALCASAR.

✔️ Il met à jour la base de données en utilisant les identifiants fournis. 

✔️ Les mots de passe sont chiffrés avec l'algorithme SHA-256, et les identifiants sont automatiquement incrémentés. 

✔️ Le bouton "Reverse CSV" permet de lire le fichier CSV et de supprimer les données correspondantes dans la base. 

✔️ Vous pouvez utiliser votre CSV récemment importé pour supprimer les utilisateurs qui viennent d'être ajoutés. 

✔️ Le bouton "Extraire la base de données" récupère la base au format `sql.gz`. Ce format est compatible pour l'importation depuis la page ALCASAR prévue à cet effet. Cependant, la génération de la base peut prendre un certain temps en fonction de son ancienneté, merci d'être patient. Il faudra extraire une première fois l'archive pour retrouver l'archive compatible avec ALCASAR. L'archive doit ressembler à ceci : `alcasar-users-database-"date"-"heure".sql.gz`. 

✔️ Le bouton "Vérifier les doublons" permet d'afficher une liste des occurrences des utilisateurs présents dans la table `radcheck` s'il y a des doublons. Un bouton "Supprimer les doublons" en bas de la page permet d'effacer un doublon tout en gardant une session présente. 

⚠️ Pensez à l'utiliser avec précaution, il est toujours nécessaire d'avoir une sauvegarde de votre base. 

<div align="center">

![Capture](https://github.com/user-attachments/assets/5bc4810e-39e7-42c8-b0bc-7371dcc013bb)

</div>

