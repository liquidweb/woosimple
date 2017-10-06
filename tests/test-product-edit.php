<?php
/**
 * Tests for inc/product-edit.php.
 *
 * @package WooSimple
 */

namespace WooSimple\ProductEdit;

use WP_UnitTestCase;

class ProductEditTest extends WP_UnitTestCase {

	const SCRIPT_HANDLE = 'woosimple-product-edit';
	const STYLE_HANDLE = 'woosimple-admin';

	/**
	 * @dataProvider page_hook_provider()
	 */
	public function test_enqueues_assets( $hook, $should_be_enqueued ) {
		wp_dequeue_style( self::STYLE_HANDLE );
		wp_dequeue_script( self::SCRIPT_HANDLE );

		$this->assertFalse( wp_script_is( self::STYLE_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( self::SCRIPT_HANDLE, 'enqueued' ) );

		do_action( 'admin_enqueue_scripts', $hook );

		$this->assertEquals(
			$should_be_enqueued,
			wp_style_is( self::STYLE_HANDLE, 'enqueued' )
		);

		$this->assertEquals(
			$should_be_enqueued,
			wp_script_is( self::SCRIPT_HANDLE, 'enqueued' )
		);
	}

	public function page_hook_provider() {
		return [
			'Dashboard' => [ 'index.php', false ],
			'New product' => [ 'post-new.php', true ],
			'Edit product' => [ 'post.php', true ],
		];
	}

	public function test_registers_toggle_metabox() {
		global $wp_meta_boxes;

		$this->assertEmpty(
			$wp_meta_boxes['product']['side']['high']['woosimple-toggle'],
			'The toggle meta box should only be registered on specific pages.'
		);
		$this->assertEmpty(
			$wp_meta_boxes['product']['side']['high']['woosimple-price'],
			'The price meta box should only be registered on specific pages.'
		);

		$this->go_to(admin_url('post-new.php?post_type=product'));

		do_action( 'add_meta_boxes' );

		$this->assertNotEmpty(
			$wp_meta_boxes['product']['side']['high']['woosimple-toggle'],
			'The toggle meta box should be available on the new product screen.'
		);
		$this->assertNotEmpty(
			$wp_meta_boxes['product']['side']['core']['woosimple-price'],
			'The price meta box should be available on the new product screen.'
		);
	}
}
