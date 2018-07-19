=== Send Gateway for Woocommerce ===

Author: ant0n0vich 
Donate link: SEND: SSPfJ3jJ31Bk8ziq2aJx5jiTdqyXKdUvrE 
Tags: billing, invoicing, woocommerce, payment 
Requires at least: 3.0.1 
Tested up to: 4.8 
Stable tag: 0.4.3 
License: GPLv2 or later 
License URI: http://www.gnu.org/licenses/gpl-2.0.html 

Show prices in SocialSend (SEND) and accept SocialSend payments in your woocommerce webshop

== Description ==

Display prices in SocialSend (SEND) and let your clients pay through the SocialSend client software. Built on top of Ripple Gateway developed by Casper Mekel and uses the Social Send Extended Api developed by Hernss. 

* Display prices in SocialSend (SEND) in store and on checkout
* Prices are calculated based on coinmarketcap rate
* Links can be copied by clicking and a QR code is supplied which can be used in the SocialSend wallet app Android
* Countdown refreshes form each 10 minutes, updating amounts using the most recent conversion rate
* Matches payments on unique amount and price rate time.
* Checkout page is automatically refreshed after a successful payment 

== Installation ==

Install the plugin by uploading the zipfile in your WP admin interface or via FTP:

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Find the configuration in Woocommerce->Payment page and fill out your settings and preferences

The order module of WooCommerce only works when you force users to register an account on WordPress.
With guest payments there is no information about the orders, clients will not get an email about their order and you will receive the payment without any information.

This can be solved by using some extra plugins, we are using:

    WooCommerce Checkout Manager
    YITH WooCommerce Multi-step Checkout Premium (paid plugin)

It is important to disable guest checkouts on your shop.
With these plugins you can set mandatory fields for visitors registering an account on WordPress.

== Screenshots ==

1. Admin Settings

![screenshot-1](assets/screenshot-1.png?raw=true "screenshot-1")

2. Front-end view

![screenshot-2](assets/screenshot-2.png?raw=true "screenshot-2")

![screenshot-3](assets/screenshot-3.png?raw=true "screenshot-3")

== Changelog ==

- 0.0.1
* Initial release




