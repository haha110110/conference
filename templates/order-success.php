<?php
/**
 * Order Success Template
 * 
 * URL: ?action=order_success&id=123
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$order_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

$order = get_post( $order_id );

if ( ! $order || $order->post_type !== 'conf_order' ) {
	wp_die( __( 'Order not found.', 'conf-manager' ) );
}

if ( $order->post_author != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have permission to view this order.', 'conf-manager' ) );
}

$status = Conf_Utils::get_order_status( $order_id );
$payment_method = Conf_Utils::get_payment_method( $order_id );
$attendees = Conf_Utils::get_attendees( $order_id );

$first_attendee = Conf_Utils::get_first_attendee( $order_id );
$six_digit_code = Conf_Utils::get_six_digit_code( $order_id );
$attendee_list_text = Conf_Utils::get_attendee_names( $order_id );

$attendee_names = array();
foreach ( $attendees as $att ) {
    $attendee_names[] = $att->name;
}

$is_onsite_pending = Conf_Utils::is_onsite_pending( $order_id );
$is_paid = Conf_Utils::is_paid( $order_id );

$badge_class = $is_paid ? 'bg-tertiary/10 text-tertiary border-tertiary/20' : 'bg-amber-100 text-amber-700 border-amber-200';
$badge_text = $is_paid ? __( 'Payment Confirmed', 'conf-manager' ) : __( 'Pay at Venue', 'conf-manager' );

$qr_api_url = $first_attendee ? Conf_Utils::generate_qr_url( $six_digit_code, $first_attendee->name, $first_attendee->phone ) : '';

$subtitle_text = '';
if ( $is_onsite_pending ) {
	$subtitle_text = __( 'Please complete the payment at the registration desk on the day of the event.', 'conf-manager' );
} elseif ( $is_paid ) {
	$subtitle_text = __( 'Payment confirmed. Your seats are reserved.', 'conf-manager' );
} else {
	$subtitle_text = __( 'Your registration is complete.', 'conf-manager' );
}
?>

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script>
	tailwind.config = {
		theme: {
			extend: {
				colors: {
					tertiary: { DEFAULT: '#007c59', fixed: '#70fbc4' }
				}
			}
		}
	}
</script>

<div class="min-h-screen bg-gradient-to-br from-surface to-surface-bright">
	<div class="max-w-lg mx-auto px-4 py-12">
		<section class="flex flex-col items-center text-center mb-12 pt-8">
			<div class="w-20 h-20 bg-tertiary-fixed text-tertiary rounded-full flex items-center justify-center mb-6 shadow-sm">
				<span class="material-symbols-outlined text-5xl font-bold" style="font-variation-settings: 'FILL' 1;">check_circle</span>
			</div>
			<h1 class="text-[2.5rem] font-extrabold tracking-tight leading-tight text-on-surface mb-2">
				<?php echo $is_paid ? esc_html__( 'Registration Complete!', 'conf-manager' ) : esc_html__( 'Registration Success!', 'conf-manager' ); ?>
			</h1>
			<p class="text-on-surface-variant text-lg">
				<?php echo esc_html( $subtitle_text ); ?>
			</p>
		</section>

		<section class="relative mb-12">
			<div class="bg-surface-container-lowest rounded-xl p-8 shadow-[0_12px_32px_rgba(27,27,28,0.06)] relative overflow-hidden border border-outline-variant/10">
				<div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 blur-3xl -mr-16 -mt-16"></div>
				<div class="relative z-10 flex flex-col items-center">
					<?php if ( $badge_text ) : ?>
					<span class="<?php echo esc_attr( $badge_class ); ?> inline-block px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border mb-6">
						<?php echo esc_html( $badge_text ); ?>
					</span>
					<?php endif; ?>
					
					<span class="text-xs text-on-surface-variant uppercase tracking-widest mb-6 font-bold">Check-in QR / 入场签到码</span>
					
					<div class="w-48 h-48 bg-surface-container p-4 rounded-xl border border-outline-variant/20 mb-2">
						<img src="<?php echo esc_url( $qr_api_url ); ?>" alt="QR Code" class="w-full h-full object-contain">
					</div>

					<div class="text-center mb-6">
						<p class="text-[0.75rem] text-on-surface-variant uppercase tracking-widest font-bold mb-1">Pass Code / 签到码</p>
						<p class="text-[2.5rem] font-black text-primary tracking-[0.1em]"><?php echo esc_html( $six_digit_code ); ?></p>
					</div>
					
					<div class="w-full space-y-4">
						<div class="flex justify-between items-end border-b border-outline-variant/10 pb-4">
							<div class="w-full flex flex-col items-center pt-2">
								<p class="text-[0.75rem] text-on-surface-variant uppercase tracking-widest font-semibold mb-1">Order No.</p>
								<p class="text-xl font-bold text-on-surface tracking-tight">#<?php echo esc_html( $order_id ); ?></p>
							</div>
						</div>
						<div class="pt-2">
							<p class="text-[0.75rem] text-on-surface-variant uppercase tracking-wider font-medium text-center mb-3">Attendees</p>
							<ul class="space-y-2 text-center text-on-surface font-bold text-lg">
								<?php foreach ( $attendee_names as $name ) : ?>
								<li><?php echo esc_html( $name ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $is_onsite_pending ) : ?>
		<div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6">
			<div class="flex items-start gap-3">
				<span class="material-symbols-outlined text-amber-600 mt-0.5">info</span>
				<div class="text-sm text-amber-900">
					<p class="font-bold mb-2"><?php esc_html_e( 'Payment Required at Venue', 'conf-manager' ); ?></p>
					<p><?php esc_html_e( 'Please bring this QR code or pass code to the registration desk and complete your payment on the day of the event.', 'conf-manager' ); ?></p>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class="flex gap-4">
			<a href="<?php echo esc_url( remove_query_arg( array( 'action', 'id' ) ) ); ?>" class="w-full flex items-center justify-center gap-2 bg-surface-container-low text-primary py-4 rounded-xl font-bold hover:bg-surface-container-high transition-colors active:scale-95">
				<span class="material-symbols-outlined">home</span> Return to Dashboard
			</a>
		</div>
	</div>
</div>
