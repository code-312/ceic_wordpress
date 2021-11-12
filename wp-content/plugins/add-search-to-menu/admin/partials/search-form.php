<?php
/**
 * Represents the view for the plugin to add new search form page or edit it.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user to create or edit search form.
 *
 * @package IS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exits if accessed directly.
}
?>
<div class="wrap">

	<h1 class="wp-heading-inline">
	<span class="is-search-image"></span>
	<?php
		if ( $post->initial() ) {
			esc_html_e( 'Add New Search Form', 'add-search-to-menu' );
		} else {
			esc_html_e( 'Edit Search Form', 'add-search-to-menu' );
		}
	?></h1>

	<?php
		if ( ! $post->initial() && current_user_can( 'is_edit_search_forms' ) ) {
			echo sprintf( '<a href="%1$s" class="add-new-h2">%2$s</a>',
				esc_url( menu_page_url( 'ivory-search-new', false ) ),
				esc_html( __( 'Add New Search Form', 'add-search-to-menu' ) ) );
		}
	?>

	<hr class="wp-header-end">

	<?php do_action( 'is_admin_notices' ); ?>

	<?php
	if ( $post ) :

		if ( current_user_can( 'is_edit_search_form', $post_id ) ) {
			$disabled = '';
		} else {
			$disabled = ' disabled="disabled"';
		}

		$tab = 'includes';
		if ( isset( $_GET['tab'] ) ) {
			switch ( $_GET['tab'] ) {
				case 'excludes':
					$tab = 'excludes';
					break;
				case 'customize':
					$tab = 'customize';
					break;
				case 'ajax':
					$tab = 'ajax';
					break;
				case 'options':
					$tab = 'options';
					break;
			}
		}
	?>

	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post_id, 'tab' => $tab ), menu_page_url( 'ivory-search', false ) ) ); ?>" id="is-admin-form-element"<?php do_action( 'is_post_edit_form_tag' ); ?>>
		<?php
			if ( current_user_can( 'is_edit_search_form', $post_id ) ) {
				wp_nonce_field( 'is-save-search-form_' . $post_id );
			}
		?>
		<input type="hidden" id="post_ID" name="post_ID" value="<?php echo (int) $post_id; ?>" />
		<input type="hidden" id="is_locale" name="is_locale" value="<?php echo esc_attr( $post->locale() ); ?>" />
		<input type="hidden" id="hiddenaction" name="action" value="save" />
		<input type="hidden" id="tab" name="tab" value="<?php echo $tab; ?>" />

		<div id="poststuff">
		<div id="search-body" class="metabox-holder columns-2">
			<div id="searchtbox-container-1" class="postbox-container">
			<div id="post-body-content">
				<div id="titlediv">
					<div id="titlewrap">
						<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo esc_html( __( 'Add title', 'add-search-to-menu' ) ); ?></label>
					<?php
						$posttitle_atts = array(
							'type' => 'text',
							'name' => 'post_title',
							'size' => 30,
							'value' => $post->initial() ? '' : $post->title(),
							'id' => 'title',
							'spellcheck' => 'true',
							'autocomplete' => 'off',
							'disabled' =>
								current_user_can( 'is_edit_search_form', $post_id ) && 'default-search-form' !== $post->name() ? '' : 'disabled',
							'title' => 'default-search-form' !== $post->name() ? $post->title() : __( "Editing the title of Default Search Form is restricted", 'add-search-to-menu' ),
						);

						echo sprintf( '<input %s />', IS_Admin_Public::format_atts( $posttitle_atts ) );
					?>
					</div><!-- #titlewrap -->

					<div class="inside">
						<p class="description">
						<?php
						if ( ! $post->initial() ) {
						?>
						<label for="is-shortcode"><?php echo esc_html( __( "Copy this shortcode and paste it into your post, page, or text widget content:", 'add-search-to-menu' ) ); ?></label>
						<?php
						}
						$shortcode_text = __( "Please save search form to generate shortcode", 'add-search-to-menu' );
						if ( ! $post->initial() ) {
							$shortcode_text = esc_attr( $post->shortcode() );
						}
						?>
						<span class="shortcode wp-ui-highlight"><input type="text" id="is-shortcode" onfocus="this.select();" readonly="readonly" class="large-text code" value="<?php echo $shortcode_text; ?>" title="<?php _e( "Click to copy shortcode", 'add-search-to-menu' ); ?>" /></span>

						</p>
					</div>
				</div><!-- #titlediv -->
			</div><!-- #post-body-content -->
			<div id="search-form-editor">
			<?php
				$editor = new IS_Search_Editor( $post );
				$panels = array();

				if ( current_user_can( 'is_edit_search_form', $post_id ) ) {
					$panels = array(
						'includes' => array(
							'title' => __( 'Search', 'add-search-to-menu' ),
							'callback' => 'includes_panel',
                                                        'description' => __( 'Configure Searchable Content', 'add-search-to-menu' ),
						),
						'excludes' => array(
							'title' => __( 'Exclude', 'add-search-to-menu' ),
							'callback' => 'excludes_panel',
                                                        'description' => __( 'Exclude Content From Search', 'add-search-to-menu' ),
						),
						'customize' => array(
							'title' => __( 'Design', 'add-search-to-menu' ),
							'callback' => 'customize_panel',
                                                        'description' => __( 'Design Search Form Colors, Text and Style', 'add-search-to-menu' ),
						),
						'ajax' => array(
							'title' => __( 'AJAX', 'add-search-to-menu' ),
							'callback' => 'ajax_panel',
                                                        'description' => __( 'Configure AJAX Search', 'add-search-to-menu' ),
						),
						'options' => array(
							'title' => __( 'Options', 'add-search-to-menu' ),
							'callback' => 'options_panel',
                                                        'description' => __( 'Advanced Search Form Options', 'add-search-to-menu' ),
						),
					);
				}

				$panels = apply_filters( 'is_editor_panels', $panels );

				foreach ( $panels as $id => $panel ) {
					$editor->add_panel( $id, $panel['title'], $panel['callback'], $panel['description'] );
				}

				$editor->display();
			?>
			</div><!-- #search-form-editor -->

			<?php if ( current_user_can( 'is_edit_search_form', $post_id ) ) : ?>
				<p class="submit"><?php $this->save_button( $post_id ); ?></p>
			<?php endif; ?>

			</div><!-- #searchtbox-container-1 -->
 			<div id="searchtbox-container-2" class="postbox-container">
				<?php if ( current_user_can( 'is_edit_search_form', $post_id ) ) : ?>
				<div id="submitdiv" class="searchbox">
					<div class="inside">
					<div class="submitbox" id="submitpost">

					<div id="major-publishing-actions">

					<div id="publishing-action">
						<span class="spinner"></span>
						<?php $this->save_button( $post_id ); ?>
					</div>
					<?php
						if ( ! $post->initial() && ( 'default-search-form' !== $post->name() || defined( 'DELETE_DEFAULT_SEARCH_FORM' ) ) ) :
							$delete_nonce = wp_create_nonce( 'is-delete-search-form_' . $post_id );
					?>
					<div id="delete-action">
						<input type="submit" name="is-delete" class="delete submitdelete" value="<?php echo esc_attr( __( 'Delete', 'add-search-to-menu' ) ); ?>" <?php echo "onclick=\"if (confirm('" . esc_js( __( "You are about to delete this search form.\n  'Cancel' to stop, 'OK' to delete.", 'add-search-to-menu' ) ) . "')) {this.form._wpnonce.value = '$delete_nonce'; this.form.action.value = 'delete'; return true;} return false;\""; ?> />
					</div><!-- #delete-action -->
					<?php endif; ?>
					<div class="clear"></div>
					</div><!-- #major-publishing-actions -->
					<?php if ( ! $post->initial() ) : ?>
					<div id="minor-publishing-actions">

					<div class="hidden">
						<input type="submit" class="button-primary" name="is_save" value="<?php echo esc_attr( __( 'Save', 'add-search-to-menu' ) ); ?>" />
					</div>

					<?php
						$copy_nonce = wp_create_nonce( 'is-copy-search-form_' . $post_id );
					?>
						<input type="submit" name="is-copy" class="copy button" value="<?php echo esc_attr( __( 'Duplicate', 'add-search-to-menu' ) ); ?>" <?php echo "onclick=\"this.form._wpnonce.value = '$copy_nonce'; this.form.action.value = 'copy'; return true;\""; ?> />
					<?php
						$reset_nonce = wp_create_nonce( 'is-reset-search-form_' . $post_id );
					?>
						<p><input type="submit" name="is-reset" class="reset button" value="<?php echo esc_attr( __( 'Reset', 'add-search-to-menu' ) ); ?>" <?php echo "onclick=\"if (confirm('" . esc_js( __( "You are about to reset this search form.\n  'Cancel' to stop, 'OK' to reset.", 'add-search-to-menu' ) ) . "')) {this.form._wpnonce.value = '$reset_nonce'; this.form.action.value = 'reset'; return true;} return false;\""; ?> /></p>
					</div><!-- #minor-publishing-actions -->
					<?php endif; ?>
					</div><!-- #submitpost -->
					</div>
				</div><!-- #submitdiv -->
				<?php endif; ?>

				<div id="informationdiv" class="searchbox">
					<div class="inside">
						<ul>
							<li><a href="https://ivorysearch.com/documentation/" target="_blank"><?php _e( 'Documentation', 'add-search-to-menu' ); ?></a></li>
							<li><a href="https://ivorysearch.com/support/" target="_blank"><?php _e( 'Support', 'add-search-to-menu' ); ?></a></li>
							<li><a href="https://ivorysearch.com/contact/" target="_blank"><?php _e( 'Contact Us', 'add-search-to-menu' ); ?></a></li>
							<li><a href="https://wordpress.org/support/plugin/add-search-to-menu/reviews/?filter=5#new-post" target="_blank"><?php _e( 'Rate Ivory Search', 'add-search-to-menu' ); ?></a></li>
						</ul>
					</div>
				</div><!-- #informationdiv -->

			</div><!-- #searchtbox-container-2 -->
		</div><!-- #search-body -->
		<br class="clear" />
		</div><!-- #poststuff -->
	</form>

	<?php endif; ?>

</div><!-- .wrap -->

<?php
	do_action( 'is_admin_footer' );
