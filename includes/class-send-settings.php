<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings class
 */
if (!class_exists('SendSettings')) {

    class SendSettings
    {

        public static function fields()
        {

            return apply_filters('wc_send_settings',

                array(
                    'enabled'     => array(
                        'title'   => __('Enable/Disable', 'send-gateway-for-woocommerce'),
                        'type'    => 'checkbox',
                        'label'   => __('Enable SocialSend payments', 'send-gateway-for-woocommerce'),
                        'default' => 'yes',
                    ),
                    'title'       => array(
                        'title'       => __('Title', 'send-gateway-for-woocommerce'),
                        'type'        => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'send-gateway-for-woocommerce'),
                        'default'     => __('Pay with SocialSend', 'send-gateway-for-woocommerce'),
                        'desc_tip'    => true,
                    ),
                    'description' => array(
                        'title'   => __('Customer Message', 'send-gateway-for-woocommerce'),
                        'type'    => 'textarea',
                        'default' => __('Ultra-fast and secure checkout with SocialSend'),
                    ),
                    'address'     => array(
                        'title'       => __('Destination wallet address', 'send-gateway-for-woocommerce'),
                        'type'        => 'text',
                        'default'     => '',
                        'description' => __('This addresses will be used for receiving funds.', 'send-gateway-for-woocommerce'),
                    ),
                    'show_prices' => array(
                        'title'   => __('Convert prices', 'send-gateway-for-woocommerce'),
                        'type'    => 'checkbox',
                        'label'   => __('Add prices in SocialSend (SEND)', 'send-gateway-for-woocommerce'),
                        'default' => 'no',

                    ),
                    'secret'      => array(
                        'type'    => 'hidden',
                        'default' => sha1(get_bloginfo() . Date('U')),

                    )
                )
            );
        }
    }

}
