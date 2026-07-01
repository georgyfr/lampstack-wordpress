#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════════
# NutriVitaX Pro — Start Script (portable LAMP-like environment)
# Similaire à XAMPP/Laragon : PHP 8.3 + SQLite + WordPress 7
# ═══════════════════════════════════════════════════════════════════

THEME_DIR="$(cd "$(dirname "$0")" && pwd)"
PHP_BIN="$THEME_DIR/bin/php/bin/php"
WP_DIR="$THEME_DIR/wordpress"
PORT="${1:-8080}"

# Vérifications
if [ ! -f "$PHP_BIN" ]; then
    echo "ERREUR: PHP non trouvé à $PHP_BIN"
    echo "Exécutez d'abord: bash setup.sh"
    exit 1
fi

if [ ! -f "$WP_DIR/wp-load.php" ]; then
    echo "ERREUR: WordPress non trouvé à $WP_DIR"
    exit 1
fi

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║  NutriVitaX Pro — Serveur de développement            ║"
echo "║  PHP 8.3.20 | SQLite | WordPress 7.0 (fr_FR)          ║"
echo "╠═══════════════════════════════════════════════════════════╣"
echo "║  URL:        http://localhost:$PORT                   ║"
echo "║  Admin:      http://localhost:$PORT/wp-admin/         ║"
echo "║  User:       admin                                    ║"
echo "║  Password:   Admin@1234!                              ║"
echo "║  Thème:      NutriVitaX Pro (actif)                   ║"
echo "║  DB:         SQLite (wp-content/database/)            ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter le serveur."
echo ""

cd "$WP_DIR" || exit 1
exec "$PHP_BIN" -S "0.0.0.0:$PORT" router.php