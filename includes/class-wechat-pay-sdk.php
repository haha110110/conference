<?php
/**
 * WeChat Pay SDK Wrapper
 * 使用 riverslei/payment SDK
 * 部署时需运行: composer require riverslei/payment
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Conf_WeChat_SDK {

	private $config;
	private $gateway;

	public function __construct() {
		$this->init_config();
	}

	private function init_config() {
		$this->config = array(
			'app_id'      => get_option( 'conf_wechat_appid' ),
			'mch_id'      => get_option( 'conf_wechat_mchid' ),
			'api_key'     => get_option( 'conf_wechat_key' ),
			'cert_path'   => CONF_MANAGER_PATH . get_option( 'conf_wechat_cert_path' ),
			'key_path'    => CONF_MANAGER_PATH . get_option( 'conf_wechat_key_path' ),
			// notify_url is resolved dynamically in payment methods
		);
	}

	public function is_configured() {
		return ! empty( $this->config['app_id'] ) 
			&& ! empty( $this->config['mch_id'] ) 
			&& ! empty( $this->config['api_key'] );
	}

	public function get_config() {
		return $this->config;
	}

	public function create_native_order( $order_id, $amount, $subject, $body = '' ) {
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'not_configured', 'WeChat Pay is not configured' );
		}

		try {
			$client = new \Payment\Client( \Payment\Client::WECHAT, $this->config );
			
			$pay_data = new \Payment\Common\PayData();
			$pay_data->setBody( $body ?: $subject );
			$pay_data->setSubject( $subject );
			$pay_data->setOrderNo( (string) $order_id );
			$pay_data->setAmount( $amount / 100 ); // 元转分
			$pay_data->setCallbackUrl( rest_url( 'conf-manager/v1/wechat-callback' ) );
			
			$response = $client->pay( \Payment\Client::WX_CHANNEL_NATIVE, $pay_data );
			
			return $response;
		} catch ( \Payment\Exception\CallbackException $e ) {
			return new WP_Error( 'payment_error', $e->getMessage() );
		} catch ( Exception $e ) {
			return new WP_Error( 'payment_error', $e->getMessage() );
		}
	}

	public function create_h5_order( $order_id, $amount, $subject, $body = '', $trade_type = 'MWEB' ) {
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'not_configured', 'WeChat Pay is not configured' );
		}

		try {
			$client = new \Payment\Client( \Payment\Client::WECHAT, $this->config );
			
			$pay_data = new \Payment\Common\PayData();
			$pay_data->setBody( $body ?: $subject );
			$pay_data->setSubject( $subject );
			$pay_data->setOrderNo( (string) $order_id );
			$pay_data->setAmount( $amount / 100 );
			$pay_data->setCallbackUrl( rest_url( 'conf-manager/v1/wechat-callback' ) );
			$pay_data->setParameter( 'trade_type', $trade_type );
			$pay_data->setParameter( 'scene_info', json_encode( array(
				'h5_info' => array(
					'type'     => 'Wap',
					'wap_url'  => home_url(),
					'wap_name' => get_bloginfo( 'name' ),
				),
			) ) );
			
			$response = $client->pay( \Payment\Client::WX_CHANNEL_H5, $pay_data );
			
			return $response;
		} catch ( Exception $e ) {
			return new WP_Error( 'payment_error', $e->getMessage() );
		}
	}

	public function query_order( $order_id ) {
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'not_configured', 'WeChat Pay is not configured' );
		}

		try {
			$client = new \Payment\Client( \Payment\Client::WECHAT, $this->config );
			
			$query_data = new \Payment\Common\PayData();
			$query_data->setOrderNo( (string) $order_id );
			
			$response = $client->pay( \Payment\Client::WECHAT_CHANNEL_QUERY, $query_data );
			
			return $response;
		} catch ( Exception $e ) {
			return new WP_Error( 'query_error', $e->getMessage() );
		}
	}

	public function refund( $order_id, $refund_id, $total_amount, $refund_amount ) {
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'not_configured', 'WeChat Pay is not configured' );
		}

		try {
			$client = new \Payment\Client( \Payment\Client::WECHAT, $this->config );
			
			$refund_data = new \Payment\Common\PayData();
			$refund_data->setOrderNo( (string) $order_id );
			$refund_data->setParameter( 'refund_id', $refund_id );
			$refund_data->setParameter( 'total_fee', $total_amount );
			$refund_data->setParameter( 'refund_fee', $refund_amount );
			$refund_data->setParameter( 'op_user_id', $this->config['mch_id'] );
			
			$response = $client->pay( \Payment\Client::WECHAT_CHANNEL_REFUND, $refund_data );
			
			return $response;
		} catch ( Exception $e ) {
			return new WP_Error( 'refund_error', $e->getMessage() );
		}
	}

	public function verify_callback( $data ) {
		if ( ! $this->is_configured() ) {
			return false;
		}

		try {
			$client = new \Payment\Client( \Payment\Client::WECHAT, $this->config );
			return $client->verify( $data );
		} catch ( Exception $e ) {
			return false;
		}
	}
}
