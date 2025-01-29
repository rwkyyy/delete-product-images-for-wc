<?php
/*
Plugin Name: Delete product images for WooCommerce
Plugin URI: https://uprise.ro
Description: Removes product assigned images (featured and gallery only) on product delete.
Requires at least: 4.7
Tested up to: 6.5.2
Stable tag: trunk
Requires PHP: 7.4
Version: 2.0
Author: Eduard V. Doloc
Author Email: eduard@uprise.ro
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'UPRISE_WC_DELETE_IMAGES_VERSION', '2.0' );
define( 'UPRISE_WC_DELETE_IMAGES_MIN_WC_VERSION', '3.0.0' );

// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	// Initialize the plugin
	add_action( 'plugins_loaded', 'uprise_wc_delete_images_init' );

	/**
	 * Get WooCommerce logger instance
	 *
	 * @return WC_Logger
	 */
	function uprise_get_wc_logger() {
		static $logger = null;

		if ( $logger === null ) {
			$logger = wc_get_logger();
		}

		return $logger;
	}

	/**
	 * Log message to WooCommerce logs
	 *
	 * @param string $message Message to log
	 * @param string $level One of: emergency, alert, critical, error, warning, notice, info, debug
	 *
	 * @return void
	 */
	function uprise_wc_log( $message, $level = 'error' ) {
		$logger  = uprise_get_wc_logger();
		$context = array( 'source' => 'uprise-delete-product-images' );
		$logger->log( $level, $message, $context );
	}

	/**
	 * Initialize plugin functionality
	 *
	 * @return void
	 */
	function uprise_wc_delete_images_init() {
		// Check WooCommerce version
		if ( version_compare( WC_VERSION, UPRISE_WC_DELETE_IMAGES_MIN_WC_VERSION, '<' ) ) {
			add_action( 'admin_notices', 'uprise_wc_delete_images_wc_version_notice' );

			return;
		}

		// Hook into product deletion
		add_action( 'before_delete_post', 'uprise_wc_delete_product_images', 10, 1 );
	}

	/**
	 * Display admin notice for minimum WooCommerce version
	 *
	 * @return void
	 */
	function uprise_wc_delete_images_wc_version_notice() {
		?>
        <div class="error">
            <p><?php echo esc_html( sprintf(
					'Delete Product Images for WooCommerce requires WooCommerce %s or higher. Please update WooCommerce to use this plugin.',
					esc_html( UPRISE_WC_DELETE_IMAGES_MIN_WC_VERSION )
				) ); ?></p>
        </div>
		<?php
	}

	/**
	 * Deletes product featured and gallery images when a product is deleted.
	 *
	 * @param int $post_id The ID of the product being deleted.
	 *
	 * @return void
	 */
	function uprise_wc_delete_product_images( $post_id ) {
		try {
			// Verify it's a product
			if ( get_post_type( $post_id ) !== 'product' ) {
				return;
			}

			// Verify nonce if triggered via admin action
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? wp_unslash( sanitize_text_field( $_REQUEST['_wpnonce'] ) ) : '';
			if ( ! empty( $nonce ) && ! wp_verify_nonce( $nonce, 'delete-post_' . $post_id ) ) {
				return;
			}

			// Get product object
			$product = wc_get_product( $post_id );

			// Failsafe check
			if ( ! $product ) {
				uprise_wc_log( sprintf( 'Failed to get product with ID: %d', absint( $post_id ) ) );

				return;
			}

			// Get image IDs
			$featured_image_id  = $product->get_image_id();
			$image_galleries_id = $product->get_gallery_image_ids();

			// Delete featured image if exists and is valid
			if ( ! empty( $featured_image_id ) && wp_attachment_is_image( $featured_image_id ) ) {
				$result = wp_delete_attachment( $featured_image_id, true );
				if ( is_wp_error( $result ) ) {
					uprise_wc_log( sprintf( 'Failed to delete featured image (ID: %d) for product %d: %s',
						absint( $featured_image_id ),
						absint( $post_id ),
						esc_html( $result->get_error_message() )
					) );
				} else {
					uprise_wc_log(
						sprintf( 'Successfully deleted featured image (ID: %d) for product %d',
							absint( $featured_image_id ),
							absint( $post_id )
						),
						'info'
					);
				}
			}

			// Delete gallery images if they exist
			if ( ! empty( $image_galleries_id ) ) {
				foreach ( $image_galleries_id as $single_image_id ) {
					if ( wp_attachment_is_image( $single_image_id ) ) {
						$result = wp_delete_attachment( $single_image_id, true );
						if ( is_wp_error( $result ) ) {
							uprise_wc_log( sprintf( 'Failed to delete gallery image (ID: %d) for product %d: %s',
								absint( $single_image_id ),
								absint( $post_id ),
								esc_html( $result->get_error_message() )
							) );
						} else {
							uprise_wc_log(
								sprintf( 'Successfully deleted gallery image (ID: %d) for product %d',
									absint( $single_image_id ),
									absint( $post_id )
								),
								'info'
							);
						}
					}
				}
			}

		} catch ( Exception $e ) {
			uprise_wc_log( sprintf( 'Error deleting product images for product %d: %s',
				absint( $post_id ),
				esc_html( $e->getMessage() )
			) );

			return;
		}
	}
}
