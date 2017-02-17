<?php
function init_brasawporg_cpt() {
	require_once( 'class-metabox.php');
	if ( ! is_admin() ) {
		return;
	}
	$args = array(
		'public'    => true,
		'label'     => __( 'WP.org Plugins', 'brasa-wporg-deploy' ),
		'menu_icon' => 'dashicons-wordpress',
	);
	register_post_type( 'wporg', $args );
	remove_post_type_support( 'wporg', 'editor' );

	$options = new Brasa_GitHub_Release_To_WPORG_Metabox(
		'brasawporg_options', // Slug/ID of the Metabox (required)
    	__( 'WP.org Deploy Settings', 'brasa-wporg-deploy' ), // name (required)
    	'wporg', //CPT
    	'normal', // Context
    	'high' // Prioridade (opções: high, core, default ou low) (opcional)
	);
	$options->set_fields(
		array(
			array(
				'id'          => 'wporg_user',
				'label'       => __( 'WordPress.org Username', 'brasa-wporg-deploy' ),
				'type'        => 'text',
    		),
    		array(
				'id'          => 'wporg_password',
				'label'       => __( 'WordPress.org Password', 'brasa-wporg-deploy' ),
				'type'        => 'text',
    		),
    		array(
				'id'          => 'wporg_slug',
				'label'       => __( 'WordPress.org Plugin Slug', 'brasa-wporg-deploy' ),
				'type'        => 'text',
    		),
    		array(
				'id'          => 'github_secret',
				'label'       => __( 'GitHub Hook Secret', 'brasa-wporg-deploy' ),
				'type'        => 'text',
    		)

		)
	);
}
add_action( 'init', 'init_brasawporg_cpt' );
