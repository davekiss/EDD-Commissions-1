<?php

/**
 * Register Dashboard Widgets
 *
 * Registers the dashboard widgets.
 *
 * @access      private
 * @since       1.6
*/

function eddc_register_dashboard_commission_widgets() {
	if( eddc_user_has_commissions() ) {
		wp_add_dashboard_widget( 'edd_dashboard_user_commissions', __('Commissions Summary', 'edd'), 'eddc_dashboard_commissions_widget' );
	}
}
add_action('wp_dashboard_setup', 'eddc_register_dashboard_commission_widgets', 100 );


/**
 * Commissions Summary Dashboard Widget
 *
 * @access      private
 * @since       1.6
*/

function eddc_dashboard_commissions_widget() {
	global $user_ID;

	$unpaid_commissions = eddc_get_unpaid_commissions( $user_ID );
	$paid_commissions 	= eddc_get_paid_commissions( $user_ID );
	$stats 				= '';
	if( ! empty( $unpaid_commissions ) || ! empty( $paid_commissions ) ) : // only show tables if user has commission data
		ob_start(); ?>
			<div id="edd_user_commissions" class="edd_dashboard_widget">
				<style>#edd_user_commissions_unpaid { margin-top: 30px; }#edd_user_commissions_unpaid_total { padding-bottom: 20px; } .edd_user_commissions { width: 100%; margin: 0 0 20px; }.edd_user_commissions th, .edd_user_commissions td { text-align:left; padding: 4px 4px 4px 0; }</style>
				<!-- unpaid -->
				<div id="edd_user_commissions_unpaid" class="table">
					<p class="edd_user_commissions_header sub"><?php _e('Unpaid Commissions', 'eddc'); ?></p>
					<table id="edd_user_unpaid_commissions_table" class="edd_user_commissions">
						<thead>
							<tr class="edd_user_commission_row">
								<th class="edd_commission_item"><?php _e('Item', 'eddc'); ?></th>
								<th class="edd_commission_amount"><?php _e('Amount', 'eddc'); ?></th>
								<th class="edd_commission_rate"><?php _e('Rate', 'eddc'); ?></th>
								<th class="edd_commission_date"><?php _e('Date', 'eddc'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php $total = (float) 0; ?>
						<?php if( ! empty( $unpaid_commissions ) ) : ?>
							<?php foreach( $unpaid_commissions as $commission ) : ?>
								<tr class="edd_user_commission_row">
									<?php
									$item_name 			= get_the_title( get_post_meta( $commission->ID, '_download_id', true ) );
									$commission_info 	= get_post_meta( $commission->ID, '_edd_commission_info', true );
									$amount 			= $commission_info['amount'];
									$rate 				= $commission_info['rate'];
									$total 				+= $amount;
									?>
									<td class="edd_commission_item"><?php echo esc_html( $item_name ); ?></td>
									<td class="edd_commission_amount"><?php echo edd_currency_filter( $amount ); ?></td>
									<td class="edd_commission_rate"><?php echo $rate . '%'; ?></td>
									<td class="edd_commission_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->post_date ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="edd_user_commission_row edd_row_empty">
								<td colspan="4"><?php _e('No unpaid commissions', 'eddc'); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div id="edd_user_commissions_unpaid_total"><strong><?php _e('Total unpaid:', 'eddc');?>&nbsp;<?php echo edd_currency_filter( $total ); ?></strong></div>
				</div><!--end #edd_user_commissions_unpaid-->

				<!-- paid -->
				<div id="edd_user_commissions_paid" class="table">
					<p class="edd_user_commissions_header sub"><?php _e('Paid Commissions', 'eddc'); ?></p>
					<table id="edd_user_paid_commissions_table" class="edd_user_commissions">
						<thead>
							<tr class="edd_user_commission_row">
								<th class="edd_commission_item"><?php _e('Item', 'eddc'); ?></th>
								<th class="edd_commission_amount"><?php _e('Amount', 'eddc'); ?></th>
								<th class="edd_commission_rate"><?php _e('Rate', 'eddc'); ?></th>
								<th class="edd_commission_date"><?php _e('Date', 'eddc'); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php $total = (float) 0; ?>
						<?php if( ! empty( $paid_commissions ) ) : ?>
							<?php foreach( $paid_commissions as $commission ) : ?>
								<tr class="edd_user_commission_row">
									<?php
									$item_name 			= get_the_title( get_post_meta( $commission->ID, '_download_id', true ) );
									$commission_info 	= get_post_meta( $commission->ID, '_edd_commission_info', true );
									$amount 			= $commission_info['amount'];
									$rate 				= $commission_info['rate'];
									$total 				+= $amount;
									?>
									<td class="edd_commission_item"><?php echo esc_html( $item_name ); ?></td>
									<td class="edd_commission_amount"><?php echo edd_currency_filter( $amount ); ?></td>
									<td class="edd_commission_rate"><?php echo $rate . '%'; ?></td>
									<td class="edd_commission_date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $commission->post_date ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="edd_user_commission_row edd_row_empty">
								<td colspan="4"><?php _e('No paid commissions', 'eddc'); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
					<div id="edd_user_commissions_paid_total"><strong><?php _e('Total paid:', 'eddc');?>&nbsp;<?php echo edd_currency_filter( $total ); ?></strong></div>
				</div><!--end #edd_user_commissions_unpaid-->

			</div><!--end #edd_user_commissions-->
		<?php
		$stats = ob_get_clean();
	endif;

	echo $stats;
}