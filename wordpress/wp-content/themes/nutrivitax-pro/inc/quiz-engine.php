/**
 * NutriVitaX Pro — Moteur de quiz santé (Phase 1 - Rule-based)
 *
 * Ce fichier contient le moteur de quiz basique qui recommande des
 * produits selon des règles pré-définies. Il est chargé conditionnellement
 * par functions.php quand nvx_quiz_enabled = 'yes'.
 *
 * @package NutriVitaX_Pro
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre le custom post type pour les questions de quiz (si nécessaire).
 * Utilise un CPT préfixé nvx_ pour éviter les conflits.
 *
 * @since  0.1.0
 */
function nvx_register_quiz_cpt(): void {
	// Placeholder pour Phase 1 - les questions sont stockées en option
	// et/ou via ACF fields. Pas de CPT supplémentaire en MVP.
}
