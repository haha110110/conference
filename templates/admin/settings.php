<?php
if ( ! defined( 'ABSPATH' ) ) die;

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

if ( isset( $_POST['conf_action'] ) && $_POST['conf_action'] === 'save_settings' && check_admin_referer( 'conf_save_settings', 'conf_settings_nonce' ) ) {
	if ( $active_tab === 'general' ) {
		update_option( 'conf_event_name', sanitize_text_field( $_POST['conf_event_name'] ) );
		update_option( 'conf_event_location', sanitize_text_field( $_POST['conf_event_location'] ) );
		update_option( 'conf_reg_start_time', sanitize_text_field( $_POST['conf_reg_start_time'] ) );
		update_option( 'conf_reg_end_time', sanitize_text_field( $_POST['conf_reg_end_time'] ) );
	} elseif ( $active_tab === 'tickets' ) {
		$tiers = isset( $_POST['conf_ticket_tiers'] ) ? wp_unslash( $_POST['conf_ticket_tiers'] ) : '[]';
		if ( ! is_array( json_decode( $tiers, true ) ) ) {
			$tiers = '[]';
		}
		update_option( 'conf_ticket_tiers', wp_slash( $tiers ) );

		$discount_enabled = isset( $_POST['conf_discount_enabled'] ) ? 1 : 0;
		$discount_threshold = isset( $_POST['conf_discount_threshold'] ) ? intval( $_POST['conf_discount_threshold'] ) : 5;
		$discount_percentage = isset( $_POST['conf_discount_percentage'] ) ? floatval( $_POST['conf_discount_percentage'] ) : 10;
		update_option( 'conf_discount_enabled', $discount_enabled );
		update_option( 'conf_discount_threshold', $discount_threshold );
		update_option( 'conf_discount_percentage', $discount_percentage );
	} elseif ( $active_tab === 'email' ) {
		update_option( 'conf_email_received_body', wp_kses_post( wp_unslash( $_POST['conf_email_received_body'] ) ) );
		update_option( 'conf_email_confirmed_body', wp_kses_post( wp_unslash( $_POST['conf_email_confirmed_body'] ) ) );
	} elseif ( $active_tab === 'payment' ) {
		$appid  = sanitize_text_field( $_POST['conf_payment_wechat_appid'] );
		$secret = sanitize_text_field( $_POST['conf_payment_wechat_secret'] );
		update_option( 'conf_payment_wechat_appid', $appid );
		update_option( 'conf_payment_wechat_secret', $secret );

		$bank_info = array(
			'bank'    => sanitize_text_field( $_POST['conf_bank_name'] ),
			'account' => sanitize_text_field( $_POST['conf_bank_account'] ),
			'name'    => sanitize_text_field( $_POST['conf_bank_recipient'] ),
		);
		update_option( 'conf_payment_bank_info', wp_json_encode( $bank_info ) );

		$toggles = array(
			'wechat' => isset( $_POST['conf_toggle_wechat'] ) ? true : false,
			'bank'   => isset( $_POST['conf_toggle_bank'] ) ? true : false,
			'onsite' => isset( $_POST['conf_toggle_onsite'] ) ? true : false,
		);
		update_option( 'conf_payment_toggles', wp_json_encode( $toggles ) );
	}

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'conf-manager' ) . '</p></div>';
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Settings', 'conf-manager' ); ?></h1>

	<h2 class="nav-tab-wrapper">
		<a href="?page=conf-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General', 'conf-manager' ); ?></a>
		<a href="?page=conf-settings&tab=tickets" class="nav-tab <?php echo $active_tab === 'tickets' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Tickets & Pricing', 'conf-manager' ); ?></a>
		<a href="?page=conf-settings&tab=payment" class="nav-tab <?php echo $active_tab === 'payment' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Payment Methods', 'conf-manager' ); ?></a>
		<a href="?page=conf-settings&tab=email" class="nav-tab <?php echo $active_tab === 'email' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Email Notifications', 'conf-manager' ); ?></a>
	</h2>

	<form method="post" action="">
		<input type="hidden" name="conf_action" value="save_settings">
		<?php wp_nonce_field( 'conf_save_settings', 'conf_settings_nonce' ); ?>

		<?php if ( $active_tab === 'general' ) : ?>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="conf_event_name"><?php esc_html_e( 'Event Name', 'conf-manager' ); ?></label></th>
						<td>
							<input name="conf_event_name" type="text" id="conf_event_name" value="<?php echo esc_attr( get_option( 'conf_event_name', '' ) ); ?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_event_location"><?php esc_html_e( 'Event Location', 'conf-manager' ); ?></label></th>
						<td>
							<input name="conf_event_location" type="text" id="conf_event_location" value="<?php echo esc_attr( get_option( 'conf_event_location', '' ) ); ?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_reg_start_time"><?php esc_html_e( 'Registration Start Time', 'conf-manager' ); ?></label></th>
						<td>
							<input name="conf_reg_start_time" type="datetime-local" id="conf_reg_start_time" value="<?php echo esc_attr( get_option( 'conf_reg_start_time', '' ) ); ?>" class="regular-text">
							<p class="description"><?php esc_html_e( 'Frontend registration form will only be active after this time.', 'conf-manager' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_reg_end_time"><?php esc_html_e( 'Registration End Time', 'conf-manager' ); ?></label></th>
						<td>
							<input name="conf_reg_end_time" type="datetime-local" id="conf_reg_end_time" value="<?php echo esc_attr( get_option( 'conf_reg_end_time', '' ) ); ?>" class="regular-text">
							<p class="description"><?php esc_html_e( 'Frontend registration form will be disabled after this time.', 'conf-manager' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

		<?php elseif ( $active_tab === 'tickets' ) : 
			$tiers_json = get_option( 'conf_ticket_tiers', '[]' );
		?>
			<p class="description"><?php esc_html_e( 'Configure the available ticket tiers. You can add, edit, or remove tiers below.', 'conf-manager' ); ?></p>
			
			<div id="conf-ticket-tiers-wrapper" style="margin-top:20px; max-width: 900px;">
				<!-- JS will render the tiers here -->
			</div>
			
			<p style="margin-top: 15px;">
				<button type="button" class="button button-secondary" id="conf-add-tier-btn">
					<?php esc_html_e( '+ Add New Ticket Tier', 'conf-manager' ); ?>
				</button>
			</p>

			<textarea name="conf_ticket_tiers" id="conf_ticket_tiers" style="display:none;"><?php echo esc_textarea( $tiers_json ); ?></textarea>

			<script>
				document.addEventListener('DOMContentLoaded', function() {
					const wrapper = document.getElementById('conf-ticket-tiers-wrapper');
					const textarea = document.getElementById('conf_ticket_tiers');
					const addBtn = document.getElementById('conf-add-tier-btn');

					let tiers = [];
					try {
						tiers = JSON.parse(textarea.value);
						if (!Array.isArray(tiers)) tiers = [];
					} catch(e) {
						tiers = [];
					}

					function renderTiers() {
						wrapper.innerHTML = '';
						if (tiers.length === 0) {
							wrapper.innerHTML = '<div class="notice notice-warning inline" style="margin: 0;"><p><?php esc_html_e( 'No ticket tiers defined. Please add one.', 'conf-manager' ); ?></p></div>';
							return;
						}

						tiers.forEach((tier, index) => {
							const box = document.createElement('div');
							box.className = 'postbox';
							box.style.marginBottom = '15px';
							
							box.innerHTML = `
								<div class="postbox-header" style="display:flex; justify-content:space-between; align-items:center; padding: 0 15px; border-bottom: 1px solid #ccd0d4; background: #f9f9f9;">
									<h2 style="font-size: 14px; margin: 0; padding:10px 0;">${tier.name ? escapeHtml(tier.name) : '<?php esc_html_e( 'New Tier', 'conf-manager' ); ?>'}</h2>
									<button type="button" class="button-link button-link-delete remove-tier-btn" data-index="${index}"><?php esc_html_e( 'Remove', 'conf-manager' ); ?></button>
								</div>
								<div class="inside">
									<table class="form-table" role="presentation" style="margin-top: 0;">
										<tbody>
											<tr>
												<th scope="row" style="padding: 10px 10px 10px 0; width: 80px;"><label><?php esc_html_e( 'Tier ID', 'conf-manager' ); ?></label></th>
												<td style="padding: 10px 10px;"><input type="text" class="regular-text tier-input" data-key="id" data-index="${index}" value="${escapeHtml(tier.id || '')}" placeholder="e.g. early_bird" required></td>
												<th scope="row" style="padding: 10px 10px; width: 60px;"><label><?php esc_html_e( 'Name', 'conf-manager' ); ?></label></th>
												<td style="padding: 10px 10px;"><input type="text" class="regular-text tier-input" data-key="name" data-index="${index}" value="${escapeHtml(tier.name || '')}" placeholder="e.g. Early Bird" required></td>
											</tr>
											<tr>
												<th scope="row" style="padding: 10px 10px 10px 0;"><label><?php esc_html_e( 'Price', 'conf-manager' ); ?></label></th>
												<td style="padding: 10px 10px;"><input type="number" step="0.01" class="regular-text tier-input" data-key="price" data-index="${index}" value="${escapeHtml(tier.price !== undefined ? tier.price : '')}" required></td>
												<th scope="row" style="padding: 10px 10px;"><label><?php esc_html_e( 'Quota', 'conf-manager' ); ?></label></th>
												<td style="padding: 10px 10px;"><input type="number" class="regular-text tier-input" data-key="quota" data-index="${index}" value="${escapeHtml(tier.quota !== undefined ? tier.quota : '')}"></td>
											</tr>
											<tr>
												<th scope="row" style="padding: 10px 10px 10px 0;"><label><?php esc_html_e( 'Description', 'conf-manager' ); ?></label></th>
												<td colspan="3" style="padding: 10px 10px;">
													<input type="text" class="large-text tier-input" data-key="description" data-index="${index}" value="${escapeHtml(tier.description || '')}" placeholder="Brief description visible to attendees" style="width: 100%;">
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							`;
							wrapper.appendChild(box);
						});
					}

					function updateTextarea() {
						textarea.value = JSON.stringify(tiers, null, 2);
					}

					function escapeHtml(unsafe) {
						if (unsafe === null || unsafe === undefined) return '';
						return String(unsafe)
							.replace(/&/g, "&amp;")
							.replace(/</g, "&lt;")
							.replace(/>/g, "&gt;")
							.replace(/"/g, "&quot;")
							.replace(/'/g, "&#039;");
					}

					wrapper.addEventListener('input', function(e) {
						if (e.target.classList.contains('tier-input')) {
							const index = parseInt(e.target.getAttribute('data-index'), 10);
							const key = e.target.getAttribute('data-key');
							let val = e.target.value;
							if (key === 'price' || key === 'quota') {
								val = val === '' ? '' : Number(val);
							}
							tiers[index][key] = val;
							updateTextarea();
							
							if (key === 'name') {
								const header = e.target.closest('.postbox').querySelector('h2');
								header.textContent = val || '<?php esc_html_e( 'New Tier', 'conf-manager' ); ?>';
							}
						}
					});

					wrapper.addEventListener('click', function(e) {
						if (e.target.classList.contains('remove-tier-btn')) {
							if (confirm('<?php esc_html_e( 'Are you sure you want to remove this ticket tier?', 'conf-manager' ); ?>')) {
								const index = parseInt(e.target.getAttribute('data-index'), 10);
								tiers.splice(index, 1);
								updateTextarea();
								renderTiers();
							}
						}
					});

					addBtn.addEventListener('click', function() {
						tiers.push({
							id: '',
							name: '',
							price: '',
							quota: '',
							description: ''
						});
						updateTextarea();
						renderTiers();
					});

					renderTiers();
				});
			</script>

			<hr>

			<h3><?php esc_html_e( 'Group Discounts', 'conf-manager' ); ?></h3>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Group Discount', 'conf-manager' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="conf_discount_enabled" value="1" <?php checked( get_option( 'conf_discount_enabled', 0 ) ); ?>>
								<?php esc_html_e( 'Apply automatic discounts for group registrations', 'conf-manager' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_discount_threshold"><?php esc_html_e( 'Min. People (Threshold)', 'conf-manager' ); ?></label></th>
						<td>
							<input name="conf_discount_threshold" type="number" id="conf_discount_threshold" value="<?php echo esc_attr( get_option( 'conf_discount_threshold', 5 ) ); ?>" class="small-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_discount_percentage"><?php esc_html_e( 'Discount Percentage (%)', 'conf-manager' ); ?></label></th>
						<td>
							<input name="conf_discount_percentage" type="number" step="0.1" id="conf_discount_percentage" value="<?php echo esc_attr( get_option( 'conf_discount_percentage', 10 ) ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'E.g., enter 10 for a 10% discount.', 'conf-manager' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

		<?php elseif ( $active_tab === 'payment' ) : 
			$bank = json_decode( get_option( 'conf_payment_bank_info', '{"bank":"","account":"","name":""}' ), true );
			$toggles = json_decode( get_option( 'conf_payment_toggles', '{"wechat":true,"bank":true,"onsite":false}' ), true );
		?>
			<h3><?php esc_html_e( 'Payment Gateway Toggles', 'conf-manager' ); ?></h3>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Enabled Methods', 'conf-manager' ); ?></th>
						<td>
							<fieldset>
								<label><input type="checkbox" name="conf_toggle_wechat" value="1" <?php checked( isset($toggles['wechat']) && $toggles['wechat'] ); ?>> <?php esc_html_e( 'WeChat Pay', 'conf-manager' ); ?></label><br>
								<label><input type="checkbox" name="conf_toggle_bank" value="1" <?php checked( isset($toggles['bank']) && $toggles['bank'] ); ?>> <?php esc_html_e( 'Bank Transfer', 'conf-manager' ); ?></label><br>
								<label><input type="checkbox" name="conf_toggle_onsite" value="1" <?php checked( isset($toggles['onsite']) && $toggles['onsite'] ); ?>> <?php esc_html_e( 'Pay on Site', 'conf-manager' ); ?></label>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<hr>

			<h3><?php esc_html_e( 'WeChat Pay Settings', 'conf-manager' ); ?></h3>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="conf_payment_wechat_appid"><?php esc_html_e( 'App ID', 'conf-manager' ); ?></label></th>
						<td><input name="conf_payment_wechat_appid" type="text" id="conf_payment_wechat_appid" value="<?php echo esc_attr( get_option( 'conf_payment_wechat_appid', '' ) ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_payment_wechat_secret"><?php esc_html_e( 'API Secret Key', 'conf-manager' ); ?></label></th>
						<td><input name="conf_payment_wechat_secret" type="password" id="conf_payment_wechat_secret" value="<?php echo esc_attr( get_option( 'conf_payment_wechat_secret', '' ) ); ?>" class="regular-text"></td>
					</tr>
				</tbody>
			</table>

			<hr>

			<h3><?php esc_html_e( 'Bank Transfer Instructions', 'conf-manager' ); ?></h3>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="conf_bank_name"><?php esc_html_e( 'Bank Name', 'conf-manager' ); ?></label></th>
						<td><input name="conf_bank_name" type="text" id="conf_bank_name" value="<?php echo esc_attr( $bank['bank'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_bank_account"><?php esc_html_e( 'Account Number / IBAN', 'conf-manager' ); ?></label></th>
						<td><input name="conf_bank_account" type="text" id="conf_bank_account" value="<?php echo esc_attr( $bank['account'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_bank_recipient"><?php esc_html_e( 'Recipient Name', 'conf-manager' ); ?></label></th>
						<td><input name="conf_bank_recipient" type="text" id="conf_bank_recipient" value="<?php echo esc_attr( $bank['name'] ?? '' ); ?>" class="regular-text"></td>
					</tr>
				</tbody>
			</table>

		<?php elseif ( $active_tab === 'email' ) : ?>
			<div class="notice notice-info inline"><p><?php esc_html_e( 'To ensure deliverability, please make sure you have configured the WP Mail SMTP plugin on this site.', 'conf-manager' ); ?></p></div>
			<p class="description"><?php esc_html_e( 'You map use the following placeholders in your email templates:', 'conf-manager' ); ?><br>
			<code>{registrant_name}</code>, <code>{order_id}</code>, <code>{payment_method}</code>, <code>{attendee_list}</code>
			</p>
			
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><label for="conf_email_received_body"><?php esc_html_e( 'Order Received (Pending Payment)', 'conf-manager' ); ?></label></th>
						<td>
							<?php 
							$rcv = get_option( 'conf_email_received_body', 'Your order #{order_id} has been received. Payment Method: {payment_method}.' );
							wp_editor( $rcv, 'conf_email_received_body', array( 'textarea_rows' => 10 ) ); 
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="conf_email_confirmed_body"><?php esc_html_e( 'Order Confirmed (Paid)', 'conf-manager' ); ?></label></th>
						<td>
							<?php 
							$cnf = get_option( 'conf_email_confirmed_body', 'Your payment is confirmed! Here is your check-in code info: {attendee_list}' );
							wp_editor( $cnf, 'conf_email_confirmed_body', array( 'textarea_rows' => 10 ) ); 
							?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php endif; ?>

		<?php submit_button( __( 'Save Changes', 'conf-manager' ) ); ?>
	</form>
</div>
