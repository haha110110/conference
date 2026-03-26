<?php
/**
 * Login/Register Template (Tailwind UI)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
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

<div id="conf-registration-container" class="min-h-screen bg-[#fcf9f8] font-body text-on-surface py-12 px-4 sm:px-6">
    <div class="w-full sm:max-w-4xl mx-auto">
        <!-- Header / Language Switcher -->
        <div class="flex justify-center sm:justify-end mb-6">
            <div class="bg-surface-container-low sm:bg-transparent px-4 py-2 sm:p-0 rounded-full border border-outline-variant/10 sm:border-none flex items-center gap-3 text-sm">
                <a href="<?php echo add_query_arg( 'conf_lang', 'zh_CN' ); ?>" class="<?php echo get_locale() === 'zh_CN' ? 'font-bold text-primary underline underline-offset-4' : 'text-on-surface-variant hover:text-primary transition-colors'; ?>">中文</a>
                <span class="text-outline-variant/30">|</span>
                <a href="<?php echo add_query_arg( 'conf_lang', 'en_US' ); ?>" class="<?php echo get_locale() === 'en_US' ? 'font-bold text-primary underline underline-offset-4' : 'text-on-surface-variant hover:text-primary transition-colors'; ?>">English</a>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-surface-container-lowest rounded-[2rem] p-4 sm:p-12 shadow-[0_12px_48px_rgba(27,27,28,0.06)] border border-outline-variant/10">
            <div class="text-center mb-10">
                <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-primary text-3xl">meeting_room</span>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-on-surface mb-2"><?php esc_html_e( 'Conference Management', 'conf-manager' ); ?></h1>
                <p class="text-on-surface-variant"><?php esc_html_e( 'Please log in or register to manage your conference registrations.', 'conf-manager' ); ?></p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-12">
                <!-- Login Section -->
                <div class="bg-surface-container-low rounded-2xl p-8 hover:bg-surface-container-high transition-colors group">
                    <div class="flex flex-col items-center text-center">
                        <span class="material-symbols-outlined text-primary text-4xl mb-4 group-hover:scale-110 transition-transform">login</span>
                        <h2 class="text-xl font-bold mb-4"><?php esc_html_e( 'Already Registered?', 'conf-manager' ); ?></h2>
                        <a href="<?php echo esc_url( wp_login_url( remove_query_arg( 'conf_lang' ) ) ); ?>" class="w-full bg-primary text-on-primary font-bold py-3 px-6 rounded-xl shadow-lg hover:bg-primary-container transition-all text-center">
                            <?php esc_html_e( 'Log In', 'conf-manager' ); ?>
                        </a>
                    </div>
                </div>

                <!-- Register Section -->
                <div class="bg-surface-container-low rounded-2xl p-8 hover:bg-surface-container-high transition-colors group">
                    <div class="flex flex-col items-center text-center">
                        <span class="material-symbols-outlined text-tertiary text-4xl mb-4 group-hover:scale-110 transition-transform">person_add</span>
                        <h2 class="text-xl font-bold mb-4"><?php esc_html_e( 'New Attendee?', 'conf-manager' ); ?></h2>
                        <a href="<?php echo esc_url( wp_registration_url() ); ?>" class="w-full bg-tertiary text-on-primary font-bold py-3 px-6 rounded-xl shadow-lg hover:bg-tertiary-container transition-all text-center">
                            <?php esc_html_e( 'Sign Up', 'conf-manager' ); ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-outline-variant/10 text-center">
                <p class="text-sm text-on-surface-variant flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm">shield</span>
                    <?php esc_html_e( 'Secure access for authorized attendees only.', 'conf-manager' ); ?>
                </p>
            </div>
        </div>
    </div>
</div>
