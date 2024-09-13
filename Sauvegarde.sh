#!/bin/bash

# alcasar-mysql.sh adapté pour une utilistation local

# Variables de configuration
rep_tr="$USER"   # Remplacez par le chemin de votre répertoire de sauvegarde local
DIR_BIN="$USER"           # Remplacez par le chemin de votre répertoire de scripts local
PASSWD_FILE="/root/ALCASAR-passwords.txt" # Remplacez par le chemin de votre fichier de mot de passe local
DB_RADIUS="radius"            # Remplacez par le nom de votre base de données
DB_USER="radius"                    # Remplacez par votre utilisateur de base de données
DB_PASS="db_password"                # Remplacez par votre mot de passe de base de données
new="$(date +%Y%m%d-%Hh%M)"               # date & heure du fichier
fichier="alcasar-users-database-$new.sql"

stop_acct () #met fin à toutes les sessions actives en ajustant les valeurs dans la base de données et avec la date du jour.
{
    date_now=`date "+%F %X"`
    echo "UPDATE radacct SET acctstoptime = '$date_now', acctterminatecause = 'Admin-Reset' WHERE acctstoptime IS NULL" | mysql -u$DB_USER -p$DB_PASS $DB_RADIUS
}

check () 
{
    echo "check (and repair if needed) the database :"
    mysqlcheck --databases $DB_RADIUS -u $DB_USER -p$DB_PASS --auto-repair
}

expire_user () 
{
    del_date=`date +%F`
    MYSQL_USER=""
    MYSQL_USER=`/usr/bin/mysql -u$DB_USER -p$DB_PASS $DB_RADIUS -ss --execute  "SELECT username FROM radcheck WHERE ( DATE_SUB(CURDATE(),INTERVAL 7 DAY) > STR_TO_DATE(value,'%d %M %Y')) AND attribute='Expiration';"`
    for u in $MYSQL_USER
    do
         /usr/bin/mysql -u$DB_USER -p$DB_PASS $DB_RADIUS --execute "DELETE FROM radusergroup WHERE username = '$u'; DELETE FROM radreply WHERE username = '$u'; DELETE FROM userinfo WHERE UserName = '$u'; DELETE FROM radcheck WHERE username = '$u';"
        if [ $? = 0 ]
        then
            echo "User $u was deleted $del_date" >> /var/log/mysqld/delete_user.log
        else
            echo "Delete User $u : Error $del_date" >> /var/log/mysqld/delete_user.log
        fi
    done
}

expire_group ()
{
    del_date=`date +%F`
    MYSQL_GROUP=""
    MYSQL_GROUP=`/usr/bin/mysql -u$DB_USER -p$DB_PASS $DB_RADIUS -ss --execute  "SELECT groupname FROM radgroupcheck WHERE ( DATE_SUB(CURDATE(),INTERVAL 7 DAY) > STR_TO_DATE(value,'%d %M %Y')) AND attribute='Expiration';"`
    for g in $MYSQL_GROUP
    do
        MYSQL_USERGROUP=""
        MYSQL_USERGROUP=`/usr/bin/mysql -u$DB_USER -p$DB_PASS $DB_RADIUS -ss --execute  "SELECT username FROM radusergroup WHERE groupname = '$g';"`
        for u in $MYSQL_USERGROUP
        do
            /usr/bin/mysql -u$DB_USER -p$DB_PASS $DB_RADIUS --execute "DELETE FROM radusergroup WHERE username = '$u'; DELETE FROM radreply WHERE username = '$u'; DELETE FROM userinfo WHERE UserName = '$u'; DELETE FROM radcheck WHERE username = '$u';"
            if [ $? = 0 ]
            then
                echo "User $u was deleted $del_date" >> /var/log/mysqld/delete_user.log
            else
                echo "Delete User $u : Error $del_date" >> /var/log/mysqld/delete_user.log
            fi
        done
        /usr/bin/mysql -u$DB_USER -p$DB_PASS $DB_RADIUS --execute "DELETE FROM radgroupreply WHERE groupname = '$g'; DELETE FROM radgroupcheck WHERE groupname = '$g';"
        if [ $? = 0 ]
        then
            echo "Group $g was deleted $del_date" >> /var/log/mysqld/delete_group.log
        else
            echo "Delete Group $g : Error $del_date" >> /var/log/mysqld/delete_group.log
        fi
    done
}

usage="Usage: alcasar-mysql.sh { -d or --dump } | { -c or --check } | { -i or --import } | { -r or --raz } | { -a or --acct_stop } | [ -e or --expire_user ]"
nb_args=$#
args=$1
if [ $nb_args -eq 0 ]
then
    nb_args=1
    args="-h"
fi
case $args in
    -\? | -h* | --h*)
        echo "$usage"
        exit 0
        ;;
    -d | --dump | -dump)
        [ -d $rep_tr ] || mkdir -p $rep_tr
        if [ -e  $fichier ];
            then rm -f  $fichier
        fi
        check
        echo "Export the database in file : $fichier.gz"
        mysqldump -u $DB_USER -p$DB_PASS --opt -BcQC  $DB_RADIUS > $rep_tr/$fichier
        gzip -f $rep_tr/$fichier
        echo "End of export $( date "+%Hh %Mmn" )"
        ;;
    -c | --check | -check)
        check
        ;;
    -i | --import | -import)
        if [ $nb_args -ne 2 ]
            then
                echo "Enter a SQL file name ('.sql' or '.sql.gz')"
            exit 0
        else
            case $2 in
            *.sql.gz )
                gunzip -f < $2 | mysql -u $DB_USER -p$DB_PASS
                stop_acct
                ;;
            *.sql )
                mysql -u $DB_USER -p$DB_PASS < $2
                stop_acct
                ;;
            esac
            migrationsPath="$DIR_BIN/alcasar-db-migrations"
            "$migrationsPath/alcasar-migration-3.2.0_dbStructure.sh"
            "$migrationsPath/alcasar-migration-3.3.0_dbRadiusAttrs.sh"
            "$migrationsPath/alcasar-migration-3.3.1_dbRadiusAttrs.sh"
        fi
        ;;
    -r | --raz | -raz)
        mysqldump -u $DB_USER -p$DB_PASS --opt -BcQC  $DB_RADIUS > $rep_tr/$fichier
        gzip -f $rep_tr/$fichier
        mysql -u$DB_USER -p$DB_PASS $DB_RADIUS < /etc/raddb/empty-radiusd-db.sql
        ;;
    -a | --acct_stop | -acct_stop)
        stop_acct
        ;;
    -e | --expire_user)
        expire_user
        expire_group
        ;;
    *)
        echo "Unknown argument :$1";
        echo "$usage"
        exit 1
        ;;
esac
