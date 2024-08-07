# Alcasar-Importer-CSV
Simplifier l'importation d'un fichier CSV pour le système ALCASAR

Ce script PHP permet de :

Télécharger un fichier CSV via une interface web.

Lire le fichier CSV et fragmenter les colonnes pour une importation dans la base de données que utilise alacasar.

Mettre à jour la base de données en utilisant les identifiants fournis, en chiffrant les mots de passe avec l'algorithme SHA-256, et en assurant l'incrémentation automatique des identifiants.

Installation-Instructions
Placez les fichiers index.php et upload.php dans le répertoire de votre serveur web (par exemple, /var/www/html/csv/index.php - upload.php).

Accédez à http://localhost/csv (ou l'URL correspondant à votre configuration) pour utiliser le formulaire d'upload et importer le fichier CSV dans votre base de données.
