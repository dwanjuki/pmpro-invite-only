<?php
/**
 * Add a panel to the Edit Member dashboard page.
 *
 * @since TBD
 *
 * @param array $panels Array of panels.
 * @return array
 */
function pmproio_pmpro_member_edit_panels( $panels ) {
	// If the class doesn't exist and the abstract class does, require the class.
	if ( ! class_exists( 'PMProio_Member_Edit_Panel' ) && class_exists( 'PMPro_Member_Edit_Panel' ) ) {
		require_once( PMPROIO_DIR . '/classes/class-pmproio-member-edit-panel.php' );
	}

	// If the class exists, add a panel.
	if ( class_exists( 'PMProio_Member_Edit_Panel' ) ) {
		$panels[] = new PMProio_Member_Edit_Panel();
	}

	return $panels;
}

/**
 * Hook the panel function for admins editing a member's profile.
 *
 * @since TBD
 */
function pmproio_hook_edit_member_profile() {
	// If the `pmpro_member_edit_get_panels()` function exists, add a panel.
	if ( function_exists( 'pmpro_member_edit_get_panels' ) ) {
		add_filter( 'pmpro_member_edit_panels', 'pmproio_pmpro_member_edit_panels' );
	}
}
add_action( 'admin_init', 'pmproio_hook_edit_member_profile', 0 );
