# Installation Guide

## Requirements
- WordPress 5.8+
- WooCommerce 5.0+
- PHP 7.4+

## Standard Installation
1. Download the latest `order-shield.zip` from the [Releases](https://github.com/hasibulhasansakib/order-shield/releases) page.
2. Go to your WordPress Dashboard -> **Plugins** -> **Add New**.
3. Click **Upload Plugin** and select the downloaded zip file.
4. Click **Install Now** and then **Activate**.

## Developer Installation (Git)
1. Navigate to your `wp-content/plugins/` directory.
2. Run `git clone https://github.com/hasibulhasansakib/order-shield.git`
3. Go into the folder: `cd order-shield`
4. Run `composer install`
5. Activate the plugin via the WordPress admin panel or WP-CLI: `wp plugin activate order-shield`.