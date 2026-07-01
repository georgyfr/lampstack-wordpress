<?php
/**
 * NutriVitaX Pro — Fallback index pour compatibilité
 *
 * Ce fichier n'est PAS utilisé en navigation normale (le template FSE
 * templates/index.html est utilisé). Il sert uniquement de fallback
 * pour les fonctionnalités WP qui scannent le thème (activation,
 * listing, compatibilité plugins).
 *
 * @package NutriVitaX_Pro
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

// Rediriger vers le template FSE si possible
if ( function_exists( 'wp_blocks_theme') && wp_blocks_theme() ) {
	// Pour un Block Theme, le contenu est géré par les templates FSE
	// Ce fichier ne devrait jamais être atteint en navigation normale.
} else {
	// Fallback classique si le FSE n'est pas disponible
	get_header();
	?>
	<main id="nvx-woo-content" class="nvx-woo-content" role="main">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
		else :
			?>
			<p><?php esc_html_e( 'Aucun contenu trouvé.', 'nutrivitax-pro' ); ?></p>
			<?php
		endif;
		?>
	</main>
	<?php
	get_footer();
}
