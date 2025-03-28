#!/bin/bash

# Installation de Git si nécessaire
if ! command -v git &> /dev/null
then
    echo "Git n'est pas installé. Installation de Git..."
    sudo urpmi git
else
    echo "Git est déjà installé."
fi
sudo urpmi php-zip

# Variables
REPO_URL="https://github.com/oxo140/Alcasar-Importer-CSV"
DEST_DIR="/var/www/html/csv"
PASSWORD_FILE="/root/ALCASAR-passwords.txt"
WEB_GROUP="apache"  # Groupe utilisé par Apache sur Mageia

# Demander le mot de passe à l'utilisateur
read -sp "Votre mot de passe pour l'accès web : " web_password
echo

# Récupérer les variables db_root et db_password depuis le fichier ALCASAR-passwords.txt
db_root=$(grep -w "db_root" $PASSWORD_FILE | cut -d '=' -f2)
db_password=$(grep -w "db_password" $PASSWORD_FILE | cut -d '=' -f2)

# Cloner le dépôt Git
git clone $REPO_URL /tmp/alcasar-importer-csv

# Créer le répertoire de destination s'il n'existe pas déjà
mkdir -p $DEST_DIR

# Déplacer les fichiers .php et Sauvegarde.sh vers le répertoire de destination
find /tmp/alcasar-importer-csv -type f \( -name "*.php" -o -name "Sauvegarde.sh" \) -exec mv {} $DEST_DIR \;

# Créer les répertoires apache et uploads
mkdir -p $DEST_DIR/apache
mkdir -p $DEST_DIR/uploads

# Changer le groupe des répertoires vers le groupe Apache
chown -R :$WEB_GROUP $DEST_DIR/apache
chown -R :$WEB_GROUP $DEST_DIR/uploads

# Attribuer les droits de lecture, écriture et exécution pour le groupe
chmod 775 $DEST_DIR/apache
chmod 775 $DEST_DIR/uploads

# Protéger Sauvegarde.sh contre le téléchargement via Apache
echo "Order allow,deny
Deny from all" > $DEST_DIR/.htaccess

# Remplacer les placeholders db_root et db_password dans les fichiers PHP
find $DEST_DIR -type f -name "*.php" -exec sed -i "s/db_root/$db_root/g" {} \;
find $DEST_DIR -type f -name "*.php" -exec sed -i "s/db_password/$db_password/g" {} \;

# Remplacer les placeholders db_root et db_password dans Sauvegarde.sh
sed -i "s/db_root/$db_root/g" $DEST_DIR/Sauvegarde.sh
sed -i "s/db_password/$db_password/g" $DEST_DIR/Sauvegarde.sh

# Remplacer 'votremotdepasse' dans login.php par le mot de passe saisi par l'utilisateur
sed -i "s/votremotdepasse/$web_password/g" $DEST_DIR/login.php

# Nettoyer le répertoire temporaire
rm -rf /tmp/alcasar-importer-csv

> /var/www/html/csv/correspondancedhcp.txt

# Crée un groupe dédié si nécessaire
sudo groupadd alcasar

# Ajoute apache à ce groupe
sudo usermod -a -G alcasar apache

# Change le groupe du fichier
sudo chown root:alcasar /usr/local/etc/alcasar-ethers

sudo chown -R apache:apache /var/www/html/csv
sudo chown apache:apache /var/www/html/csv/correspondancedhcp.txt
sudo chmod 664 /var/www/html/csv/correspondancedhcp.txt
sudo chown apache:apache /usr/local/etc/alcasar-ethers
sudo chmod 664 /usr/local/etc/alcasar-ethers
# Donne les droits d’écriture au groupe
sudo chmod 664 /usr/local/etc/alcasar-ethers

echo "Les fichiers ont été déplacés vers $DEST_DIR, les variables ont été remplacées, les permissions ont été ajustées, et le téléchargement de Sauvegarde.sh a été bloqué."
