<?php
/**
 * User Dashboard Template (Tailwind UI)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$current_user = wp_get_current_user();
$posts_per_page = 10;
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$args = array(
	'post_type'      => 'conf_order',
	'posts_per_page' => $posts_per_page,
	'paged'          => $paged,
	'author'         => $current_user->ID,
	'post_status'    => 'publish',
);

$query = new WP_Query( $args );
$total_orders = $query->found_posts;
$total_pages = ceil( $total_orders / $posts_per_page );
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
    <div class="w-full sm:max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-10">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-on-surface mb-1"><?php esc_html_e( 'My Registrations', 'conf-manager' ); ?></h1>
                <p class="text-on-surface-variant"><?php printf( esc_html__( 'Welcome back, %s', 'conf-manager' ), $current_user->display_name ); ?></p>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                <!-- Language Switcher -->
                <div class="bg-surface-container-low px-4 py-2 rounded-full border border-outline-variant/10 flex items-center gap-3 text-sm">
                    <a href="<?php echo add_query_arg( 'conf_lang', 'zh_CN' ); ?>" class="<?php echo get_locale() === 'zh_CN' ? 'font-bold text-primary underline underline-offset-4' : 'text-on-surface-variant hover:text-primary transition-colors'; ?>">中文</a>
                    <span class="text-outline-variant/30">|</span>
                    <a href="<?php echo add_query_arg( 'conf_lang', 'en_US' ); ?>" class="<?php echo get_locale() === 'en_US' ? 'font-bold text-primary underline underline-offset-4' : 'text-on-surface-variant hover:text-primary transition-colors'; ?>">English</a>
                </div>
                
                <a href="<?php echo esc_url( add_query_arg( 'action', 'register' ) ); ?>" class="w-full sm:w-auto bg-primary text-on-primary font-bold py-3 px-6 rounded-xl shadow-lg hover:bg-primary-container transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">add_circle</span>
                    <?php esc_html_e( 'New Registration', 'conf-manager' ); ?>
                </a>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-3xl p-4 sm:p-10 shadow-[0_12px_48px_rgba(27,27,28,0.06)] border border-outline-variant/10">
            <?php if ( $query->have_posts() ) : ?>
                <div class="space-y-6">
                    <?php while ( $query->have_posts() ) : $query->the_post(); 
                        $order_id = get_the_ID();
                        $total = get_post_meta( $order_id, 'conf_order_total', true );
                        $status = get_post_meta( $order_id, 'conf_payment_status', true );
                        
                        // Fallback for status
                        if ( empty( $status ) ) {
                            $status = get_post_meta( $order_id, 'conf_status', true );
                        }
                        
                        // Status styling
                        $status_classes = 'px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ';
                        switch( $status ) {
                            case 'paid': $status_classes .= 'bg-tertiary/10 text-tertiary'; break;
                            case 'pending': $status_classes .= 'bg-amber-100 text-amber-700'; break;
                            default: $status_classes .= 'bg-error/10 text-error'; break;
                        }
                    ?>
                        <div class="group bg-surface-container-low hover:bg-surface-container-high transition-all rounded-2xl p-6 border border-outline-variant/5">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm text-primary">
                                        <span class="material-symbols-outlined">confirmation_number</span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg text-on-surface">Order #<?php the_ID(); ?></h3>
                                        <p class="text-sm text-on-surface-variant"><?php echo get_the_date(); ?> • <?php echo esc_html( get_post_meta( $order_id, 'conf_ticket_tier', true ) ); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between w-full sm:w-auto gap-6">
                                    <div class="text-right hidden sm:block">
                                        <p class="text-sm text-on-surface-variant mb-1"><?php esc_html_e( 'Amount', 'conf-manager' ); ?></p>
                                        <p class="font-black text-on-surface">￥<?php echo number_format( floatval( $total ), 2 ); ?></p>
                                    </div>
                                    
                                    <div class="flex items-center gap-4">
                                        <span class="<?php echo esc_attr( $status_classes ); ?>">
                                            <?php echo esc_html( $status ); ?>
                                        </span>
                                        <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'order', 'id' => $order_id ) ) ); ?>" class="p-2 hover:bg-primary/10 rounded-full text-primary transition-colors">
                                            <span class="material-symbols-outlined">arrow_forward_ios</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>

                <!-- Pagination -->
                <?php if ( $total_pages > 1 ) : ?>
                    <div class="mt-10 flex justify-center gap-2">
                        <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>" class="w-10 h-10 flex items-center justify-center rounded-xl font-bold transition-all <?php echo $paged == $i ? 'bg-primary text-on-primary shadow-lg' : 'bg-surface-container-high text-on-surface-variant hover:bg-primary/10 hover:text-primary'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-surface-container-high rounded-full flex items-center justify-center mx-auto mb-6 opacity-40">
                        <span class="material-symbols-outlined text-4xl">inbox</span>
                    </div>
                    <h3 class="text-xl font-bold text-on-surface-variant mb-2"><?php esc_html_e( 'No registrations found', 'conf-manager' ); ?></h3>
                    <p class="text-on-surface-variant mb-8"><?php esc_html_e( 'You haven\'t registered for any conferences yet.', 'conf-manager' ); ?></p>
                    <a href="<?php echo esc_url( add_query_arg( 'action', 'register' ) ); ?>" class="inline-flex items-center gap-2 bg-primary text-on-primary font-bold py-3 px-8 rounded-xl shadow-lg hover:bg-primary-container transition-all">
                        <span class="material-symbols-outlined">add_circle</span>
                        <?php esc_html_e( 'Start Registration', 'conf-manager' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-10 text-center">
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="text-on-surface-variant hover:text-error transition-colors text-sm font-medium flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">logout</span>
                <?php esc_html_e( 'Log Out', 'conf-manager' ); ?>
            </a>
        </div>
    </div>
</div>
