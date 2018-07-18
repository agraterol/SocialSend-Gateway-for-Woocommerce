<?php

if (!defined('ABSPATH')) {
    exit;
}


/**
 * API class
 */
class SendApi
{

    private $address;
    private $url;
    private $port;

    public function __construct($address)
    {
        $this->address = $address;
        $this->url_api = 'http://send-explorer.online:3001/';
        $this->url_api_ext = 'http://140.82.15.8:8080/SocialSendApi/api/';
    }

    private function get( $endpoint, $is_ext = false )
    {
        $url = ($is_ext) ? $this->url_api_ext . $endpoint : $this->url_api . $endpoint;
        $response = wp_remote_get( $url );
        $result = wp_remote_retrieve_body( $response );
        return $result;
    }

    function findByAmount($amount)
    {
        $result = $this->get('ext/getaddress/' . $this->address);
        if ($result) {
            $payments = json_decode($result, true);
            foreach($payments['last_txs'] as $payment) {
                $res = $this->get('txinfo/getinfo/' . $payment['addresses'] . '/' . $this->address, true);
                $payment_info = json_decode($res, true);
                if ($payment_info['data']['amount'] == $amount) {
                    return array(
                    'result'  => true,
                    'tx_hash' => $payment['addresses'],
                    'amount' => $payment_info['data']['amount'],
                    'time' => $payment_info['data']['time']
                    );
                }
            }

            return array(
                'result' => false,
            );
        } else {
            return array(
                'result' => false,
            );
        }
    }

    public function getTransaction($tx_hash)
    {
        $result = $this->get('txinfo/getinfo/' . $tx_hash . '/' . $this->address, true);
        return ($result) ? json_decode($result, true) : false;
    }

}
