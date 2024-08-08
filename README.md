# Alcasar-Importer-CSV 🚧

🧰 Simplifier l'importation d'un fichier CSV pour le système ALCASAR 🧰

🏗️ Ce script PHP permet d'importer des utilisateurs dans la base à partir d'un fichier CSV. 🏗️

✔️ Le bouton "importer CSV" permet de lire le fichier CSV et fragmenter les colonnes pour une importation dans la base de données de alacasar. ✔️

✔️ Il met à jour la base de données en utilisant les identifiants fournis. 

✔️ Ils Chiffrent les mots de passe avec l'algorithme SHA-256, et en assurant l'incrémentation automatique des identifiants. ✔️

✔️ Le bouton Reverse CSV permet de lire le fichier CSV et supprimer les donnees corespondante dans la base. ✔️

✔️ Vous pouvez basique importer votre CSV récemment utiliser pour supprimer les utilisateurs qui viennent d'etre ajouté. ✔️

⚠️ Penser a l'utiliser avec précaution, il faut toujours avoir une sauvegarder de votre base. ⚠️


🛠️ Installation-Instructions 🛠️

🔧 Placez les fichiers index.php et upload.php dans le répertoire de votre serveur web (par exemple, /var/www/html/csv/index.php - upload.php). 🔧

🔧Accédez à http://localhost/csv (ou l'URL correspondant à votre configuration) pour utiliser le formulaire d'upload et importer le fichier CSV dans votre base de données.🔧
