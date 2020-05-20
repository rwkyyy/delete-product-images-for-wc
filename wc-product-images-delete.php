<?php

/*
Plugin Name: WC Delete Product Images
Plugin URI: https://uprise.ro
Description: Removes product assigned images (featured and gallery only) on product delete.
Version: 1.0
Author: Eduard V. Doloc
Author Email: eduard@uprise.ro
*/

//@todo: check if WC is active

//hooking into WP event
add_action( 'before_delete_post', 'wc_delete_product_images', 10, 1 );

function wc_delete_product_images( $post_id ) {
	//get product id
	$product = wc_get_product( $post_id );

	//failsafe
	if ( ! $product ) {
		return;
	}

	//get images
	$featured_image_id  = $product->get_image_id();
	$image_galleries_id = $product->get_gallery_image_ids();

	//delete featured (check first if empty)s
	if ( ! empty( $featured_image_id ) ) {
		wp_delete_post( $featured_image_id );
	}

	//delete gallery/attachment (check first if empty)
	if ( ! empty( $image_galleries_id ) ) {
		foreach ( $image_galleries_id as $single_image_id ) {
			wp_delete_post( $single_image_id );
		}
	}
}
//@todo: create config for multiple CPTs
