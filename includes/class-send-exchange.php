<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Exchange class
 */
class SendExchange
{
    private static function getBodyAsJson($url,$retries=1) {
        $response = wp_remote_get( $url );
        $result = json_decode(wp_remote_retrieve_body($response));
        if(!$result && $retries>0) {
            return SendExchange::getBodyAsJson($url,--$retries);
        }
        return $result?$result:null;
    }

    private static function getExchangePrice($currency) {
        $pair = "SEND/".$currency;
        $result = wp_cache_get($pair,'exchangePrices');
        if (false === $result ) {
            $result = SendExchange::getBodyAsJson("https://api.coinmarketcap.com/v2/ticker/2255/?convert=".$currency);
            $result = isset($result->{'data'}->{'quotes'}->{$currency}->{'price'}) ? $result->{'data'}->{'quotes'}->{$currency}->{'price'} : false;
            wp_cache_set( $pair, $result, 'exchangePrices', 600);
        }
        return $result;
    }

    private static function exchange($currency,$price) {
        $exchange_price = SendExchange::getExchangePrice($currency);
        if(!$exchange_price || $exchange_price==0 || $price==null) {
            return null;
        }
        return round($price / $exchange_price, 2, PHP_ROUND_HALF_UP);
    }

    public static function convertToSend($currency, $price) {
        $price_in_send = SendExchange::exchange($currency,$price);
        return $price_in_send;
    }
}
