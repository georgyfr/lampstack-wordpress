# NutriVitaX Pro — WordPress FSE Block Theme

Thème WooCommerce FSE premium pour boutiques de compléments alimentaires, nutraceutique et bien-être.

## Design System: BioLab Luxe

| Élément | Valeur |
|---------|--------|
| Primaire | `#1A6B3A` (Forêt Profonde) |
| Secondaire | `#2ECC71` (Émeraude Vif) |
| Accent | `#F4A900` (Or Scientifique) |
| Dark | `#12243A` (Nuit Marine) |
| Light | `#F0F9F4` (Brume Verte) |

## Architecture

```
nutrivitax-pro/
├── style.css          # Theme header
├── theme.json         # FSE design system (palette, typography, spacing)
├── functions.php      # Bootstrap + anti-conflict guards
├── index.php          # Fallback template
├── uninstall.php       # Clean uninstall
├── screenshot.png     # Theme preview
├── templates/         # FSE templates
│   ├── home.html
│   ├── index.html
│   ├── single-product.html
│   ├── archive-product.html
│   ├── page-quiz.html
│   └── page-stack-builder.html
├── parts/             # Template parts
│   ├── header.html
│   ├── footer.html
│   └── sidebar-shop.html
├── patterns/          # Block patterns (placeholder)
├── inc/               # PHP includes
│   ├── setup.php
│   ├── woo-enhancements.php
│   └── quiz-engine.php
├── assets/
│   ├── js/
│   │   └── theme.js
│   ├── css/layers/
│   └── images/
└── languages/
```

## Version

v0.1.0 — Phase 1 MVP skeleton
