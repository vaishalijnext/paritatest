<?php
/**
 * Currency per Product for WooCommerce - Functions - Exchange Rates
 *
 * @version 1.2.0
 * @since   1.0.0
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'alg_wc_cpp_get_currency_exchange_rate_servers' ) ) {
	/**
	 * alg_wc_cpp_get_currency_exchange_rate_servers.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_get_currency_exchange_rate_servers() {
		return array(
			'yahoo' => __( 'Yahoo', 'currency-per-product-for-woocommerce' ),
			'ecb'   => __( 'European Central Bank (ECB)', 'currency-per-product-for-woocommerce' ),
			'tcmb'  => __( 'TCMB', 'currency-per-product-for-woocommerce' ),
		);
	}
}

if ( ! function_exists( 'alg_wc_cpp_get_exchange_rate' ) ) {
	/*
	 * alg_wc_cpp_get_exchange_rate.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @todo    (maybe) set `ecb` as default
	 */
	function alg_wc_cpp_get_exchange_rate( $currency_from, $currency_to ) {
		if ( 'yes' === ( $calculate_by_invert = get_option( 'alg_wc_cpp_currency_exchange_rates_calculate_by_invert', 'no' ) ) ) {
			$_currency_to  = $currency_to;
			$currency_to   = $currency_from;
			$currency_from = $_currency_to;
		}
		$exchange_rates_server = get_option( 'alg_wc_cpp_currency_exchange_rates_server', 'yahoo' );
		switch ( $exchange_rates_server ) {
			case 'tcmb':
				$return = alg_wc_cpp_tcmb_get_exchange_rate( $currency_from, $currency_to );
				break;
			case 'ecb':
				$return = alg_wc_cpp_ecb_get_exchange_rate( $currency_from, $currency_to );
				break;
			default: // 'yahoo'
				$return = alg_wc_cpp_yahoo_get_exchange_rate( $currency_from, $currency_to );
				break;
		}
		return ( 'yes' === $calculate_by_invert ) ? round( ( 1 / $return ), 6 ) : $return;
	}
}

if ( ! function_exists( 'alg_wc_cpp_ecb_get_exchange_rate' ) ) {
	/*
	 * alg_wc_cpp_ecb_get_exchange_rate.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_ecb_get_exchange_rate( $currency_from, $currency_to ) {
		$final_rate = false;
		if ( function_exists( 'simplexml_load_file' ) ) {
			$xml = simplexml_load_file( 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml' );
			if ( isset( $xml->Cube->Cube->Cube ) ) {
				if ( 'EUR' === $currency_from ) {
					$EUR_currency_from_rate = 1;
				}
				if ( 'EUR' === $currency_to ) {
					$EUR_currency_to_rate = 1;
				}
				foreach ( $xml->Cube->Cube->Cube as $currency_rate ) {
					$currency_rate = $currency_rate->attributes();
					if ( ! isset( $EUR_currency_from_rate ) && $currency_from == $currency_rate->currency ) {
						$EUR_currency_from_rate = (float) $currency_rate->rate;
					}
					if ( ! isset( $EUR_currency_to_rate ) && $currency_to == $currency_rate->currency ) {
						$EUR_currency_to_rate = (float) $currency_rate->rate;
					}
				}
				if ( isset( $EUR_currency_from_rate ) && isset( $EUR_currency_to_rate ) && 0 != $EUR_currency_from_rate ) {
					$final_rate = round( $EUR_currency_to_rate / $EUR_currency_from_rate, 6 );
				} else {
					$final_rate = false;
				}
			}
		}
		return $final_rate;
	}
}

if ( ! function_exists( 'alg_wc_cpp_tcmb_get_exchange_rate_TRY' ) ) {
	/*
	 * alg_wc_cpp_tcmb_get_exchange_rate_TRY.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_tcmb_get_exchange_rate_TRY( $currency_from ) {
		if ( 'TRY' === $currency_from ) {
			return 1;
		}
		$xml = simplexml_load_file( 'http://www.tcmb.gov.tr/kurlar/today.xml' );
		if ( isset( $xml->Currency ) ) {
			foreach ( $xml->Currency as $the_rate ) {
				$attributes = $the_rate->attributes();
				if ( isset( $attributes['CurrencyCode'] ) ) {
					$currency_code = (string) $attributes['CurrencyCode'];
					if ( $currency_code === $currency_from  ) {
						// Possible values: ForexSelling, ForexBuying, BanknoteSelling, BanknoteBuying. Not used: CrossRateUSD, CrossRateOther.
						if ( '' != ( $property_to_check = apply_filters( 'alg_wc_cpp_currency_exchange_rates_tcmb_property_to_check', '' ) ) ) {
							if ( isset( $the_rate->{$property_to_check} ) ) {
								$rate = (float) $the_rate->{$property_to_check};
							} else {
								continue;
							}
						} else {
							if ( isset( $the_rate->ForexSelling ) ) {
								$rate = (float) $the_rate->ForexSelling;
							} elseif ( isset( $the_rate->ForexBuying ) ) {
								$rate = (float) $the_rate->ForexBuying;
							} elseif ( isset( $the_rate->BanknoteSelling ) ) {
								$rate = (float) $the_rate->BanknoteSelling;
							} elseif ( isset( $the_rate->BanknoteBuying ) ) {
								$rate = (float) $the_rate->BanknoteBuying;
							} else {
								continue;
							}
						}
						$unit = ( isset( $the_rate->Unit ) ) ? (float) $the_rate->Unit : 1;
						return ( $rate / $unit );
					}
				}
			}
		}
		return false;
	}
}

if ( ! function_exists( 'alg_wc_cpp_tcmb_get_exchange_rate' ) ) {
	/*
	 * alg_wc_cpp_tcmb_get_exchange_rate.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cpp_tcmb_get_exchange_rate( $currency_from, $currency_to ) {
		$currency_from_TRY = alg_wc_cpp_tcmb_get_exchange_rate_TRY( strtoupper( $currency_from ) );
		if ( false == $currency_from_TRY  ) {
			return false;
		}
		$currency_to_TRY = alg_wc_cpp_tcmb_get_exchange_rate_TRY( strtoupper( $currency_to )  );
		if ( false == $currency_to_TRY ) {
			return false;
		}
		if ( 1 == $currency_to_TRY ) {
			return round( $currency_from_TRY, 6 );
		}
		return round( ( $currency_from_TRY / $currency_to_TRY ), 6 );
	}
}

if ( ! function_exists( 'alg_wc_cpp_yahoo_get_exchange_rate' ) ) {
	/*
	 * alg_wc_cpp_yahoo_get_exchange_rate.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 * @return  float rate on success, else 0
	 * @todo    (maybe) use `download_url()` function
	 */
	function alg_wc_cpp_yahoo_get_exchange_rate( $currency_from, $currency_to ) {
		$url = "https://finance.yahoo.com/webservice/v1/symbols/allcurrencies/quote?format=json";
		$response = '';
		if ( function_exists( 'curl_version' ) ) {
			$curl = curl_init( $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
			$response = curl_exec( $curl );
			curl_close( $curl );
		} elseif ( ini_get( 'allow_url_fopen' ) ) {
			$response = file_get_contents( $url );
		}
		$response = json_decode( $response );
		if ( ! isset( $response->list->resources ) ) {
			return 0;
		}
		$currencies = array(
			'currency_from' => array(
				'name'     => $currency_from . '=X',
				'usd_rate' => false,
			),
			'currency_to' => array(
				'name'     => $currency_to . '=X',
				'usd_rate' => false,
			),
		);
		foreach ( $currencies as &$currency ) {
			foreach ( $response->list->resources as $resource ) {
				if ( isset( $resource->resource->fields->symbol ) && $currency['name'] === $resource->resource->fields->symbol ) {
					if ( ! isset( $resource->resource->fields->price ) ) {
						return 0;
					}
					$currency['usd_rate'] = $resource->resource->fields->price;
					break;
				}
			}
		}
		return ( false == $currencies['currency_to']['usd_rate'] || false == $currencies['currency_from']['usd_rate'] ? 0 :
			round( ( $currencies['currency_to']['usd_rate'] / $currencies['currency_from']['usd_rate'] ), 6 ) );
	}

}
