<div align="center">

# Alcasar-Importer-CSV

</div>

🧰 Simplifiez l'importation d'un fichier CSV pour le système ALCASAR 

🏗️ Ces modules PHP permettent d'importer des utilisateurs dans la base de données à partir d'un fichier CSV. 

🛠️ Instructions d'installation 

```

 curl -O https://raw.githubusercontent.com/oxo140/Alcasar-Importer-CSV/main/deploy.sh

 chmod +x deploy.sh

 sudo ./deploy.sh


```

✔️ Accédez à http://IPdeAlcasasr/csv
 
✔️ Le bouton "Importer CSV" permet de lire le fichier CSV pour une importation dans la base de données d'ALCASAR.

✔️ Il met à jour la base de données en utilisant les identifiants fournis. 

✔️ Les mots de passe sont chiffrés avec l'algorithme SHA-256, et les identifiants sont automatiquement incrémentés. 

✔️ Le bouton "Reverse CSV" permet de lire le fichier CSV et de supprimer les données correspondantes dans la base. 

✔️ Vous pouvez utiliser votre CSV récemment importé pour supprimer les utilisateurs qui viennent d'être ajoutés. 

✔️ Le bouton "Extraire la base de données" récupère la base au format `sql.gz`. Ce format est compatible pour l'importation depuis la page ALCASAR prévue à cet effet. Cependant, la génération de la base peut prendre un certain temps en fonction de son ancienneté, merci d'être patient. Il faudra extraire une première fois l'archive pour retrouver l'archive compatible avec ALCASAR. L'archive doit ressembler à ceci : `alcasar-users-database-"date"-"heure".sql.gz`. 

✔️ Le bouton "Vérifier les doublons" permet d'afficher une liste des occurrences des utilisateurs présents dans la table `radcheck` s'il y a des doublons. Un bouton "Supprimer les doublons" en bas de la page permet d'effacer un doublon tout en gardant une session présente. 

⚠️ Pensez à l'utiliser avec précaution, il est toujours nécessaire d'avoir une sauvegarde de votre base. 

<div align="center">

![image](https://github.com/user-attachments/assets/758ac6fd-12a2-4364-9ccc-d452d4aaf847)

</div>

