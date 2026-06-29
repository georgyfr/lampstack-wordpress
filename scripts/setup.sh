#!/bin/bash
set -e

LAMP=$LAMP
export LD_LIBRARY_PATH=$LAMP/usr/lib/x86_64-linux-gnu:$LAMP/lib/x86_64-linux-gnu
export PHPRC=$LAMP/etc/php/8.4/cli
export PATH=$LAMP/usr/bin:$LAMP/usr/sbin:$PATH

echo '========================================'
echo '  CONFIGURATION FINALE LAMP STACK'
echo '========================================'

# ============================================
# 1. FIX PHP INI (correct extension loading)
# ============================================
echo '[1/6] Configuration PHP...'

cat > $LAMP/etc/php/8.4/cli/php.ini << 'PHPEOF'
[PHP]
engine = On
short_open_tag = Off
precision = 14
output_buffering = 4096
zlib.output_compression = Off
implicit_flush = Off
serialize_precision = -1
disable_functions =
disable_classes =
zend.enable_gc = On
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
post_max_size = 128M
upload_max_filesize = 128M
max_file_uploads = 50
display_errors = On
display_startup_errors = On
error_reporting = E_ALL
log_errors = On
error_log = $LAMP/var/log/php_error.log
date.timezone = Africa/Douala
session.save_handler = files
session.save_path = $LAMP/var/lib/php/sessions
session.use_strict_mode = 0
session.use_cookies = 1
session.use_only_cookies = 1
session.name = PHPSESSID
session.cookie_httponly = 1
session.gc_maxlifetime = 1440
file_uploads = On
extension_dir = $LAMP/usr/lib/php/20240924

; Load order: pdo first, then mysqlnd, then mysql extensions
extension=pdo
extension=mysqlnd
extension=mysqli
extension=pdo_mysql
extension=xml
extension=simplexml
extension=dom
extension=mbstring
extension=curl
extension=zip
extension=gd
extension=intl
extension=fileinfo
extension=exif
extension=readline
extension=tokenizer

; opcache is a Zend extension, load separately
zend_extension=$LAMP/usr/lib/php/20240924/opcache.so

; json and iconv are built-in since PHP 8.4

max_input_vars = 3000
allow_url_fopen = On
allow_url_include = Off

[CLI Server]
cli_server.color = On
PHPEOF

# Copy for Apache
cp $LAMP/etc/php/8.4/cli/php.ini $LAMP/etc/php/8.4/apache2/php.ini

# Verify PHP
PHP_VER=$(php -r 'echo PHP_VERSION;')
echo "  PHP $PHP_VER"
php -m 2>/dev/null | rg -q 'mysqli' && echo '  mysqli: OK' || echo '  mysqli: FAIL'
php -m 2>/dev/null | rg -q 'pdo_mysql' && echo '  pdo_mysql: OK' || echo '  pdo_mysql: FAIL'

# ============================================
# 2. FIX APACHE PHP CONFIG
# ============================================
echo '[2/6] Configuration Apache PHP...'

cat > $LAMP/etc/apache2/mods-enabled/php8.4.conf << 'PHPCONF'
<FilesMatch ".+\\.ph(?:ar|p|tml)$">
    SetHandler application/x-httpd-php
</FilesMatch>

<FilesMatch ".+\\.phps$">
    SetHandler application/x-httpd-php-source
    Require all denied
</FilesMatch>

<FilesMatch "^\\.ph(?:ar|p|ps|tml)$">
    Require all denied
</FilesMatch>

php_admin_value extension_dir "$LAMP/usr/lib/php/20240924"
php_admin_value extension "pdo.so mysqlnd.so mysqli.so pdo_mysql.so xml.so simplexml.so dom.so mbstring.so curl.so zip.so gd.so intl.so fileinfo.so exif.so readline.so tokenizer.so"
PHPCONF

# Restart Apache
echo '  Redémarrage Apache...'
$LAMP/usr/sbin/apache2 -f $LAMP/etc/apache2/apache2.conf -k restart 2>&1 || true
sleep 2
HTTP=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/ 2>/dev/null)
echo "  HTTP Response: $HTTP"

# ============================================
# 3. WP-CLI
# ============================================
echo '[3/6] Installation WP-CLI...'
if [ ! -f $LAMP/usr/local/bin/wp ] || ! php -r 'Phar::isValid()' 2>/dev/null; then
    curl -fsSL --max-time 120 -o $LAMP/usr/local/bin/wp \
        'https://github.com/wp-cli/builds/raw/gh-pages/phar/wp-cli.phar' 2>&1
    chmod +x $LAMP/usr/local/bin/wp
fi

# Verify
if php -r 'new Phar("$LAMP/usr/local/bin/wp"); echo "WP-CLI OK\\n";' 2>/dev/null; then
    echo '  WP-CLI: OK'
else
    echo '  WP-CLI: tentative alternative...'
    curl -fsSL --max-time 120 -o $LAMP/usr/local/bin/wp.phar \
        'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar' 2>&1
    chmod +x $LAMP/usr/local/bin/wp.phar
    mv $LAMP/usr/local/bin/wp.phar $LAMP/usr/local/bin/wp 2>/dev/null || true
fi

# ============================================
# 4. WORDPRESS
# ============================================
echo '[4/6] Installation WordPress...'

WPCLI="php $LAMP/usr/local/bin/wp --path=$LAMP/wordpress --allow-root"

if ! $WPCLI core is-installed 2>/dev/null; then
    $WPCLI core install \
        --url='http://localhost:8080' \
        --title='Mon Site WooCommerce' \
        --admin_user='admin' \
        --admin_password='admin123' \
        --admin_email='admin@localhost.com' \
        --locale='fr_FR' 2>&1
    echo '  WordPress installé !'
else
    echo '  WordPress déjà installé'
fi

WP_VER=$($WPCLI core version 2>/dev/null)
echo "  WordPress $WP_VER"

# ============================================
# 5. WOOCOMMERCE + ELEMENTOR
# ============================================
echo '[5/6] Installation des plugins...'

if ! $WPCLI plugin is-installed woocommerce 2>/dev/null; then
    echo '  WooCommerce...'
    $WPCLI plugin install woocommerce --activate 2>&1
else
    echo '  WooCommerce: déjà installé'
fi

if ! $WPCLI plugin is-installed elementor 2>/dev/null; then
    echo '  Elementor...'
    $WPCLI plugin install elementor --activate 2>&1
else
    echo '  Elementor: déjà installé'
fi

echo ''
echo '  Plugins actifs:'
$WPCLI plugin list --status=active --format=table 2>/dev/null

# ============================================
# 6. CREATE MANAGEMENT SCRIPT
# ============================================
echo '[6/6] Script de gestion...'

cat > $LAMP/lamp.sh << 'MGREOF'
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
        echo 'MariaDB OK'
        echo 'Démarrage Apache...'
        source $LAMP/etc/apache2/envvars
        $LAMP/usr/sbin/apache2 -f $APACHE_CONF -k start 2>&1
        sleep 2
        echo "Apache OK - http://localhost:8080"
        ;;
    stop)
        $LAMP/usr/sbin/apache2 -f $APACHE_CONF -k stop 2>/dev/null
        mysql --defaults-file=$LAMP/etc/mysql/my.cnf -u root -e 'SHUTDOWN' 2>/dev/null
        pkill -f mariadbd 2>/dev/null
        echo 'Arrêté'
        ;;
    restart)
        $0 stop; sleep 2; $0 start
        ;;
    status)
        echo "MariaDB: $(pgrep -f mariadbd > /dev/null && echo EN MARCHE || echo ARRETE)"
        echo "Apache:  $(pgrep -f apache2 > /dev/null && echo EN MARCHE || echo ARRETE)"
        ;;
    wp)
        shift
        php $LAMP/usr/local/bin/wp --path=$LAMP/wordpress --allow-root "$@"
        ;;
    mysql)
        mysql --defaults-file=$LAMP/etc/mysql/my.cnf -u root
        ;;
    *)
        echo 'Usage: lamp.sh {start|stop|restart|status|wp|mysql}'
        ;;
esac
MGREOF
chmod +x $LAMP/lamp.sh

echo ''
echo '============================================'
echo '  ENVIRONNEMENT LAMP COMPLET !'
echo '============================================'
echo ''
echo "  Site:     http://localhost:8080"
echo '  Admin:    http://localhost:8080/wp-admin'
echo '  Login:    admin / admin123'
echo ''
echo "  MariaDB:  port 3307"
echo '  BDD:      wordpress (wp_user / wp_password_2024)'
echo ''
echo '  Gestion:  $LAMP/lamp.sh'
echo '            start | stop | restart | status | wp | mysql'
echo '============================================'
