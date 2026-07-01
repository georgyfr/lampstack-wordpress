#!/bin/bash
LAMP=$LAMP
export LD_LIBRARY_PATH=$LAMP/usr/lib/x86_64-linux-gnu:$LAMP/lib/x86_64-linux-gnu
export PHPRC=$LAMP/etc/php/8.4/cli
export PATH=$LAMP/usr/bin:$LAMP/usr/sbin:$PATH
APACHE_CONF=$LAMP/etc/apache2/apache2.conf

case "$1" in
    start)
        echo 'Démarrage MariaDB...'
        $LAMP/usr/bin/mysqld_safe --defaults-file=$LAMP/etc/mysql/my.cnf --ledir=$LAMP/usr/sbin &>/dev/null &
        for i in $(seq 1 30); do
            mysql --defaults-file=$LAMP/etc/mysql/my.cnf -u root -e 'SELECT 1' &>/dev/null && break
            sleep 1
        done
        echo 'MariaDB OK (port 3307)'
        echo 'Démarrage Apache...'
        export APACHE_RUN_USER=z
        export APACHE_RUN_GROUP=z
        export APACHE_PID_FILE=$LAMP/var/run/apache2/apache2.pid
        export APACHE_RUN_DIR=$LAMP/var/run/apache2
        export APACHE_LOCK_DIR=$LAMP/var/lock/apache2
        export APACHE_LOG_DIR=$LAMP/var/log/apache2
        export APACHE_CONFDIR=$LAMP/etc/apache2
        export LD_LIBRARY_PATH=$LAMP/usr/lib/x86_64-linux-gnu:$LAMP/lib/x86_64-linux-gnu
        export PHPRC=$LAMP/etc/php/8.4/apache2
        $LAMP/usr/sbin/apache2 -f $APACHE_CONF -k start 2>&1
        sleep 2
        HTTP=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/ 2>/dev/null)
        echo "Apache OK (port 8080) - HTTP $HTTP"
        echo ""
        echo "  Site:   http://localhost:8080"
        echo "  Admin:  http://localhost:8080/wp-admin"
        echo "  Login:  admin / admin123"
        ;;
    stop)
        export APACHE_RUN_USER=z APACHE_RUN_GROUP=z
        export APACHE_PID_FILE=$LAMP/var/run/apache2/apache2.pid
        export APACHE_RUN_DIR=$LAMP/var/run/apache2
        export APACHE_LOCK_DIR=$LAMP/var/lock/apache2
        export APACHE_LOG_DIR=$LAMP/var/log/apache2
        export APACHE_CONFDIR=$LAMP/etc/apache2
        export LD_LIBRARY_PATH=$LAMP/usr/lib/x86_64-linux-gnu:$LAMP/lib/x86_64-linux-gnu
        $LAMP/usr/sbin/apache2 -f $APACHE_CONF -k stop 2>/dev/null
        mysql --defaults-file=$LAMP/etc/mysql/my.cnf -u root -e 'SHUTDOWN' 2>/dev/null
        pkill -f mariadbd 2>/dev/null
        echo 'Tout arrêté'
        ;;
    restart)
        $0 stop; sleep 2; $0 start
        ;;
    status)
        echo "MariaDB: $(pgrep -f mariadbd > /dev/null && echo EN MARCHE || echo ARRETE)"
        echo "Apache:  $(pgrep -f apache2 > /dev/null && echo EN MARCHE || echo ARRETE)"
        echo ""
        echo "WordPress:"
        $LAMP/usr/bin/php $LAMP/usr/local/bin/wp --path=$LAMP/wordpress --allow-root core version 2>/dev/null
        echo ""
        echo "Plugins actifs:"
        $LAMP/usr/bin/php $LAMP/usr/local/bin/wp --path=$LAMP/wordpress --allow-root plugin list --status=active --format=table 2>/dev/null
        ;;
    wp)
        shift
        $LAMP/usr/bin/php $LAMP/usr/local/bin/wp --path=$LAMP/wordpress --allow-root "$@"
        ;;
    mysql)
        mysql --defaults-file=$LAMP/etc/mysql/my.cnf -u root
        ;;
    *)
        echo 'Usage: lamp.sh {start|stop|restart|status|wp|mysql}'
        echo ''
        echo '  start   - Demarrer Apache + MariaDB'
        echo '  stop    - Arreter tout'
        echo '  restart - Redemarrer tout'
        echo '  status  - Voir le statut complet'
        echo '  wp ...  - Lancer WP-CLI'
        echo '  mysql   - Ouvrir le client MySQL'
        ;;
esac