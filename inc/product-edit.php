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

	// Set our minified check and version number.
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	$ver = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : WOOSIMPLE_VERSION;

	// Enqueue the necessary assets.
	wp_enqueue_style(
		'woosimple-admin',
		WOOSIMPLE_URL . '/assets/css/admin.css',
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
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

/**
 * Register the WooSimple meta box.
 */
function register_metaboxes() {
	add_meta_box(
		'woosimple-toggle',
		_x( 'WooSimple', 'Product edit meta box name', 'woosimple' ),
		__NAMESPACE__ . '\render_toggle_metabox',
		'product',
		'side',
		'high'
	);

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
 * Render the WooSimple meta box.
 */
function render_toggle_metabox() {

	// Fetch our user setting.
	$active = (bool) get_user_meta( wp_get_current_user()->ID, 'woosimple_product', true );

	// Output our nonce field.
	wp_nonce_field( 'woosimple-toggle-nonce', 'woosimple-toggle-nonce' );
?>

	<p>
		<label>
			<input name="woosimple-toggle-switch" value="on" id="woosimple-toggle-switch" class="woosimple-toggle-switch" type="checkbox" <?php checked( 'on', $active ); ?>>
			<?php esc_html_e( 'Simplify this Page', 'woosimple' ); ?>
		</label>
	</p>

<?php
}

/**
 * Render the simplified price meta box.
 */
function render_price_metabox() {
	$product = wc_get_product( get_the_ID() );
?>

	<p class="woosimple-render-price-field">
		<label for="_regular_price">
			<?php echo esc_html( __( 'Regular price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')' ); ?>
		</label>
		<input id="woosimple_regular_price" type="text" class="woosimple-render-price" value="<?php echo esc_attr( $product->get_regular_price( 'edit' ) ); ?>">
	</p>

<?php
}

/**
 * Handle saving meta boxes.
 */
function handle_post_save() {

	// Run our nonce check.
	if ( ! isset( $_POST['woosimple-toggle-nonce'] ) || ! wp_verify_nonce( $_POST['woosimple-toggle-nonce'], 'woosimple-toggle-nonce' ) ) { // WPCS: sanitization ok.
		return;
	}

	// Store whether or not the user was in "Easy Mode".
	update_user_meta( wp_get_current_user()->ID, 'woosimple_product', empty( $_POST['woosimple-toggle-switch'] ) ? 0 : 1 );
}
add_action( 'save_post_product', __NAMESPACE__ . '\handle_post_save' );
