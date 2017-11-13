<?php
/**
 * Plugin Name:           WooSimple
 * Description:           Enhancements to simplify WooCommerce management.
 * Author:                Liquid Web
 * Author URI:            https://www.liquidweb.com
 * Version:               0.1.0
 * Text Domain:           woosimple
 * WC requires at least:  3.0.0
 * WC tested up to:       3.2.0
 *
 * @package WooSimple
 * @author  Liquid Web
 */

namespace WooSimple;

define( 'WOOSIMPLE_VERSION', '0.1.0' );
define( 'WOOSIMPLE_URL', plugins_url( '', __FILE__ ) );
define( 'WOOSIMPLE_INC', __DIR__ . '/inc' );

require_once WOOSIMPLE_INC . '/product-edit.php';
