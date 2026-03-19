<?php
/*
Plugin Name: Delete product images for WooCommerce
Plugin URI: https://uprise.ro
Description: Deletes product images (featured, gallery, and variation images) when a WooCommerce product is permanently deleted — with built-in safeguards to prevent accidental data loss.
Requires at least: 4.7
Tested up to: 6.9.3
Stable tag: trunk
Requires PHP: 7.4
Version: 3.0
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
define( 'UPRISE_WC_DELETE_IMAGES_VERSION', '3.0' );
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
        $context = array( 'source' => 'wc-delete-product-images' );
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
            uprise_wc_log( sprintf( 'START delete process for post ID %d', $post_id ), 'info' );

            // Check if feature is disabled
            if ( get_option( 'uprise_wc_delete_images_enabled', 'yes' ) !== 'yes' ) {
                uprise_wc_log( 'Image deletion is disabled via admin toggle.', 'notice' );

                return;
            }

            // Verify it's a product
            if ( get_post_type( $post_id ) !== 'product' ) {
                uprise_wc_log( sprintf( 'Skipping ID %d - not a product (type: %s)', $post_id, get_post_type( $post_id ) ), 'debug' );

                return;
            }


            // Extra safety: only continue when the product is already in Trash.
            if ( get_post_status( $post_id ) !== 'trash' ) {
                uprise_wc_log( sprintf( 'Skipping ID %d - not permanently deleted (status: %s)', $post_id, get_post_status( $post_id ) ), 'debug' );

                return;
            }

            // Get product object
            $product = wc_get_product( $post_id );

            uprise_wc_log( sprintf( 'Product object loaded for ID %d', $post_id ), 'info' );

            // Failsafe check
            if ( ! $product ) {
                uprise_wc_log( sprintf( 'Failed to get product with ID: %d', absint( $post_id ) ) );

                return;
            }

            // Collect all image IDs (featured, gallery, variations)
            $featured_image_id   = $product->get_image_id();
            $image_galleries_id  = $product->get_gallery_image_ids();
            $variation_image_ids = [];

            if ( $product->is_type( 'variable' ) ) {
                foreach ( $product->get_children() as $variation_id ) {
                    $variation = wc_get_product( $variation_id );
                    if ( $variation ) {
                        $vid = $variation->get_image_id();
                        if ( $vid ) {
                            $variation_image_ids[] = $vid;
                        }
                    }
                }
            }

            $all_image_ids = array_unique( array_filter( array_merge(
                    [ $featured_image_id ],
                    $image_galleries_id,
                    $variation_image_ids
            ) ) );

            uprise_wc_log( sprintf( 'Collected images for product %d: %s', $post_id, implode( ',', $all_image_ids ) ), 'info' );

            // Check if any image is used elsewhere
            foreach ( $all_image_ids as $image_id ) {
                uprise_wc_log( sprintf( 'Checking usage for image ID %d (product %d)', $image_id, $post_id ), 'debug' );

                global $wpdb;

                // Check featured image usage (ignore products in trash)
                $used_as_thumb = $wpdb->get_var( $wpdb->prepare(
                        "SELECT pm.post_id FROM {$wpdb->postmeta} pm
                         INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                         WHERE pm.meta_key = '_thumbnail_id'
                         AND pm.meta_value = %d
                         AND pm.post_id != %d
                         AND p.post_status != 'trash'
                         LIMIT 1",
                        $image_id,
                        $post_id
                ) );

                // Check gallery usage (ignore products in trash)
                $used_in_gallery = $wpdb->get_var( $wpdb->prepare(
                        "SELECT pm.post_id FROM {$wpdb->postmeta} pm
                         INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                         WHERE pm.meta_key = '_product_image_gallery'
                         AND pm.meta_value LIKE %s
                         AND pm.post_id != %d
                         AND p.post_status != 'trash'
                         LIMIT 1",
                        '%' . $wpdb->esc_like( $image_id ) . '%',
                        $post_id
                ) );

                if ( $used_as_thumb || $used_in_gallery ) {
                    set_transient(
                            'uprise_delete_images_notice_' . $post_id,
                            sprintf(
                                    'Product #%d skipped. Image #%d is used by another product (%d).',
                                    $post_id,
                                    $image_id,
                                    $used_as_thumb ?: $used_in_gallery
                            ),
                            30
                    );

                    uprise_wc_log(
                            sprintf(
                                    'Skipped deletion for product %d because image %d is used elsewhere (product %d)',
                                    $post_id,
                                    $image_id,
                                    $used_as_thumb ?: $used_in_gallery
                            ),
                            'warning'
                    );

                    continue;
                }
            }

            // Re-check and collect skipped images before deletion
            $skipped_images = [];
            // Collect skipped images again (same logic but store IDs)
            foreach ( $all_image_ids as $image_id ) {
                global $wpdb;

                $used_as_thumb = $wpdb->get_var( $wpdb->prepare(
                        "SELECT pm.post_id FROM {$wpdb->postmeta} pm
                     INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                     WHERE pm.meta_key = '_thumbnail_id'
                     AND pm.meta_value = %d
                     AND pm.post_id != %d
                     AND p.post_status != 'trash'
                     LIMIT 1",
                        $image_id,
                        $post_id
                ) );

                $used_in_gallery = $wpdb->get_var( $wpdb->prepare(
                        "SELECT pm.post_id FROM {$wpdb->postmeta} pm
                     INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                     WHERE pm.meta_key = '_product_image_gallery'
                     AND pm.meta_value LIKE %s
                     AND pm.post_id != %d
                     AND p.post_status != 'trash'
                     LIMIT 1",
                        '%' . $wpdb->esc_like( $image_id ) . '%',
                        $post_id
                ) );

                if ( $used_as_thumb || $used_in_gallery ) {
                    $skipped_images[] = $image_id;
                }
            }

            // Delete all collected images safely, skipping shared images
            foreach ( $all_image_ids as $image_id ) {
                if ( in_array( $image_id, $skipped_images, true ) ) {
                    uprise_wc_log( sprintf( 'Skipping delete for shared image ID %d (product %d)', $image_id, $post_id ), 'warning' );
                    continue;
                }
                uprise_wc_log( sprintf( 'Attempting to delete image ID %d for product %d', $image_id, $post_id ), 'info' );
                if ( wp_attachment_is_image( $image_id ) ) {
                    $result = wp_delete_attachment( $image_id, true );

                    if ( is_wp_error( $result ) ) {
                        uprise_wc_log(
                                sprintf(
                                        'Failed to delete image (ID: %d) for product %d: %s',
                                        $image_id,
                                        $post_id,
                                        $result->get_error_message()
                                )
                        );
                    } else {
                        uprise_wc_log(
                                sprintf(
                                        'Deleted image (ID: %d) for product %d',
                                        $image_id,
                                        $post_id
                                ),
                                'info'
                        );
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

    add_action( 'admin_notices', function () {
        global $pagenow;

        if ( $pagenow !== 'edit.php' && $pagenow !== 'post.php' ) {
            return;
        }

        foreach ( $_GET as $key => $value ) {
            if ( strpos( $key, 'post' ) !== false ) {
                $post_id = intval( $value );
                $notice  = get_transient( 'uprise_delete_images_notice_' . $post_id );

                if ( $notice ) {
                    echo '<div class="notice notice-warning"><p>' . esc_html( $notice ) . '</p></div>';
                    delete_transient( 'uprise_delete_images_notice_' . $post_id );
                }
            }
        }
    } );

// Admin bar toggle
    add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $enabled = get_option( 'uprise_wc_delete_images_enabled', 'yes' );

        $title = $enabled === 'yes'
                ? '🟢 Delete Images: ON'
                : '🔴 Delete Images: OFF';

        $url = wp_nonce_url(
                admin_url( '?uprise_toggle_delete_images=1' ),
                'uprise_toggle_delete_images'
        );

        $wp_admin_bar->add_node( [
                'id'    => 'uprise_delete_images_toggle',
                'title' => $title,
                'href'  => $url,
        ] );

    }, 100 );

// Handle toggle action
    add_action( 'init', function () {

        if ( ! isset( $_GET['uprise_toggle_delete_images'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'uprise_toggle_delete_images' ) ) {
            return;
        }

        $current = get_option( 'uprise_wc_delete_images_enabled', 'yes' );
        $new     = $current === 'yes' ? 'no' : 'yes';

        update_option( 'uprise_wc_delete_images_enabled', $new );

        wp_safe_redirect( remove_query_arg( [ 'uprise_toggle_delete_images', '_wpnonce' ] ) );
        exit;

    } );
}