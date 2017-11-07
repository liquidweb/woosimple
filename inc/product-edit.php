<?php
/**
 * Customizations to the product edit screen.
 *
 * @package WooSimple
 * @author  Liquid Web
 */

namespace WooSimple\ProductEdit;

/**
 * Enqueue the script for the product edit screen.
 *
 * @param string $hook The name of the current admin page.
 */
function enqueue_scripts( $hook ) {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$ver = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : WOOSIMPLE_VERSION;

	wp_enqueue_style(
		'woosimple-admin',
		WOOSIMPLE_URL . "/assets/css/admin.css",
		null,
		$ver,
		'all'
	);

	wp_enqueue_script(
		'woosimple-product-edit',
		WOOSIMPLE_URL . "/assets/js/product-edit{$min}.js",
		[ 'postbox' ],
		$ver,
		true
	);

	wp_localize_script( 'woosimple-product-edit', 'wooSimple', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

/**
 * Register the WooSimple meta box.
 */
function register_metaboxes() {

	add_meta_box(
		'woosimple-price',
		_x( 'Pricing', 'Product edit meta box name', 'woosimple' ),
		__NAMESPACE__ . '\render_price_metabox',
		'product',
		'side',
		'core'
	);
}
add_action( 'add_meta_boxes', __NAMESPACE__ . '\register_metaboxes' );


/**
 * Check for our URL with the nonce and woosimple string.
 *
 * @return void
 */
function check_manual_setting() {

	// Make sure our values were passed correctly.
	if ( empty( $_GET['woosimple'] ) || empty( $_GET['_wsnonce'] ) || empty( $_GET['post'] ) ) {
		return;
	}

	// Handle our nonce check.
	if ( ! wp_verify_nonce( $_GET['_wsnonce'], 'woosimple-link' ) ) {
		return;
	}

	// Make sure it's one of the two we want.
	if ( ! in_array( esc_attr( $_GET['woosimple'] ), array( 'on', 'off' ) ) ) {
		return;
	}

	// Now set the setting.
	set_user_setting( 'woosimple', esc_attr( $_GET['woosimple'] ) );

	// Recreate the edit URL and redirect.
	wp_redirect( get_edit_post_link( absint( $_GET['post'] ), 'raw' ) );
	exit();
}
add_action( 'admin_head', __NAMESPACE__ . '\check_manual_setting' );

/**
 * Render the WooSimple screen options box.
 *
 * @param  string $settings  Our existing settings string.
 * @param  object $instance  The screen instance object.
 *
 * @return string            The updated settings string.
 */
function set_screen_setting( $settings, $instance ) {

	// Bail if we aren't on a single product edit page.
	if ( ! is_object( $instance ) || empty( $instance->base ) || 'post' !== $instance->base || empty( $instance->post_type ) || 'product' !== $instance->post_type ) {
		return $settings;
	}

	// Check for whether we've turned this on or not.
	$check  = get_user_setting( 'woosimple', 'off' );

	// Set the toggle (opposite) value for the URL.
	$toggle = 'on' === esc_attr( $check ) ? 'off' : 'on';

	// Set my args for the link.
	$args   = array(
		'woosimple' => $toggle,
		'_wsnonce'  => wp_create_nonce( 'woosimple-link' ),
	);

	// Construct the link and text.
	$link   = add_query_arg( $args, get_edit_post_link( get_the_ID(), 'raw' ) );

	// Set our empty.
	$build  = '';

	// Build our button link for the non-JS fallback.
	$build .= '<fieldset class="editor-woosimple editor-woosimple-manual hide-if-js">';
		$build .= '<p class="woosimple-toggle-row">';
			$build .= '<a href="' . esc_url( $link ) . '" class="button button-small button-secondary woosimple-button">' . __( 'Click', 'woosimple' ) . '</a>';
			$build .= __( 'Toggle the display of extra settings from the WooCommerce product details.', 'woosimple' );
		$build .= '</p>';
	$build .= '</fieldset>';

	// Build our checkbox for the JS version.
	$build .= '<fieldset class="editor-woosimple editor-woosimple-ajax hide-if-no-js">';
		$build .= '<label for="editor-woosimple-toggle">';
		$build .= '<input type="checkbox" name="editor-woosimple-toggle" id="editor-woosimple-toggle"' . checked( 'on', $check, false ) . ' />';
		$build .= __( 'Remove extra settings from the WooCommerce product details', 'woosimple' ) . '</label>';
		$build .= wp_nonce_field( 'woosimple-toggle-nonce', 'woosimple-toggle-nonce', true, false );
	$build .= '</fieldset>';

	// Now add this build to our existing setting string.
	$settings .= $build;

	// And return the settings string.
	return $settings;
}
add_filter( 'screen_settings', __NAMESPACE__ . '\set_screen_setting', 10, 2 );


/**
 * Render the simplified price meta box.
 */
function render_price_metabox() {
	$product = wc_get_product( get_the_ID() );
?>

	<p>
		<label for="_regular_price">
			<?php echo esc_html( __( 'Regular price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ); ?>
		</label>
		<input id="woosimple_regular_price" type="text" value="<?php echo esc_attr( $product->get_regular_price( 'edit' ) ); ?>">
	</p>

<?php
}

/**
 * Handle the Ajax call for setting the WooSimple state.
 *
 * @return array
 */
function save_user_setting() {

	// Only run this on the admin side.
	if ( ! is_admin() ) {
		die();
	}

	// Bail if we are doing a REST API request.
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	// Bail out if running an autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Bail out if running a cron, unless we've skipped that.
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}

	// Make sure we can even edit posts.
	if ( ! current_user_can( 'edit_post' ) ) {
		return;
	}

	// Do our nonce check.
	if ( empty( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_GET['nonce'] ), 'woosimple-toggle-nonce' ) ) {
		return;
	}

	// Make sure the action is correct.
	if ( empty( $_GET['action'] ) || 'woosimple_save_setting' !== sanitize_text_field( $_GET['action'] ) ) {
		return;
	}

	// Make sure we have one of the two actions.
	if ( empty( $_GET['woosimple'] ) || ! in_array( sanitize_text_field( $_GET['woosimple'] ), array( 'on', 'off' ) ) ) {
		return;
	}

	// Now set the setting.
	set_user_setting( 'woosimple', esc_attr( $_GET['woosimple'] ) );
}

add_action( 'wp_ajax_woosimple_save_setting', __NAMESPACE__ . '\save_user_setting' );
