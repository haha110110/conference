<?php
/**
 * Multi-step Registration Form Template (Tailwind UI)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$current_user = wp_get_current_user();

// Fetch ticket tiers from settings
$tickets_raw = get_option( 'conf_tickets_raw', "Standard|1200\nVIP|2500" );
$ticket_tiers = array();
if ( ! empty( $tickets_raw ) ) {
	$lines = explode( "\n", $tickets_raw );
	foreach ( $lines as $line ) {
		$parts = explode( '|', trim( $line ) );
		if ( count( $parts ) >= 2 ) {
			$ticket_tiers[] = array(
				'name'  => trim( $parts[0] ),
				'price' => floatval( trim( $parts[1] ) )
			);
		}
	}
}

// Fallback if empty
if ( empty( $ticket_tiers ) ) {
	$ticket_tiers[] = array( 'name' => 'Standard', 'price' => 1200 );
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
            fontFamily: {
                "headline": ["Inter"],
                "body": ["Inter"],
                "label": ["Inter"]
            }
        }
    }
}
</script>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .conf-tw-wrapper { font-family: 'Inter', sans-serif; min-height: 100vh; background-color: #fcf9f8; color: #1b1b1c; }
    .step-section { display: none; }
    .step-section.active { display: block; animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .qr-pattern { background-image: radial-gradient(circle at 2px 2px, #005986 1px, transparent 0); background-size: 8px 8px; }
    
    /* Scoped Tailwind Reset overrides to avoid messing up WP Admin if loaded there, but mostly for frontend */
    .conf-tw-wrapper *, .conf-tw-wrapper *::before, .conf-tw-wrapper *::after {
        box-sizing: border-box;
    }
    .conf-tw-wrapper input[type="text"], .conf-tw-wrapper input[type="email"], .conf-tw-wrapper input[type="tel"] {
        background-color: transparent;
        border: none;
        border-bottom: 1px solid #bfc7d1;
        border-radius: 0;
        padding-left: 0;
        padding-right: 0;
    }
    .conf-tw-wrapper input:focus {
        outline: none;
        box-shadow: none;
        border-bottom-color: #005986;
    }
</style>

<div class="conf-tw-wrapper pb-32">
    <!-- Header -->
    <header class="bg-[#fcf9f8] sticky top-0 z-50 border-b border-[#f6f3f2]">
        <div class="flex items-center justify-between px-6 py-4 w-full max-w-2xl mx-auto">
            <div class="flex items-center gap-4">
                <a href="<?php echo esc_url( remove_query_arg( 'action' ) ); ?>" class="text-[#005986] hover:bg-[#f6f3f2] p-1 rounded-full transition-colors flex items-center justify-center">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="font-bold tracking-tight text-lg text-[#005986]">Conference Registration</h1>
            </div>
        </div>
    </header>

    <main class="pt-8 px-6 max-w-2xl mx-auto" id="registration-app">
        <form id="conf-registration-form">
            
            <!-- STEP 1: Attendee Details -->
            <div class="step-section active" id="step-1">
                <div class="mb-10">
                    <div class="flex justify-between items-end mb-2">
                        <p class="text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase">Step 01 of 03</p>
                        <p class="text-[0.75rem] font-bold text-tertiary">Attendee Details</p>
                    </div>
                    <div class="h-1 w-full bg-outline-variant/20 rounded-full overflow-hidden">
                        <div class="h-full bg-tertiary w-1/3"></div>
                    </div>
                </div>

                <h2 class="text-[2.75rem] font-bold tracking-[-0.02em] leading-tight text-on-surface mb-8">Who is joining us?</h2>

                <div id="attendees-container" class="space-y-6">
                    <!-- Attendee 1 -->
                    <div class="attendee-item bg-surface-container-lowest rounded-xl p-6 shadow-[0_12px_32px_rgba(27,27,28,0.06)]">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">1</div>
                                <h3 class="text-lg font-bold tracking-tight">Primary Attendee</h3>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div class="relative">
                                <label class="block text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase mb-1">Full Name</label>
                                <input type="text" name="attendees[0][name]" value="<?php echo esc_attr( $current_user->display_name ); ?>" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" required placeholder="Name" />
                            </div>
                            <div class="relative">
                                <label class="block text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase mb-1">Phone</label>
                                <input type="tel" name="attendees[0][phone]" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_phone', true ) ); ?>" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" required placeholder="Phone" />
                            </div>
                            <div class="relative">
                                <label class="block text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase mb-1">Company / Organization</label>
                                <input type="text" name="attendees[0][company]" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_company', true ) ); ?>" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" placeholder="Organization" />
                            </div>
                            <div class="relative">
                                <label class="block text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase mb-1">Job Title</label>
                                <input type="text" name="attendees[0][job_title]" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_job_title', true ) ); ?>" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" placeholder="Job Title" />
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" id="btn-add-attendee" class="mt-6 w-full py-6 border-2 border-dashed border-outline-variant/40 rounded-xl flex flex-col items-center justify-center gap-2 group hover:border-primary/40 hover:bg-primary/5 transition-all">
                    <div class="w-10 h-10 rounded-full bg-surface-container-lowest flex items-center justify-center shadow-sm group-hover:bg-primary group-hover:text-on-primary transition-colors">
                        <span class="material-symbols-outlined">add</span>
                    </div>
                    <span class="text-[0.75rem] font-bold tracking-[0.05em] text-on-surface-variant uppercase group-hover:text-primary">Add Attendee</span>
                </button>

                <!-- Contact Info -->
                <div class="mt-8 bg-surface-container-lowest rounded-xl p-6 shadow-[0_12px_32px_rgba(27,27,28,0.06)]">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-sm">contact_page</span>
                        </div>
                        <h3 class="text-lg font-bold tracking-tight">Contact Information</h3>
                    </div>
                    <div class="space-y-6">
                        <div class="relative">
                            <label class="block text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase mb-1">Name</label>
                            <input type="text" name="contact_name" value="<?php echo esc_attr( $current_user->display_name ); ?>" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" required placeholder="Contact Name" />
                        </div>
                        <div class="relative">
                            <label class="block text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase mb-1">Phone</label>
                            <input type="tel" name="contact_phone" value="<?php echo esc_attr( get_user_meta( $current_user->ID, 'conf_phone', true ) ); ?>" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" required placeholder="Contact Phone" />
                        </div>
                        <div class="relative">
                            <label class="block text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase mb-1">Email</label>
                            <input type="email" name="contact_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" required placeholder="Contact Email" />
                        </div>
                    </div>
                </div>

                <div class="mt-12 mb-8">
                    <button type="button" class="btn-next w-full bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-all" data-target="step-2">
                        Continue to Pricing
                    </button>
                </div>
            </div>

            <!-- STEP 2: Pricing -->
            <div class="step-section" id="step-2">
                <div class="mb-10">
                    <div class="flex justify-between items-end mb-2">
                        <p class="text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase">Step 02 of 03</p>
                        <p class="text-[0.75rem] font-bold text-tertiary">Select Tier</p>
                    </div>
                    <div class="h-1 w-full bg-outline-variant/20 rounded-full overflow-hidden">
                        <div class="h-full bg-tertiary w-2/3"></div>
                    </div>
                </div>

                <h2 class="text-[2.75rem] font-bold tracking-[-0.02em] leading-tight text-primary mb-8">Choose Pricing</h2>

                <div class="grid grid-cols-1 gap-6">
                    <?php foreach ( $ticket_tiers as $index => $tier ) : ?>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="ticket_tier" value="<?php echo esc_attr( $tier['name'] ); ?>" data-price="<?php echo esc_attr( $tier['price'] ); ?>" class="sr-only peer tier-radio" <?php echo $index === 0 ? 'checked' : ''; ?> />
                        <div class="p-8 rounded-xl bg-surface-container-lowest transition-all duration-300 ring-1 ring-inset ring-outline-variant/15 peer-checked:ring-2 peer-checked:ring-primary peer-checked:bg-surface shadow-[0_4px_12px_rgba(27,27,28,0.02)]">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="text-[1.375rem] font-bold text-on-surface"><?php echo esc_html( $tier['name'] ); ?></h3>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm text-on-surface-variant block">Price</span>
                                    <span class="text-2xl font-black text-primary">￥<?php echo esc_html( $tier['price'] ); ?></span>
                                </div>
                            </div>
                            <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">verified</span>
                            </div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="mt-12 flex gap-4">
                    <button type="button" class="btn-prev w-1/3 bg-surface-container-high text-on-surface font-bold py-4 rounded-xl active:scale-95 transition-all" data-target="step-1">Back</button>
                    <button type="button" class="btn-next w-2/3 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-all" data-target="step-3">
                        Continue to Review
                    </button>
                </div>
            </div>

            <!-- STEP 3: Review & Payment -->
            <div class="step-section" id="step-3">
                <div class="mb-10">
                    <div class="flex justify-between items-end mb-2">
                        <p class="text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase">Step 03 of 03</p>
                        <p class="text-[0.75rem] font-bold text-tertiary">Review & Pay</p>
                    </div>
                    <div class="h-1 w-full bg-outline-variant/20 rounded-full overflow-hidden">
                        <div class="h-full bg-tertiary w-full"></div>
                    </div>
                </div>

                <div class="space-y-8">
                    <!-- Ticket Summary -->
                    <section class="space-y-4">
                        <h2 class="text-[1.375rem] font-bold tracking-tight text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">confirmation_number</span> Summary
                        </h2>
                        <div class="bg-surface-container-low rounded-xl overflow-hidden">
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="font-bold text-lg text-primary" id="summary-tier-name">Tier</h3>
                                        <p class="text-sm text-on-surface-variant" id="summary-attendee-count">1 Attendee(s)</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xl font-black text-on-surface" id="summary-total-price">¥0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Payment Options -->
                    <section class="space-y-4">
                        <h2 class="text-[1.375rem] font-bold tracking-tight text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">payments</span> Payment Method
                        </h2>
                        <div class="space-y-3">
                            <label class="relative block cursor-pointer group">
                                <input type="radio" name="payment_method" value="wechat" class="sr-only peer payment-radio" checked />
                                <div class="bg-surface-container-low rounded-xl p-4 border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center border border-outline-variant/10">
                                            <span class="material-symbols-outlined text-[#07C160] text-3xl" style="font-variation-settings: 'FILL' 1;">qr_code_2</span>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-on-surface">WeChat Pay (微信支付)</h3>
                                            <p class="text-xs text-on-surface-variant mt-0.5">Instant confirmation</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="relative block cursor-pointer group">
                                <input type="radio" name="payment_method" value="bank" class="sr-only peer payment-radio" />
                                <div class="bg-surface-container-low rounded-xl p-4 border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center border border-outline-variant/10">
                                            <span class="material-symbols-outlined text-[#006494] text-3xl">account_balance</span>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-on-surface">Bank Transfer (银行汇款)</h3>
                                            <p class="text-xs text-on-surface-variant mt-0.5">Requires receipt upload</p>
                                        </div>
                                    </div>
                                </div>
                            </label>

                            <label class="relative block cursor-pointer group">
                                <input type="radio" name="payment_method" value="onsite" class="sr-only peer payment-radio" />
                                <div class="bg-surface-container-low rounded-xl p-4 border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center border border-outline-variant/10">
                                            <span class="material-symbols-outlined text-[#006494] text-3xl">payments</span>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-on-surface">On-site Payment (现场缴费)</h3>
                                            <p class="text-xs text-on-surface-variant mt-0.5">Pay at the venue</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Bank Details Dropdown -->
                        <div id="bank-details-wrap" class="hidden mt-4 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/20 shadow-sm text-center">
                            <span class="material-symbols-outlined text-primary text-3xl mb-2">info</span>
                            <p class="text-sm text-on-surface-variant">You will be asked to upload the transfer receipt on the next step after confirming your order.</p>
                        </div>
                    </section>

                    <div id="form-error-message" class="hidden bg-error-container text-error p-4 rounded-xl text-sm font-medium"></div>

                    <div class="mt-8 flex gap-4">
                        <button type="button" class="btn-prev w-1/3 bg-surface-container-high text-on-surface font-bold py-4 rounded-xl active:scale-95 transition-all" data-target="step-2">Back</button>
                        <button type="submit" id="btn-submit" class="w-2/3 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-all flex justify-center items-center gap-2">
                            Confirm & Pay
                            <span class="material-symbols-outlined text-lg">arrow_forward</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Bank Transfer -->
            <div class="step-section" id="step-bank-transfer">
                <div class="mb-10">
                    <div class="flex justify-between items-end mb-2">
                        <p class="text-[0.75rem] font-medium tracking-[0.05em] text-on-surface-variant uppercase">Payment</p>
                        <p class="text-[0.75rem] font-bold text-tertiary">Bank Transfer</p>
                    </div>
                    <div class="h-1 w-full bg-outline-variant/20 rounded-full overflow-hidden">
                        <div class="h-full bg-tertiary w-full"></div>
                    </div>
                </div>

                <div class="mb-8 text-center">
                    <h2 class="text-xs uppercase tracking-wider text-on-surface-variant mb-2">Amount to Transfer 汇款金额</h2>
                    <div class="text-4xl font-extrabold text-primary">
                        <span class="text-2xl mr-1">￥</span><span id="bank-transfer-amount">0.00</span>
                    </div>
                    <p class="text-sm text-on-surface-variant mt-2">Please transfer exactly this amount.</p>
                </div>
                
                <div class="bg-surface-container-low rounded-xl p-6 border border-outline-variant/20 mb-8">
                    <h3 class="font-bold text-on-surface mb-4">Bank Account Info 银行账户信息</h3>
                    <div class="space-y-3 text-sm">
                        <div><span class="text-on-surface-variant block text-xs">Account Name 账户名称</span> <span class="font-bold text-on-surface text-lg"><?php echo esc_html( get_option('conf_bank_acc_name') ); ?></span></div>
                        <div><span class="text-on-surface-variant block text-xs mt-3">Bank 开户银行</span> <span class="font-medium text-on-surface"><?php echo esc_html( get_option('conf_bank_name') ); ?></span></div>
                        <div><span class="text-on-surface-variant block text-xs mt-3">Account No. 账号</span> <span class="font-mono font-bold text-on-surface text-lg tracking-wider"><?php echo esc_html( get_option('conf_bank_acc_no') ); ?></span></div>
                        <div class="mt-4 p-4 bg-primary-fixed text-on-primary-container rounded-lg text-sm flex items-start gap-2 border border-primary/20">
                            <span class="material-symbols-outlined text-primary text-xl">info</span>
                            <div>* Please include your Order No. <span class="font-bold" id="bank-transfer-order-id"></span> in the transfer notes/remarks.</div>
                        </div>
                    </div>
                </div>

                <div class="bg-surface-container-lowest border-2 border-dashed border-outline-variant/40 rounded-xl p-10 text-center relative hover:bg-surface-container-low hover:border-primary/40 transition-colors cursor-pointer group">
                    <input type="file" id="bank_receipt_upload" name="bank_receipt_upload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/jpeg,image/png,application/pdf">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-3xl text-primary">cloud_upload</span>
                    </div>
                    <p class="font-bold text-on-surface">Click or drag to upload receipt<br>点击上传汇款凭证</p>
                    <p class="text-xs text-on-surface-variant mt-2">支持 JPG, PNG, PDF (最大 5MB)</p>
                    <p id="bank-file-name-display" class="mt-4 text-sm text-primary font-bold hidden"></p>
                </div>

                <div id="bank-upload-error-message" class="hidden mt-4 bg-error-container text-error p-4 rounded-xl text-sm font-medium"></div>

                <div class="mt-8">
                    <button type="button" id="btn-submit-receipt" class="w-full bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-all disabled:opacity-50 flex justify-center items-center gap-2">
                        Submit Receipt 提交凭证
                    </button>
                </div>
            </div>

            <!-- STEP 5: Bank Success Screen -->
            <div class="step-section" id="step-bank-success">
                <section class="flex flex-col items-center text-center mb-12 pt-8">
                    <div class="w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center mb-6 shadow-sm">
                        <span class="material-symbols-outlined text-5xl font-bold" style="font-variation-settings: 'FILL' 1;">hourglass_empty</span>
                    </div>
                    <h1 class="text-[2.5rem] font-extrabold tracking-tight leading-tight text-on-surface mb-2">
                        Under Review
                    </h1>
                    <p class="text-on-surface-variant text-lg">
                        We have received your receipt and will verify it shortly. 我们已收到您的汇款凭证，将尽快核实。
                    </p>
                </section>
                <div class="flex gap-4 mt-8">
                    <a href="<?php echo esc_url( remove_query_arg( 'action' ) ); ?>" class="w-full flex items-center justify-center gap-2 bg-surface-container-low text-primary py-4 rounded-xl font-bold hover:bg-surface-container-high transition-colors active:scale-95">
                        <span class="material-symbols-outlined">home</span> Return Home
                    </a>
                </div>
            </div>

            <!-- STEP 6: Success Screen -->
            <div class="step-section" id="step-success">
                <section class="flex flex-col items-center text-center mb-12 pt-8">
                    <div class="w-20 h-20 bg-tertiary-fixed text-tertiary rounded-full flex items-center justify-center mb-6 shadow-sm">
                        <span class="material-symbols-outlined text-5xl font-bold" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    </div>
                    <h1 class="text-[2.5rem] font-extrabold tracking-tight leading-tight text-on-surface mb-2" id="success-title">
                        Registration Success!
                    </h1>
                    <p class="text-on-surface-variant text-lg" id="success-subtitle">
                        Your seats are reserved.
                    </p>
                </section>

                <section class="relative mb-12">
                    <div class="bg-surface-container-lowest rounded-xl p-8 shadow-[0_12px_32px_rgba(27,27,28,0.06)] relative overflow-hidden border border-outline-variant/10">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 blur-3xl -mr-16 -mt-16"></div>
                        <div class="relative z-10 flex flex-col items-center">
                            <span class="text-xs text-on-surface-variant uppercase tracking-widest mb-6 font-bold">Check-in QR / 入场签到码</span>
                            
                            <div class="w-48 h-48 bg-surface-container p-4 rounded-xl border border-outline-variant/20 mb-2">
                                <div class="w-full h-full bg-white rounded-lg flex items-center justify-center relative overflow-hidden qr-pattern opacity-80">
                                    <div class="absolute inset-4 border-2 border-primary/30 rounded-md"></div>
                                    <span class="material-symbols-outlined text-primary/40 text-4xl">qr_code_2</span>
                                </div>
                            </div>

                            <div class="text-center mb-6">
                                <p class="text-[0.75rem] text-on-surface-variant uppercase tracking-widest font-bold mb-1">Pass Code / 签到码</p>
                                <p class="text-[2.5rem] font-black text-primary tracking-[0.1em]" id="success-six-digit-code">000000</p>
                            </div>
                            
                            <div class="w-full space-y-4">
                                <div class="flex justify-between items-end border-b border-outline-variant/10 pb-4">
                                    <div class="w-full flex flex-col items-center pt-2">
                                        <p class="text-[0.75rem] text-on-surface-variant uppercase tracking-widest font-semibold mb-1">Order No.</p>
                                        <p class="text-xl font-bold text-on-surface tracking-tight" id="success-order-id">#SUM24-000</p>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <p class="text-[0.75rem] text-on-surface-variant uppercase tracking-wider font-medium text-center mb-3">Attendees</p>
                                    <ul id="success-attendee-list" class="space-y-2 text-center text-on-surface font-bold text-lg">
                                        <!-- Vertical list populated via JS -->
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="flex gap-4">
                    <a href="<?php echo esc_url( remove_query_arg( 'action' ) ); ?>" class="w-full flex items-center justify-center gap-2 bg-surface-container-low text-primary py-4 rounded-xl font-bold hover:bg-surface-container-high transition-colors active:scale-95">
                        <span class="material-symbols-outlined">home</span> Return Home
                    </a>
                </div>
            </div>

        </form>
    </main>
</div>

<!-- Template for cloning Attendee -->
<template id="attendee-template">
    <div class="attendee-item bg-surface-container-lowest rounded-xl p-6 shadow-[0_12px_32px_rgba(27,27,28,0.06)] relative mt-4">
        <button type="button" class="btn-remove-attendee absolute top-6 right-6 text-error hover:bg-error-container p-1 rounded-full transition-colors">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-outline-variant/30 flex items-center justify-center text-on-surface-variant font-bold text-sm attendee-number">2</div>
                <h3 class="text-lg font-bold tracking-tight text-on-surface-variant">Additional Attendee</h3>
            </div>
        </div>
        <div class="space-y-6">
            <div class="relative">
                <input type="text" name="attendees[{index}][name]" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" required placeholder="Full Name" />
            </div>
            <div class="relative">
                <input type="tel" name="attendees[{index}][phone]" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" required placeholder="Phone" />
            </div>
            <div class="relative">
                <input type="text" name="attendees[{index}][company]" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" placeholder="Organization" />
            </div>
            <div class="relative">
                <input type="text" name="attendees[{index}][job_title]" class="w-full py-2 text-lg focus:ring-0 transition-colors placeholder:text-on-surface-variant/40" placeholder="Job Title" />
            </div>
        </div>
    </div>
</template>
