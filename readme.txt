=== Delete product images for WooCommerce ===
Contributors: rwky
Donate link: https://www.paypal.me/eduardvd
Tags: product images delete, woocommerce product images delete, woocommerce product images remove, product images remove, remove product images automatically
Requires at least: 4.7
Requires PHP: 7.0
Tested up to: 6.9.4
Stable tag: 3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Safely deletes WooCommerce product images (featured, gallery, variations) on permanent delete, with protection for shared images.

== Description ==
Automatically deletes all images associated with a WooCommerce product when the product is permanently deleted from the Trash.

The plugin includes multiple safety mechanisms to ensure that shared images are never removed if they are used by other active products.

== Features ==

- Deletes:
  - Featured images
  - Gallery images
  - Variation images

- Runs only on permanent delete:
  - Does NOT trigger when moving products to Trash

- Smart protection:
  - Skips images used by other active products
  - Ignores products already in Trash

- Partial deletion support:
  - Deletes only unused images
  - Keeps shared images intact

- Bulk-safe:
  - Works with bulk delete and "Empty Trash"

- Logging:
  - Full WooCommerce logger integration
  - Tracks every step for debugging

- Admin control:
  - Toggle image deletion ON/OFF from the WordPress admin bar

== Notes ==

- The plugin is designed to be safe by default. If an image is detected as being used elsewhere, it will not be deleted.
- Best suited for stores with frequent product imports/cleanup where media clutter can become an issue.

Contributions are welcomed on `https://github.com/rwkyyy/delete-product-images-for-wc`


== Installation ==
1. Download the plugin
2. Upload it to your site (if you've installed it through Wordpress Dashboard skip this step)
3. Activate
4. Enjoy!

== Frequently Asked Questions ==
= Will this work with any product? =
Yes, it supports all WooCommerce product types, including simple, variable, and variations.
= Where can I see it working? =
Please check the default WooCommerce logs (Woo > Status > Logs)
= Can you make it work with more CPTs? =
To be honest, I did not found the motivation in putting the work for making the plugin compatible with any CPT, but if I see interest in this plugin I'll do that in time, or if you want to submit the update/any other updates yourself, just make a pull on Github and I'll update it here.


== Changelog ==
= 3.0 =
* Added support for variation images
* Implemented shared image protection (prevents deleting images used by other products)
* Fixed deletion logic to work only on permanent delete (not on move to trash)
* Rework support for bulk delete on empty trash
* Added admin bar toggle to enable/disable image deletion
* Improved logging with detailed step-by-step tracking (WC Logger)
* Improved database queries to ignore trashed products
* Fixed multiple edge cases and improved overall reliability
* Performance improvements (100 simple products in < 10 seconds)

= 2.0 =
* rewrote plugin logic for better handling of both database and filesystem
* implemented multiple safeguards and error-checking
* fixed a bug where in some cases the files would not get deleted
* added logging (wc logs) for easy tracking of the activity

= 1.0 =
* Initial release

== Upgrade Notice ==
None