<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax class
 */
class SendAjax
{

    private static $instance;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        add_action('wp_ajax_check_send_payment', array(__CLASS__, 'checkSendPayment'));
    }

    public function checkSendPayment()
    {
        global $woocommerce;
        $woocommerce->cart->get_cart();

        $options = get_option('woocommerce_send_settings');

        $payment_total   = WC()->session->get('send_payment_total');
        $price_time = WC()->session->get('send_price_time');

        $ra     = new SendApi($options['address']);
        $result = $ra->findByAmount($payment_total);

        $result['match'] = ($result['amount'] == $payment_total && $result['time'] <= ($price_time + 600 )) ? true : false;

        echo json_encode($result);
        exit();
    }

} 

SendAjax::getInstance();
