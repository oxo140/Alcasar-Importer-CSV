<div align="center">

# Alcasar-Importer-CSV

</div>

🧰 Simplifiez l'importation d'un fichier CSV pour le système ALCASAR

🏗️ Fonction principale : Les modules PHP permettent d'importer des utilisateurs dans la base de données à partir d'un fichier CSV et de remplir tous les champs.

🛠️ Instructions d'installation

```
curl -O https://raw.githubusercontent.com/oxo140/Alcasar-Importer-CSV/main/deploy.sh
chmod +x deploy.sh
sudo ./deploy.sh
```

🖥️ Accédez à http://IPdeAlcasar/csv

⚠️ Toujours avoir une sauvegarde de votre base sous la main ! ⚠️

Le bouton "Gérer CSV" permet la gestion des fichiers CSV pour une fusion dans la base de données d'ALCASAR. Le bouton "Reverse CSV" permet la suppression des utilisateurs avec un `username` identique. Vous pouvez écraser la base en important un fichier CSV ⚠️ ATTENTION, la base est vidée ! ⚠️ Vous pouvez télécharger la base au format CSV, les mots de passe sont chiffrés. Si vous travaillez sur ce fichier, vous pouvez écrire les mots de passe en clair, le système les chiffrera à son importation.

Vous pouvez ajouter une adresse MAC en utilisant les identifiants et paramètres fournis. L'utilisateur sera automatiquement créé avec le champ `password` comme mot de passe.

Le bouton "Extraire la base de données" récupère la base au format `sql.gz`. Ce format est compatible pour l'importation depuis la page ALCASAR prévue à cet effet. Cependant, la génération de la base peut prendre un certain temps en fonction de sa taille, merci d'être patient. L'archive doit ressembler à ceci : `alcasar-users-database-"date"-"heure".sql.gz`.

Le bouton "Vérifier les doublons" permet d'afficher une liste des occurrences des utilisateurs présents dans la table `radcheck` s'il y a des doublons. Un bouton "Supprimer les doublons" en bas de la page permet d'effacer un doublon tout en gardant la session la plus récente.

Le bouton "Utilisateurs Inactifs" permet d'afficher une liste des utilisateurs non connectés depuis un mois et est ajustable sur la page. Un bouton "Supprimer les utilisateurs inactifs" en bas de la page permet d'effacer les utilisateurs inactifs.

Le bouton "Générer Logs" permet de créer un fichier CSV affichant la date et l'heure de chacune des connexions des utilisateurs.

<div align="center">

![image](https://github.com/user-attachments/assets/758ac6fd-12a2-4364-9ccc-d452d4aaf847)

</div>
