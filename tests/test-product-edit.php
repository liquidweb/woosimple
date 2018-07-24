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

	public function test_saves_toggled_value() {
		$user_id = $this->factory->user->create( [
			'role' => 'contributor',
		] );
		wp_set_current_user( $user_id );

		$_POST = [
			'woosimple-toggle-switch' => 1,
			'woosimple-toggle-nonce'  => wp_create_nonce( 'woosimple-toggle-nonce' ),
		];

		handle_post_save();

		$this->assertTrue(
			(bool) get_user_meta( $user_id, 'woosimple_product', true ),
			'The woosimple_product user meta should have been set.'
		);
	}

	public function test_saves_toggled_value_when_empty() {
		$user_id = $this->factory->user->create( [
			'role' => 'contributor',
		] );
		wp_set_current_user( $user_id );
		add_user_meta( $user_id, 'woosimple_product', 1 );

		$_POST = [
			'woosimple-toggle-nonce' => wp_create_nonce( 'woosimple-toggle-nonce' ),
		];

		handle_post_save();

		$this->assertFalse(
			(bool) get_user_meta( $user_id, 'woosimple_product', true ),
			'Not having a value for $_POST["woosimple-toggle-switch"] means it\'s empty.'
		);
	}

	public function test_verifies_nonce_when_saving_toggled_value() {
		$user_id = $this->factory->user->create( [
			'role' => 'contributor',
		] );
		wp_set_current_user( $user_id );

		$_POST = [
			'woosimple-toggle-switch' => 1,
			'woosimple-toggle-nonce'  => wp_create_nonce( 'INVALID-woosimple-toggle' ),
		];

		handle_post_save();

		$this->assertFalse(
			(bool) get_user_meta( $user_id, 'woosimple_product', true ),
			'Nothing should change if nonce verification fails.'
		);
	}
}
