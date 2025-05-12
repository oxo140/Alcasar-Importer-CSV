<div align="center">

# Alcasar-Importer-CSV

</div>

⚠️ Cette application est faite pour Alcasar en 3.6.0.

🧰 Simplifiez l'importation d'un fichier CSV pour le système ALCASAR

🏗️ Fonction principale : Les modules PHP permettent d'importer des utilisateurs dans la base de données à partir d'un fichier CSV et de remplir tous les champs.

🛠️ Instructions d'installation

```
curl -O https://raw.githubusercontent.com/oxo140/Alcasar-Importer-CSV/main/deploy.sh
chmod +x deploy.sh
sudo ./deploy.sh
```

🖥️ Accédez à https://alcasar.localdomain/csv

⚠️ Toujours avoir une sauvegarde de votre base sous la main ! ⚠️

Le bouton "Gérer CSV" permet la gestion des fichiers CSV pour une fusion dans la base de données d'ALCASAR. Le bouton "Reverse CSV" permet la suppression des utilisateurs avec un `username` identique. Vous pouvez écraser la base en important un fichier CSV ⚠️ ATTENTION, la base est vidée ! ⚠️ Vous pouvez télécharger la base au format CSV, les mots de passe sont chiffrés. Si vous travaillez sur ce fichier, vous pouvez écrire les mots de passe en clair, le système les chiffrera à son importation. L'importation devra etre associé a un groupe.

Vous pouvez ajouter une adresse MAC en utilisant les identifiants et paramètres fournis. L'utilisateur sera automatiquement créé avec le champ `password` comme mot de passe et devra etre associé a un groupe.

Le bouton "Extraire la base de données" récupère la base au format `sql.gz`. Ce format est compatible pour l'importation depuis la page ALCASAR prévue à cet effet. Cependant, la génération de la base peut prendre un certain temps en fonction de sa taille.

Le bouton "Gérer DHCP" permet la gestion complète des réservations DHCP :

- Vous pouvez saisir manuellement une adresse MAC et son adresse IP associée, elles seront ajoutées directement au fichier DHCP (`/usr/local/etc/alcasar-ethers`).
- Vous pouvez importer plusieurs réservations à partir d'un fichier CSV contenant 3 colonnes (Adresse MAC, Adresse IP, Commentaire).
- Les réservations sont aussi sauvegardées avec commentaires dans `/var/www/html/csv/correspondancedhcp.txt`.
- Le bouton "Télécharger la correspondance DHCP" permet de récupérer les réservations au format CSV exploitable directement dans Excel.
- Un bouton "Vider les baux DHCP" permet de vider tous les baux existants pour repartir à zéro.

⚠️ **Important** : Après chaque modification sur les réservations DHCP, vous devez redémarrer le service `chilli` ou le serveur pour une prise en compte.

Le bouton "Utilisateurs Inactifs" permet d'afficher une liste des utilisateurs non connectés depuis un mois et est ajustable sur la page. Un bouton "Supprimer les utilisateurs inactifs" en bas de la page permet d'effacer les utilisateurs inactifs.

Le bouton "Générer Logs" permet de créer un fichier CSV affichant la date et l'heure de chacune des connexions des utilisateurs.

<div align="center">

![image](https://github.com/user-attachments/assets/758ac6fd-12a2-4364-9ccc-d452d4aaf847)

</div>
