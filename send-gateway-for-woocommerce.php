<?php

/**
 * SocialSend Gateway for Woocommerce
 *
 * Plugin Name: SocialSend Gateway for Woocommerce
 * Plugin URI:
 * Description: Show prices in SocialSend (SEND) and accept SocialSend payments in your woocommerce webshop
 * Version: 0.0.1
 * Author: ant0n0vich - Socialsend Team
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: send-gateway-for-woocommerce
 * Domain Path: /languages/
  *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WcSend')) {

    class WcSend
    {

        private static $instance;
        public static $version = '0.0.1';
        public static $plugin_basename;
        public static $plugin_path;
        public static $plugin_url;

        protected function __construct()
        {
        	self::$plugin_basename = plugin_basename(__FILE__);
        	self::$plugin_path = trailingslashit(dirname(__FILE__));
        	self::$plugin_url = plugin_dir_url(self::$plugin_basename);
            add_action('plugins_loaded', array($this, 'init'));
        }
        
        public static function getInstance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function init()
        {
            $this->initGateway();
        }

        public function initGateway()
        {

            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }

            if (class_exists('WC_Send_Gateway')) {
	            return;
	        }

	        /*
	         * Include gateway classes
	         * */
	        include_once plugin_basename('includes/class-send-gateway.php');
	        include_once plugin_basename('includes/class-send-api.php');
	        include_once plugin_basename('includes/class-send-exchange.php');
	        include_once plugin_basename('includes/class-send-settings.php');
	        include_once plugin_basename('includes/class-send-ajax.php');

	        add_filter('woocommerce_payment_gateways', array($this, 'addToGateways'));
            add_filter('woocommerce_currencies', array($this, 'SendCurrencies'));
            add_filter('woocommerce_currency_symbol', array($this, 'SendCurrencySymbols'), 10, 2);

	        add_filter('woocommerce_get_price_html', array($this, 'SendFilterPriceHtml'), 10, 2);
	        add_filter('woocommerce_cart_item_price', array($this, 'SendFilterCartItemPrice'), 10, 3);
	        add_filter('woocommerce_cart_item_subtotal', array($this, 'SendFilterCartItemSubtotal'), 10, 3);
	        add_filter('woocommerce_cart_subtotal', array($this, 'SendFilterCartSubtotal'), 10, 3);
	        add_filter('woocommerce_cart_totals_order_total_html', array($this, 'SendFilterCartTotal'), 10, 1);

	    }

	    public static function addToGateways($gateways)
	    {
	        $gateways['send'] = 'WcSendGateway';
	        return $gateways;
	    }

        public function SendCurrencies( $currencies )
        {
            $currencies['SEND'] = __( 'SocialSend', 'send' );
            return $currencies;
        }

        public function SendCurrencySymbols( $currency_symbol, $currency ) {
            switch( $currency ) {
                case 'SEND': $currency_symbol = 'SEND'; break;
            }
            return $currency_symbol;
        }

	    public function SendFilterCartTotal($value)
	    {
	        return $this->convertToSendPrice($value, WC()->cart->total);
	    }

	    public function SendFilterCartItemSubtotal($cart_subtotal, $compound, $that)
	    {
	        return $this->convertToSendPrice($cart_subtotal, $that->subtotal);
	    }

	    public function SendFilterPriceHtml($price, $that)
	    {
	        return $this->convertToSendPrice($price, $that->price);
	    }

	    public function SendFilterCartItemPrice($price, $cart_item, $cart_item_key)
	    {
	        $item_price = ($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax']) / $cart_item['quantity'];
	        return $this->convertToSendPrice($price,$item_price);
	    }

	    public function SendFilterCartSubtotal($price, $cart_item, $cart_item_key)
	    {
	        $subtotal = $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'];
	        return $this->convertToSendPrice($price, $subtotal);
	    }

	    private function convertToSendPrice($price_string, $price)
	    {
            $options = get_option('woocommerce_send_settings');
            if(!in_array(get_woocommerce_currency(), array("SEND")) && $options['show_prices'] == 'yes') {
                $send_currency = 'SEND';

                $send_price = SendExchange::convertToSend(get_woocommerce_currency(), $price);
                
                if ($send_price) {
                    $price_string .= '&nbsp;(<span class="woocommerce-price-amount amount">' . $send_price . '&nbsp;</span><span class="woocommerce-price-currencySymbol">'.$send_currency.')</span>';
                }
            }
	        return $price_string;
	    }
    }

}

WcSend::getInstance();

function sendGateway_textdomain() {
    load_plugin_textdomain( 'send-gateway-for-woocommerce', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
        
add_action( 'plugins_loaded', 'sendGateway_textdomain' );