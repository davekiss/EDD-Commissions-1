<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Register a view for the single commission view
 *
 * @since  3.3
 * @param  array $views An array of existing views
 * @return array        The altered list of views
 */
function eddc_register_default_commission_views( $views ) {
	$default_views = array(
		'overview' => 'eddc_commissions_view',
		'delete'   => 'eddc_commissions_delete_view'
	);

	return array_merge( $views, $default_views );
}
add_filter( 'eddc_commission_views', 'eddc_register_default_commission_views', 1, 1 );


/**
 * Register a tab for the single commission view
 *
 * @since  3.3
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs
 */
function eddc_register_default_commission_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array( 'dashicon' => 'dashicons-lock', 'title' => __( 'Commission', 'eddc' ) ),
	);

	return array_merge( $tabs, $default_tabs );
}
add_filter( 'eddc_commission_tabs', 'eddc_register_default_commission_tabs', 1, 1 );


/**
 * Register the Delete icon as late as possible so it's at the bottom
 *
 * @since  3.3
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs, with 'delete' at the bottom
 */
function eddc_register_delete_commission_tab( $tabs ) {
	$tabs['delete'] = array( 'dashicon' => 'dashicons-trash', 'title' => __( 'Delete Commission', 'eddc' ) );

	return $tabs;
}
add_filter( 'eddc_commission_tabs', 'eddc_register_delete_commission_tab', PHP_INT_MAX, 1 );


/**
 * Setup commission view item info card rows
 *
 * @since 3.3
 * @param int $commission_id The ID of the commission we are viewing
 * @return array $rows The rows we are rendering
 */
function eddc_get_commission_info_rows( $commission_id ) {
	$download        = get_post_meta( $commission_id, '_download_id', true );
	$type            = eddc_get_commission_type( $download );
	$commission_info = get_post_meta( $commission_id, '_edd_commission_info', true );
	$payment         = get_post_meta( $commission_id, '_edd_commission_payment_id', true );
	$user_data       = get_userdata( $commission_info['user_id'] );

	if ( 'percentage' == $type ) {
		$rate = $commission_info['rate'] . '%';
	} else {
		$rate = edd_currency_filter( edd_sanitize_amount( $commission_info['rate'] ) );
	}

	if ( false !== $user_data ) {
		$user = '<a href="' . esc_url( add_query_arg( 'user', $user_data->ID ) ) . '" title="' . __( 'View all commissions for this user', 'eddc' ) . '"">' . $user_data->display_name . '</a>';
	} else {
		$user = '<em>' . __( 'Invalid User', 'eddc' ) . '</em>';
	}

	$has_variable_prices = edd_has_variable_prices( $download );
	if ( $has_variable_prices ) {
		$variation = get_post_meta( $commission_id, '_edd_commission_download_variation', true );
	}

	$rows = apply_filters( 'eddc_commission_info_rows', array(
		'commission_id' => array(
			'name' => __( 'Commission ID', 'eddc' ),
			'data' => $commission_id
		),
		'payment_id' => array(
			'name' => __( 'Payment', 'eddc' ),
			'data' => $payment ? '<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment ) ) . '" title="' . __( 'View payment details', 'eddc' ) . '">#' . $payment . '</a> - ' . edd_get_payment_status( get_post( $payment ), true  ) : ''
		),
		'status' => array(
			'name' => __( 'Status', 'eddc' ),
			'data' => eddc_get_commission_status( $commission_id )
		),
		'purchase_date' => array(
			'name' => __( 'Purchase Date', 'eddc' ),
			'data' => date_i18n( get_option( 'date_format' ), strtotime( get_post_field( 'post_date', $commission_id ) ) )
		),
		'user' => array(
			'name' => __( 'User', 'eddc' ),
			'data' => $user
		),
		'download' => array(
			'name' => __( 'Download', 'eddc' ),
			'data' => ! empty( $download ) ? '<a href="' . esc_url( add_query_arg( 'download', $download ) ) . '" title="' . __( 'View all commissions for this item', 'eddc' ) . '">' . get_the_title( $download ) . '</a>' . ( ! empty( $variation ) ? ' - ' . $variation : '') : ''
		),
		'rate' => array(
			'name' => __( 'Rate', 'eddc' ),
			'data' => $rate
		),
		'amount' => array(
			'name' => __( 'Amount', 'eddc' ),
			'data' => edd_currency_filter( edd_format_amount( $commission_info['amount'] ) )
		)
	) );

	return $rows;
}


/**
 * Render item info card rows
 *
 * @since 3.3
 */
function eddc_render_commission_info_rows( $commission_id ) {
	$rows = eddc_get_commission_info_rows( $commission_id );

	foreach ( $rows as $row_data ) {
		?>
		<tr>
			<td class="row-title">
				<label for="tablecell"><?php echo esc_html( $row_data['name'] ); ?></label>
			</td>
			<td style="word-wrap: break-word">
				<?php echo $row_data['data']; ?>
			</td>
		</tr>
		<?php
	}
}
