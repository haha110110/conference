<?php
/**
 * Frontend Registration SPA Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
?>

<div id="conf-registration-app" class="conf-design-system antialiased font-sans bg-gray-50 min-h-screen text-gray-900">
	<!-- Tailwind config specific to this app -->
	<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
	<script>
		tailwind.config = {
			important: '#conf-registration-app',
			corePlugins: {
				preflight: false,
			},
			theme: {
				extend: {
					colors: {
						primary: {
							50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa',
							500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a',
						}
					}
				}
			}
		}
	</script>
	<style>
		/* Custom scrollbar and minimal resets scoped to the app */
		#conf-registration-app {
			font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
		}
		#conf-registration-app *,
		#conf-registration-app *::before,
		#conf-registration-app *::after {
			box-sizing: border-box;
		}
		#conf-registration-app input[type="text"], 
		#conf-registration-app input[type="email"], 
		#conf-registration-app input[type="tel"] {
			box-sizing: border-box;
		}
	</style>

	<!-- App Container -->
	<div id="conf-app-mount">
		<!-- JS will mount views here -->
		<div class="flex items-center justify-center min-h-screen pb-20">
			<div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
		</div>
	</div>

	<!-- Pass PHP variables to JS -->
	<script>
		window.confAppConfig = {
			apiUrl: '<?php echo esc_url( rest_url( 'conf/v1' ) ); ?>',
			nonce: '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>',
			assetsUrl: '<?php echo esc_url( CONF_MANAGER_URL . 'assets' ); ?>',
			currency: '￥'
		};
	</script>
	<script src="<?php echo esc_url( CONF_MANAGER_URL . 'assets/js/conf-registration.js' ); ?>?ver=<?php echo time(); ?>" defer></script>
</div>
