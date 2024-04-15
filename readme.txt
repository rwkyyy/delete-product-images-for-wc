=== Delete product images for WooCommerce ===
Contributors: rwky
Donate link: https://www.paypal.me/eduardvd
Tags: product images delete, woocommerce product images delete, woocommerce product images remove, product images remove, remove product images automatically
Requires at least: 4.7
Requires PHP: 7.0
Tested up to: 6.5.2
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Removes product assigned images (featured and gallery only) on product delete.

== Description ==
This small and lightweight plugin deletes all product images automatically when the product is deleted (from trash).

The plugin uses the "before_delete_post" action and runs a delete (wp_delete_post) for the product ID that is being deleted, this is done automatically and works with any number of products.

Contributions are welcomed on `https://github.com/rwkyyy/delete-product-images-for-wc`

Plugin is based on:
`https://stackoverflow.com/a/45998408/5317637`

== Installation ==
1. Download the plugin
2. Upload it to your site (if you've installed it through Wordpress Dashboard skip this step)
3. Activate
4. Enjoy!

== Frequently Asked Questions ==
= Will this work with any product? =
Yes, it should work with any type of product (CPT).
= Can you make it work with more CPTs? =
To be honest, I did not found the motivation in putting the work for making the plugin compatible with any CPT, but if I see interest in this plugin I'll do that in time, or if you want to submit the update/any other updates yourself, just make a pull on Github and I'll update it here.


== Changelog ==
= 1.0 =
* Initial release

== Upgrade Notice ==
None
