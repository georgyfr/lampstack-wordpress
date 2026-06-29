# LAMP Stack - Environnement WordPress Développement

Environnement LAMP complet (Apache + MariaDB + PHP) sans accès root, avec WordPress, WooCommerce et Elementor pré-installés.

## Stack Technique

| Composant | Version | Port |
|-----------|---------|------|
| Apache | 2.4 | 8080 |
| MariaDB | 11.8 | 3307 |
| PHP | 8.4.21 | - |
| WordPress | 7.0 | - |
| WooCommerce | 10.9.1 | - |
| Elementor | 4.1.4 | - |

## Installation

```bash
git clone https://github.com/z/lampstack-wordpress.git
cd lampstack-wordpress
bash scripts/setup.sh
```

## Gestion

```bash
# Démarrer l'environnement
bash scripts/lamp.sh start

# Arrêter
bash scripts/lamp.sh stop

# Redémarrer
bash scripts/lamp.sh restart

# Voir le statut
bash scripts/lamp.sh status

# WP-CLI
bash scripts/lamp.sh wp plugin list
bash scripts/lamp.sh wp theme list

# Client MySQL
bash scripts/lamp.sh mysql
```

## Accès

- **Site** : http://localhost:8080
- **Admin** : http://localhost:8080/wp-admin
- **Login** : admin / admin123
- **Base de données** : wordpress (wp_user / wp_password_2024)

## Structure

```
lampstack-wordpress/
├── config/
│   ├── apache2/          # Configuration Apache
│   ├── mysql/            # Configuration MariaDB
│   └── php/              # Configuration PHP
├── scripts/
│   ├── setup.sh          # Script d'installation complet
│   └── lamp.sh           # Script de gestion (start/stop/restart)
├── wordpress/
│   └── wp-content/
│       ├── themes/       # Vos thèmes personnalisés
│       └── plugins/      # Vos plugins personnalisés
├── .gitignore
└── README.md
```

## Développement de Thèmes

Placez vos thèmes dans `wordpress/wp-content/themes/` et utilisez WP-CLI pour les activer :

```bash
bash scripts/lamp.sh wp theme activate votre-theme
```
