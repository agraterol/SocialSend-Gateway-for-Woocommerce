<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Gateway class
 */
class WcSendGateway extends WC_Payment_Gateway
{
    public $id;
    public $title;
    public $form_fields;
    public $addresses;
    private $currencyIsSend;

    public function __construct()
    {

        $this->id          			= 'send';
        $this->title       			= $this->get_option('title');
        $this->description 			= $this->get_option('description');
        $this->address   			= $this->get_option('address');
        $this->secret   			= $this->get_option('secret');
        $this->order_button_text 	= __('Awaiting transfer..','send-gateway-for-woocommerce');
        $this->has_fields 			= true;

        // if woocommerce_currency is set to Send like currency
        $this->currencyIsSend = (get_woocommerce_currency() == "Send") ? true : false;

        $this->initFormFields();

        $this->initSettings();

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
            $this,
            'process_admin_options',
        ));
        add_action('wp_enqueue_scripts', array($this, 'paymentScripts'));

        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyouPage'));        

    }

    public function initFormFields()
    {
        parent::init_form_fields();
        $this->form_fields = SendSettings::fields();
    }

    public function initSettings()
    {
    	// sha1( get_bloginfo() )
        parent::init_settings();
    }
   
    public function payment_fields()
    {
    	global $woocommerce;
    	$woocommerce->cart->get_cart();
        $total_converted = $this->get_order_total();
        $rate = null;
        if(!$this->currencyIsSend) {
            $total_converted = SendExchange::convertToSend(get_woocommerce_currency(), $total_converted);
            $rate = $total_converted / $this->get_order_total();
        }

        $price_tag = hexdec( substr(sha1(current_time(timestamp,1) . key ($woocommerce->cart->cart_contents )  ), 0, 4) );

        // adding a small amount for the identification of the payment
        $total_send = $total_converted + ( $price_tag / 100000000 );

        // set session data 
        WC()->session->set('send_payment_total', $total_send);
        WC()->session->set('send_price_time',  Date('U'));
        WC()->session->set('send_data_hash', sha1( $this->secret . $total_send ));

        //QR uri
        $url = "send:". $this->address ."?amount=". $total_send;

        ?>
        <div id="send-form">
            <div class="send-container">
            <div>
                <img src="https://s2.coinmarketcap.com/static/img/coins/64x64/2255.png" class="send-logo" alt="Social Send">
                <?if ($this->description) { ?>
                <div class="separator"></div>
                <div id="send-description">
                    <?=apply_filters( 'wc_send_description', wpautop(  $this->description ) )?>
                </div>
                <?}?>
                <div class="separator"></div>
                <div class="send-container">
                <?if($rate!=null){?>
                <label class="send-label">
                    (1<?=get_woocommerce_currency()?> = <?=round($rate,6)?> SEND)
                </label>
                <?}?>
                <p class="send-amount">
                    <span class="copy" data-success-label="<?=__('copied','send-gateway-for-woocommerce')?>"
                          data-clipboard-text="<?=esc_attr($total_send)?>"><?=esc_attr($total_send)?>
                    </span> <strong>SEND</strong>
                </p>
                </div>
            </div>
            <div class="separator"></div>
            <div class="send-container">
                <label class="send-label"><?=__('Payment address', 'send-gateway-for-woocommerce')?></label>
                <p class="send-address">
                    <span class="copy" data-success-label="<?=__('copied','send-gateway-for-woocommerce')?>"
                          data-clipboard-text="<?=esc_attr($this->address)?>"><?=esc_attr($this->address)?>
                    </span>
                </p>
            </div>
            <div class="separator"></div>
            </div>
            <div id="send-qr-code" data-contents="<?=$url?>"></div>
            <div class="separator"></div>
            <div class="send-container">
                <p>
                    <?=sprintf(__('Send a payment of exactly %s to the address above (click the links to copy or scan the QR code). We will check in the background and notify you when the payment has been validated.', 'send-gateway-for-woocommerce'), '<strong>'. esc_attr($total_send).' SEND</strong>' )?>
                </p>
                <p>
                    <?=sprintf(__('Please send your payment within %s.', 'send-gateway-for-woocommerce'), '<strong><span class="send-countdown" data-minutes="10">10:00</span></strong>' )?>
                </p>
                <p>
                    <?=__('When the timer reaches <strong>0</strong> this form will refresh and update the total amount using the latest conversion rate.', 'send-gateway-for-woocommerce')?>
                </p>
            </div>
            <input type="hidden" name="tx_hash" id="tx_hash" value="0"/>
        </div>
        <?
    }

    public function process_payment( $order_id ) 
    {
    	global $woocommerce;
        $this->order = new WC_Order( $order_id );
        
	    $payment_total   = WC()->session->get('send_payment_total');
        $price_time = WC()->session->get('send_price_time');

	    $ra = new SendApi($this->address);
	    $transaction = $ra->getTransaction( $_POST['tx_hash']);
	    
        if($transaction['data']['time'] > ($price_time + 600 )) {
	    	exit('price');
	    	return array(
		        'result'    => 'failure',
		        'messages' 	=> 'SEND price rate mismatch'
		    );
	    }
		
		
	    if($transaction['data']['amount'] != $payment_total) {
	    	return array(
		        'result'    => 'failure',
		        'messages' 	=> 'amount mismatch'
		    );
	    }
        
        $this->order->add_order_note( 'SEND txid: '. $_POST['tx_hash'] . ' / Mount: '. $payment_total);
        $this->order->payment_complete();

        $woocommerce->cart->empty_cart();
	   
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url($this->order)
        );
	}

    public function paymentScripts()
    {
        wp_enqueue_script('qrcode', plugins_url('assets/js/jquery.qrcode.min.js', WcSend::$plugin_basename), array('jquery'), WcSend::$version, true);
        wp_enqueue_script('initialize', plugins_url('assets/js/jquery.initialize.js', WcSend::$plugin_basename), array('jquery'), WcSend::$version, true);
        
        wp_enqueue_script('clipboard', plugins_url('assets/js/clipboard.js', WcSend::$plugin_basename), array('jquery'), WcSend::$version, true);
        wp_enqueue_script('woocommerce_send_js', plugins_url('assets/js/send.js', WcSend::$plugin_basename), array(
            'jquery',
        ), WcSend::$version, true);
        wp_enqueue_style('woocommerce_send_css', plugins_url('assets/css/send.css', WcSend::$plugin_basename), array(), WcSend::$version);

        // //Add js variables
        $send_vars = array(
            'wc_ajax_url' => WC()->ajax_url(),
            'nonce'      => wp_create_nonce("send-gateway-for-woocommerce"),
        );

        wp_localize_script('woocommerce_send_js', 'send_vars', apply_filters('send_vars', $send_vars));

    }

}
