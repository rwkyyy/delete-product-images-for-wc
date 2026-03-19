[![WP compatibility](https://plugintests.com/plugins/wporg/wc-delete-product-images/wp-badge.svg)](https://plugintests.com/plugins/wporg/wc-delete-product-images/latest) [![PHP compatibility](https://plugintests.com/plugins/wporg/wc-delete-product-images/php-badge.svg)](https://plugintests.com/plugins/wporg/wc-delete-product-images/latest)

This small and lightweight plugin deletes all product images automatically when the product is deleted (from trash).

# Delete Product Images for WooCommerce

Safely deletes WooCommerce product images (featured, gallery, variations) on permanent delete, with protection for shared images.

---

## 🧩 Description

Automatically deletes all images associated with a WooCommerce product when the product is permanently deleted from the Trash.

The plugin includes multiple safety mechanisms to ensure that shared images are never removed if they are used by other active products.

---

## 🚀 Features

### 🖼 Image Handling
- Deletes:
  - Featured images
  - Gallery images
  - Variation images

### 🔒 Safe Execution
- Runs **only on permanent delete**
- Does **NOT** trigger when moving products to Trash

### 🧠 Smart Protection
- Skips images used by other active products
- Ignores products already in Trash

### ⚙️ Partial Deletion
- Deletes only unused images
- Keeps shared images intact

### 📦 Bulk Safe
- Works with bulk delete
- Works with **Empty Trash**

### 📝 Logging
- Full WooCommerce logger integration
- Tracks every step for debugging

### 🧭 Admin Control
- Toggle image deletion ON/OFF from the WordPress admin bar

---

## ⚠️ Notes

- The plugin is designed to be safe by default.
- If an image is detected as being used elsewhere, it will **not** be deleted.
- Ideal for stores with frequent imports or cleanup needs.

---

## 📦 Installation

1. Download the plugin
2. Upload it to your site (skip if installing via WP Dashboard)
3. Activate the plugin
4. Done

---

## ❓ FAQ

### Will this work with any product?
Yes, it supports all WooCommerce product types:
- Simple
- Variable
- Variations

### Where can I see it working?
Check WooCommerce logs: