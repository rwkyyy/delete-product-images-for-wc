This small and lightweight plugin deletes all product images automatically when the product is deleted (from trash).
# Delete product images for WooCommerce

The plugin uses the "before_delete_post" action and runs a delete (wp_delete_post) for the product ID that is being deleted, this is done automatically and works with any number of products.

Contributions are welcomed on `https://github.com/rwkyyy/delete-product-images-for-wc`

Plugin is based on:
`https://stackoverflow.com/a/45998408/5317637`

**Installation:**

1. Download  the plugin

2. Upload it to your site (if you've installed it through Wordpress Dashboard skip this step)

3. Activate

4. Enjoy!

**Notes:** 

This plugin relies on WordPress "before_delete_post" action, if that action is disabled the plugin won't work.