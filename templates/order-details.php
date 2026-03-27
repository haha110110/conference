<?php
/**
 * Order Details Template (Tailwind UI - Restored Functionality)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
if (!$order_id && isset($_GET['id'])) $order_id = intval($_GET['id']);

$order = get_post( $order_id );

if ( ! $order || $order->post_type !== 'conf_order' ) {
	wp_die( __( 'Order not found.', 'conf-manager' ) );
}

// Permission check
if ( $order->post_author != get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'You do not have permission to view this order.', 'conf-manager' ) );
}

$status = Conf_Utils::get_order_status( $order_id );
$payment_method = Conf_Utils::get_payment_method( $order_id );
$order_total = get_post_meta( $order_id, 'conf_order_total', true );
$attendees = Conf_Utils::get_attendees( $order_id );

// Redirect to success page if onsite pending or already paid
if ( Conf_Utils::is_onsite_pending( $order_id ) || Conf_Utils::is_paid( $order_id ) ) {
    wp_redirect( Conf_Utils::get_success_url( $order_id ) );
    exit;
}
?>

<!-- Tailwind Config & CDN -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
<script id="tailwind-config">
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "outline-variant": "#bfc7d1",
                "outline": "#707880",
                "on-surface": "#1b1b1c",
                "surface-container": "#f0eded",
                "primary-fixed": "#cbe6ff",
                "surface-container-high": "#eae7e7",
                "surface-container-highest": "#e5e2e1",
                "surface-container-lowest": "#ffffff",
                "surface-bright": "#fcf9f8",
                "surface": "#fcf9f8",
                "on-primary-container": "#e4f1ff",
                "on-primary": "#ffffff",
                "surface-dim": "#dcd9d9",
                "error-container": "#ffdad6",
                "error": "#ba1a1a",
                "tertiary-container": "#007c59",
                "tertiary": "#006144",
                "on-surface-variant": "#40484f",
                "surface-variant": "#e5e2e1",
                "primary-container": "#0073aa",
                "primary": "#005986",
                "on-background": "#1b1b1c",
                "background": "#fcf9f8",
                "surface-container-low": "#f6f3f2",
                "tertiary-fixed": "#70fbc4",
            },
            fontFamily: { "headline": ["Inter"], "body": ["Inter"], "label": ["Inter"] }
        }
    }
}
</script>

<div id="conf-registration-container" class="min-h-screen bg-[#fcf9f8] font-body text-on-surface">
    <div class="w-full sm:max-w-4xl mx-auto px-3 sm:px-6 py-8">
        <!-- Back Button -->
        <div class="mb-8">
            <a href="<?php echo esc_url( remove_query_arg( array( 'action', 'order_id', 'id' ) ) ); ?>" class="inline-flex items-center gap-2 text-primary font-bold hover:underline transition-all">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <?php esc_html_e( 'Back to Dashboard', 'conf-manager' ); ?>
            </a>
        </div>

        <div class="bg-surface-container-lowest rounded-3xl p-4 sm:p-12 shadow-[0_12px_48px_rgba(27,27,28,0.06)] border border-outline-variant/10">
            <!-- Header Info -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-10 pb-10 border-b border-outline-variant/10">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-on-surface mb-2">
                        <?php printf( esc_html__( 'Order #%d', 'conf-manager' ), $order_id ); ?>
                    </h1>
                    <p class="text-on-surface-variant font-medium"><?php printf( esc_html__( 'Total Amount: ￥%s', 'conf-manager' ), number_format( floatval($order_total), 2 ) ); ?></p>
                </div>
                
                <?php 
                    $status_class = 'bg-error/10 text-error';
                    if ($status === 'paid') $status_class = 'bg-tertiary/10 text-tertiary';
                    if ($status === 'pending') $status_class = 'bg-amber-100 text-amber-700';
                ?>
                <div class="flex flex-col items-end">
                    <span class="text-[0.65rem] font-black uppercase tracking-widest text-on-surface-variant mb-2"><?php esc_html_e( 'Current Status', 'conf-manager' ); ?></span>
                    <span class="inline-block px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider <?php echo $status_class; ?>">
                        <?php echo esc_html( $status ); ?>
                    </span>
                </div>
            </div>

            <!-- Payment Management (If Unpaid/Pending) -->
            <?php if ( $status !== 'paid' ) : ?>
                <div class="bg-surface-container-low rounded-3xl p-8 border border-outline-variant/10 mb-12">
                    <h3 class="text-xl font-black text-on-surface mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">payments</span>
                        <?php esc_html_e( 'Payment Management', 'conf-manager' ); ?>
                    </h3>

                    <?php if ( $payment_method === 'bank' ) : ?>
                        <!-- ========== 银行汇款界面 ========== -->
                        <div class="mb-4">
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">account_balance</span>
                                Bank Transfer
                            </span>
                        </div>
                        
                        <div class="bg-amber-50 rounded-2xl p-6 border border-amber-200 mb-6">
                            <div class="flex items-start gap-3 mb-4">
                                <span class="material-symbols-outlined text-amber-600 mt-0.5">info</span>
                                <div class="text-sm text-amber-900 font-medium">
                                    <?php echo wp_kses_post( sprintf( __( 'Transfer to: <br><strong>%s</strong><br>Acc: <strong>%s</strong><br>Bank: <strong>%s</strong>', 'conf-manager' ), get_option( 'conf_bank_acc_name' ), get_option( 'conf_bank_acc_no' ), get_option( 'conf_bank_name' ) ) ); ?>
                                </div>
                            </div>
                        </div>
                        
                        <form id="conf-update-payment-form" class="space-y-6">
                            <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
                            <input type="hidden" name="payment_method" value="bank">
                            
                            <label class="block text-xs font-black uppercase tracking-widest text-on-surface-variant mb-2"><?php esc_html_e( 'Upload Receipt', 'conf-manager' ); ?></label>
                            <div class="relative group">
                                <input type="file" name="bank_receipt" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*">
                                <div class="bg-white border-2 border-dashed border-amber-300 rounded-xl p-6 text-center group-hover:bg-amber-100 transition-colors">
                                    <span class="material-symbols-outlined text-amber-400 text-3xl mb-1">cloud_upload</span>
                                    <p class="text-xs font-bold text-amber-700">Tap to select or change receipt</p>
                                </div>
                            </div>
                            
                            <div id="update-payment-message" class="text-sm font-bold text-center"></div>
                            
                            <button type="submit" class="w-full bg-on-surface text-white font-black py-4 rounded-xl shadow-lg hover:opacity-90 active:scale-95 transition-all">
                                <?php esc_html_e( 'Update Order Info', 'conf-manager' ); ?>
                            </button>
                        </form>
                        
                        <button onclick="history.back()" class="w-full mt-4 text-primary font-bold py-3 rounded-xl border border-primary/20 hover:bg-primary/5 transition-all">
                            <?php esc_html_e( '← Change Payment Method', 'conf-manager' ); ?>
                        </button>

                    <?php elseif ( $payment_method === 'wechat' ) : ?>
                        <!-- ========== 微信支付界面 ========== -->
                        <div class="mb-4">
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">qr_code_2</span>
                                WeChat Pay
                            </span>
                        </div>
                        
                        <div id="wechat-pay-quick" class="mb-6">
                            <button type="button" class="conf-wechat-pay-btn w-full bg-[#07C160] text-white font-black py-4 rounded-xl shadow-lg hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2" data-order-id="<?php echo esc_attr( $order_id ); ?>" data-payment-type="auto">
                                <span class="material-symbols-outlined">qr_code_2</span>
                                <?php esc_html_e( 'Pay with WeChat Now', 'conf-manager' ); ?>
                            </button>
                        </div>
                        
                        <button onclick="history.back()" class="w-full text-primary font-bold py-3 rounded-xl border border-primary/20 hover:bg-primary/5 transition-all">
                            <?php esc_html_e( '← Change Payment Method', 'conf-manager' ); ?>
                        </button>

                    <?php else : ?>
                        <!-- ========== 选择支付方式界面 ========== -->
                        <form id="conf-update-payment-form" class="space-y-6">
                            <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
                            
                            <!-- Payment Method Toggle -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <?php 
                                $methods = array(
                                    'wechat' => array('icon' => 'qr_code_2', 'label' => 'WeChat', 'color' => 'text-[#07C160]'),
                                    'bank'   => array('icon' => 'account_balance', 'label' => 'Bank', 'color' => 'text-primary'),
                                    'onsite' => array('icon' => 'payments', 'label' => 'On-site', 'color' => 'text-amber-600')
                                );
                                foreach ($methods as $key => $data): ?>
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="payment_method" value="<?php echo $key; ?>" <?php checked( $payment_method, $key ); ?> class="sr-only peer">
                                    <div class="p-4 rounded-xl border-2 border-transparent bg-white shadow-sm ring-1 ring-inset ring-outline-variant/10 peer-checked:border-primary peer-checked:ring-primary peer-checked:bg-primary/5 transition-all flex flex-col items-center justify-center gap-2">
                                        <span class="material-symbols-outlined <?php echo $data['color']; ?> text-2xl"><?php echo $data['icon']; ?></span>
                                        <span class="text-xs font-black uppercase tracking-tighter"><?php echo esc_html($data['label']); ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>

                            <!-- Dynamic Content: Bank Instructions & Upload -->
                            <div id="bank-transfer-instructions" class="hidden bg-amber-50 rounded-2xl p-6 border border-amber-200">
                                <div class="flex items-start gap-3 mb-4">
                                    <span class="material-symbols-outlined text-amber-600 mt-0.5">info</span>
                                    <div class="text-sm text-amber-900 font-medium">
                                        <?php echo wp_kses_post( sprintf( __( 'Transfer to: <br><strong>%s</strong><br>Acc: <strong>%s</strong><br>Bank: <strong>%s</strong>', 'conf-manager' ), get_option( 'conf_bank_acc_name' ), get_option( 'conf_bank_acc_no' ), get_option( 'conf_bank_name' ) ) ); ?>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <label class="block text-xs font-black uppercase tracking-widest text-on-surface-variant mb-2"><?php esc_html_e( 'Upload Receipt', 'conf-manager' ); ?></label>
                                    <div class="relative group">
                                        <input type="file" name="bank_receipt" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*">
                                        <div class="bg-white border-2 border-dashed border-amber-300 rounded-xl p-6 text-center group-hover:bg-amber-100 transition-colors">
                                            <span class="material-symbols-outlined text-amber-400 text-3xl mb-1">cloud_upload</span>
                                            <p class="text-xs font-bold text-amber-700">Tap to select or change receipt</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- WeChat Pay Button (Quick Access) -->
                            <div id="wechat-pay-quick" class="hidden">
                                <button type="button" class="conf-wechat-pay-btn w-full bg-[#07C160] text-white font-black py-4 rounded-xl shadow-lg hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2" data-order-id="<?php echo esc_attr( $order_id ); ?>" data-payment-type="auto">
                                    <span class="material-symbols-outlined">qr_code_2</span>
                                    <?php esc_html_e( 'Pay with WeChat Now', 'conf-manager' ); ?>
                                </button>
                            </div>

                            <div id="update-payment-message" class="text-sm font-bold text-center"></div>
                            
                            <button type="submit" class="w-full bg-on-surface text-white font-black py-4 rounded-xl shadow-lg hover:opacity-90 active:scale-95 transition-all">
                                <?php esc_html_e( 'Update Order Info', 'conf-manager' ); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Attendees List -->
            <div class="space-y-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight"><?php esc_html_e( 'Attendee Tickets', 'conf-manager' ); ?></h2>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ( $attendees as $attendee ) : ?>
                        <div class="bg-surface-container-low rounded-3xl p-6 sm:p-8 border border-outline-variant/10 group">
                            <div class="flex flex-col sm:flex-row justify-between gap-8">
                                <div class="flex-1">
                                    <h4 class="text-2xl font-black tracking-tight text-on-surface mb-2"><?php echo esc_html( $attendee->name ); ?></h4>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-on-surface-variant font-medium text-sm">
                                        <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">business</span><?php echo esc_html( $attendee->company ); ?></div>
                                        <div class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">smartphone</span><?php echo esc_html( Conf_Utils::mask_phone( $attendee->phone ) ); ?></div>
                                    </div>
                                    
                                    <?php if ( $status === 'paid' || ( $status === 'unpaid' && $payment_method === 'onsite' ) ) : ?>
                                        <div class="mt-8 pt-6 border-t border-outline-variant/10">
                                            <div class="flex items-center justify-between mb-3">
                                                <p class="text-[0.65rem] font-black tracking-widest <?php echo $status === 'paid' ? 'text-tertiary' : 'text-amber-600'; ?> uppercase">
                                                    <?php echo $status === 'paid' ? esc_html__( 'Check-in Code', 'conf-manager' ) : esc_html__( 'Pending On-site Payment', 'conf-manager' ); ?>
                                                </p>
                                                <?php if ( $status === 'unpaid' && $payment_method === 'onsite' ) : ?>
                                                    <span class="text-xs px-2 py-1 bg-amber-100 text-amber-700 rounded-full font-bold"><?php esc_html_e( 'Pay at venue', 'conf-manager' ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <div id="code-<?php echo $attendee->id; ?>" class="bg-tertiary/10 text-tertiary text-2xl font-black tracking-[0.2em] px-5 py-3 rounded-2xl cursor-pointer hover:bg-tertiary/20 transition-all border border-tertiary/20" onclick="copyCode('<?php echo $attendee->six_digit_code; ?>', <?php echo $attendee->id; ?>)">
                                                    <?php echo esc_html( $attendee->six_digit_code ); ?>
                                                </div>
                                                <span id="copy-tip-<?php echo $attendee->id; ?>" class="text-xs font-bold text-on-surface-variant flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-sm">content_copy</span> Tap to copy
                                                </span>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div class="mt-8 p-6 bg-surface-container-high rounded-2xl border border-outline-variant/10 flex items-start gap-3">
                                            <span class="material-symbols-outlined text-on-surface-variant mt-0.5">lock</span>
                                            <p class="text-sm font-medium text-on-surface-variant">
                                                <?php esc_html_e( 'Tickets will be issued automatically after payment confirmation.', 'conf-manager' ); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ( $status === 'paid' || ( $status === 'unpaid' && $payment_method === 'onsite' ) ) : ?>
                                    <div class="flex flex-col items-center justify-center bg-white p-5 rounded-3xl shadow-sm border border-outline-variant/5 w-full sm:w-44 <?php echo $status === 'unpaid' ? 'border-amber-300' : ''; ?>">
                                        <?php
                                        $qr_data = 'conf:' . $attendee->six_digit_code . '|' . $attendee->name . '|' . $attendee->phone;
                                        $qr_api_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode( $qr_data );
                                        ?>
                                        <img src="<?php echo esc_url( $qr_api_url ); ?>" alt="QR Code" class="w-28 h-28 mb-4 group-hover:scale-105 transition-transform">
                                        <span class="text-[0.65rem] font-black <?php echo $status === 'paid' ? 'text-on-surface-variant' : 'text-amber-600'; ?> uppercase tracking-widest">
                                            <?php echo $status === 'paid' ? esc_html__( 'Scan at door', 'conf-manager' ) : esc_html__( 'Pay at venue', 'conf-manager' ); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Payment method toggle for selection interface
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
        const bankWrap = document.getElementById('bank-transfer-instructions');
        const wechatBtn = document.getElementById('wechat-pay-quick');
        bankWrap?.classList.toggle('hidden', e.target.value !== 'bank');
        wechatBtn?.classList.toggle('hidden', e.target.value !== 'wechat');
    });
});

// Form submission handler
const paymentForm = document.getElementById('conf-update-payment-form');
if (paymentForm) {
    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'conf_update_payment_method');
        formData.append('nonce', '<?php echo wp_create_nonce('conf_registration_nonce'); ?>');

        const btn = this.querySelector('button[type="submit"]');
        const msg = document.getElementById('update-payment-message');

        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined animate-spin">refresh</span> Processing...';
        msg.textContent = '';

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const nextAction = data.data.next_action;
                const paymentMethod = data.data.payment_method;

                msg.textContent = '✓ ' + data.data.message;
                msg.classList.remove('text-error');
                msg.classList.add('text-tertiary');

                if (nextAction === 'redirect_success') {
                    window.location.href = '?action=order_success&id=' + <?php echo $order_id; ?>;
                } else if (nextAction === 'trigger_wechat') {
                    if (typeof initiateWeChatPay === 'function') {
                        initiateWeChatPay(<?php echo $order_id; ?>, 'auto');
                    } else if (typeof $ !== 'undefined') {
                        $('.conf-wechat-pay-btn').trigger('click');
                    }
                } else if (nextAction === 'show_bank') {
                    location.reload();
                } else {
                    location.reload();
                }
            } else {
                msg.textContent = '✗ ' + (data.data.message || 'Update failed.');
                msg.classList.remove('text-tertiary');
                msg.classList.add('text-error');
                btn.disabled = false;
                btn.textContent = '<?php esc_attr_e('Update Order Info', 'conf-manager'); ?>';
            }
        })
        .catch(err => {
            msg.textContent = '✗ Network error. Please try again.';
            msg.classList.remove('text-tertiary');
            msg.classList.add('text-error');
            btn.disabled = false;
            btn.textContent = '<?php esc_attr_e('Update Order Info', 'conf-manager'); ?>';
        });
    });
}

function copyCode(code, id) {
    navigator.clipboard.writeText(code).then(function() {
        var tip = document.getElementById('copy-tip-' + id);
        var originalHtml = tip.innerHTML;
        tip.innerHTML = '<span class="material-symbols-outlined text-sm">check_circle</span> Copied!';
        tip.classList.add('text-tertiary');
        setTimeout(function() {
            tip.innerHTML = originalHtml;
            tip.classList.remove('text-tertiary');
        }, 2000);
    });
}
</script>
