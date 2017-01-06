<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Renders the main commissions admin page
 *
 * @since       3.3
 * @return      void
*/
function eddc_commissions_page() {
	$default_views  = eddc_commission_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'commissions';

	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[$requested_view] ) ) {
		eddc_render_commission_view( $requested_view, $default_views );
	} else {
		eddc_commissions_list();
	}
}


/**
 * Register the views for commission management
 *
 * @since  3.3
 * @return array Array of views and their callbacks
 */
function eddc_commission_views() {
	$views = array();
	return apply_filters( 'eddc_commission_views', $views );
}


/**
 * Register the tabs for commission management
 *
 * @since  3.3
 * @return array Array of tabs for the customer
 */
function eddc_commission_tabs() {
	$tabs = array();
	return apply_filters( 'eddc_commission_tabs', $tabs );
}


/**
 * List table of commissions
 *
 * @since  3.3
 * @return void
 */
function eddc_commissions_list() {
	?>
	<div class="wrap">

		<div id="icon-edit" class="icon32"><br/></div>
		<h2><?php _e( 'Easy Digital Download Commissions', 'eddc' ); ?></h2>

		<style>
			.column-status, .column-count { width: 100px; }
			.column-limit { width: 150px; }
		</style>
		<form id="commissions-filter" method="get">
			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-commissions" />
			<?php
			$commissions_table = new edd_C_List_Table();
			$commissions_table->prepare_items();
			$commissions_table->search_box( 'search', 'eddc_search' );
			$commissions_table->views();
			$commissions_table->display();
			?>
		</form>
	</div>
	<?php

	$redirect = get_transient( '_eddc_bulk_actions_redirect' );

	if( false !== $redirect ) : delete_transient( '_eddc_bulk_actions_redirect' );
	$redirect = admin_url( 'edit.php?post_type=download&page=edd-commissions' );

	if( isset( $_GET['s'] ) ) {
		$redirect = add_query_arg( 's', $_GET['s'], $redirect );
	}
	?>
	<script type="text/javascript">
	window.location = "<?php echo $redirect; ?>";
	</script>
	<?php endif;
}


/**
 * Renders the commission view wrapper
 *
 * @since  3.5
 * @param  string $view      The View being requested
 * @param  array $callbacks  The Registered views and their callback functions
 * @return void
 */
function eddc_render_commission_view( $view, $callbacks ) {
	$render = true;

	if( ! current_user_can( 'edit_shop_payments' ) ) {
		edd_set_error( 'edd-no-access', __( 'You are not permitted to view this data.', 'eddc' ) );
		$render = false;
	}

	if( ! isset( $_GET['commission'] ) || ! is_numeric( $_GET['commission'] ) ) {
		edd_set_error( 'edd-invalid-commission', __( 'Invalid commission ID provided.', 'eddc' ) );
		$render = false;
	}

	$commission_id  = (int) $_GET['commission'];
	$commission     = get_post( $commission_id );

	$commission_tabs = eddc_commission_tabs();
	?>
	<div class="wrap">
		<h2><?php _e( 'Commission Details', 'eddc' ); ?></h2>
		<?php if( edd_get_errors() ) : ?>
			<div class="error settings-error">
				<?php edd_print_errors(); ?>
			</div>
		<?php endif; ?>

		<?php if( $render ) : ?>
			<div id="edd-item-tab-wrapper" class="commission-tab-wrapper">
				<ul id="edd-item-tab-wrapper-list" class="commission-tab-wrapper-list">
					<?php foreach ( $commission_tabs as $key => $tab ) : ?>
						<?php $active = $key === $view ? true : false; ?>
						<?php $class  = $active ? 'active' : 'inactive'; ?>

						<li class="<?php echo sanitize_html_class( $class ); ?>">
							<?php if ( ! $active ) : ?>
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-commissions&view=' . $key . '&commission=' . $commission_id . '#wpbody-content' ) ); ?>">
							<?php endif; ?>
								<span class="dashicons <?php echo sanitize_html_class( $tab['dashicon'] ); ?>" aria-hidden="true"></span>
								<span class="screen-reader-text"><?php echo esc_attr( $tab['title'] ); ?></span>
							<?php if ( ! $active ) : ?>
								</a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div id="edd-item-card-wrapper" class="edd-sl-commission-card" style="float: left">
				<?php $callbacks[$view]( $commission ) ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}


/**
 * View a commission
 *
 * @since  3.5
 * @param  $commission The commission object being displayed
 * @return void
 */
function eddc_commissions_view( $commission ) {
	$base           = admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission->ID );
	$base           = wp_nonce_url( $base, 'eddc_commission_nonce' );
	$commission_id  = $commission->ID;
	$child_args     = array(
		'post_type'      => 'edd_commission',
		'post_status'    => array( 'publish', 'future' ),
		'posts_per_page' => -1,
		'post_parent'    => $commission_id
	);

	do_action( 'eddc_commission_card_top', $commission_id );
	?>
	<div class="info-wrapper item-section">
		<form id="edit-item-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission_id ); ?>">
			<div class="item-info">
				<table class="widefat striped">
					<tbody>
						<?php eddc_render_commission_info_rows( $commission_id ); ?>
					</tbody>
				</table>
			</div>
		</form>
	</div>

	<?php
	do_action( 'eddc_commission_card_bottom', $commission_id );
}


/**
 * Delete a commission
 *
 * @since 3.3
 * @param object $commission The commission being deleted
 * @return void
 */
function eddc_commissions_delete_view( $commission ) {
	$commission_id = $commission->ID;
	?>

	<div class="eddc-commission-delete-header">
		<span><?php printf( __( 'Commission ID: %s', 'eddc' ), $commission_id ); ?></span>
	</div>

	<?php do_action( 'eddc_commissions_before_commission_delete', $commission_id ); ?>

	<form id="delete-commission" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=delete&commission=' . $commission_id ); ?>">
		<div class="edd-item-info delete-commission">
			<span class="delete-commission-options">
				<p>
					<?php echo EDD()->html->checkbox( array( 'name' => 'eddc-commission-delete-comfirm' ) ); ?>
					<label for="eddc-commission-delete-comfirm"><?php _e( 'Are you sure you want to delete this commission?', 'eddc' ); ?></label>
				</p>

				<?php do_action( 'eddc_commissions_delete_inputs', $commission_id ); ?>
			</span>

			<span id="commission-edit-actions">
				<input type="hidden" name="commission_id" value="<?php echo $commission_id; ?>" />
				<?php wp_nonce_field( 'delete-commission', '_wpnonce', false, true ); ?>
				<input type="hidden" name="edd_action" value="delete_commission" />
				<input type="submit" disabled="disabled" id="eddc-delete-commission" class="button-primary" value="<?php _e( 'Delete Commission', 'eddc' ); ?>" />
				<a id="eddc-delete-commission-cancel" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-commissions&view=overview&commission=' . $commission_id ); ?>" class="delete"><?php _e( 'Cancel', 'eddc' ); ?></a>
			</span>
		</div>
	</form>

	<?php do_action( 'eddc_commissions_after_commission_delete', $commission_id );
}
