<?php

class PMProio_Member_Edit_Panel extends PMPro_Member_Edit_Panel {
	/**
	 * Set up the panel.
	 */
	public function __construct() {
		$this->slug        = 'invite-codes';
		$this->title       = __( 'Invite Codes', 'pmpro-invite-only' );
		$this->submit_text = current_user_can( 'edit_users' ) ? __( 'Update Member', 'pmpro-invite-only' ) : '';
	}

	/**
	 * Display the panel contents.
	 */
	protected function display_panel_contents() {
		// Get the user being edited.
		$user = self::get_user();

		// Check if the user used an invite code at signup.
		$invite_code_used = $user->pmpro_invite_code_at_signup;
		if ( ! empty( $invite_code_used ) ) {
			// Display invite code used by the member at signup.
			echo '<h3>' . esc_html__( 'Invite Code Used at Signup', 'pmpro-invite-only' ) . '</h3>';
			$invite_giver_user_id   = pmproio_getUserFromInviteCode( $invite_code_used );
			$invite_giver_user_data = get_userdata( $invite_giver_user_id );

			if ( false !== $invite_giver_user_data ) {
				echo '<p>' . esc_html( sprintf( '%s (%s)', $invite_code_used, $invite_giver_user_data->user_login ) ) . '</p>';
			} else {
				echo '<p>' . esc_html( $invite_code_used ) . '</p>';
			}
		}

		// Get the user's Invite Codes.
		$codes = pmproio_getInviteCodes( $user->ID, true );

		// Display available invite codes.
		?>
		<h3><?php esc_html_e( 'Available Invite Codes', 'pmpro-invite-only' ); ?></h3>
		<?php

		// If the user has no codes, show a message, let admin generate codes, and bail.
		if ( empty( $codes ) ) {
			echo '<p>' . esc_html__( 'This user does not have any invite codes.', 'pmpro-invite-only' ) . '</p>';
			echo '<p>' . esc_html__( 'Increase total available invites to', 'pmpro-invite-only' ) . ' <input type="text" name="pmpro_add_invites" id="pmpro_add_invites" value="" /></p>';
			return;
		}

		// Display unused codes.
		if ( empty( $codes['unused'] ) ) {
			echo '<p>' . esc_html__( 'All codes have been used.', 'pmpro-invite-only' ) . '</p>';
		} else {
			?>
			<ul>
				<?php
				foreach ( $codes['unused'] as $code ) {
					echo '<li>' . esc_html( $code ) . '</li>';
				}
				?>
			</ul>
			<?php
		}

		// Let admins generate more invite codes.
		echo '<p>' . esc_html__( 'Increase total available invites to', 'pmpro-invite-only' ) . ' <input type="text" name="pmpro_add_invites" id="pmpro_add_invites" value="" /></p>';

		// Display used codes.
		?>
		<h3><?php esc_html_e( 'Used Invite Codes', 'pmpro-invite-only' ); ?></h3>
		<?php
		if ( empty( $codes['used'] ) ) {
			echo '<p>' . esc_html__( "None of the member's codes have been used.", 'pmpro-invite-only' ) . '</p>';
		} else {
			?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Member', 'pmpro-invite-only' ); ?></th>
						<th><?php esc_html_e( 'Invite Code', 'pmpro-invite-only' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $codes['used'] as $code => $user_ids ) {
						foreach ( $user_ids as $user_id ) {
							$user = get_userdata( $user_id );
							?>
							<tr>
								<td>
									<?php
									if ( ! empty( $user ) ) {
										echo '<a href="' . esc_url( add_query_arg( 'user_id', $user_id, admin_url( 'user-edit.php' ) ) ) . '">' . esc_html( $user->user_login ) . '</a>';
									} else {
										esc_html_e( 'N/A (Deleted or abandoned)', 'pmpro-invite-only' );
									}
									?>
								</td>
								<td><?php echo esc_html( $code ); ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
			</table>
			<?php
		}
	}

	/**
	 * Save the panel data.
	 */
	public function save() {

		// If the current user can't edit users, bail.
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		// Get the user we are editing.
		$user = self::get_user();

		// Check if the save is coming from the Invite Codes panel.
		if ( ! empty( $_REQUEST['pmpro_member_edit_panel'] ) && 'invite-codes' === $_REQUEST['pmpro_member_edit_panel'] ) {

			if ( ! empty( $_POST['pmpro_add_invites'] ) ) {
				$invites_to_add = intval( $_POST['pmpro_add_invites'] );
				if ( $invites_to_add > 0 ) {
					$new_codes = pmproio_createInviteCodes( $user->ID, true, $invites_to_add );
					if ( ! empty( $new_codes ) ) {
						$new_codes_saved = pmproio_saveInviteCodes( $new_codes, $user->ID );
						if ( $new_codes_saved ) {
							pmpro_setMessage( __( 'Invite codes added successfully.', 'pmpro-invite-only' ), 'pmpro_success' );
							return;
						}
					}

					// If we get here, there was an error generating or saving the codes.
					pmpro_setMessage( __( 'There was an error adding the invite codes.', 'pmpro-invite-only' ), 'pmpro_error' );
				}
			}
		}
	}
}
